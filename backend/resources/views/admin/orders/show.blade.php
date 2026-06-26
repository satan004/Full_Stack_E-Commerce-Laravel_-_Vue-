@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
    @php
        $statusTone = fn ($status) => match ($status) {
            'completed' => 'badge-success',
            'processing', 'paid' => 'badge-info',
            'pending' => 'badge-warning',
            'cancelled', 'failed' => 'badge-danger',
            default => 'badge-neutral',
        };
    @endphp

    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Sales</p>
            <h1>Order #{{ $order->id }}</h1>
            <p>Placed {{ $order->created_at->format('M d, Y \a\t H:i') }} · {{ $order->items->count() }} line item{{ $order->items->count() === 1 ? '' : 's' }} · <span class="badge {{ $statusTone($order->status) }}">{{ $order->status }}</span></p>
        </div>
        <div class="admin-topbar-actions">
            <a class="btn btn-muted" href="{{ route('admin.orders.index') }}">Back to orders</a>
        </div>
    </div>

    <section class="two-column">
        <div class="panel stack">
            <div class="panel-header">
                <div>
                    <h2>Order Details</h2>
                    <p>Placed {{ $order->created_at->format('M d, Y \a\t H:i') }}</p>
                </div>
                <span class="badge {{ $statusTone($order->status) }}">{{ $order->status }}</span>
            </div>

            <div style="display: grid; gap: 0.85rem; padding: 1rem; background: var(--surface-2); border-radius: var(--radius-sm); border: 1px solid var(--line);">
                <div>
                    <small style="color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.7rem;">Customer</small>
                    <div style="margin-top: 0.2rem;"><strong>{{ $order->user->name }}</strong> <span style="color: var(--muted);">({{ $order->user->email }})</span></div>
                </div>
                <div>
                    <small style="color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.7rem;">Shipping</small>
                    <div style="margin-top: 0.2rem;">{{ $order->shipping_address }}</div>
                </div>
                <div>
                    <small style="color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.7rem;">Payment</small>
                    <div style="margin-top: 0.2rem;">{{ str_replace('_', ' ', $order->payment_method) }}</div>
                </div>
                @if ($order->notes)
                    <div>
                        <small style="color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.7rem;">Notes</small>
                        <div style="margin-top: 0.2rem;">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="inline-form">
                @csrf
                @method('PATCH')
                <label style="flex: 1;">
                    Update status
                    <select name="status">
                        @foreach (\App\Models\Order::STATUSES as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="btn btn-primary" type="submit" style="align-self: end;">Update Status</button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Order Items</h2>
                    <p>{{ $order->items->count() }} line item{{ $order->items->count() === 1 ? '' : 's' }}</p>
                </div>
                <strong style="font-size: 1.15rem;">${{ number_format($order->total, 2) }}</strong>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align: right;">Qty</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td style="text-align: right;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                            <td style="text-align: right; font-weight: 600;">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
