<?php

namespace App\Services\Checkout;

use App\Models\Shared\Location\Poblacion;

class AddressValidator
{
    /**
     * Valida la población y el código postal de una dirección
     * 
     * @param array $addressData
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validate(array $addressData)
    {
        $poblacion = Poblacion::find($addressData['poblacion_id'] ?? null);
        if (!$poblacion) {
            return response()->json([
                'error' => 'invalid_poblacion',
                'message' => 'La población especificada no es válida.'
            ], 422);
        }

        $codigoPostal = $addressData['codigo_postal'] ?? '';
        if (!preg_match('/^\d{5}$/', $codigoPostal)) {
            return response()->json([
                'error' => 'invalid_postal_code_format',
                'message' => 'El código postal debe tener exactamente 5 dígitos.'
            ], 422);
        }

        $provinciaId = str_pad($poblacion->provincia_id, 2, '0', STR_PAD_LEFT);
        if (substr($codigoPostal, 0, 2) !== $provinciaId) {
            return response()->json([
                'error' => 'postal_code_provincia_mismatch',
                'message' => 'El código postal no coincide con la provincia de la población seleccionada.'
            ], 422);
        }

        return null;
    }
}