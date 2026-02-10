<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/src/TwilioProvider.php';
require_once __DIR__ . '/src/MetaWhatsAppProvider.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/UserRepository.php';
require_once __DIR__ . '/src/BotService.php';

$providerName = $config['provider'] ?? 'twilio';
$provider = $providerName === 'meta'
    ? new MetaWhatsAppProvider($config['meta'] ?? [])
    : new TwilioProvider($config['twilio'] ?? []);

$payload = $_POST;
if (empty($payload)) {
    $rawInput = file_get_contents('php://input');
    $decoded = json_decode($rawInput ?? '', true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

try {
    $pdo = Database::connect($config['db'] ?? []);
    $repository = new UserRepository($pdo);
    $bot = new BotService($provider, $repository);
    $bot->handleIncoming($payload);
} catch (Throwable $exception) {
    error_log('Webhook error: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}
