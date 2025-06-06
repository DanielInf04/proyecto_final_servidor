<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('Middleware is_admin ejecutado');
        
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user || $user->role !== 'admin') {
                return response()->json(['error' => 'Acceso restringido'], 403);
            }

            return $next($request);
        } catch(\Exception $e) {
            return response()->json(['error' => 'Token inválido o ausente'], 401);
        }
    }
}


