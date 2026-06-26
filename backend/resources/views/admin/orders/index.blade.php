@extends('layouts.admin')

@section('title', 'Orders')

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
            <h1>Orders</h1>
            <p>{{ $orders->total() }} order{{ $orders->total() === 1 ? '' : 's' }} total. Review customer orders and update their fulfillment status.</p>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div>
                <h2>All Orders</h2>
                <p>Click an order to view full details and update status.</p>
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th style="text-align: right;">Items</th>
                    <th style="text-align: right;">Total</th>
                    <th>Status</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--primary); font-weight: 600;">#{{ $order->id }}</a></td>
                        <td>
                            <div style="display: grid;">
                                <strong style="color: var(--ink); font-weight: 600;">{{ $order->user->name }}</strong>
                                <small style="color: var(--muted);">{{ $order->user->email }}</small>
                            </div>
                        </td>
                        <td style="text-align: right;">{{ $order->items->sum('quantity') }}</td>
                        <td style="text-align: right; font-weight: 600;">${{ number_format($order->total, 2) }}</td>
                        <td><span class="badge {{ $statusTone($order->status) }}">{{ $order->status }}</span></td>
                        <td class="actions">
                            <a class="btn btn-muted" href="{{ route('admin.orders.show', $order) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--muted); padding: 2rem;">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">{{ $orders->links() }}</div>
    </div>
@endsection
