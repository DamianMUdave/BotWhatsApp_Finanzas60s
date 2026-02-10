<?php

return [
    'provider' => getenv('BOT_PROVIDER') ?: 'twilio',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: 3306,
        'name' => getenv('DB_NAME') ?: 'finanzas60s',
        'user' => getenv('DB_USER') ?: '',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ],
    'twilio' => [
        'account_sid' => getenv('TWILIO_ACCOUNT_SID') ?: '',
        'auth_token' => getenv('TWILIO_AUTH_TOKEN') ?: '',
        'from' => getenv('TWILIO_WHATSAPP_FROM') ?: '',
    ],
    'meta' => [
        'access_token' => getenv('WHATSAPP_ACCESS_TOKEN') ?: '',
        'phone_number_id' => getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '',
        'api_base' => getenv('WHATSAPP_API_BASE') ?: 'https://graph.facebook.com/v20.0',
    ],
];
