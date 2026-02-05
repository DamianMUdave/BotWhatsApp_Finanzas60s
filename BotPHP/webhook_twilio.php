<?php
require 'core/bot.php';
require 'adapters/twilio.php';

$mensaje = $_POST['Body'] ?? '';
$from    = $_POST['From'] ?? '';

$respuesta = procesarMensaje($mensaje, $from);

responderTwilio($respuesta);
