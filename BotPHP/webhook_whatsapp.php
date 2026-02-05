<?php
require 'core/bot.php';
require 'adapters/whatsapp.php';

$input = json_decode(file_get_contents("php://input"), true);

$mensaje = $input['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] ?? '';
$from    = $input['entry'][0]['changes'][0]['value']['messages'][0]['from'] ?? '';

$respuesta = procesarMensaje($mensaje, $from);

responderCloud($from, $respuesta);
