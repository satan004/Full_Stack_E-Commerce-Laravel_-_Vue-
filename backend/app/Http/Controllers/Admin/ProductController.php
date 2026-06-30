<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with('category')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $product = Product::create($this->validatedProductData($request));

        if ($request->expectsJson()) {
            return response()->json($product->load('category'), 201);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $product->update($this->validatedProductData($request, $product));

        if ($request->expectsJson()) {
            return response()->json($product->fresh()->load('category'));
        }

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteLocalImage($product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function validatedProductData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($product?->id)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_url' => ['nullable', 'url', 'max:1000'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $this->uniqueSlug($data['name'], $product?->id);
        unset($data['image'], $data['image_url']);

        if ($request->hasFile('image')) {
            if ($product) {
                $this->deleteLocalImage($product);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
            $this->mirrorPublicImage($data['image_path']);
        } elseif ($request->filled('image_url')) {
            if ($product) {
                $this->deleteLocalImage($product);
            }

            $data['image_path'] = $request->input('image_url');
        } elseif (! $product) {
            $data['image_path'] = null;
        }

        return $data;
    }

    private function deleteLocalImage(Product $product): void
    {
        if ($this->isLocalImagePath($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
            $this->deletePublicMirror($product->image_path);
        }
    }

    private function isLocalImagePath(?string $path): bool
    {
        return filled($path)
            && ! str_starts_with($path, 'http')
            && ! str_starts_with($path, 'data:');
    }

    private function mirrorPublicImage(string $path): void
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath)) {
            return;
        }

        $source = Storage::disk('public')->path($path);

        if (! is_file($source)) {
            return;
        }

        $target = $publicStoragePath.DIRECTORY_SEPARATOR.str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            ltrim($path, '/\\'),
        );
        $targetDirectory = dirname($target);

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        copy($source, $target);
    }

    private function deletePublicMirror(string $path): void
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath)) {
            return;
        }

        $target = $publicStoragePath.DIRECTORY_SEPARATOR.str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            ltrim($path, '/\\'),
        );

        if (is_file($target)) {
            unlink($target);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
