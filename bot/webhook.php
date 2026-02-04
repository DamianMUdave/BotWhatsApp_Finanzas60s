<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/src/TwilioProvider.php';
require_once __DIR__ . '/src/MetaWhatsAppProvider.php';
require_once __DIR__ . '/src/BotService.php';

$providerName = $config['provider'] ?? 'twilio';
$provider = $providerName === 'meta'
    ? new MetaWhatsAppProvider($config['meta'] ?? [])
    : new TwilioProvider($config['twilio'] ?? []);

$bot = new BotService($provider);

$payload = $_POST;
if (empty($payload)) {
    $rawInput = file_get_contents('php://input');
    $decoded = json_decode($rawInput ?? '', true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$bot->handleIncoming($payload);
