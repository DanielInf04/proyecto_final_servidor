<?php

namespace App\Services\Checkout;

use Illuminate\Support\Facades\Http;

class PaypalValidator
{
    /**
     * Valida que el pago de PayPal sea correcto y esté completado.
     * 
     * @param array $paypalData
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validate(array $paypalData)
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');

        // Obtener token de acceso
        $accessTokenResponse = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$accessTokenResponse->ok()) {
            return response()->json([
                'error' => 'paypal_token_error',
                'message' => 'No se pudo obtener el token de acceso de PayPal.'
            ], 500);
        }

        $accessToken = $accessTokenResponse->json()['access_token'];

        // Consultar el estado del pedido de PayPal
        $paypalOrderId = $paypalData['id'];
        $paypalResponse = Http::withToken($accessToken)
            ->get("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paypalOrderId}");
        
        if (!$paypalResponse->ok() || $paypalResponse->json()['status'] !== 'COMPLETED') {
            return response()->json([
                'error' => 'paypal_payment_invalid',
                'message' => 'El pago de PayPal no es válido o no está completado.'
            ], 400);
        }

        return null;
    }
}