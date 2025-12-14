<?php
// list_tracking.php - returns an array of tracking summaries
header('Content-Type: application/json');
session_start();

// require admin session to view full list
if (empty($_SESSION['admin'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$dataFile = __DIR__ . '/../data/tracking.json';
$all = [];
if (file_exists($dataFile)) $all = json_decode(file_get_contents($dataFile), true) ?: [];

$list = [];
foreach($all as $t => $rec) {
    $list[] = [
        'tcode' => $t,
        'status' => $rec['status'] ?? '',
        'created_at' => $rec['created_at'] ?? ($rec['date'] ?? ''),
        'origin' => $rec['origin'] ?? '',
        'destination' => $rec['destination'] ?? '',
        'products_count' => is_array($rec['products'])?count($rec['products']):0,
    ];
}

echo json_encode(['ok'=>true,'list'=>$list]);

?>
