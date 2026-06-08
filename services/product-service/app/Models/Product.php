<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    // ── Scope : produits en stock uniquement ──────────────────────
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // ── Scope : filtrer par catégorie ─────────────────────────────
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}