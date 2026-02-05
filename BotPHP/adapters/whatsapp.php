<?php
function responderCloud($to, $mensaje) {
    $token = 'TU_TOKEN_DE_META';
    $phoneId = 'PHONE_NUMBER_ID';

    $data = [
        "messaging_product" => "whatsapp",
        "to" => $to,
        "text" => ["body" => $mensaje]
    ];

    $ch = curl_init("https://graph.facebook.com/v19.0/$phoneId/messages");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true
    ]);

    curl_exec($ch);
    curl_close($ch);
}
