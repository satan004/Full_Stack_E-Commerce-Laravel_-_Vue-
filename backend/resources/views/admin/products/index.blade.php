@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    @php
        $statusTone = fn (bool $active) => $active ? 'badge-success' : 'badge-neutral';
    @endphp

    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Catalog</p>
            <h1>Products</h1>
            <p>{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} total. Add, edit and organize the items available in your store.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="btn btn-primary" href="{{ route('admin.products.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Create Product
            </a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>All Products</h2>
            <p>Manage your active and hidden products.</p>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Stock</th>
                    <th>Status</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                @else
                                    <span style="width:44px;height:44px;border-radius:8px;background:var(--bg-2);display:grid;place-items:center;color:var(--muted);font-size:0.7rem;">N/A</span>
                                @endif
                                <div style="display:grid;">
                                    <span class="name">{{ $product->name }}</span>
                                    <span class="slug">/{{ $product->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td style="text-align: right; font-weight: 600;">${{ number_format($product->price, 2) }}</td>
                        <td style="text-align: right;">
                            <span class="badge {{ $product->stock <= 5 ? ($product->stock <= 2 ? 'badge-danger' : 'badge-warning') : 'badge-neutral' }}">{{ $product->stock }}</span>
                        </td>
                        <td><span class="badge {{ $statusTone($product->is_active) }}">{{ $product->is_active ? 'active' : 'hidden' }}</span></td>
                        <td class="actions">
                            <a class="btn btn-muted" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--muted); padding: 2rem;">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">{{ $products->links() }}</div>
    </div>
@endsection
