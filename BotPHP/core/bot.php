<?php
function procesarMensaje($mensaje, $from) {
    $mensaje = strtolower(trim($mensaje));

    if ($mensaje === 'hola') {
        return "Hola 👋 bot funcionando";
    }

    if ($mensaje === 'confirm') {
        return "✅ Confirmado";
    }

    return "No entendí tu mensaje";
}
