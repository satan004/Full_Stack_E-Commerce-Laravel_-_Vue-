<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $lowStockThreshold = 5;
        $period = $request->string('period')->toString();
        $period = in_array($period, ['today', 'week', 'month', 'year', 'custom'], true) ? $period : 'week';
        $periodStart = match ($period) {
            'today' => Carbon::today(),
            'month' => Carbon::today()->subDays(29),
            'year' => Carbon::today()->startOfYear(),
            'custom' => Carbon::today()->subDays(29),
            default => Carbon::today()->subDays(6),
        };
        $periodLabel = match ($period) {
            'today' => 'Today',
            'month' => 'Last 30 days',
            'year' => 'This year',
            'custom' => 'Custom range',
            default => 'Last 7 days',
        };

        $orders = Order::query()->get(['id', 'status', 'total', 'created_at']);
        $periodOrders = $orders->filter(fn (Order $order) => $order->created_at->greaterThanOrEqualTo($periodStart));
        $salesTrend = $this->salesTrend($orders, $period);

        $statusCounts = collect(Order::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => $orders->where('status', $status)->count()]);

        $categoryBars = Category::query()
            ->withCount('products')
            ->withSum('products', 'stock')
            ->orderByDesc('products_count')
            ->limit(8)
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'products' => $category->products_count,
                'stock' => (int) ($category->products_sum_stock ?? 0),
            ]);

        $topProducts = OrderItem::query()
            ->selectRaw('product_id, product_name, SUM(quantity) as quantity_sold, SUM(total) as sales_total')
            ->with('product')
            ->whereHas('order', fn ($query) => $query->where('created_at', '>=', $periodStart))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('quantity_sold')
            ->limit(8)
            ->get();

        $categoryRevenue = OrderItem::query()
            ->with(['order', 'product.category'])
            ->whereHas('order', fn ($query) => $query->where('created_at', '>=', $periodStart))
            ->get()
            ->groupBy(fn (OrderItem $item) => $item->product?->category?->name ?? 'Uncategorized')
            ->map(fn ($items, string $name): array => [
                'name' => $name,
                'revenue' => round((float) $items->sum('total'), 2),
                'quantity' => (int) $items->sum('quantity'),
            ])
            ->sortByDesc('revenue')
            ->values()
            ->take(8);

        $stockHealth = [
            'healthy' => Product::where('stock', '>', $lowStockThreshold)->count(),
            'low' => Product::whereBetween('stock', [1, $lowStockThreshold])->count(),
            'out' => Product::where('stock', '<=', 0)->count(),
        ];

        $recentOrders = Order::with('user')->latest()->limit(5)->get();
        $lowStockProducts = Product::with('category')->where('stock', '<=', $lowStockThreshold)->orderBy('stock')->limit(5)->get();

        return view('admin.dashboard', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'stats' => [
                'categories' => Category::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'users' => User::where('is_admin', false)->count(),
                'low_stock' => Product::where('stock', '<=', $lowStockThreshold)->count(),
                'active_products' => Product::where('is_active', true)->count(),
                'revenue' => $orders->sum('total'),
                'completed_revenue' => $orders->where('status', 'completed')->sum('total'),
                'average_order' => $orders->count() > 0 ? round((float) $orders->avg('total'), 2) : 0,
                'period_orders' => $periodOrders->count(),
                'period_revenue' => $periodOrders->sum('total'),
            ],
            'recentOrders' => $recentOrders,
            'recentOrdersTotal' => Order::count(),
            'lowStockProducts' => $lowStockProducts,
            'lowStockProductsTotal' => Product::where('stock', '<=', $lowStockThreshold)->count(),
            'charts' => [
                'trend' => $salesTrend,
                'statuses' => $statusCounts,
                'categories' => $categoryBars,
                'categoryRevenue' => $categoryRevenue,
                'stockHealth' => $stockHealth,
            ],
            'topProducts' => $topProducts,
        ]);
    }

    public function orderStatus(): JsonResponse
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', Order::STATUSES)
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'completed' => (int) ($counts['completed'] ?? 0),
            'pending' => (int) ($counts['pending'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ]);
    }

    public function productsByCategory(): JsonResponse
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'products' => $category->products_count,
            ]);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function revenueByCategory(Request $request): JsonResponse
    {
        $period = $request->string('period')->toString();
        $period = in_array($period, ['today', 'week', 'month', 'year', 'custom'], true) ? $period : 'week';
        $periodStart = match ($period) {
            'today' => Carbon::today(),
            'month', 'custom' => Carbon::today()->subDays(29),
            'year' => Carbon::today()->startOfYear(),
            default => Carbon::today()->subDays(6),
        };

        $categories = OrderItem::query()
            ->with(['order', 'product.category'])
            ->whereHas('order', fn ($query) => $query->where('created_at', '>=', $periodStart))
            ->get()
            ->groupBy(fn (OrderItem $item) => $item->product?->category?->name ?? 'Uncategorized')
            ->map(fn ($items, string $name): array => [
                'name' => $name,
                'revenue' => round((float) $items->sum('total'), 2),
                'products_sold' => (int) $items->sum('quantity'),
            ])
            ->sortByDesc('revenue')
            ->values();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    private function salesTrend(Collection $orders, string $period): array
    {
        [$currentBuckets, $previousBuckets] = match ($period) {
            'today' => [$this->todayBuckets(), $this->todayBuckets(Carbon::yesterday())],
            'month', 'custom' => [$this->monthBuckets(Carbon::today()), $this->monthBuckets(Carbon::today()->subMonthNoOverflow())],
            'year' => [$this->yearBuckets(Carbon::today()), $this->yearBuckets(Carbon::today()->subYear())],
            default => [$this->weekBuckets(Carbon::today()), $this->weekBuckets(Carbon::today()->subWeek())],
        };

        $current = $this->bucketData($orders, $currentBuckets);
        $previous = $this->bucketData($orders, $previousBuckets);
        $currentRevenue = round((float) collect($current)->sum('revenue'), 2);
        $previousRevenue = round((float) collect($previous)->sum('revenue'), 2);
        $change = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100 : 0);

        return [
            'labels' => collect($currentBuckets)->pluck('label')->values(),
            'current' => collect($current)->pluck('revenue')->values(),
            'previous' => collect($previous)->pluck('revenue')->values(),
            'meta' => $current,
            'previous_meta' => $previous,
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'change' => $change,
            'has_sales' => $currentRevenue > 0 || $previousRevenue > 0,
        ];
    }

    private function bucketData(Collection $orders, array $buckets): array
    {
        return collect($buckets)->map(function (array $bucket) use ($orders): array {
            $bucketOrders = $orders->filter(
                fn (Order $order) => $order->created_at->betweenIncluded($bucket['start'], $bucket['end']),
            );
            $revenue = round((float) $bucketOrders->sum('total'), 2);
            $count = $bucketOrders->count();

            return [
                'label' => $bucket['label'],
                'date' => $bucket['display'],
                'revenue' => $revenue,
                'orders' => $count,
                'average_order' => $count > 0 ? round($revenue / $count, 2) : 0,
            ];
        })->values()->all();
    }

    private function weekBuckets(Carbon $date): array
    {
        $start = $date->copy()->startOfWeek();

        return collect(range(0, 6))->map(fn (int $day): array => [
            'label' => $start->copy()->addDays($day)->format('D'),
            'display' => $start->copy()->addDays($day)->format('M j'),
            'start' => $start->copy()->addDays($day)->startOfDay(),
            'end' => $start->copy()->addDays($day)->endOfDay(),
        ])->all();
    }

    private function monthBuckets(Carbon $date): array
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return collect(range(0, 3))->map(function (int $week) use ($start, $end): array {
            $bucketStart = $start->copy()->addDays($week * 7)->startOfDay();
            $bucketEnd = $week === 3 ? $end->copy()->endOfDay() : $bucketStart->copy()->addDays(6)->endOfDay();

            return [
                'label' => 'Week '.($week + 1),
                'display' => $bucketStart->format('M j').' - '.$bucketEnd->format('M j'),
                'start' => $bucketStart,
                'end' => $bucketEnd,
            ];
        })->all();
    }

    private function yearBuckets(Carbon $date): array
    {
        $start = $date->copy()->startOfYear();

        return collect(range(0, 11))->map(fn (int $month): array => [
            'label' => $start->copy()->addMonths($month)->format('M'),
            'display' => $start->copy()->addMonths($month)->format('F Y'),
            'start' => $start->copy()->addMonths($month)->startOfMonth(),
            'end' => $start->copy()->addMonths($month)->endOfMonth(),
        ])->all();
    }

    private function todayBuckets(?Carbon $date = null): array
    {
        $start = ($date ?? Carbon::today())->copy()->startOfDay();

        return collect(range(0, 5))->map(function (int $slot) use ($start): array {
            $bucketStart = $start->copy()->addHours($slot * 4);
            $bucketEnd = $bucketStart->copy()->addHours(3)->addMinutes(59)->addSeconds(59);

            return [
                'label' => $bucketStart->format('H:00'),
                'display' => $bucketStart->format('M j, H:00'),
                'start' => $bucketStart,
                'end' => $bucketEnd,
            ];
        })->all();
    }
}
