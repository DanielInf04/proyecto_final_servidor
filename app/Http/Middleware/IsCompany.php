<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class IsCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user || $user->role !== 'empresa') {
                return response()->json(['error' => 'Acceso restringido'], 403);
            }

            return $next($request);
        } catch(\Exception $e) {
            return response()->json(['error' => 'Token inválido o ausente'], 401);
        }
    }
}



