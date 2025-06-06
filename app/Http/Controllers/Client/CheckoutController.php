<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Services\Checkout\CheckoutService;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $checkout = new CheckoutService();
        return $checkout->handle($request);
    }

    public function validate(Request $request)
    {
        $checkout = new CheckoutService();
        if ($error = $checkout->validateCheckoutData($request)) {
            return $error;
        }

        return response()->json(['valid' => true]);
    }

}