<?php
function responderTwilio($mensaje) {
    header("Content-Type: text/xml");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Response>';
    echo '<Message>' . htmlspecialchars($mensaje) . '</Message>';
    echo '</Response>';
}
