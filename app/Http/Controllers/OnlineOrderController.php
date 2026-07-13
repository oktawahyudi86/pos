<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Category;
use App\Models\OnlineOrder;
use App\Models\OnlineOrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\VariantOption;
use App\Services\DeliveryCoverageService;
use App\Services\ReverseGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;

class OnlineOrderController extends Controller
{
    public function __construct(
        private readonly DeliveryCoverageService $deliveryCoverageService,
    ) {}
    public function index(Tenant $tenant, Request $request): View
    {
        abort_unless($tenant->isActive(), 404);

        [$cart, $cartSubtotal, $shippingCost, $total] = $this->cartSummary($tenant);

        $categories = Category::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->withCount(['variantGroups', 'addons'])
            ->where('tenant_id', $tenant->id)
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

        $paymentInfo = $this->paymentInfo($tenant);

        return view('online-orders.catalog', compact(
            'tenant',
            'categories',
            'products',
            'cart',
            'cartSubtotal',
            'shippingCost',
            'total',
            'paymentInfo'
        ));
    }

    public function productDetail(Tenant $tenant, Product $product)
    {
        abort_unless($tenant->isActive(), 404);
        abort_unless((int) $product->tenant_id === (int) $tenant->id && $product->is_active, 404);

        $product->load([
            'category',
            'variantGroups' => fn ($query) => $query->where('is_active', true)->with(['options' => fn ($optionQuery) => $optionQuery->where('is_active', true)]),
            'addons' => fn ($query) => $query->where('is_active', true),
        ]);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => (int) $product->price,
            'stock' => (int) $product->stock,
            'image_url' => $product->image_path ? asset('storage/'.$product->image_path) : null,
            'category' => [
                'name' => $product->category?->name ?? '',
            ],
            'variant_groups' => $product->variantGroups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'is_required' => (bool) $group->is_required,
                'selection_type' => $group->selection_type,
                'options' => $group->options->map(fn ($option) => [
                    'id' => $option->id,
                    'name' => $option->name,
                    'price_delta' => (int) $option->price_delta,
                ])->values(),
            ])->values(),
            'addons' => $product->addons->map(fn ($addon) => [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => (int) $addon->price,
            ])->values(),
        ]);
    }

    public function storeCart(Tenant $tenant, Request $request): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:160'],
            'variant_options' => ['nullable', 'array'],
            'variant_options.*' => ['nullable', 'integer', Rule::exists('variant_options', 'id')],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer', Rule::exists('addons', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id))],
        ]);

        $cart = $this->loadCart($tenant);
        $cartItem = $this->makeCartItem($tenant, $validated);
        $existingQuantity = $cart[$cartItem['key']]['quantity'] ?? 0;
        $newQuantity = min($existingQuantity + $cartItem['quantity'], $cartItem['stock']);

        $cartItem['quantity'] = $newQuantity;
        $cartItem['line_total'] = $cartItem['unit_price'] * $newQuantity;
        $cart[$cartItem['key']] = $cartItem;

        session([$this->cartSessionKey($tenant) => $cart->all()]);

        return back()->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function updateCart(Tenant $tenant, Request $request, string $key): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        $validated = $request->validate([
            'action' => ['required', 'in:increment,decrement'],
        ]);

        $cart = $this->loadCart($tenant);
        if (! isset($cart[$key])) {
            return back();
        }

        $quantity = $cart[$key]['quantity'] + ($validated['action'] === 'increment' ? 1 : -1);

        if ($quantity < 1) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = min($quantity, $cart[$key]['stock']);
            $cart[$key]['line_total'] = $cart[$key]['unit_price'] * $cart[$key]['quantity'];
        }

        session([$this->cartSessionKey($tenant) => $cart->all()]);

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function destroyCart(Tenant $tenant, string $key): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        $cart = $this->loadCart($tenant);
        unset($cart[$key]);

        session([$this->cartSessionKey($tenant) => $cart->all()]);

        return back();
    }

    public function checkout(Tenant $tenant, Request $request): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        $cart = $this->loadCart($tenant);
        abort_if($cart->isEmpty(), 422, 'Keranjang masih kosong.');

        $request->merge([
            'wa_number' => preg_replace('/\D+/', '', (string) $request->input('wa_number')),
        ]);

        $activePaymentMethods = $this->activeOnlinePaymentMethodKeys($tenant);
        $request->merge([
            'payment_method' => $request->input('payment_method', $activePaymentMethods[0] ?? 'manual_transfer'),
        ]);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'wa_number' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'address_detail' => ['nullable', 'string', 'max:500'],
            'address_label' => ['required', 'string', Rule::in(['rumah', 'kantor', 'lainnya'])],
            'delivery_latitude' => ['required', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['required', 'numeric', 'between:-180,180'],
            'place_id' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'subdistrict' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', Rule::in($activePaymentMethods)],
        ]);

        $deliverySettings = $this->deliveryCoverageService->settingsForTenant($tenant->id);
        $coverage = $this->deliveryCoverageService->evaluate(
            isset($validated['delivery_latitude']) ? (float) $validated['delivery_latitude'] : null,
            isset($validated['delivery_longitude']) ? (float) $validated['delivery_longitude'] : null,
            $deliverySettings,
        );

        if ($coverage['active'] && ! $coverage['within_coverage']) {
            return back()
                ->withInput()
                ->withErrors([
                    'address' => $coverage['message'] ?? DeliveryCoverageService::OUT_OF_COVERAGE_MESSAGE,
                ]);
        }

        [$cartSubtotal, $shippingCost, $total] = $this->cartTotals($cart);

        $order = DB::transaction(function () use ($tenant, $cart, $validated, $cartSubtotal, $shippingCost, $total) {
            $quantityByProduct = $cart
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            foreach ($quantityByProduct as $productId => $quantity) {
                $product = Product::query()
                    ->where('tenant_id', $tenant->id)
                    ->lockForUpdate()
                    ->find($productId);

                if (! $product || $product->stock < $quantity) {
                    abort(422, 'Stok produk tidak cukup.');
                }
            }

            $order = OnlineOrder::create([
                'tenant_id' => $tenant->id,
                'order_number' => $this->makeOrderNumber($tenant),
                'customer_name' => $validated['customer_name'],
                'wa_number' => $validated['wa_number'],
                'address' => $validated['address'],
                'address_note' => $validated['address_detail'] ?? null,
                'delivery_latitude' => $validated['delivery_latitude'],
                'delivery_longitude' => $validated['delivery_longitude'],
                'delivery_province' => $validated['province'] ?? null,
                'delivery_city' => $validated['city'] ?? null,
                'delivery_district' => $validated['district'] ?? null,
                'delivery_village' => $validated['subdistrict'] ?? null,
                'delivery_postal_code' => $validated['postal_code'] ?? null,
                'delivery_place_id' => $validated['place_id'] ?? null,
                'delivery_address_label' => $validated['address_label'],
                'status' => 'pesanan_masuk',
                'payment_method' => $validated['payment_method'],
                'subtotal' => $cartSubtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'placed_at' => now(),
            ]);

            foreach ($cart as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'base_price' => $item['base_price'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                    'note' => $item['note'] ?: null,
                    'variant_payload' => $item['variant_options'],
                    'addon_payload' => $item['addons'],
                ]);
            }

            $order->statusLogs()->create([
                'status' => 'pesanan_masuk',
                'changed_at' => now(),
            ]);

            return $order;
        });

        session()->forget($this->cartSessionKey($tenant));

        return redirect()->route('online-orders.success', [$tenant, $order]);
    }

    public function reverseGeocode(Tenant $tenant, Request $request, ReverseGeocodingService $reverseGeocodingService): JsonResponse
    {
        abort_unless($tenant->isActive(), 404);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $result = $reverseGeocodingService->resolve(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
            );
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Alamat tidak dapat ditentukan dari lokasi ini. Silakan pilih titik lain di peta.',
            ], 422);
        }

        $deliverySettings = $this->deliveryCoverageService->settingsForTenant($tenant->id);
        $coverage = $this->deliveryCoverageService->evaluate(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $deliverySettings,
        );

        return response()->json([
            ...$result,
            'coverage' => $coverage,
        ]);
    }

    public function geocodeSearch(Tenant $tenant, Request $request, ReverseGeocodingService $reverseGeocodingService): JsonResponse
    {
        abort_unless($tenant->isActive(), 404);

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        try {
            $results = $reverseGeocodingService->search($validated['q']);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Pencarian alamat gagal. Coba lagi dalam beberapa detik.',
            ], 422);
        }

        return response()->json([
            'results' => $results,
        ]);
    }

    public function review(Tenant $tenant): View
    {
        abort_unless($tenant->isActive(), 404);

        [$cart, $cartSubtotal, $shippingCost, $total] = $this->cartSummary($tenant);
        $paymentInfo = $this->paymentInfo($tenant);
        $onlinePaymentMethods = $this->activeOnlinePaymentMethodKeys($tenant);
        $deliveryCoverage = $this->deliveryCoverageService->settingsForTenant($tenant->id);

        return view('online-orders.checkout', compact(
            'tenant',
            'cart',
            'cartSubtotal',
            'shippingCost',
            'total',
            'paymentInfo',
            'onlinePaymentMethods',
            'deliveryCoverage',
        ));
    }

    public function success(Tenant $tenant, OnlineOrder $order): View
    {
        abort_unless((int) $order->tenant_id === (int) $tenant->id, 404);

        $order->loadMissing('items');
        $paymentInfo = $this->paymentInfo($tenant);
        $cashierConfirmationUrl = $this->makeCashierConfirmationUrl($order, $paymentInfo);

        return view('online-orders.success', compact(
            'tenant',
            'order',
            'paymentInfo',
            'cashierConfirmationUrl',
        ));
    }

    public function track(Tenant $tenant, Request $request): View
    {
        abort_unless($tenant->isActive(), 404);

        $waNumber = preg_replace('/\D+/', '', $request->string('wa_number')->toString());
        $orders = collect();

        if ($waNumber !== '') {
            $orders = OnlineOrder::query()
                ->where('tenant_id', $tenant->id)
                ->where('wa_number', $waNumber)
                ->with('items')
                ->latest('placed_at')
                ->get();
        }

        return view('online-orders.track', compact('tenant', 'waNumber', 'orders'));
    }

    private function cartSessionKey(Tenant $tenant): string
    {
        return 'online_order_cart_'.$tenant->id;
    }

    private function loadCart(Tenant $tenant): Collection
    {
        return collect(session($this->cartSessionKey($tenant), []));
    }

    private function cartTotals(Collection $cart): array
    {
        $cartSubtotal = (int) $cart->sum('line_total');
        $shippingCost = $cartSubtotal > 0 ? 10000 : 0;
        $total = $cartSubtotal + $shippingCost;

        return [$cartSubtotal, $shippingCost, $total];
    }

    private function cartSummary(Tenant $tenant): array
    {
        $cart = $this->loadCart($tenant);
        [$cartSubtotal, $shippingCost, $total] = $this->cartTotals($cart);

        return [$cart, $cartSubtotal, $shippingCost, $total];
    }

    private function paymentInfo(Tenant $tenant): array
    {
        $settings = Setting::getValue('online_payment', [], $tenant->id);

        return array_merge([
            'methods' => [
                'transfer_bank' => true,
                'qris' => true,
            ],
            'bank_name' => 'Mandiri',
            'account_number' => '1234567890',
            'account_name' => $tenant->name,
            'qris_image_path' => null,
            'qris_merchant_name' => '',
            'cashier_wa_number' => '',
        ], is_array($settings) ? $settings : []);
    }

    private function activeOnlinePaymentMethodKeys(Tenant $tenant): array
    {
        $paymentInfo = $this->paymentInfo($tenant);
        $methods = [];

        if ($paymentInfo['methods']['transfer_bank'] ?? true) {
            $methods[] = 'manual_transfer';
        }

        if ($paymentInfo['methods']['qris'] ?? true) {
            $methods[] = 'qris';
        }

        return $methods === [] ? ['manual_transfer'] : $methods;
    }

    private function makeCashierConfirmationMessage(OnlineOrder $order): string
    {
        $items = $order->items
            ->map(fn ($item) => "- {$item->quantity}x {$item->product_name} (".$this->formatRupiah((int) $item->line_total).')')
            ->join("\n");

        return implode("\n", array_filter([
            "Halo, saya {$order->customer_name} ingin konfirmasi pembayaran pesanan {$order->order_number}.",
            '',
            'Metode pembayaran: '.$order->paymentMethodLabel(),
            'Total: '.$this->formatRupiah((int) $order->total),
            '',
            'Ringkasan pesanan:',
            $items,
            '',
            'Nomor WhatsApp saya: '.$order->wa_number,
            'Mohon dicek ya. Terima kasih.',
        ]));
    }

    private function makeCashierConfirmationUrl(OnlineOrder $order, array $paymentInfo): ?string
    {
        $cashierNumber = preg_replace('/\D+/', '', (string) ($paymentInfo['cashier_wa_number'] ?? '')) ?: '';

        if ($cashierNumber === '') {
            return null;
        }

        if (str_starts_with($cashierNumber, '0')) {
            $cashierNumber = '62'.substr($cashierNumber, 1);
        }

        return 'https://wa.me/'.$cashierNumber.'?text='.rawurlencode($this->makeCashierConfirmationMessage($order));
    }

    private function formatRupiah(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private function makeCartItem(Tenant $tenant, array $validated): array
    {
        $product = Product::query()
            ->with(['category', 'variantGroups.options', 'addons'])
            ->where('tenant_id', $tenant->id)
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
            ->where('tenant_id', $tenant->id)
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

    private function makeOrderNumber(Tenant $tenant): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (OnlineOrder::query()->where('tenant_id', $tenant->id)->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
