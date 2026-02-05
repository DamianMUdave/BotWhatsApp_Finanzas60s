<?php

return [
    'provider' => getenv('BOT_PROVIDER') ?: 'twilio',
    'twilio' => [
        'account_sid' => getenv('TWILIO_ACCOUNT_SID') ?: 'AC5130883676e22c64058fe26d1861ca3b',
        'auth_token' => getenv('TWILIO_AUTH_TOKEN') ?: '8430d934ec2f6e1884c0cc8205e6daf4',
        'from' => getenv('TWILIO_WHATSAPP_FROM') ?: '',
    ],
    'meta' => [
        'access_token' => getenv('WHATSAPP_ACCESS_TOKEN') ?: '',
        'phone_number_id' => getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '',
        'api_base' => getenv('WHATSAPP_API_BASE') ?: 'https://graph.facebook.com/v20.0',
    ],
];
