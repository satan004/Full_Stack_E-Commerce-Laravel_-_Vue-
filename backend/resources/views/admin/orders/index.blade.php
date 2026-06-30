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

        @if($orders->hasPages())
            <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.875rem; color: var(--muted); font-weight: 600;">
                    Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} results
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    @if($orders->onFirstPage())
                        <span class="btn btn-muted" style="opacity: 0.5; cursor: not-allowed;">« Previous</span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" class="btn btn-muted">« Previous</a>
                    @endif
                    
                    <span style="padding: 0 0.75rem; font-size: 0.875rem; font-weight: 600; color: var(--ink);">
                        Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}
                    </span>
                    
                    @if($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="btn btn-muted">Next »</a>
                    @else
                        <span class="btn btn-muted" style="opacity: 0.5; cursor: not-allowed;">Next »</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
