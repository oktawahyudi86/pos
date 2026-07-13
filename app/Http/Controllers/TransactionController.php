<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\ReceiptPngService;
use App\Support\AppUrl;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Transaction::query()
            ->where('tenant_id', $tenantId)
            ->with('cashier')
            ->when($request->filled('date'), fn ($query) => $query->whereDate('paid_at', $request->date('date')))
            ->latest('paid_at');

        $totalSales = (clone $query)->sum('total');
        $totalTransactions = (clone $query)->count();
        $averageBasket = $totalTransactions > 0 ? (int) round($totalSales / $totalTransactions) : 0;
        $topProduct = TransactionItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_quantity')
            ->whereHas('transaction', fn ($transactionQuery) => $transactionQuery->where('tenant_id', $tenantId))
            ->when($request->filled('date'), fn ($query) => $query->whereHas('transaction', fn ($transactionQuery) => $transactionQuery->whereDate('paid_at', $request->date('date'))))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->value('product_name') ?? '-';

        $transactions = $query
            ->paginate(20)
            ->withQueryString();

        return view('transactions.index', compact('transactions', 'totalSales', 'totalTransactions', 'averageBasket', 'topProduct'));
    }

    public function show(Transaction $transaction): View
    {
        abort_unless((int) $transaction->tenant_id === (int) auth()->user()->tenant_id, 404);
        $transaction->load(['cashier', 'items.variantOptions', 'items.addons']);

        return view('transactions.show', compact('transaction'));
    }

    public function receipt(Transaction $transaction): SymfonyResponse|BinaryFileResponse
    {
        abort_unless((int) $transaction->tenant_id === (int) auth()->user()->tenant_id, 404);
        $service = app(ReceiptPngService::class);
        $path = $service->ensure($transaction);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function publicReceipt(Transaction $transaction): SymfonyResponse|BinaryFileResponse
    {
        abort_unless((int) $transaction->tenant_id > 0, 404);
        $service = app(ReceiptPngService::class);
        $path = $service->ensure($transaction);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public function publicReceiptByCode(string $code): SymfonyResponse|BinaryFileResponse
    {
        $transaction = Transaction::query()
            ->when(
                ctype_digit($code),
                fn ($query) => $query->whereKey((int) $code),
                fn ($query) => $query->where('receipt_code', $code)
            )
            ->firstOrFail();

        $service = app(ReceiptPngService::class);
        $path = $service->ensure($transaction);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public static function signedReceiptUrl(Transaction $transaction): string
    {
        $code = $transaction->receipt_code ?: (string) $transaction->getKey();

        return AppUrl::publicReceipt($code);
    }
}
