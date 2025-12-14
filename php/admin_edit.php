<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
$cfg = require __DIR__ . '/config.php';
if (empty($_SESSION['admin'])) {
    header('Location: admin.php'); exit;
}
 $db = get_db();
 $use_db = ($db !== null);
 $tcode = $_GET['tcode'] ?? '';
 $row = null;
 if ($tcode) {
   if ($use_db) {
     $stmt = $db->prepare('SELECT * FROM tracking WHERE tcode = :t');
     $stmt->execute([':t' => $tcode]);
     $row = $stmt->fetch(PDO::FETCH_ASSOC);
   } else {
     $jsonFile = __DIR__ . '/../data/tracking.json';
     if (file_exists($jsonFile)) {
       $arr = json_decode(file_get_contents($jsonFile), true);
       if (isset($arr[$tcode])) {
         $rec = $arr[$tcode];
         $row = [
           'tcode' => $rec['tcode'] ?? $tcode,
           'origin' => $rec['origin'] ?? '',
           'destination' => $rec['destination'] ?? '',
           'sender_name' => $rec['sender']['name'] ?? '',
           'sender_phone' => $rec['sender']['phone'] ?? '',
           'sender_email' => $rec['sender']['email'] ?? '',
           'receiver_name' => $rec['receiver']['name'] ?? '',
           'receiver_phone' => $rec['receiver']['phone'] ?? '',
           'receiver_email' => $rec['receiver']['email'] ?? '',
           'expectedDelivery' => $rec['expectedDelivery'] ?? '',
           'status' => $rec['status'] ?? '',
           'date' => $rec['date'] ?? '',
           'item' => $rec['item'] ?? '',
           'history' => json_encode($rec['history'] ?? []),
         ];
       }
     }
   }
 }
function esc($s){ return htmlspecialchars($s ?? ''); }
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Edit <?php echo esc($tcode) ?></title></head><body>
<h1>Edit Record</h1>
<p><a href="admin.php">Back</a></p>
<form method="post" action="admin.php">
  <input type="hidden" name="action" value="update">
  <label>TCode: <input name="tcode" value="<?php echo esc($row['tcode'] ?? '') ?>"></label><br>
  <label>Origin: <input name="origin" value="<?php echo esc($row['origin'] ?? '') ?>"></label>
  <label>Destination: <input name="destination" value="<?php echo esc($row['destination'] ?? '') ?>"></label><br>
  <label>Sender Name: <input name="sender_name" value="<?php echo esc($row['sender_name'] ?? '') ?>"></label>
  <label>Sender Phone: <input name="sender_phone" value="<?php echo esc($row['sender_phone'] ?? '') ?>"></label>
  <label>Sender Email: <input name="sender_email" value="<?php echo esc($row['sender_email'] ?? '') ?>"></label><br>
  <label>Receiver Name: <input name="receiver_name" value="<?php echo esc($row['receiver_name'] ?? '') ?>"></label>
  <label>Receiver Phone: <input name="receiver_phone" value="<?php echo esc($row['receiver_phone'] ?? '') ?>"></label>
  <label>Receiver Email: <input name="receiver_email" value="<?php echo esc($row['receiver_email'] ?? '') ?>"></label><br>
  <label>Expected Delivery: <input name="expectedDelivery" value="<?php echo esc($row['expectedDelivery'] ?? '') ?>"></label>
  <label>Status: <input name="status" value="<?php echo esc($row['status'] ?? '') ?>"></label>
  <label>Date: <input name="date" value="<?php echo esc($row['date'] ?? '') ?>"></label>
  <label>Item: <input name="item" value="<?php echo esc($row['item'] ?? '') ?>"></label><br>
  <label>History (JSON): <textarea name="history" rows="6" cols="80"><?php echo esc($row['history'] ?? '[]') ?></textarea></label><br>
  <button>Save</button>
</form>
</body></html>
