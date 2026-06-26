@extends('layouts.admin')

@section('title', $category->exists ? 'Edit Category' : 'Create Category')
@section('subtitle', $category->exists ? 'Update category name and description.' : 'Add a new category to organize your products.')

@section('content')
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="panel stack" style="max-width: 720px;">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <label>
            Name
            <input type="text" name="name" value="{{ old('name', $category->name) }}" placeholder="Category name" required>
        </label>

        <label>
            Description
            <textarea name="description" rows="5" placeholder="Brief description of this category...">{{ old('description', $category->description) }}</textarea>
        </label>

        <div class="form-actions" style="justify-content: flex-end;">
            <a class="btn btn-muted" href="{{ route('admin.categories.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12l4 4L19 6"/></svg>
                {{ $category->exists ? 'Update Category' : 'Create Category' }}
            </button>
        </div>
    </form>
@endsection
