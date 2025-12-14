<?php
// Simple tracking endpoint for environments without Node.js
// - If called via AJAX or with Accept: application/json, returns JSON for the tcode
// - Otherwise redirects to track.html?tcode=... so the front-end renders the result

$t = '';
if (!empty($_GET['tcode'])) $t = $_GET['tcode'];
elseif (!empty($_GET['track'])) $t = $_GET['track'];

if (!$t) {
    header('Location: track.html');
    exit;
}


// Use SQLite DB if available
require_once __DIR__ . '/php/db.php';
$record = null;
try {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM tracking WHERE tcode = :t');
    $stmt->execute([':t' => $t]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $record = [
            'tcode' => $row['tcode'],
            'origin' => $row['origin'],
            'destination' => $row['destination'],
            'sender' => ['name' => $row['sender_name'], 'phone' => $row['sender_phone'], 'email' => $row['sender_email']],
            'receiver' => ['name' => $row['receiver_name'], 'phone' => $row['receiver_phone'], 'email' => $row['receiver_email']],
            'expectedDelivery' => $row['expectedDelivery'],
            'status' => $row['status'],
            'date' => $row['date'],
            'item' => $row['item'],
            'history' => json_decode($row['history'] ?? '[]', true),
        ];
    }
} catch (Exception $e) {
    // fallback to JSON file if DB fails
    $dataFile = __DIR__ . '/data/tracking.json';
    if (file_exists($dataFile)) {
        $json = json_decode(file_get_contents($dataFile), true);
        $record = $json[$t] ?? null;
    }
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$acceptsJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($isAjax || $acceptsJson) {
    if ($record) {
        header('Content-Type: application/json');
        echo json_encode($record);
    } else {
        header('Content-Type: application/json', true, 404);
        echo json_encode(['error' => 'Tracking code not found']);
    }
    exit;
}

// For normal browser form submissions, redirect to the track page which will render results
header('Location: track.html?tcode=' . urlencode($t));
exit;

?>
