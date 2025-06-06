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

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'telefono' => $user->cliente->telefono ?? '',
            'fecha_creacion' => $user->created_at->format('Y-m-d')
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $user->name = $request->input('name');
        $user->save();

        if ($user->cliente) {
            $user->cliente->telefono = $request->input('telefono');
            $user->cliente->save();
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => [
                'name' => $user->name,
                'telefono' => $user->cliente->telefono
            ],
        ]);
    }
}
