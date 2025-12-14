<?php
// create_tracking.php
// Accepts POST (application/json or form) to create a new tracking entry and returns JSON { tcode, record }
session_start();
header('Content-Type: application/json');

// require admin session for creation
if (empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$dataFile = __DIR__ . '/../data/tracking.json';
if (!file_exists(dirname($dataFile))) mkdir(dirname($dataFile), 0755, true);

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Validate minimal fields
$products = $input['products'] ?? null; // expected array
if (!$products || !is_array($products) || count($products) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing products array']);
    exit;
}

$sender = $input['sender'] ?? ['name'=>'','phone'=>'','email'=>''];
$receiver = $input['receiver'] ?? ['name'=>'','phone'=>'','email'=>''];
$origin = $input['origin'] ?? '';
$destination = $input['destination'] ?? '';
$expected = $input['expectedDelivery'] ?? '';
$status = $input['status'] ?? 'pending';

// Load existing
$all = [];
if (file_exists($dataFile)) {
    $raw = file_get_contents($dataFile);
    $all = json_decode($raw, true) ?: [];
}

// Generate unique TRK-###### using incremental counter stored in data/last_id.txt
$lastFile = __DIR__ . '/../data/last_id.txt';
$last = 0;
if (file_exists($lastFile)) { $last = (int)trim(file_get_contents($lastFile)); }
else {
    // compute from existing keys
    foreach(array_keys($all) as $k){
        if (preg_match('/TRK-(\d{6})$/', $k, $m)) { $n = (int)$m[1]; if ($n>$last) $last=$n; }
    }
}

do {
    $last++;
    $code = sprintf('TRK-%06d', $last);
} while(isset($all[$code]));

// Save new last
file_put_contents($lastFile, (string)$last, LOCK_EX);

$record = [
    'tcode' => $code,
    'created_at' => date('Y-m-d H:i:s'),
    'origin' => $origin,
    'destination' => $destination,
    'sender' => $sender,
    'receiver' => $receiver,
    'expectedDelivery' => $expected,
    'status' => $status,
    'products' => array_values($products),
    'history' => [ ['when'=>date('Y-m-d H:i:s'), 'status'=>$status, 'note'=>'Created'] ]
];

$all[$code] = $record;
file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode(['ok'=>true,'tcode'=>$code,'record'=>$record]);

?>
