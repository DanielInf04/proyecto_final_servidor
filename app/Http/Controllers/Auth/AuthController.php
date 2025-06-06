<?php

namespace App\Http\Controllers\Auth;

use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

use App\Models\User;
use App\Models\Client\Cliente;
use App\Models\Company\Empresa;

class AuthController extends Controller
{
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:cliente,empresa',

            // Validaciones solo si aplica (cliente o empresa)
            'cliente.telefono' => 'required_if:role,cliente|string|regex:/^[0-9]{9}$/',
            'empresa.nombre' => 'required_if:role,empresa',
            'empresa.telefono' => 'required_if:role,empresa',
            'empresa.direccion'   => 'required_if:role,empresa|string|max:255',
            'empresa.descripcion' => 'nullable|string|max:500', 
            'empresa.nif' => 'required_if:role,empresa|unique:empresas,nif',

        ], [
            // Mensajes de error personalizados
            'cliente.telefono.required_if' => 'El teléfono del cliente es obligatorio.',
            'cliente.telefono.regex' => 'El teléfono del cliente debe tener exactamente 9 dígitos numéricos.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'empresa.nif.unique' => 'Este NIF ya está registrado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = $request->role;
        $user->save();

        // Según el rol, crear la entidad correspondiente
        if ($request->role === 'cliente') {
            $cliente = new Cliente;
            $cliente->user_id = $user->id;
            $cliente->telefono = $request->input('cliente.telefono');
            $cliente->save();
        } elseif ($request->role === 'empresa') {
            $empresa = new Empresa;
            $empresa->user_id = $user->id;
            $empresa->nombre = $request->input('empresa.nombre');
            $empresa->telefono = $request->input('empresa.telefono');
            $empresa->direccion = $request->input('empresa.direccion');
            $empresa->descripcion = $request->input('empresa.descripcion');
            $empresa->nif = $request->input('empresa.nif');
            $empresa->save();
        }

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'user' => $user
        ], 201);

    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'El usuario no está registrado.'], 404);
        }

        if ($user->status !== 'activo') {
            return response()->json(['error' => 'Usuario inactivo. Contacta con el administrador.'], 403);
        }

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth('api')->user();

        if ($user->role === 'cliente') {
            $user->load('cliente');
        } elseif ($user->role === 'empresa') {
            $user->load('empresa');
        }
        return response()->json($user);
    }
    /*public function me()
    {
        return response()->json(auth()->user());
    }*/

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function refresh()
    {
        \Log::info('Refrescando token');
        return $this->respondWithToken(auth('api')->refresh());
    }*/

    public function refresh()
    {
        \Log::info('Intentando refrescar token...');

        try {
            if (!JWTAuth::getToken()) {
                return response()->json(['error' => 'Token no enviado'], 401);
            }

            $newToken = JWTAuth::parseToken()->refresh();

            return $this->respondWithToken($newToken);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token enviado y fuera de tiempo de refresco'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido o no presente'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error inesperado al refrescar token'], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }*/

    // NO TOCAR NUNCA
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

}
