<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSummaryStats(string $startDate, string $endDate): array
    {
        $paidBills = Bill::where('status', 'paid')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $totalRevenue = (clone $paidBills)->sum('total_amount');
        $netSales = (clone $paidBills)->sum('subtotal');
        $taxCollected = (clone $paidBills)->sum('tax_amount');
        $serviceCollected = (clone $paidBills)->sum('service_amount');
        $billsCount = (clone $paidBills)->count();

        $averageOrderValue = $billsCount > 0 ? round($totalRevenue / $billsCount, 2) : 0.00;

        $ordersCount = Order::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        return [
            'total_revenue'   => (float) $totalRevenue,
            'net_sales'       => (float) $netSales,
            'tax_collected'   => (float) $taxCollected,
            'service_fee'     => (float) $serviceCollected,
            'bills_count'     => $billsCount,
            'orders_count'    => $ordersCount,
            'avg_order_value' => $averageOrderValue,
        ];
    }

    public function getDailyRevenueTrend(string $startDate, string $endDate): array
    {
        $sales = Bill::where('status', 'paid')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('total', 'date')
            ->toArray();

        $dates = [];
        $totals = [];

        // Days without sales are kept as zero so the chart has no gaps.
        foreach (Carbon::parse($startDate)->daysUntil(Carbon::parse($endDate)) as $date) {
            $dates[] = $date->format('M d');
            $totals[] = (float) ($sales[$date->format('Y-m-d')] ?? 0.00);
        }

        return [
            'labels' => $dates,
            'values' => $totals,
        ];
    }

    public function getTopSellingItems(string $startDate, string $endDate, int $limit = 5): array
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->where('orders.status', 'completed')
            ->whereDate('order_items.created_at', '>=', $startDate)
            ->whereDate('order_items.created_at', '<=', $endDate)
            ->select(
                'menu_items.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getPaymentMethodSplit(string $startDate, string $endDate): array
    {
        return DB::table('payments')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select('payment_method', DB::raw('SUM(amount_paid) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->map(fn ($total) => (float) $total)
            ->toArray();
    }
}
