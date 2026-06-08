<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Retourne l'ID de l'utilisateur pour le token JWT
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    // Données supplémentaires stockées dans le token
    public function getJWTCustomClaims(): array
    {
        return [
            'email' => $this->email,
            'name'  => $this->name,
        ];
    }
}