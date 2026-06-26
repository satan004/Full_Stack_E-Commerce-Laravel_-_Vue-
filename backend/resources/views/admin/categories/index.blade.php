@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Catalog</p>
            <h1>Categories</h1>
            <p>{{ $categories->total() }} categor{{ $categories->total() === 1 ? 'y' : 'ies' }} total. Organize products into clean, searchable categories.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Create Category
            </a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div>
                <h2>All Categories</h2>
                <p>Manage the categories that organize your products.</p>
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th style="text-align: right;">Products</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td><strong style="color: var(--ink);">{{ $category->name }}</strong></td>
                        <td><code style="background: var(--surface-2); padding: 0.15rem 0.45rem; border-radius: 6px; color: var(--muted); font-size: 0.82rem;">/{{ $category->slug }}</code></td>
                        <td style="text-align: right;"><span class="badge">{{ $category->products_count }}</span></td>
                        <td class="actions">
                            <a class="btn btn-muted" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: var(--muted); padding: 2rem;">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">{{ $categories->links() }}</div>
    </div>
@endsection
