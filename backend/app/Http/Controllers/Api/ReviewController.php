<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        abort_if(! $product->is_active, 404);

        $hasPurchasedProduct = $request->user()
            ->orders()
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        if (! $hasPurchasedProduct) {
            throw ValidationException::withMessages([
                'product' => ['You can review this product after buying it.'],
            ]);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ],
        );

        $product->load([
            'category',
            'reviews' => fn ($query) => $query->with('user:id,name')->latest(),
        ])->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        return response()->json($product, 201);
    }
}
