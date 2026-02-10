<?php
require __DIR__ . '/webhook_twilio.php';
/*
header("Content-Type: text/xml");

// Leer datos que manda Twilio
$mensaje = strtolower(trim($_POST['Body'] ?? ''));
$from    = $_POST['From'] ?? '';

// Log para depurar
file_put_contents(
    "logs.txt",
    date("Y-m-d H:i:s") . "\n" . print_r($_POST, true) . "\n\n",
    FILE_APPEND
);

// Respuesta
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<Response>';

if ($mensaje === 'hola') {
    echo '<Message>Hola 👋 bot funcionando correctamente</Message>';
} else {
    echo '<Message>No entendí tu mensaje</Message>';
}

echo '</Response>';
*/