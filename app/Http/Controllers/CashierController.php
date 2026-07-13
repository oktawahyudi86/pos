<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Addon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\VariantOption;
use App\Services\ReceiptPngService;
use App\Support\AppUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with(['category', 'variantGroups.options', 'addons'])
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $request->string('category'))))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($searchQuery) use ($request) {
                $search = '%'.$request->string('search')->toString().'%';

                $searchQuery
                    ->where('name', 'like', $search)
                    ->orWhere('sku', 'like', $search);
            }))
            ->orderBy('name')
            ->get();

        $cart = collect(session('cashier_cart', []));
        $cartSubtotal = $cart->sum('line_total');
        $cartDiscount = $this->resolveDiscount($cartSubtotal);
        $cartTotal = max(0, $cartSubtotal - $cartDiscount['amount']);
        $paymentMethods = $this->activePaymentMethods();
        $paymentMethod = session('cashier_payment_method', $paymentMethods[0] ?? 'cash');

        if (! in_array($paymentMethod, $paymentMethods, true)) {
            $paymentMethod = $paymentMethods[0] ?? 'cash';
            session(['cashier_payment_method' => $paymentMethod]);
        }

        return view('cashier.index', compact('categories', 'products', 'cart', 'cartSubtotal', 'cartDiscount', 'cartTotal', 'paymentMethod', 'paymentMethods'));
    }

    public function storeCart(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:160'],
            'variant_options' => ['nullable', 'array'],
            'variant_options.*' => ['nullable', 'integer', Rule::exists('variant_options', 'id')],
            'addons' => ['nullable', 'array'],
            'addons.*' => [
                'integer',
                Rule::exists('addons', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
        ]);

        $cartItem = $this->makeCartItem($validated);

        $cart = session('cashier_cart', []);
        $existingQuantity = $cart[$cartItem['key']]['quantity'] ?? 0;
        $newQuantity = min($existingQuantity + $cartItem['quantity'], $cartItem['stock']);

        $cartItem['quantity'] = $newQuantity;
        $cartItem['line_total'] = $cartItem['unit_price'] * $newQuantity;
        $cart[$cartItem['key']] = $cartItem;

        session(['cashier_cart' => $cart]);

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Produk ditambahkan ke keranjang.');
        }

        return back()->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function editCart(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:160'],
            'variant_options' => ['nullable', 'array'],
            'variant_options.*' => ['nullable', 'integer', Rule::exists('variant_options', 'id')],
            'addons' => ['nullable', 'array'],
            'addons.*' => [
                'integer',
                Rule::exists('addons', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
        ]);

        $cart = session('cashier_cart', []);

        if (! isset($cart[$key])) {
            if ($this->wantsCartJson($request)) {
                return $this->cartJsonResponse('Item keranjang tidak ditemukan.', false, 404);
            }

            return back();
        }

        $cartItem = $this->makeCartItem($validated);
        unset($cart[$key]);

        if (isset($cart[$cartItem['key']])) {
            $cart[$cartItem['key']]['quantity'] = min(
                $cart[$cartItem['key']]['quantity'] + $cartItem['quantity'],
                $cartItem['stock']
            );
            $cart[$cartItem['key']]['line_total'] = $cart[$cartItem['key']]['unit_price'] * $cart[$cartItem['key']]['quantity'];
        } else {
            $cart[$cartItem['key']] = $cartItem;
        }

        session(['cashier_cart' => $cart]);

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Item keranjang diperbarui.');
        }

        return back()->with('status', 'Item keranjang diperbarui.');
    }

    public function updateCart(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:increment,decrement'],
        ]);

        $cart = session('cashier_cart', []);

        if (! isset($cart[$key])) {
            if ($this->wantsCartJson($request)) {
                return $this->cartJsonResponse('Item keranjang tidak ditemukan.', false, 404);
            }

            return back();
        }

        $quantity = $cart[$key]['quantity'] + ($validated['action'] === 'increment' ? 1 : -1);

        if ($quantity < 1) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = min($quantity, $cart[$key]['stock']);
            $cart[$key]['line_total'] = $cart[$key]['unit_price'] * $cart[$key]['quantity'];
        }

        session(['cashier_cart' => $cart]);

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Keranjang diperbarui.');
        }

        return back();
    }

    public function updateDiscount(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'discount_type' => ['required', 'in:percent,nominal'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cartSubtotal = collect(session('cashier_cart', []))->sum('line_total');
        $value = (float) ($validated['discount_value'] ?? 0);

        if ($cartSubtotal <= 0 || $value <= 0) {
            session()->forget('cashier_discount');

            if ($this->wantsCartJson($request)) {
                return $this->cartJsonResponse('Diskon dihapus.');
            }

            return back();
        }

        if ($validated['discount_type'] === 'percent') {
            $value = min($value, 100);
        } else {
            $value = min($value, $cartSubtotal);
        }

        session([
            'cashier_discount' => [
                'type' => $validated['discount_type'],
                'value' => $value,
            ],
        ]);

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Diskon diperbarui.');
        }

        return back()->with('status', 'Diskon diperbarui.');
    }

    public function updatePaymentMethod(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,qris'],
        ]);

        if (! in_array($validated['payment_method'], $this->activePaymentMethods(), true)) {
            if ($this->wantsCartJson($request)) {
                return $this->cartJsonResponse('Metode pembayaran sedang tidak aktif.', false, 422);
            }

            return back()->with('error', 'Metode pembayaran sedang tidak aktif.');
        }

        session(['cashier_payment_method' => $validated['payment_method']]);

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Metode pembayaran diperbarui.');
        }

        return back();
    }

    public function checkout(Request $request): RedirectResponse|JsonResponse
    {
        $cart = collect(session('cashier_cart', []));

        if ($cart->isEmpty()) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        $paymentMethod = session('cashier_payment_method', 'cash');
        if (! in_array($paymentMethod, $this->activePaymentMethods(), true)) {
            return back()->with('error', 'Metode pembayaran sedang tidak aktif.');
        }
        $cartSubtotal = $cart->sum('line_total');
        $cartDiscount = $this->resolveDiscount($cartSubtotal);
        $cartTotal = max(0, $cartSubtotal - $cartDiscount['amount']);

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $paidAmount = $paymentMethod === 'cash'
            ? (int) ($validated['paid_amount'] ?? 0)
            : $cartTotal;

        if ($paymentMethod === 'cash' && $paidAmount < $cartTotal) {
            return back()->with('error', 'Uang diterima kurang dari total tagihan.');
        }

        $transaction = DB::transaction(function () use ($cart, $cartSubtotal, $cartDiscount, $cartTotal, $paymentMethod, $paidAmount, $validated) {
            $quantityByProduct = $cart
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            foreach ($quantityByProduct as $productId => $quantity) {
                $product = Product::query()
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->lockForUpdate()
                    ->find($productId);

                if (! $product || $product->stock < $quantity) {
                    abort(422, 'Stok produk tidak cukup.');
                }
            }

            $transactionData = [
                'tenant_id' => auth()->user()->tenant_id,
                'invoice_number' => $this->makeInvoiceNumber(),
                'user_id' => auth()->id(),
                'channel' => 'offline',
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'payment_method' => $paymentMethod,
                'status' => 'paid',
                'subtotal' => $cartSubtotal,
                'discount_type' => $cartDiscount['amount'] > 0 ? $cartDiscount['type'] : 'none',
                'discount_value' => $cartDiscount['value'],
                'discount_amount' => $cartDiscount['amount'],
                'tax_amount' => 0,
                'total' => $cartTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => max(0, $paidAmount - $cartTotal),
                'paid_at' => now(),
            ];

            if (Schema::hasColumn('transactions', 'receipt_code')) {
                $transactionData['receipt_code'] = $this->makeReceiptCode();
            }

            $transaction = Transaction::create($transactionData);

            foreach ($cart as $item) {
                Product::query()
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->whereKey($item['product_id'])
                    ->decrement('stock', $item['quantity']);

                $transactionItem = $transaction->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'base_price' => $item['base_price'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                    'note' => $item['note'] ?: null,
                ]);

                foreach ($item['variant_options'] as $option) {
                    $transactionItem->variantOptions()->create([
                        'variant_option_id' => $option['id'],
                        'variant_group_name' => $option['group'],
                        'option_name' => $option['name'],
                        'price_delta' => $option['price_delta'],
                    ]);
                }

                foreach ($item['addons'] as $addon) {
                    $transactionItem->addons()->create([
                        'addon_id' => $addon['id'],
                        'addon_name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                }
            }

            return $transaction;
        });

        try {
            app(ReceiptPngService::class)->ensure($transaction);
        } catch (\Throwable $exception) {
            report($exception);
        }

        session()->forget('cashier_cart');
        session()->forget('cashier_discount');
        session()->forget('cashier_payment_method');

        if ($this->wantsCartJson($request)) {
            return response()->json(array_merge([
                'ok' => true,
                'message' => 'Transaksi berhasil disimpan.',
            ], $this->cartPayload(), [
                'transaction' => [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'total' => (int) $transaction->total,
                    'paid_amount' => (int) $transaction->paid_amount,
                    'change_amount' => (int) $transaction->change_amount,
                    'payment_method' => $transaction->payment_method,
                    'receipt_url' => TransactionController::signedReceiptUrl($transaction),
                    'show_url' => route('transactions.show', $transaction),
                    'whatsapp_message' => $this->whatsappMessageForTransaction($transaction),
                ],
            ]));
        }

        return redirect()->route('transactions.show', $transaction)->with('status', 'Transaksi berhasil disimpan.');
    }

    public function destroyCart(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $cart = session('cashier_cart', []);
        unset($cart[$key]);
        session(['cashier_cart' => $cart]);

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Item dihapus dari keranjang.');
        }

        return back();
    }

    public function clearCart(Request $request): RedirectResponse|JsonResponse
    {
        session()->forget('cashier_cart');
        session()->forget('cashier_discount');
        session()->forget('cashier_payment_method');

        if ($this->wantsCartJson($request)) {
            return $this->cartJsonResponse('Keranjang dikosongkan.');
        }

        return back();
    }

    private function wantsCartJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function cartJsonResponse(string $message, bool $ok = true, int $status = 200): JsonResponse
    {
        return response()->json(array_merge([
            'ok' => $ok,
            'message' => $message,
        ], $this->cartPayload()), $status);
    }

    private function cartPayload(): array
    {
        $cart = collect(session('cashier_cart', []));
        $cartSubtotal = $cart->sum('line_total');
        $cartDiscount = $this->resolveDiscount($cartSubtotal);
        $cartTotal = max(0, $cartSubtotal - $cartDiscount['amount']);
        $paymentMethods = $this->activePaymentMethods();
        $paymentMethod = session('cashier_payment_method', $paymentMethods[0] ?? 'cash');

        if (! in_array($paymentMethod, $paymentMethods, true)) {
            $paymentMethod = $paymentMethods[0] ?? 'cash';
            session(['cashier_payment_method' => $paymentMethod]);
        }

        $products = Product::query()
            ->with(['category', 'variantGroups.options', 'addons'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('id', $cart->pluck('product_id')->unique()->values())
            ->get();
        $formatRupiah = fn ($value) => $this->formatRupiah($value);
        $viewData = compact('cart', 'cartSubtotal', 'cartDiscount', 'cartTotal', 'paymentMethod', 'paymentMethods', 'products', 'formatRupiah');

        return [
            'cart' => $cart->values()->all(),
            'summary' => [
                'count' => $cart->count(),
                'subtotal' => (int) $cartSubtotal,
                'discount' => $cartDiscount,
                'total' => (int) $cartTotal,
                'payment_method' => $paymentMethod,
                'payment_methods' => $paymentMethods,
                'formatted' => [
                    'subtotal' => $this->formatRupiah($cartSubtotal),
                    'discount_amount' => $this->formatRupiah($cartDiscount['amount']),
                    'total' => $this->formatRupiah($cartTotal),
                ],
            ],
            'html' => [
                'cart_panel' => view('cashier._cart-panel', array_merge($viewData, ['showCloseButton' => false]))->render(),
                'cart_drawer_panel' => view('cashier._cart-panel', array_merge($viewData, ['showCloseButton' => true]))->render(),
                'cart_edit_modals' => view('cashier._cart-edit-modals', $viewData)->render(),
            ],
        ];
    }

    private function formatRupiah(int|float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private function resolveDiscount(int|float $subtotal): array
    {
        $discount = session('cashier_discount', ['type' => 'nominal', 'value' => 0]);
        $type = in_array($discount['type'] ?? null, ['percent', 'nominal'], true) ? $discount['type'] : 'nominal';
        $value = max(0, (float) ($discount['value'] ?? 0));

        if ($subtotal <= 0 || $value <= 0) {
            return ['type' => $type, 'value' => 0, 'amount' => 0];
        }

        if ($type === 'percent') {
            $value = min($value, 100);
            $amount = (int) round($subtotal * ($value / 100));
        } else {
            $amount = (int) min($value, $subtotal);
        }

        return [
            'type' => $type,
            'value' => $value,
            'amount' => $amount,
        ];
    }

    private function activePaymentMethods(): array
    {
        $settings = Setting::getValue('payment_methods', [
            'cash' => true,
            'qris' => true,
        ]);

        $active = collect($settings)
            ->filter()
            ->keys()
            ->intersect(['cash', 'qris'])
            ->values()
            ->all();

        return $active ?: ['cash'];
    }

    private function makeCartItem(array $validated): array
    {
        $product = Product::query()
            ->with(['category', 'variantGroups.options', 'addons'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->findOrFail($validated['product_id']);

        abort_if($product->stock < 1, 422, 'Produk sedang habis.');

        $quantity = min($validated['quantity'] ?? 1, $product->stock);
        $variantOptionIds = collect($validated['variant_options'] ?? [])->filter()->values();
        $addonIds = collect($validated['addons'] ?? [])->filter()->values();

        $allowedGroupIds = $product->variantGroups->pluck('id');
        $variantOptions = VariantOption::query()
            ->with('variantGroup')
            ->whereIn('id', $variantOptionIds)
            ->where('is_active', true)
            ->whereIn('variant_group_id', $allowedGroupIds)
            ->get();

        $addons = Addon::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('id', $addonIds)
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->whereKey($product->id))
            ->get();

        $unitPrice = (int) $product->price + (int) $variantOptions->sum('price_delta') + (int) $addons->sum('price');
        $key = md5(json_encode([
            'product_id' => $product->id,
            'variant_options' => $variantOptions->pluck('id')->sort()->values(),
            'addons' => $addons->pluck('id')->sort()->values(),
            'note' => $validated['note'] ?? '',
        ]));

        return [
            'tenant_id' => auth()->user()->tenant_id,
            'key' => $key,
            'product_id' => $product->id,
            'name' => $product->name,
            'base_price' => (int) $product->price,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
            'stock' => (int) $product->stock,
            'note' => $validated['note'] ?? '',
            'variant_options' => $variantOptions->map(fn ($option) => [
                'id' => $option->id,
                'group' => $option->variantGroup->name,
                'name' => $option->name,
                'price_delta' => (int) $option->price_delta,
            ])->values()->all(),
            'addons' => $addons->map(fn ($addon) => [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => (int) $addon->price,
            ])->values()->all(),
        ];
    }

    private function makeReceiptCode(): string
    {
        if (! Schema::hasColumn('transactions', 'receipt_code')) {
            return (string) Str::upper(Str::random(8));
        }

        do {
            $code = Str::upper(Str::random(8));
        } while (Transaction::query()->where('receipt_code', $code)->exists());

        return $code;
    }

    private function whatsappMessageForTransaction(Transaction $transaction): string
    {
        $receipt = Setting::getValue('receipt', [
            'cafe_name' => config('app.name', 'Keijora POS'),
        ], auth()->user()->tenant_id);

        $brand = $receipt['cafe_name'] ?: config('app.name', 'Keijora POS');
        $amount = 'Rp '.number_format($transaction->total, 0, ',', '.');

        return "Halo Kak, kami dari {$brand}.\n\n"
            ."Terima kasih sudah memesan makanan dan minuman di tempat kami.\n\n"
            ."Total pesanan Kakak sebesar {$amount}.\n"
            ."Untuk rincian lengkapnya, silakan lihat nota yang kami lampirkan di bawah ini ya, Kak.\n\n"
            ."Link Nota:\n"
            .AppUrl::publicReceipt($transaction->receipt_code ?: (string) $transaction->getKey())."\n\n"
            ."Kalau ada yang kurang sesuai atau ada kendala, silakan kabari admin kami ya, Kak. Kami dengan senang hati akan membantu.\n\n"
            ."Terima kasih atas kepercayaannya. Selamat menikmati pesanan Kakak.";
    }

    private function makeInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Transaction::query()->where('invoice_number', $invoice)->exists());

        return $invoice;
    }


}
