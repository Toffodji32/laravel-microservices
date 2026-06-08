<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ProductServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private ProductServiceClient $productClient;

    public function __construct()
    {
        $this->productClient = new ProductServiceClient();
    }

    // ── GET /api/orders ──────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $userId = $request->auth_user_id;

        $orders = Order::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    // ── GET /api/orders/{id} ─────────────────────────────────
    public function show(Request $request, string $id): JsonResponse
    {
        $userId = $request->auth_user_id;
        $order  = Order::forUser($userId)->findOrFail($id);

        return response()->json($order);
    }

    // ── POST /api/orders ─────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'integer'],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'shipping_address'         => ['required', 'array'],
            'shipping_address.street'  => ['required', 'string'],
            'shipping_address.city'    => ['required', 'string'],
            'shipping_address.country' => ['required', 'string'],
            'notes'                    => ['nullable', 'string'],
        ]);

        $userId      = $request->auth_user_id;
        $token       = $request->bearerToken();
        $totalAmount = 0;
        $enrichedItems = [];

        // ── Vérifier chaque produit sur product-service ──────
        foreach ($validated['items'] as $item) {
            $product = $this->productClient->getProduct($item['product_id']);

            if (! $product) {
                return response()->json([
                    'error' => "Produit {$item['product_id']} introuvable",
                ], 404);
            }

            if ($product['stock'] < $item['quantity']) {
                return response()->json([
                    'error' => "Stock insuffisant pour {$product['name']}. Stock disponible : {$product['stock']}",
                ], 422);
            }

            $lineTotal     = $product['price'] * $item['quantity'];
            $totalAmount  += $lineTotal;

            $enrichedItems[] = [
                'product_id'   => $item['product_id'],
                'product_name' => $product['name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $product['price'],
                'line_total'   => $lineTotal,
            ];
        }

        // ── Créer la commande ────────────────────────────────
        $order = Order::create([
            'user_id'          => $userId,
            'status'           => Order::STATUS_PENDING,
            'total_amount'     => $totalAmount,
            'items'            => $enrichedItems,
            'shipping_address' => $validated['shipping_address'],
            'notes'            => $validated['notes'] ?? null,
        ]);

        // ── Décrémenter les stocks sur product-service ───────
        foreach ($enrichedItems as $item) {
            $this->productClient->decrementStock(
                $item['product_id'],
                $item['quantity'],
                $token
            );
        }

        return response()->json($order, 201);
    }

    // ── PATCH /api/orders/{id}/status ────────────────────────
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', Order::STATUSES)],
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json($order);
    }

    // ── DELETE /api/orders/{id} ──────────────────────────────
    public function destroy(Request $request, string $id): JsonResponse
    {
        $userId = $request->auth_user_id;
        $order  = Order::forUser($userId)->findOrFail($id);

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'error' => 'Seules les commandes en attente peuvent être annulées',
            ], 422);
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return response()->json(['message' => 'Commande annulée avec succès']);
    }
}
