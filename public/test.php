<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Test endpoint is working',
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'php_version' => phpversion(),
    'time' => date('Y-m-d H:i:s')
]);
