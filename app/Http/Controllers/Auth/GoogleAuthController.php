<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Client\Cliente;

class GoogleAuthController extends Controller
{

    public function login(Request $request)
    {
        $idToken = $request->input('token');

        // Verificar token con Google
        $response = Http::get("https://oauth2.googleapis.com/tokeninfo", [
            'id_token' => $idToken
        ]);

        if (!$response->ok()) {
            return response()->json(['error' => 'Token de Google inválido'], 401);
        }

        $googleUser = $response->json();

        // Buscar si ya existe el usuario
        $user = User::where('email', $googleUser['email'])->first();

        if (!$user) {
            // Crear usuario
            $user = User::create([
                'name' => $googleUser['name'] ?? 'Usuario Google',
                'email' => $googleUser['email'],
                'password' => bcrypt(Str::random(24)),
                'role' => 'cliente'
            ]);

            // Creamos entrada en la tabla clientes
            Cliente::create([
                'user_id' => $user->id,
                'telefono' => null
            ]);
        }

        // Buscar o crear el usuario
        /*$user = User::firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name' => $googleUser['name'] ?? 'Usuario Google',
                'password' => bcrypt(Str::random(24)),
                'role' => 'cliente',
            ]
        );*/

        // Generar JWT con guard api
        $token = auth('api')->login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ]);

    }
}
