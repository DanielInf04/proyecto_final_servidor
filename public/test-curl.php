<?php

/*$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "❌ Error: " . curl_error($ch);
} else {
    echo "✅ Conexión exitosa. Respuesta:<br><pre>" . htmlspecialchars($response) . "</pre>";
}

curl_close($ch);*/