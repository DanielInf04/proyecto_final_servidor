<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Models\User;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $perPage = $request->input('per_page', 10); // Default: 10 usuarios por página
        $role = $request->input('role');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        $usuarios = $query->paginate($perPage);

        //$usuarios = User::paginate($perPage);

        return response()->json($usuarios);
    }

    // Retorna todos los usuarios
    /*public function getUsers(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $perPage = $request->input('per_page', 10); // Default: 10 usuarios por página

        $usuarios = User::paginate($perPage);

        return response()->json($usuarios);
    }*/

    public function searchUser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $query = $request->input('query');
        $perPage = $request->input('per_page', 10);

        $usuarios = User::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%$query%")
            ->orWhere('email', 'LIKE', "%$query%");
        })->paginate($perPage);

        return response()->json($usuarios);
    }

    // Cambiamos el estado del usuario
    public function updateStatus(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'El usuario no existe'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:activo,inactivo'
        ]);

        $usuario->status = $request->status;
        $usuario->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'usuario' => $usuario
        ], 200);

    }

    public function deleteUser($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'El usuario no existe'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => 'El usuario ha sido eliminado correctamente'], 200);

    }

}
