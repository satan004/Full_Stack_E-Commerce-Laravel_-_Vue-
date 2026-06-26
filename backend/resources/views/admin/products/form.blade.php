@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Product' : 'Create Product')

@section('content')
    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Catalog</p>
            <h1>{{ $product->exists ? 'Edit Product' : 'Create Product' }}</h1>
            <p>{{ $product->exists ? 'Update product details, pricing and visibility.' : 'Add a new product to your catalog.' }}</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="btn btn-muted" href="{{ route('admin.products.index') }}">Back to products</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="panel stack" style="max-width: 880px;">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <label>
                Category
                <select name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Name
                <input type="text" name="name" value="{{ old('name', $product->name) }}" placeholder="Product name" required>
            </label>

            <label>
                Price
                <input type="number" name="price" min="0" step="0.01" value="{{ old('price', $product->price) }}" required>
            </label>

            <label>
                Stock
                <input type="number" name="stock" min="0" step="1" value="{{ old('stock', $product->stock ?? 0) }}" required>
            </label>
        </div>

        <label>
            Description
            <textarea name="description" rows="5" placeholder="Describe this product...">{{ old('description', $product->description) }}</textarea>
        </label>

        <div class="form-grid">
            <label>
                Product Image Upload
                <input type="file" name="image" accept="image/*">
            </label>
            <label>
                Image URL
                <input type="url" name="image_url" value="{{ old('image_url', str_starts_with((string) $product->image_path, 'http') ? $product->image_path : '') }}" placeholder="https://...">
            </label>
        </div>

        @if ($product->image_url)
            <div>
                <small style="color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.7rem;">Current image</small>
                <div style="margin-top: 0.5rem;"><img class="form-preview" src="{{ $product->image_url }}" alt="{{ $product->name }}"></div>
            </div>
        @endif

        <label class="check-row" style="background: var(--surface-2); padding: 0.85rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--line);">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
            <span>
                <strong style="display: block; color: var(--ink);">Active product</strong>
                <small style="color: var(--muted);">Visible to customers in the storefront.</small>
            </span>
        </label>

        <div class="form-actions" style="justify-content: flex-end;">
            <a class="btn btn-muted" href="{{ route('admin.products.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12l4 4L19 6"/></svg>
                {{ $product->exists ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </form>
@endsection
