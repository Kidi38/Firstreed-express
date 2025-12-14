<?php
// update_status.php
// POST { tcode, status, note } - updates status and appends history. Requires admin session.
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['admin'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$dataFile = __DIR__ . '/../data/tracking.json';
if (!file_exists($dataFile)) { http_response_code(404); echo json_encode(['error'=>'No tracking data']); exit; }

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$tcode = $input['tcode'] ?? null;
$status = $input['status'] ?? null;
$note = $input['note'] ?? '';

if (!$tcode || !$status) { http_response_code(400); echo json_encode(['error'=>'Missing tcode or status']); exit; }

$all = json_decode(file_get_contents($dataFile), true) ?: [];
if (!isset($all[$tcode])) { http_response_code(404); echo json_encode(['error'=>'Tracking ID not found']); exit; }

$rec = $all[$tcode];
$rec['status'] = $status;
if (!isset($rec['history']) || !is_array($rec['history'])) $rec['history'] = [];
$rec['history'][] = ['when'=>date('Y-m-d H:i:s'),'status'=>$status,'note'=>$note];
$all[$tcode] = $rec;
file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode(['ok'=>true,'record'=>$rec]);

?>
