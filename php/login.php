<?php
session_start();
header('Content-Type: application/json');

// Ensure credentials exist (bootstrap from config if needed)
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';
$dataFile = __DIR__ . '/../data/admin.json';
if (!file_exists($dataFile)) {
    if (!file_exists(dirname($dataFile))) mkdir(dirname($dataFile), 0755, true);
    $initial = [
        'user' => $cfg['initial_admin_user'] ?? 'admin',
        'pass_hash' => password_hash($cfg['initial_admin_password'] ?? 'admin', PASSWORD_DEFAULT),
    ];
    file_put_contents($dataFile, json_encode($initial, JSON_PRETTY_PRINT));
}
$creds = json_decode(file_get_contents($dataFile), true);

// Backwards compatibility: if creds stored plaintext 'pass', allow login and upgrade to hashed password
$plainFallback = false;
if (isset($creds['user']) && isset($creds['pass'])) {
    $plainFallback = true;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$user = $input['username'] ?? null;
$pass = $input['password'] ?? null;

if (!$user || !$pass) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing username or password']);
    exit;
}

if (isset($creds['user']) && isset($creds['pass_hash']) && $user === $creds['user'] && password_verify($pass, $creds['pass_hash'])) {
    $_SESSION['admin'] = true;
    echo json_encode(['ok' => true]);
} elseif ($plainFallback && $user === $creds['user'] && hash_equals($creds['pass'], $pass)) {
    // Plaintext credential matched; upgrade to secure hash
    $creds['pass_hash'] = password_hash($creds['pass'], PASSWORD_DEFAULT);
    unset($creds['pass']);
    file_put_contents($dataFile, json_encode($creds, JSON_PRETTY_PRINT));
    $_SESSION['admin'] = true;
    echo json_encode(['ok' => true, 'upgraded' => true]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
}

?>
