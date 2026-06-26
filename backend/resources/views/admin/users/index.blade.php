@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Customers</p>
            <h1>Users</h1>
            <p>{{ $users->total() }} registered customer{{ $users->total() === 1 ? '' : 's' }} — browse customers who have an account in your store.</p>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div>
                <h2>All Customers</h2>
                <p>Click a customer to view their order history.</p>
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th style="text-align: right;">Orders</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <span class="avatar" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #22d3ee); color: #fff; display: grid; place-items: center; font-weight: 700;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <strong class="name">{{ $user->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td style="text-align: right;"><span class="badge">{{ $user->orders_count }}</span></td>
                        <td style="color: var(--muted);">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2rem;">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">{{ $users->links() }}</div>
    </div>
@endsection
