<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

use App\Models\Company\Empresa;

class ProfileController extends Controller
{
    public function getCompany($id)
    {
        \Log::info('User_id', ['id' => $id]);
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        return response()->json($empresa, 200);
    }

    public function getCurrentCompany()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        return response()->json($empresa, 200);
    }

    public function updateCompany(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        $empresa->update($request->all());

        return $empresa;
    }

}
