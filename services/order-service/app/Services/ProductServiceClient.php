<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductServiceClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PRODUCT_SERVICE_URL', 'http://127.0.0.1:8001/api');
    }

    // ── Vérifie qu'un produit existe et retourne ses infos ────
    public function getProduct(int $productId): ?array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/products/{$productId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('ProductService::getProduct failed', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ── Décrémente le stock après une commande ────────────────
    public function decrementStock(int $productId, int $quantity, string $token): bool
    {
        try {
            $response = Http::timeout(5)
                ->withToken($token)
                ->patch("{$this->baseUrl}/products/{$productId}/stock", [
                    'quantity' => -$quantity,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('ProductService::decrementStock failed', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }
}
