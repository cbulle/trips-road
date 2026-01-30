<?php
function jsonError($message, $debug = null) {
    echo json_encode([
        'success' => false,
        'message' => $message,
        'debug'   => $debug
    ]);
    exit;
}
