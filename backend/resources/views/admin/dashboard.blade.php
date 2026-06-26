@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $currency = fn ($value) => '$' . number_format($value, 2);
        $statusTone = fn ($status) => match ($status) {
            'completed' => 'badge-success',
            'processing', 'paid' => 'badge-info',
            'pending' => 'badge-warning',
            'cancelled', 'failed' => 'badge-danger',
            default => 'badge-neutral',
        };
        $todayRevenue = $stats['revenue'] > 0 ? round($stats['revenue'] / max(1, $stats['orders']), 2) : 0;
    @endphp

    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Overview</p>
            <h1>Dashboard</h1>
        </div>
        <div class="admin-topbar-actions">
            <a class="btn btn-muted" href="{{ route('store') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M3 9l1.5-5h15L21 9"/><path d="M3 9v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9"/><path d="M9 13h6"/></svg>
                View storefront
            </a>
        </div>
    </div>

    <div class="admin-banner">
        <p>👋 Like what you see? Check out our <strong>premium version</strong> for more analytics, exports and integrations.</p>
        <div class="admin-banner-actions">
            <a class="btn btn-muted" href="#">Download Free Version</a>
            <a class="btn btn-primary" href="#">Upgrade to Pro</a>
        </div>
    </div>

    <section class="stats-grid">
        <article class="stat-card pink">
            <div class="stat-card-head">
                <span>Weekly Sales</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg>
                </span>
            </div>
            <strong>{{ $currency($stats['revenue']) }}</strong>
            <span class="delta">↑ Increased by 60%</span>
        </article>

        <article class="stat-card blue">
            <div class="stat-card-head">
                <span>Weekly Orders</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 4h14l-1.5 12.5a2 2 0 0 1-2 1.7H8.5a2 2 0 0 1-2-1.7L5 4z"/><path d="M9 8v0M15 8v0"/></svg>
                </span>
            </div>
            <strong>{{ number_format($stats['orders']) }}</strong>
            <span class="delta">↓ Decreased by 10%</span>
        </article>

        <article class="stat-card teal">
            <div class="stat-card-head">
                <span>Visitors Online</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                </span>
            </div>
            <strong>{{ number_format($stats['users']) }}</strong>
            <span class="delta">↑ Increased by 5%</span>
        </article>

        <article class="stat-card purple">
            <div class="stat-card-head">
                <span>Avg Order Value</span>
                <span class="stat-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
            </div>
            <strong>{{ $currency($todayRevenue) }}</strong>
            <span class="delta">↑ Steady performance</span>
        </article>
    </section>

    <section class="two-column">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Visit and Sales Statistics</h2>
                    <p>Monthly visitor and order trends across regions.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.85rem; color: var(--muted); font-size: 0.78rem; font-weight: 600;">
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><span style="width:9px;height:9px;border-radius:50%;background:#a855f7;display:inline-block;"></span> CHN</span>
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><span style="width:9px;height:9px;border-radius:50%;background:#ec4899;display:inline-block;"></span> USA</span>
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem;"><span style="width:9px;height:9px;border-radius:50%;background:#3b82f6;display:inline-block;"></span> UK</span>
                </div>
            </div>

            @php
                $months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
                $buckets = array_fill(0, 12, 0);
                foreach ($recentOrders as $order) {
                    $m = (int) $order->created_at->format('n') - 1;
                    $buckets[$m] += $order->total;
                }
                $max = max(1, max($buckets));
            @endphp

            <div style="display: grid; grid-template-columns: repeat(12, 1fr); align-items: end; gap: 0.6rem; height: 220px; padding: 1rem 0;">
                @foreach ($months as $i => $month)
                    @php $h = max(8, ($buckets[$i] / $max) * 180); @endphp
                    <div style="display: grid; gap: 0.4rem; justify-items: center;">
                        <div style="display: flex; gap: 4px; align-items: end; height: 200px;">
                            <span title="CHN" style="width: 6px; height: {{ max(8, $h * 0.7) }}px; background: #a855f7; border-radius: 3px;"></span>
                            <span title="USA" style="width: 6px; height: {{ max(8, $h * 0.55) }}px; background: #ec4899; border-radius: 3px;"></span>
                            <span title="UK" style="width: 6px; height: {{ max(8, $h * 0.4) }}px; background: #3b82f6; border-radius: 3px;"></span>
                        </div>
                        <small style="font-size: 0.7rem; color: var(--muted); font-weight: 600;">{{ $month }}</small>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Traffic Sources</h2>
                    <p>Where your visitors come from.</p>
                </div>
            </div>

            @php
                $search = max(1, $stats['users'] * 0.4);
                $direct = max(1, $stats['users'] * 0.3);
                $bookmarks = max(1, $stats['users'] * 0.3);
                $total = $search + $direct + $bookmarks;
                $pct = fn ($v) => round($v / $total * 100);
            @endphp

            <div style="display: grid; place-items: center; padding: 0.75rem 0 1rem;">
                <svg viewBox="0 0 42 42" width="190" height="190" style="transform: rotate(-90deg);">
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#f1f3f8" stroke-width="6"></circle>
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#a855f7" stroke-width="6" stroke-dasharray="{{ $pct($search) }} {{ 100 - $pct($search) }}" stroke-dashoffset="0"></circle>
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#14b8a6" stroke-width="6" stroke-dasharray="{{ $pct($direct) }} {{ 100 - $pct($direct) }}" stroke-dashoffset="-{{ $pct($search) }}"></circle>
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#ec4899" stroke-width="6" stroke-dasharray="{{ $pct($bookmarks) }} {{ 100 - $pct($bookmarks) }}" stroke-dashoffset="-{{ $pct($search) + $pct($direct) }}"></circle>
                </svg>
            </div>

            <div style="display: grid; gap: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: inline-flex; align-items: center; gap: 0.55rem; color: var(--ink-2); font-weight: 600;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#a855f7;"></span> Search Engines
                    </span>
                    <strong>{{ $pct($search) }}%</strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: inline-flex; align-items: center; gap: 0.55rem; color: var(--ink-2); font-weight: 600;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#14b8a6;"></span> Direct Click
                    </span>
                    <strong>{{ $pct($direct) }}%</strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: inline-flex; align-items: center; gap: 0.55rem; color: var(--ink-2); font-weight: 600;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ec4899;"></span> Bookmarks Click
                    </span>
                    <strong>{{ $pct($bookmarks) }}%</strong>
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
                        <tr><td colspan="4" style="text-align: center; color: var(--muted); padding: 2rem;">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Low Stock Alerts</h2>
                    <p>Products with 5 or fewer items remaining.</p>
                </div>
                <a class="btn btn-muted" href="{{ route('admin.products.index') }}">Manage</a>
            </div>
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
                        <tr><td colspan="3" style="text-align: center; color: var(--muted); padding: 2rem;">Inventory looks healthy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
