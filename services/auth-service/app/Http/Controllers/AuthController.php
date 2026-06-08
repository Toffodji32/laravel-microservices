<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // ── POST /api/auth/register ──────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user  = User::create($validated);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Compte créé avec succès',
            'user'    => $user,
            'token'   => $token,
            'type'    => 'Bearer',
        ], 201);
    }

    // ── POST /api/auth/login ─────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'error' => 'Email ou mot de passe incorrect',
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    // ── POST /api/auth/logout ────────────────────────────────────
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    // ── GET /api/auth/me ─────────────────────────────────────────
    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    // ── POST /api/auth/refresh ───────────────────────────────────
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    // ── Helper : formater la réponse avec le token ───────────────
    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'token'      => $token,
            'type'       => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'       => auth('api')->user(),
        ]);
    }
}