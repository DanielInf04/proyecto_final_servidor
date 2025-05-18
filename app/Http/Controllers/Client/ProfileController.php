<?php

namespace App\Http\Controllers\Client;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getProfile()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'cliente') {
            return response()->json(['error' => 'No autorizado'], 404);
        }

        return response()->json([
            'email' => $user->email,
            'telefono' => $user->cliente->telefono ?? ''
        ]);
    }
}
