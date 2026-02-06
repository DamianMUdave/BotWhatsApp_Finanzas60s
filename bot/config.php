<?php

return [
    'provider' => getenv('BOT_PROVIDER') ?: 'twilio',
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
