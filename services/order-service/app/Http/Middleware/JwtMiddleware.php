<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = JWTAuth::getToken();

            if (! $token) {
                return response()->json(['error' => 'Token manquant'], 401);
            }

            // Décode sans chercher en base de données
            $payload = JWTAuth::decode($token);

            $request->merge([
                'auth_user_id'    => $payload->get('sub'),
                'auth_user_email' => $payload->get('email'),
                'auth_user_name'  => $payload->get('name'),
            ]);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expiré'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token manquant ou invalide'], 401);
        }

        return $next($request);
    }
}