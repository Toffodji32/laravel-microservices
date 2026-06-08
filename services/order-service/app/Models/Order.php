<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'items',
        'shipping_address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'items'            => 'array',
            'shipping_address' => 'array',
            'total_amount'     => 'decimal:2',
        ];
    }

    // ── Statuts possibles ─────────────────────────────────────
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    // ── Scope : commandes d'un utilisateur ────────────────────
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}