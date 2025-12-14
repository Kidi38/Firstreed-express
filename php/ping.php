<?php
// Simple connectivity/debug endpoint. Returns JSON with server time and session info.
session_start();
header('Content-Type: application/json');
$info = [
    'server_time' => date('c'),
    'session_id' => session_id(),
    'session_admin' => !empty($_SESSION['admin']),
    'php_version' => PHP_VERSION,
];
echo json_encode(['ok'=>true,'info'=>$info]);
?>
