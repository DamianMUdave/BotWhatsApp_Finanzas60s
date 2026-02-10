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

$to = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';

if ($to === '' || $message === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'to y message son obligatorios']);
    exit;
}

try {
    $pdo = Database::connect($config['db'] ?? []);
    $repository = new UserRepository($pdo);
    $bot = new BotService($provider, $repository);
    $ok = $bot->sendMessage($to, $message);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok]);
} catch (Throwable $exception) {
    error_log('Send error: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}
