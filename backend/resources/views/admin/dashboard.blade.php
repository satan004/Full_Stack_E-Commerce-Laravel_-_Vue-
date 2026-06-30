@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $currency = fn ($value) => '$' . number_format((float) $value, 2);
        $statusTone = fn ($status) => match ($status) {
            'completed' => 'badge-success',
            'pending' => 'badge-warning',
            'cancelled' => 'badge-danger',
            default => 'badge-neutral',
        };

        $trend = $charts['trend'];
        $categoryMax = max(1, (int) collect($charts['categories'])->max('products'));
        $topProductMax = max(1, (int) $topProducts->max('quantity_sold'));
        $categoryRevenueMax = max(1, (float) collect($charts['categoryRevenue'])->max('revenue'));
        $stockTotal = max(1, (int) collect($charts['stockHealth'])->sum());
        $healthyPct = round(($charts['stockHealth']['healthy'] ?? 0) / $stockTotal * 100, 1);
        $lowPct = round(($charts['stockHealth']['low'] ?? 0) / $stockTotal * 100, 1);
        $outPct = max(0, round(100 - $healthyPct - $lowPct, 1));
        $trendChange = (float) ($trend['change'] ?? 0);
    @endphp

    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Store overview</p>
            <h1>Dashboard</h1>
            <p>Live totals from products, orders, customers, and inventory.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="btn btn-muted" href="{{ route('admin.products.index') }}">Manage products</a>
            <a class="btn btn-primary" href="{{ route('admin.orders.index') }}">View orders</a>
        </div>
    </div>

    <section class="stats-grid">
        <article class="stat-card warning">
            <div class="stat-card-head">
                <span>Total Products</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg>
                </span>
            </div>
            <strong><span class="counter" data-target="{{ $stats['products'] }}" data-decimals="0">0</span></strong>
            <span class="delta"><span class="counter" data-target="{{ $stats['active_products'] }}" data-decimals="0">0</span> active products</span>
        </article>

        <article class="stat-card primary">
            <div class="stat-card-head">
                <span>Total Orders</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 4h14l-1.5 12.5a2 2 0 0 1-2 1.7H8.5a2 2 0 0 1-2-1.7L5 4z"/><path d="M9 8v0M15 8v0"/></svg>
                </span>
            </div>
            <strong><span class="counter" data-target="{{ $stats['orders'] }}" data-decimals="0">0</span></strong>
            <span class="delta"><span class="counter" data-target="{{ $stats['average_order'] }}" data-prefix="$" data-decimals="2">$0.00</span> average order</span>
        </article>

        <article class="stat-card success">
            <div class="stat-card-head">
                <span>Total Users</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                </span>
            </div>
            <strong><span class="counter" data-target="{{ $stats['users'] }}" data-decimals="0">0</span></strong>
            <span class="delta">Registered customers</span>
        </article>

        <article class="stat-card secondary">
            <div class="stat-card-head">
                <span>Low Stock</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                </span>
            </div>
            <strong><span class="counter" data-target="{{ $stats['low_stock'] }}" data-decimals="0">0</span></strong>
            <span class="delta">Products at 5 or fewer</span>
        </article>
    </section>

    <section class="stats-grid">
        <article class="stat-card primary">
            <div class="stat-card-head"><span>Total Revenue</span></div>
            <strong><span class="counter" data-target="{{ $stats['revenue'] }}" data-prefix="$" data-decimals="2">$0.00</span></strong>
            <span class="delta"><span class="counter" data-target="{{ $stats['period_revenue'] }}" data-prefix="$" data-decimals="2">$0.00</span> in {{ strtolower($periodLabel) }}</span>
        </article>
        <article class="stat-card success">
            <div class="stat-card-head"><span>Categories</span></div>
            <strong><span class="counter" data-target="{{ $stats['categories'] }}" data-decimals="0">0</span></strong>
            <span class="delta">Product groups</span>
        </article>
        <article class="stat-card warning">
            <div class="stat-card-head"><span>Pending Orders</span></div>
            <strong><span class="counter" data-target="{{ $charts['statuses']['pending'] ?? 0 }}" data-decimals="0">0</span></strong>
            <span class="delta">Need attention</span>
        </article>
        <article class="stat-card secondary">
            <div class="stat-card-head"><span>Completed Orders</span></div>
            <strong><span class="counter" data-target="{{ $charts['statuses']['completed'] ?? 0 }}" data-decimals="0">0</span></strong>
            <span class="delta">Fulfilled sales</span>
        </article>
    </section>

    <section id="reports" class="two-column">
        <div
            class="sales-trend-card"
            data-sales-trend
            data-has-sales="{{ $trend['has_sales'] ? '1' : '0' }}"
            data-labels='@json($trend['labels'])'
            data-current='@json($trend['current'])'
            data-previous='@json($trend['previous'])'
            data-meta='@json($trend['meta'])'
        >
            <div class="sales-trend-top">
                <div>
                    <h2>Sales Trend</h2>
                    <p>Track revenue over time</p>
                </div>

                <div class="sales-trend-revenue">
                    <span>Current Revenue</span>
                    <strong><span class="counter" data-target="{{ $trend['current_revenue'] }}" data-prefix="$" data-decimals="2">$0.00</span></strong>
                    <small class="{{ $trendChange >= 0 ? 'positive' : 'negative' }}">
                        <span class="counter" data-target="{{ abs($trendChange) }}" data-suffix="%" data-decimals="1">0%</span> vs previous {{ $period === 'week' ? 'week' : $period }}
                    </small>
                </div>
            </div>

            <div class="sales-trend-controls">
                <div class="sales-trend-tabs" aria-label="Sales trend period">
                    @foreach (['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'custom' => 'Custom'] as $value => $label)
                        <a class="{{ $period === $value ? 'active' : '' }}" href="{{ route('admin.dashboard', ['period' => $value]) }}">{{ $label }}</a>
                    @endforeach
                </div>
                <select class="sales-trend-select" aria-label="Sales trend period">
                    @foreach (['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'custom' => 'Custom'] as $value => $label)
                        <option value="{{ route('admin.dashboard', ['period' => $value]) }}" @selected($period === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="sales-trend-compare">
                    <input type="checkbox" checked data-compare-sales>
                    <span>Compare Previous {{ $period === 'week' ? 'Week' : ucfirst($period) }}</span>
                </label>
            </div>

            <div class="sales-trend-chart-wrap">
                <div class="sales-trend-skeleton" data-sales-skeleton>
                    <span></span><span></span><span></span>
                </div>
                <div class="sales-trend-empty" data-sales-empty>
                    <strong>📈</strong>
                    <p>No sales data yet</p>
                </div>
                <div id="salesTrendChart" class="sales-trend-chart"></div>
            </div>
        </div>

        <div id="order-status-app" data-endpoint="{{ route('admin.analytics.order-status') }}" v-cloak>
            <order-status-card></order-status-card>
        </div>
    </section>

    <section style="margin-top:1.25rem;" class="two-column">
        <div id="products-category-app" data-endpoint="{{ route('admin.analytics.products-by-category') }}" data-categories-url="{{ route('admin.categories.index') }}" v-cloak>
            <products-by-category-card></products-by-category-card>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Popular Selling Products</h2>
                    <p>Top products by units sold in {{ strtolower($periodLabel) }}.</p>
                </div>
            </div>
            <div id="popular-products-app" data-products='@json($topProducts)' data-max-sold="{{ $topProductMax }}" v-cloak>
                <popular-products-chart></popular-products-chart>
            </div>
        </div>
    </section>

    <section style="margin-top:1.25rem;" class="two-column">
        <div id="revenue-category-app" data-endpoint="{{ route('admin.analytics.revenue-by-category', ['period' => $period]) }}" data-details-url="{{ route('admin.orders.index') }}" v-cloak>
            <revenue-by-category-card></revenue-by-category-card>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Inventory Health</h2>
                    <p>Healthy, low-stock, and out-of-stock products.</p>
                </div>
                <a class="btn btn-muted" href="{{ route('admin.products.index') }}">Inventory</a>
            </div>

            <div style="display:grid;grid-template-columns:190px 1fr;gap:1.25rem;align-items:center;">
                <div style="width:180px;height:180px;border-radius:50%;background:conic-gradient(#21c178 0 {{ $healthyPct }}%, #ff9f43 {{ $healthyPct }}% {{ $healthyPct + $lowPct }}%, #ef4444 {{ $healthyPct + $lowPct }}% 100%);display:grid;place-items:center;box-shadow:var(--shadow-sm);">
                    <div style="width:96px;height:96px;border-radius:50%;background:#fff;display:grid;place-items:center;text-align:center;">
                        <strong>{{ number_format($stockTotal) }}</strong>
                        <small style="color:var(--muted);font-weight:600;">products</small>
                    </div>
                </div>
                <div style="display:grid;gap:0.8rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;"><span style="display:inline-flex;align-items:center;gap:.55rem;font-weight:600;"><span style="width:10px;height:10px;border-radius:50%;background:#21c178;"></span>Healthy</span><strong>{{ number_format($charts['stockHealth']['healthy'] ?? 0) }}</strong></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;"><span style="display:inline-flex;align-items:center;gap:.55rem;font-weight:600;"><span style="width:10px;height:10px;border-radius:50%;background:#ff9f43;"></span>Low stock</span><strong>{{ number_format($charts['stockHealth']['low'] ?? 0) }}</strong></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;"><span style="display:inline-flex;align-items:center;gap:.55rem;font-weight:600;"><span style="width:10px;height:10px;border-radius:50%;background:#ef4444;"></span>Out of stock</span><strong>{{ number_format($charts['stockHealth']['out'] ?? 0) }}</strong></div>
                </div>
            </div>
        </div>
    </section>

    <section style="margin-top: 1.25rem;" class="two-column">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Recent Orders</h2>
                    <p>The latest customer orders placed in your store.</p>
                </div>
                <a class="btn btn-muted" href="{{ route('admin.orders.index') }}">View all</a>
            </div>
            @if($recentOrders->count() > 0)
                <div style="padding: 0 24px 12px; font-size: 0.82rem; color: var(--muted); font-weight: 600;">
                    Showing {{ $recentOrders->count() }} of {{ $recentOrdersTotal }} orders
                </div>
            @endif
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--primary); font-weight: 600;">#{{ $order->id }}</a></td>
                            <td>
                                <div style="display: grid;">
                                    <strong style="color: var(--ink); font-weight: 600;">{{ $order->user->name }}</strong>
                                    <small style="color: var(--muted);">{{ $order->user->email }}</small>
                                </div>
                            </td>
                            <td><span class="badge {{ $statusTone($order->status) }}">{{ $order->status }}</span></td>
                            <td style="text-align: right; font-weight: 600;">{{ $currency($order->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; color: var(--muted); padding: 2rem;">No recent orders</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Low Stock Products</h2>
                    <p>Products with 5 or fewer items remaining.</p>
                </div>
                <a class="btn btn-muted" href="{{ route('admin.products.index') }}">Manage</a>
            </div>
            @if($lowStockProducts->count() > 0)
                <div style="padding: 0 24px 12px; font-size: 0.82rem; color: var(--muted); font-weight: 600;">
                    Showing {{ $lowStockProducts->count() }} of {{ $lowStockProductsTotal }} low-stock products
                </div>
            @endif
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th style="text-align: right;">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lowStockProducts as $product)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    @endif
                                    <span class="name">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td style="text-align: right;">
                                <span class="badge {{ $product->stock <= 2 ? 'badge-danger' : 'badge-warning' }}">{{ $product->stock }} left</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: var(--muted); padding: 2rem;">No low-stock products</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .sales-trend-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 360px;
            background: #fff;
            border: 1px solid #e6ebf2;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            padding: 24px;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .sales-trend-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.09);
        }

        .sales-trend-top,
        .sales-trend-controls {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .sales-trend-top h2 {
            margin: 0;
            font-size: 1.15rem;
            color: #1F2937;
        }

        .sales-trend-top p,
        .sales-trend-revenue span {
            margin: 0.3rem 0 0;
            color: #5b6472;
            font-size: 0.88rem;
        }

        .sales-trend-revenue {
            display: grid;
            justify-items: end;
            gap: 0.2rem;
            text-align: right;
        }

        .sales-trend-revenue strong {
            color: #1F2937;
            font-size: 1.65rem;
            line-height: 1;
        }

        .sales-trend-revenue small {
            font-weight: 700;
        }

        .sales-trend-revenue .positive {
            color: #13794c;
        }

        .sales-trend-revenue .negative {
            color: #bd3030;
        }

        .sales-trend-controls {
            align-items: center;
            margin-top: 1.25rem;
            flex-wrap: wrap;
        }

        .sales-trend-tabs {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .sales-trend-tabs a {
            display: inline-flex;
            align-items: center;
            min-height: 2.15rem;
            padding: 0 0.8rem;
            border: 1px solid #e6ebf2;
            border-radius: 10px;
            background: #fff;
            color: #5b6472;
            font-size: 0.82rem;
            font-weight: 700;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .sales-trend-tabs a.active {
            background: #2bbef9;
            border-color: #2bbef9;
            color: #fff;
        }

        .sales-trend-select {
            display: none;
            width: 100%;
            max-width: 220px;
        }

        .sales-trend-compare {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #1F2937;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .sales-trend-compare input {
            width: 16px;
            height: 16px;
            accent-color: #2bbef9;
        }

        .sales-trend-chart-wrap {
            position: relative;
            flex: 1;
            min-height: 250px;
            margin-top: 0.75rem;
        }

        .sales-trend-chart {
            height: 100%;
            min-height: 250px;
        }

        .sales-trend-skeleton {
            position: absolute;
            inset: 0;
            display: grid;
            gap: 0.85rem;
            align-content: center;
            z-index: 2;
            background: #fff;
        }

        .sales-trend-skeleton span {
            height: 54px;
            border-radius: 14px;
            background: linear-gradient(90deg, #eef2f7 25%, #e8f7fd 50%, #eef2f7 75%);
            background-size: 220% 100%;
            animation: sales-shimmer 1.1s infinite;
        }

        .sales-trend-skeleton span:nth-child(2) {
            width: 84%;
        }

        .sales-trend-skeleton span:nth-child(3) {
            width: 68%;
        }

        @keyframes sales-shimmer {
            from { background-position: 100% 0; }
            to { background-position: -100% 0; }
        }

        .sales-trend-empty {
            position: absolute;
            inset: 0;
            display: none;
            place-items: center;
            align-content: center;
            gap: 0.65rem;
            color: #5b6472;
            text-align: center;
            background: #fff;
            z-index: 1;
        }

        .sales-trend-empty strong {
            font-size: 2rem;
        }

        .sales-trend-empty p {
            margin: 0;
            font-weight: 800;
            color: #1F2937;
        }

        .sales-tooltip {
            min-width: 190px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(17, 24, 39, 0.16);
            padding: 0.85rem;
            border: 1px solid #eef2f7;
        }

        .sales-tooltip .date {
            color: #5b6472;
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 0.55rem;
        }

        .sales-tooltip .row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            color: #1F2937;
            font-size: 0.82rem;
            padding: 0.18rem 0;
        }

        .sales-tooltip .row strong {
            color: #1F2937;
        }

        .order-status-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 360px;
            background: #fff;
            border: 1px solid #e6ebf2;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            padding: 24px;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            align-items: stretch;
        }

        .two-column > * {
            display: flex;
            flex-direction: column;
        }

        .order-status-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .order-status-header h2 {
            margin: 0;
            color: #1F2937;
            font-size: 1.15rem;
        }

        .order-status-header p {
            margin: 0.25rem 0 0;
            color: #5b6472;
            font-size: 0.88rem;
        }

        .order-status-layout {
            display: grid;
            grid-template-columns: minmax(220px, 280px) 1fr;
            gap: 1.5rem;
            align-items: center;
            flex: 1;
        }

        .order-status-chart {
            min-height: 220px;
        }

        .order-status-legend {
            display: grid;
            gap: 0.75rem;
        }

        .order-status-legend button {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 0.7rem;
            width: 100%;
            border: 1px solid #e6ebf2;
            border-radius: 14px;
            background: #fff;
            padding: 0.75rem 0.85rem;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease, opacity 0.15s ease;
        }

        .order-status-legend button:hover {
            background: #f7f9fc;
            border-color: #cfd8e3;
        }

        .order-status-legend button.muted {
            color: #8a93a3;
            opacity: 0.62;
        }

        .order-status-dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
        }

        .order-status-name {
            display: grid;
            gap: 0.1rem;
            text-align: left;
        }

        .order-status-name strong {
            color: #1F2937;
            font-size: 0.9rem;
        }

        .order-status-legend button.muted .order-status-name strong {
            color: #8a93a3;
        }

        .order-status-name span,
        .order-status-percent {
            color: #5b6472;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .order-status-empty {
            display: grid;
            min-height: 260px;
            place-items: center;
            text-align: center;
            color: #5b6472;
            font-weight: 800;
        }

        .order-status-empty p {
            margin: 0;
        }

        .order-status-tooltip {
            min-width: 150px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(17, 24, 39, 0.16);
            padding: 0.85rem;
            border: 1px solid #eef2f7;
        }

        .order-status-tooltip strong {
            display: block;
            color: #1F2937;
            margin-bottom: 0.4rem;
        }

        .order-status-tooltip span {
            display: block;
            color: #5b6472;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .popular-products-chart {
            min-height: 300px;
        }

        .popular-products-empty {
            display: grid;
            min-height: 260px;
            place-items: center;
            color: #5b6472;
            font-weight: 800;
        }

        .popular-product-tooltip {
            min-width: 200px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(17, 24, 39, 0.16);
            padding: 0.85rem;
            border: 1px solid #eef2f7;
        }

        .popular-product-tooltip strong {
            display: block;
            color: #1F2937;
            margin-bottom: 0.4rem;
            font-size: 0.95rem;
        }

        .popular-product-tooltip span {
            display: block;
            color: #5b6472;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.55;
        }

        .popular-product-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        .category-analytics-card {
            background: #fff;
            border: 1px solid #e6ebf2;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            padding: 24px;
        }

        .category-analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.9rem;
        }

        .category-analytics-header h2 {
            margin: 0;
            color: #1F2937;
            font-size: 1.15rem;
        }

        .category-analytics-header p {
            margin: 0.25rem 0 0;
            color: #5b6472;
            font-size: 0.88rem;
        }

        .category-summary {
            color: #5b6472;
            font-size: 0.84rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .category-chart-container {
            min-height: 280px;
        }

        .category-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
        }

        .category-skeleton {
            display: grid;
            gap: 0.85rem;
        }

        .category-skeleton span {
            height: 18px;
            border-radius: 10px;
            background: linear-gradient(90deg, #eef2f7 25%, #e8f7fd 50%, #eef2f7 75%);
            background-size: 220% 100%;
            animation: sales-shimmer 1.1s infinite;
        }

        .category-empty {
            display: grid;
            min-height: 230px;
            place-items: center;
            align-content: center;
            gap: 0.5rem;
            color: #5b6472;
            text-align: center;
        }

        .category-empty p {
            margin: 0;
            color: #1F2937;
            font-weight: 800;
        }

        .category-tooltip {
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(17, 24, 39, 0.16);
            padding: 0.75rem;
            border: 1px solid #eef2f7;
        }

        .category-tooltip strong {
            display: block;
            color: #1F2937;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .category-tooltip span {
            display: block;
            color: #5b6472;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.55;
        }

        .revenue-list-card {
            background: #fff;
            border: 1px solid #e6ebf2;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            padding: 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .revenue-list-card .revenue-list {
            flex: 1;
        }

        .revenue-list-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .revenue-list-header h2 {
            margin: 0;
            color: #1F2937;
            font-size: 1.15rem;
        }

        .revenue-list-header p {
            margin: 0.25rem 0 0;
            color: #5b6472;
            font-size: 0.88rem;
        }

        .revenue-list-summary {
            color: #5b6472;
            font-size: 0.84rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e6ebf2;
        }

        .revenue-list {
            display: grid;
            gap: 1.25rem;
        }

        .revenue-list-item {
            display: grid;
            gap: 0.5rem;
        }

        .revenue-list-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .revenue-list-item-name {
            color: #1F2937;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .revenue-list-item-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .revenue-list-item-amount {
            color: #1F2937;
            font-size: 0.9rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .revenue-list-item-percent {
            color: #5b6472;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
            min-width: 42px;
            text-align: right;
        }

        .revenue-list-bar-track {
            width: 100%;
            height: 10px;
            background: #eef2f7;
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .revenue-list-bar-fill {
            height: 100%;
            background: #2BBEF9;
            border-radius: 999px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .revenue-list-skeleton {
            display: grid;
            gap: 1.25rem;
        }

        .revenue-list-skeleton-item {
            display: grid;
            gap: 0.5rem;
        }

        .revenue-list-skeleton-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .revenue-list-skeleton-line {
            height: 14px;
            border-radius: 6px;
            background: linear-gradient(90deg, #eef2f7 25%, #e8f7fd 50%, #eef2f7 75%);
            background-size: 220% 100%;
            animation: sales-shimmer 1.1s infinite;
        }

        .revenue-list-skeleton-line:first-child {
            width: 35%;
        }

        .revenue-list-skeleton-line:nth-child(2) {
            width: 25%;
        }

        .revenue-list-skeleton-bar {
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(90deg, #eef2f7 25%, #e8f7fd 50%, #eef2f7 75%);
            background-size: 220% 100%;
            animation: sales-shimmer 1.1s infinite;
        }

        .revenue-list-empty {
            display: grid;
            min-height: 200px;
            place-items: center;
            color: #5b6472;
            font-weight: 800;
            text-align: center;
        }

        .revenue-list-empty p {
            margin: 0;
        }

        .revenue-list-tooltip {
            min-width: 180px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(17, 24, 39, 0.16);
            padding: 0.75rem;
            border: 1px solid #eef2f7;
        }

        .revenue-list-tooltip strong {
            display: block;
            color: #1F2937;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .revenue-list-tooltip span {
            display: block;
            color: #5b6472;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.55;
        }

        @media (max-width: 980px) {
            .sales-trend-chart-wrap,
            .sales-trend-chart {
                min-height: 320px;
            }

            .revenue-treemap-chart,
            .revenue-treemap-empty,
            .revenue-treemap-skeleton {
                min-height: 260px;
            }

            .two-column {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .sales-trend-card {
                padding: 18px;
            }

            .sales-trend-top {
                display: grid;
            }

            .sales-trend-revenue {
                justify-items: start;
                text-align: left;
            }

            .sales-trend-tabs {
                display: none;
            }

            .sales-trend-select {
                display: block;
            }

            .order-status-layout {
                grid-template-columns: 1fr;
            }

            .order-status-card {
                padding: 18px;
            }

            .popular-products-chart {
                grid-auto-columns: minmax(56px, 68px);
            }

            .category-analytics-card {
                padding: 18px;
            }

            .category-bar-head {
                display: grid;
                gap: 0.25rem;
            }

            .category-bar-meta {
                white-space: normal;
            }

        .revenue-list-card {
            padding: 18px;
        }

        .revenue-list-item-name {
            font-size: 0.85rem;
        }

        .revenue-list-item-amount {
            font-size: 0.85rem;
        }

        .revenue-list-item-percent {
            font-size: 0.78rem;
            min-width: 38px;
        }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script>
        // Animated Counter Function
        function animateCounter(element) {
            const target = parseFloat(element.dataset.target) || 0;
            const prefix = element.dataset.prefix || '';
            const suffix = element.dataset.suffix || '';
            const decimals = parseInt(element.dataset.decimals) || 0;
            const duration = 1200;
            
            if (target === 0) {
                element.textContent = prefix + target.toFixed(decimals) + suffix;
                return;
            }
            
            const startTime = performance.now();
            const startValue = 0;
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function (ease-out)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = startValue + (target - startValue) * easeOut;
                
                element.textContent = prefix + current.toFixed(decimals) + suffix;
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    element.textContent = prefix + target.toFixed(decimals) + suffix;
                }
            }
            
            requestAnimationFrame(update);
        }
        
        // Initialize all counters when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.counter');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            if (prefersReducedMotion) {
                // If user prefers reduced motion, just show final values
                counters.forEach(counter => {
                    const target = parseFloat(counter.dataset.target) || 0;
                    const prefix = counter.dataset.prefix || '';
                    const suffix = counter.dataset.suffix || '';
                    const decimals = parseInt(counter.dataset.decimals) || 0;
                    counter.textContent = prefix + target.toFixed(decimals) + suffix;
                });
            } else {
                // Animate counters with slight delay for visual effect
                counters.forEach((counter, index) => {
                    setTimeout(() => {
                        animateCounter(counter);
                    }, index * 50); // Stagger animations by 50ms
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.querySelector('[data-sales-trend]');
            if (!card || typeof ApexCharts === 'undefined') return;

            const labels = JSON.parse(card.dataset.labels || '[]');
            const current = JSON.parse(card.dataset.current || '[]');
            const previous = JSON.parse(card.dataset.previous || '[]');
            const meta = JSON.parse(card.dataset.meta || '[]');
            const hasSales = card.dataset.hasSales === '1';
            const skeleton = card.querySelector('[data-sales-skeleton]');
            const empty = card.querySelector('[data-sales-empty]');
            const compare = card.querySelector('[data-compare-sales]');
            const select = card.querySelector('.sales-trend-select');
            const chartEl = document.querySelector('#salesTrendChart');

            if (select) {
                select.addEventListener('change', () => {
                    window.location.href = select.value;
                });
            }

            if (!hasSales) {
                if (skeleton) skeleton.style.display = 'none';
                if (empty) empty.style.display = 'grid';
                return;
            }

            const money = (value) => '$' + Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const series = () => [
                {
                    name: 'Current Revenue',
                    data: current,
                },
                {
                    name: 'Previous Revenue',
                    data: compare?.checked ? previous : [],
                },
            ];

            const chart = new ApexCharts(chartEl, {
                chart: {
                    type: 'area',
                    height: 240,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 850,
                        animateGradually: { enabled: true, delay: 120 },
                        dynamicAnimation: { enabled: true, speed: 350 },
                    },
                    fontFamily: 'Inter, ui-sans-serif, system-ui',
                },
                series: series(),
                xaxis: {
                    categories: labels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#5B6472', fontSize: '12px', fontWeight: 700 },
                    },
                    tooltip: { enabled: false },
                },
                yaxis: {
                    min: 0,
                    tickAmount: 4,
                    labels: {
                        formatter: (value) => '$' + Math.round(value),
                        style: { colors: '#5B6472', fontSize: '12px', fontWeight: 700 },
                    },
                },
                stroke: {
                    curve: 'smooth',
                    width: [4, 3],
                    colors: ['#2BBEF9', '#8A93A3'],
                    dashArray: [0, 8],
                    lineCap: 'round',
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.28,
                        opacityTo: 0.02,
                        stops: [0, 90, 100],
                    },
                },
                colors: ['#2BBEF9', '#8A93A3'],
                markers: {
                    size: 0,
                    colors: ['#FFFFFF'],
                    hover: { size: 7 },
                    strokeColors: '#2BBEF9',
                    strokeWidth: 3,
                    fillOpacity: 1,
                },
                grid: {
                    borderColor: '#E6EBF2',
                    strokeDashArray: 0,
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: true } },
                    padding: { left: 8, right: 18 },
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: {
                    shared: false,
                    custom: ({ dataPointIndex }) => {
                        const point = meta[dataPointIndex] || {};
                        return `
                            <div class="sales-tooltip">
                                <div class="date">${point.date || labels[dataPointIndex] || ''}</div>
                                <div class="row"><span>Revenue</span><strong>${money(point.revenue)}</strong></div>
                                <div class="row"><span>Orders</span><strong>${point.orders || 0}</strong></div>
                                <div class="row"><span>Average Order</span><strong>${money(point.average_order)}</strong></div>
                            </div>
                        `;
                    },
                },
                responsive: [
                    {
                        breakpoint: 980,
                        options: { chart: { height: 230 } },
                    },
                    {
                        breakpoint: 640,
                        options: { chart: { height: 220 }, stroke: { width: [3, 2] } },
                    },
                ],
            });

            chart.render().then(() => {
                if (skeleton) skeleton.style.display = 'none';
            });

            compare?.addEventListener('change', () => {
                chart.updateSeries(series(), true);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mount = document.querySelector('#order-status-app');
            if (!mount || typeof Vue === 'undefined' || typeof ApexCharts === 'undefined') return;

            const OrderStatusCard = {
                template: `
                    <section class="order-status-card">
                        <div class="order-status-header">
                            <div>
                                <h2>Order Status</h2>
                                <p>Completed, pending, and cancelled orders.</p>
                            </div>
                        </div>

                        <div v-if="loading" class="order-status-empty">
                            <p>Loading order status...</p>
                        </div>

                        <div v-else-if="totalAll === 0" class="order-status-empty">
                            <p>No order data available</p>
                        </div>

                        <div v-else class="order-status-layout">
                            <div>
                                <div v-if="visibleTotal === 0" class="order-status-empty">
                                    <p>No visible order data</p>
                                </div>
                                <div v-else ref="chartEl" class="order-status-chart"></div>
                            </div>
                            <div class="order-status-legend">
                                <button
                                    v-for="item in statuses"
                                    :key="item.key"
                                    type="button"
                                    :class="{ muted: !visible[item.key] }"
                                    @click="toggle(item.key)"
                                >
                                    <span class="order-status-dot" :style="{ background: visible[item.key] ? item.color : '#8A93A3' }"></span>
                                    <span class="order-status-name">
                                        <strong v-text="item.label"></strong>
                                        <span v-text="counts[item.key] + ' orders'"></span>
                                    </span>
                                    <span class="order-status-percent" v-text="percent(item.key) + '%'"></span>
                                </button>
                            </div>
                        </div>
                    </section>
                `,
                data() {
                    return {
                        loading: true,
                        chart: null,
                        counts: { completed: 0, pending: 0, cancelled: 0 },
                        visible: { completed: true, pending: true, cancelled: true },
                        statuses: [
                            { key: 'completed', label: 'Completed', color: '#21C178' },
                            { key: 'pending', label: 'Pending', color: '#FF9F43' },
                            { key: 'cancelled', label: 'Cancelled', color: '#EF4444' },
                        ],
                    };
                },
                computed: {
                    totalAll() {
                        return this.statuses.reduce((sum, item) => sum + Number(this.counts[item.key] || 0), 0);
                    },
                    visibleItems() {
                        return this.statuses.filter((item) => this.visible[item.key]);
                    },
                    visibleSeries() {
                        return this.visibleItems.map((item) => Number(this.counts[item.key] || 0));
                    },
                    visibleLabels() {
                        return this.visibleItems.map((item) => item.label);
                    },
                    visibleColors() {
                        return this.visibleItems.map((item) => item.color);
                    },
                    visibleTotal() {
                        return this.visibleSeries.reduce((sum, value) => sum + value, 0);
                    },
                },
                async mounted() {
                    await this.fetchData();
                    this.loading = false;
                    this.$nextTick(() => this.renderChart());
                },
                methods: {
                    async fetchData() {
                        const response = await fetch(mount.dataset.endpoint, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await response.json();
                        this.counts = {
                            completed: Number(data.completed || 0),
                            pending: Number(data.pending || 0),
                            cancelled: Number(data.cancelled || 0),
                        };
                    },
                    percent(key) {
                        if (!this.visible[key] || this.visibleTotal === 0) return 0;
                        return Math.round((Number(this.counts[key] || 0) / this.visibleTotal) * 100);
                    },
                    toggle(key) {
                        this.visible[key] = !this.visible[key];
                        this.$nextTick(() => this.updateChart());
                    },
                    renderChart() {
                        if (!this.$refs.chartEl || this.visibleTotal === 0) return;

                        this.chart = new ApexCharts(this.$refs.chartEl, this.options());
                        this.chart.render();
                    },
                    updateChart() {
                        if (this.visibleTotal === 0) {
                            if (this.chart) {
                                this.chart.destroy();
                                this.chart = null;
                            }
                            return;
                        }

                        if (!this.chart) {
                            this.renderChart();
                            return;
                        }

                        this.chart.updateOptions({
                            labels: this.visibleLabels,
                            colors: this.visibleColors,
                        }, false, true);
                        this.chart.updateSeries(this.visibleSeries, true);
                    },
                    options() {
                        return {
                            chart: {
                                type: 'pie',
                                height: 230,
                                fontFamily: 'Inter, ui-sans-serif, system-ui',
                                animations: { enabled: true, speed: 650 },
                            },
                            series: this.visibleSeries,
                            labels: this.visibleLabels,
                            colors: this.visibleColors,
                            stroke: {
                                width: 4,
                                colors: ['#FFFFFF'],
                                lineCap: 'round',
                            },
                            legend: { show: false },
                            dataLabels: {
                                enabled: true,
                                formatter: (value) => Math.round(value) + '%',
                                style: {
                                    fontSize: '13px',
                                    fontWeight: 800,
                                    colors: ['#FFFFFF'],
                                },
                                dropShadow: { enabled: false },
                            },
                            tooltip: {
                                custom: ({ series, seriesIndex, w }) => {
                                    const label = w.globals.labels[seriesIndex] || '';
                                    const value = Number(series[seriesIndex] || 0);
                                    const percentage = this.visibleTotal > 0 ? Math.round((value / this.visibleTotal) * 100) : 0;
                                    return `
                                        <div class="order-status-tooltip">
                                            <strong>${label}</strong>
                                            <span>${value} orders</span>
                                            <span>${percentage}%</span>
                                        </div>
                                    `;
                                },
                            },
                            plotOptions: {
                                pie: {
                                    expandOnClick: false,
                                    dataLabels: {
                                        offset: -8,
                                        minAngleToShowLabel: 12,
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 640,
                                    options: {
                                        chart: { height: 220 },
                                    },
                                },
                            ],
                        };
                    },
                },
                beforeUnmount() {
                    if (this.chart) this.chart.destroy();
                },
            };

            Vue.createApp({ components: { OrderStatusCard } }).mount(mount);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mount = document.querySelector('#products-category-app');
            if (!mount || typeof Vue === 'undefined' || typeof ApexCharts === 'undefined') return;

            const ProductsByCategoryCard = {
                template: `
                    <section class="category-analytics-card">
                        <div class="category-analytics-header">
                            <div>
                                <h2>Products by Category</h2>
                                <p>Product distribution across categories.</p>
                            </div>
                            <a class="btn btn-muted" :href="categoriesUrl">View all</a>
                        </div>

                        <div v-if="loading" class="category-skeleton">
                            <span></span><span></span><span></span><span></span>
                        </div>

                        <div v-else-if="categories.length === 0" class="category-empty">
                            <p>No category data available</p>
                        </div>

                        <template v-else>
                            <div class="category-summary" v-text="categories.length + ' categories • ' + totalProducts + ' total products'"></div>
                            <div ref="chartEl" class="category-chart-container"></div>
                        </template>
                    </section>
                `,
                data() {
                    return {
                        loading: true,
                        chart: null,
                        categoriesUrl: mount.dataset.categoriesUrl,
                        categories: [],
                    };
                },
                computed: {
                    totalProducts() {
                        return this.categories.reduce((sum, category) => sum + Number(category.products || 0), 0);
                    },
                    maxProducts() {
                        return Math.max(1, ...this.categories.map((category) => Number(category.products || 0)));
                    },
                    chartSeries() {
                        return this.categories.map((category) => Number(category.products || 0));
                    },
                    chartCategories() {
                        return this.categories.map((category) => category.name);
                    },
                },
                async mounted() {
                    await this.fetchData();
                    this.loading = false;
                    this.$nextTick(() => this.renderChart());
                },
                methods: {
                    async fetchData() {
                        const response = await fetch(mount.dataset.endpoint, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await response.json();
                        this.categories = [...(data.categories || [])]
                            .map((category) => ({
                                name: category.name,
                                products: Number(category.products || 0),
                            }))
                            .sort((a, b) => b.products - a.products);
                    },
                    renderChart() {
                        if (!this.$refs.chartEl) return;

                        this.chart = new ApexCharts(this.$refs.chartEl, {
                            chart: {
                                type: 'bar',
                                height: 280,
                                toolbar: { show: false },
                                animations: {
                                    enabled: true,
                                    speed: 900,
                                    easing: 'easeinout',
                                    animateGradually: { enabled: true, delay: 150 },
                                    dynamicAnimation: { enabled: true, speed: 500 },
                                },
                                fontFamily: 'Inter, ui-sans-serif, system-ui',
                            },
                            series: [{
                                name: 'Products',
                                data: this.chartSeries,
                            }],
                            xaxis: {
                                categories: this.chartCategories,
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: '#5B6472',
                                        fontSize: '12px',
                                        fontWeight: 600,
                                        cssClass: 'category-label',
                                    },
                                    rotate: -30,
                                    rotateAlways: false,
                                    maxHeight: 60,
                                },
                            },
                            yaxis: {
                                min: 0,
                                tickAmount: 4,
                                forceNiceScale: true,
                                labels: {
                                    style: {
                                        colors: '#5B6472',
                                        fontSize: '12px',
                                        fontWeight: 600,
                                    },
                                    formatter: (value) => Math.round(value).toString(),
                                },
                            },
                            colors: ['#2BBEF9'],
                            fill: {
                                type: 'solid',
                                opacity: 1,
                            },
                            stroke: {
                                show: true,
                                width: 0,
                                colors: ['transparent'],
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 6,
                                    columnWidth: '35%',
                                    borderRadiusApplication: 'top',
                                },
                            },
                            dataLabels: {
                                enabled: false,
                            },
                            grid: {
                                borderColor: '#E6EBF2',
                                strokeDashArray: 0,
                                xaxis: { lines: { show: false } },
                                yaxis: {
                                    lines: { 
                                        show: true,
                                    },
                                },
                                padding: {
                                    left: 8,
                                    right: 8,
                                    top: 0,
                                    bottom: 0,
                                },
                            },
                            legend: {
                                show: false,
                            },
                            tooltip: {
                                custom: ({ seriesIndex, dataPointIndex, w }) => {
                                    const category = this.categories[dataPointIndex] || {};
                                    const products = Number(category.products || 0);
                                    const percentage = this.totalProducts > 0 
                                        ? Math.round((products / this.totalProducts) * 100) 
                                        : 0;

                                    return `
                                        <div class="category-tooltip">
                                            <strong>${category.name || 'Category'}</strong>
                                            <span>Products: ${products}</span>
                                            <span>Percentage: ${percentage}%</span>
                                        </div>
                                    `;
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 1024,
                                    options: {
                                        chart: { height: 280 },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '38%',
                                            },
                                        },
                                        xaxis: {
                                            labels: {
                                                fontSize: '11px',
                                                rotate: -30,
                                            },
                                        },
                                    },
                                },
                                {
                                    breakpoint: 980,
                                    options: {
                                        chart: { height: 260 },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '35%',
                                            },
                                        },
                                        xaxis: {
                                            labels: {
                                                fontSize: '11px',
                                                rotate: -30,
                                            },
                                        },
                                    },
                                },
                                {
                                    breakpoint: 768,
                                    options: {
                                        chart: { height: 250 },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '32%',
                                                borderRadius: 5,
                                            },
                                        },
                                        xaxis: {
                                            labels: {
                                                fontSize: '10px',
                                                rotate: -30,
                                            },
                                        },
                                    },
                                },
                                {
                                    breakpoint: 640,
                                    options: {
                                        chart: { height: 240 },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '30%',
                                                borderRadius: 5,
                                            },
                                        },
                                        xaxis: {
                                            labels: {
                                                fontSize: '9px',
                                                rotate: -30,
                                                maxHeight: 50,
                                            },
                                        },
                                    },
                                },
                            ],
                        });

                        this.chart.render();
                    },
                },
                beforeUnmount() {
                    if (this.chart) this.chart.destroy();
                },
            };

            Vue.createApp({ components: { ProductsByCategoryCard } }).mount(mount);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mount = document.querySelector('#popular-products-app');
            if (!mount || typeof Vue === 'undefined' || typeof ApexCharts === 'undefined') return;

            const PopularProductsChart = {
                template: `
                    <div v-if="loading" class="popular-products-empty">
                        <p>Loading popular products...</p>
                    </div>

                    <div v-else-if="products.length === 0" class="popular-products-empty">
                        <p>No sales data available.</p>
                    </div>

                    <div v-else ref="chartEl" class="popular-products-chart"></div>
                `,
                data() {
                    return {
                        loading: true,
                        chart: null,
                        products: [],
                        maxSold: parseInt(mount.dataset.maxSold) || 0,
                    };
                },
                computed: {
                    chartSeries() {
                        return this.products.map((product) => Number(product.quantity_sold || 0));
                    },
                    chartCategories() {
                        return this.products.map((product) => product.product_name);
                    },
                    chartRevenue() {
                        return this.products.map((product) => Number(product.sales_total || 0));
                    },
                },
                async mounted() {
                    await this.fetchData();
                    this.loading = false;
                    this.$nextTick(() => this.renderChart());
                },
                methods: {
                    async fetchData() {
                        const data = JSON.parse(mount.dataset.products || '[]');
                        this.products = data.map((product) => ({
                            product_name: product.product_name,
                            quantity_sold: Number(product.quantity_sold || 0),
                            sales_total: Number(product.sales_total || 0),
                        }));
                    },
                    money(value) {
                        return '$' + Number(value || 0).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },
                    renderChart() {
                        if (!this.$refs.chartEl || this.products.length === 0) return;

                        this.chart = new ApexCharts(this.$refs.chartEl, {
                            chart: {
                                type: 'bar',
                                height: 320,
                                toolbar: { show: false },
                                animations: {
                                    enabled: true,
                                    speed: 900,
                                    easing: 'easeinout',
                                    animateGradually: { enabled: true, delay: 120 },
                                    dynamicAnimation: { enabled: true, speed: 500 },
                                },
                                fontFamily: 'Inter, ui-sans-serif, system-ui',
                            },
                            series: [{
                                name: 'Units Sold',
                                data: this.chartSeries,
                            }],
                            xaxis: {
                                min: 0,
                                max: this.maxSold,
                                tickAmount: 4,
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: '#5B6472',
                                        fontSize: '12px',
                                        fontWeight: 600,
                                    },
                                    formatter: (value) => Math.round(value).toString(),
                                },
                            },
                            yaxis: {
                                categories: this.chartCategories,
                                labels: {
                                    style: {
                                        colors: '#1F2937',
                                        fontSize: '12px',
                                        fontWeight: 600,
                                    },
                                    maxWidth: 160,
                                },
                            },
                            colors: ['#2BBEF9'],
                            fill: {
                                type: 'solid',
                                opacity: 1,
                            },
                            stroke: {
                                show: true,
                                width: 0,
                                colors: ['transparent'],
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    columnWidth: '5%',
                                    borderRadiusApplication: 'end',
                                    horizontal: true,
                                },
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: (value) => Math.round(value).toString(),
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    colors: ['#1F2937'],
                                },
                                dropShadow: { enabled: false },
                            },
                            grid: {
                                borderColor: '#E6EBF2',
                                strokeDashArray: 0,
                                xaxis: { 
                                    lines: { 
                                        show: true,
                                    },
                                },
                                yaxis: { lines: { show: false } },
                                padding: {
                                    left: 8,
                                    right: 12,
                                    top: 12,
                                    bottom: 12,
                                },
                            },
                            legend: {
                                show: false,
                            },
                            tooltip: {
                                custom: ({ seriesIndex, dataPointIndex, w }) => {
                                    const product = this.products[dataPointIndex] || {};
                                    const unitsSold = Number(product.quantity_sold || 0);
                                    const revenue = Number(product.sales_total || 0);

                                    return `
                                        <div class="popular-product-tooltip">
                                            <strong>${product.product_name || 'Product'}</strong>
                                            <span>Units Sold: ${unitsSold}</span>
                                            <span>Revenue: ${this.money(revenue)}</span>
                                        </div>
                                    `;
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 1024,
                                        options: {
                                        chart: { height: 320 },
                                        xaxis: {
                                            labels: {
                                                fontSize: '11px',
                                            },
                                        },
                                        yaxis: {
                                            labels: {
                                                maxWidth: 140,
                                                style: {
                                                    fontSize: '11px',
                                                },
                                            },
                                        },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '4%',
                                            },
                                        },
                                    },
                                },
                                {
                                    breakpoint: 768,
                                        options: {
                                        chart: { height: 320 },
                                        xaxis: {
                                            labels: {
                                                fontSize: '10px',
                                            },
                                        },
                                        yaxis: {
                                            labels: {
                                                maxWidth: 120,
                                                style: {
                                                    fontSize: '10px',
                                                },
                                            },
                                        },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '3%',
                                                borderRadius: 3,
                                            },
                                        },
                                    },
                                },
                                {
                                    breakpoint: 640,
                                        options: {
                                        chart: { height: 320 },
                                        xaxis: {
                                            labels: {
                                                fontSize: '9px',
                                            },
                                        },
                                        yaxis: {
                                            labels: {
                                                maxWidth: 100,
                                                style: {
                                                    fontSize: '9px',
                                                },
                                            },
                                        },
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '2%',
                                                borderRadius: 3,
                                            },
                                        },
                                    },
                                },
                            ],
                        });

                        this.chart.render();
                    },
                },
                beforeUnmount() {
                    if (this.chart) this.chart.destroy();
                },
            };

            Vue.createApp({ components: { PopularProductsChart } }).mount(mount);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mount = document.querySelector('#revenue-category-app');
            if (!mount || typeof Vue === 'undefined') return;

            const RevenueByCategoryCard = {
                template: `
                    <section class="revenue-list-card">
                        <div class="revenue-list-header">
                            <div>
                                <h2>Revenue by Category</h2>
                                <p>Revenue contribution by product category.</p>
                            </div>
                            <a class="btn btn-muted" :href="detailsUrl">View Details →</a>
                        </div>

                        <div v-if="loading" class="revenue-list-skeleton">
                            <div class="revenue-list-skeleton-item" v-for="i in 4" :key="i">
                                <div class="revenue-list-skeleton-header">
                                    <div class="revenue-list-skeleton-line"></div>
                                    <div class="revenue-list-skeleton-line"></div>
                                </div>
                                <div class="revenue-list-skeleton-bar"></div>
                            </div>
                        </div>

                        <div v-else-if="categories.length === 0" class="revenue-list-empty">
                            <p>No revenue data available.</p>
                        </div>

                        <template v-else>
                            <div class="revenue-list-summary" v-text="'Showing top ' + Math.min(5, categories.length) + ' of ' + categories.length + ' categories • ' + totalRevenueFormatted + ' total revenue'"></div>
                            <div class="revenue-list">
                                <div
                                    v-for="(category, index) in categories.slice(0, 5)"
                                    :key="index"
                                    class="revenue-list-item"
                                    @mouseenter="hoveredIndex = index"
                                    @mouseleave="hoveredIndex = null"
                                >
                                    <div class="revenue-list-item-header">
                                        <span class="revenue-list-item-name" :title="category.name" v-text="category.name"></span>
                                        <div class="revenue-list-item-meta">
                                            <span class="revenue-list-item-amount" v-text="category.revenueFormatted"></span>
                                            <span class="revenue-list-item-percent" v-text="category.percentage + '%'"></span>
                                        </div>
                                    </div>
                                    <div class="revenue-list-bar-track">
                                        <div
                                            class="revenue-list-bar-fill"
                                            :style="{ width: category.percentage + '%' }"
                                        ></div>
                                    </div>
                                    <div v-if="hoveredIndex === index" class="revenue-list-tooltip" style="position: absolute; z-index: 10;">
                                        <strong v-text="category.name"></strong>
                                        <span>Revenue: <span v-text="category.revenueFormatted"></span></span>
                                        <span>Percentage: <span v-text="category.percentage"></span>%</span>
                                        <span>Products Sold: <span v-text="category.products_sold"></span></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </section>
                `,
                data() {
                    return {
                        loading: true,
                        detailsUrl: mount.dataset.detailsUrl,
                        categories: [],
                        hoveredIndex: null,
                    };
                },
                computed: {
                    totalRevenue() {
                        return this.categories.reduce((sum, category) => sum + Number(category.revenue || 0), 0);
                    },
                    totalRevenueFormatted() {
                        return '$' + this.totalRevenue.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },
                },
                async mounted() {
                    await this.fetchData();
                    this.loading = false;
                },
                methods: {
                    async fetchData() {
                        const response = await fetch(mount.dataset.endpoint, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await response.json();
                        const categories = [...(data.categories || [])]
                            .map((category) => ({
                                name: category.name,
                                revenue: parseFloat(category.revenue) || 0,
                                products_sold: parseInt(category.products_sold) || 0,
                            }))
                            .sort((a, b) => b.revenue - a.revenue);

                        const totalRevenue = categories.reduce((sum, category) => sum + category.revenue, 0);

                        this.categories = categories.map((category) => {
                            const percentage = totalRevenue > 0
                                ? Math.round((category.revenue / totalRevenue) * 100)
                                : 0;
                            return {
                                ...category,
                                percentage,
                                revenueFormatted: '$' + category.revenue.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2,
                                }),
                            };
                        });
                    },
                },
            };

            Vue.createApp({ components: { RevenueByCategoryCard } }).mount(mount);
        });
    </script>
@endpush



