<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ── GET /api/products ────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // Filtres optionnels
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        if ($request->has('search')) {
            $query->where('name', 'ilike', '%'.$request->search.'%');
        }

        $products = $query->orderBy('created_at', 'desc')
                          ->paginate($request->integer('per_page', 15));

        return response()->json($products);
    }

    // ── GET /api/products/{id} ───────────────────────────────────
    public function show(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        return response()->json($product);
    }

    // ── POST /api/products ───────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'category'    => ['required', 'string', 'max:100'],
            'image_url'   => ['nullable', 'url'],
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    // ── PUT /api/products/{id} ───────────────────────────────────
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'stock'       => ['sometimes', 'integer', 'min:0'],
            'category'    => ['sometimes', 'string', 'max:100'],
            'image_url'   => ['nullable', 'url'],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    // ── DELETE /api/products/{id} ────────────────────────────────
    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Produit supprimé avec succès'
        ]);
    }

    // ── PATCH /api/products/{id}/stock ───────────────────────────
    // Appelé par order-service pour mettre à jour le stock
    public function updateStock(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'quantity' => ['required', 'integer'],
        ]);

        $newStock = $product->stock + $validated['quantity'];

        if ($newStock < 0) {
            return response()->json([
                'error' => 'Stock insuffisant'
            ], 422);
        }

        $product->update(['stock' => $newStock]);

        return response()->json([
            'product_id' => $id,
            'old_stock'  => $product->getOriginal('stock'),
            'new_stock'  => $newStock,
        ]);
    }
}