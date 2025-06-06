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
        $empresa = Empresa::find($id);
        
        return response()->json($empresa, 200);
    }

    public function getCurrentCompany()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $empresa = $user->empresa;

        return response()->json($empresa, 200);
    }

    public function updateCompany(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $empresa = $user->empresa;

        $empresa->update($request->all());

        return $empresa;
    }

}
