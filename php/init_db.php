<?php
// Initialize SQLite DB and import data/tracking.json into table `tracking`.
require_once __DIR__ . '/db.php';

$db = get_db();

$db->exec("CREATE TABLE IF NOT EXISTS tracking (
    tcode TEXT PRIMARY KEY,
    origin TEXT,
    destination TEXT,
    sender_name TEXT,
    sender_phone TEXT,
    sender_email TEXT,
    receiver_name TEXT,
    receiver_phone TEXT,
    receiver_email TEXT,
    expectedDelivery TEXT,
    status TEXT,
    date TEXT,
    item TEXT,
    history TEXT
)");

$jsonFile = __DIR__ . '/../data/tracking.json';
if (file_exists($jsonFile)) {
    $raw = file_get_contents($jsonFile);
    $arr = json_decode($raw, true);
    if (is_array($arr)) {
        $stmt = $db->prepare("REPLACE INTO tracking (tcode, origin, destination, sender_name, sender_phone, sender_email, receiver_name, receiver_phone, receiver_email, expectedDelivery, status, date, item, history) VALUES (:tcode, :origin, :destination, :sname, :sphone, :semail, :rname, :rphone, :remail, :exp, :status, :date, :item, :history)");
        foreach ($arr as $tcode => $rec) {
            $history = isset($rec['history']) ? json_encode($rec['history']) : '[]';
            $stmt->execute([
                ':tcode' => $tcode,
                ':origin' => $rec['origin'] ?? null,
                ':destination' => $rec['destination'] ?? null,
                ':sname' => $rec['sender']['name'] ?? null,
                ':sphone' => $rec['sender']['phone'] ?? null,
                ':semail' => $rec['sender']['email'] ?? null,
                ':rname' => $rec['receiver']['name'] ?? null,
                ':rphone' => $rec['receiver']['phone'] ?? null,
                ':remail' => $rec['receiver']['email'] ?? null,
                ':exp' => $rec['expectedDelivery'] ?? null,
                ':status' => $rec['status'] ?? null,
                ':date' => $rec['date'] ?? null,
                ':item' => $rec['item'] ?? null,
                ':history' => $history,
            ]);
        }
        echo "Imported " . count($arr) . " records into SQLite DB.\n";
    } else {
        echo "No valid JSON found in data/tracking.json\n";
    }
} else {
    echo "No data/tracking.json file to import. DB initialized.\n";
}
