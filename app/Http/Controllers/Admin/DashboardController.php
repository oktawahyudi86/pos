<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayTransactions = Transaction::query()->where('tenant_id', $tenantId)->whereDate('paid_at', $today);
        $yesterdayTransactions = Transaction::query()->where('tenant_id', $tenantId)->whereDate('paid_at', $yesterday);

        $todaySales = (clone $todayTransactions)->sum('total');
        $yesterdaySales = (clone $yesterdayTransactions)->sum('total');
        $todayTransactionCount = (clone $todayTransactions)->count();
        $yesterdayTransactionCount = (clone $yesterdayTransactions)->count();
        $averageBasket = $todayTransactionCount > 0 ? (int) round($todaySales / $todayTransactionCount) : 0;

        $topProduct = TransactionItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_quantity')
            ->whereHas('transaction', fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereHas('transaction', fn ($query) => $query->whereDate('paid_at', $today))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->first();

        $salesGrowth = $this->growthPercentage((int) $todaySales, (int) $yesterdaySales);
        $transactionGrowth = $this->growthPercentage($todayTransactionCount, $yesterdayTransactionCount);

        $startDate = $today->copy()->subDays(6);
        $dailySales = Transaction::query()
            ->selectRaw('DATE(paid_at) as sale_date, SUM(total) as total_sales')
            ->where('tenant_id', $tenantId)
            ->whereBetween('paid_at', [$startDate->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('total_sales', 'sale_date');

        $chartData = collect(CarbonPeriod::create($startDate, $today))
            ->map(function (Carbon $date) use ($dailySales, $today) {
                $key = $date->toDateString();

                return [
                    'label' => $date->isSameDay($today) ? 'Hari Ini' : $date->translatedFormat('D'),
                    'date' => $date->translatedFormat('d M'),
                    'total' => (int) ($dailySales[$key] ?? 0),
                    'is_today' => $date->isSameDay($today),
                ];
            });

        $maxChartValue = max($chartData->max('total'), 1);

        $recentTransactions = Transaction::query()
            ->where('tenant_id', $tenantId)
            ->with(['cashier', 'items'])
            ->latest('paid_at')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::query()
            ->where('tenant_id', $tenantId)
            ->with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'averageBasket',
            'chartData',
            'lowStockProducts',
            'maxChartValue',
            'recentTransactions',
            'salesGrowth',
            'todaySales',
            'todayTransactionCount',
            'topProduct',
            'transactionGrowth',
        ));
    }

    private function growthPercentage(int $current, int $previous): int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
