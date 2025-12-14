<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

 $cfg = require __DIR__ . '/config.php';
 $db = get_db();
 $use_db = ($db !== null);
 $jsonFile = __DIR__ . '/../data/tracking.json';

 function read_json_records($jsonFile) {
   if (!file_exists($jsonFile)) return [];
   $raw = file_get_contents($jsonFile);
   $arr = json_decode($raw, true);
   return is_array($arr) ? $arr : [];
 }

 function write_json_records($jsonFile, $arr) {
   file_put_contents($jsonFile, json_encode($arr, JSON_PRETTY_PRINT));
 }

// Credentials file (stores username and password hash)
$credFile = __DIR__ . '/../data/admin.json';
// Bootstrap credentials file if it doesn't exist
if (!file_exists($credFile)) {
  $initial = [
    'user' => $cfg['initial_admin_user'] ?? 'admin',
    'pass_hash' => password_hash($cfg['initial_admin_password'] ?? 'admin', PASSWORD_DEFAULT),
  ];
  if (!file_exists(dirname($credFile))) mkdir(dirname($credFile), 0755, true);
  file_put_contents($credFile, json_encode($initial, JSON_PRETTY_PRINT));
}

// Read credentials
$creds = json_decode(file_get_contents($credFile), true);

// Logout
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: admin.php');
  exit;
}

// Login
if (isset($_POST['login_username']) || isset($_POST['login_password'])) {
  $u = $_POST['login_username'] ?? '';
  $p = $_POST['login_password'] ?? '';
  if ($u && $p && isset($creds['user']) && isset($creds['pass_hash']) && password_verify($p, $creds['pass_hash']) && $u === $creds['user']) {
    $_SESSION['admin'] = true;
  } else {
    $error = 'Invalid username or password';
  }
}

if (empty($_SESSION['admin'])) {
  ?>
  <!doctype html>
  <html><head><meta charset="utf-8"><title>Admin Login</title></head><body>
  <h2>Admin Login</h2>
  <?php if(!empty($error)) echo '<div style="color:red">'.htmlspecialchars($error).'</div>'; ?>
  <form method="post">
    <label>Username: <input type="text" name="login_username" required></label><br>
    <label>Password: <input type="password" name="login_password" required></label><br>
    <button>Login</button>
  </form>
  <p>If you have never logged in, use username <strong><?php echo htmlspecialchars($creds['user'] ?? 'admin') ?></strong> and the temporary password provided in the package README.</p>
  </body></html>
  <?php
  exit;
}

// Handle create/update/delete (DB or JSON fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
  $action = $_POST['action'];
  // Change credentials (only accessible while logged in)
  if ($action === 'change_credentials') {
    $new_user = $_POST['new_user'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    if ($new_user && $new_pass) {
      $adminCred = ['user' => $new_user, 'pass_hash' => password_hash($new_pass, PASSWORD_DEFAULT)];
      file_put_contents(__DIR__ . '/../data/admin.json', json_encode($adminCred, JSON_PRETTY_PRINT));
    }
    header('Location: admin.php'); exit;
  }
  // Delete
  if ($action === 'delete' && !empty($_POST['tcode'])) {
    if ($use_db) {
      $stmt = $db->prepare('DELETE FROM tracking WHERE tcode = :t');
      $stmt->execute([':t' => $_POST['tcode']]);
    } else {
      $arr = read_json_records($jsonFile);
      unset($arr[$_POST['tcode']]);
      write_json_records($jsonFile, $arr);
    }
    header('Location: admin.php'); exit;
  }

  // Create / Update
  if (($action === 'create' || $action === 'update') && !empty($_POST['tcode'])) {
    $history = isset($_POST['history']) ? $_POST['history'] : '[]';
    // ensure valid JSON
    $historyArr = json_decode($history, true);
    if (!is_array($historyArr)) { $history = '[]'; $historyArr = []; }

    if ($use_db) {
      $stmt = $db->prepare('REPLACE INTO tracking (tcode, origin, destination, sender_name, sender_phone, sender_email, receiver_name, receiver_phone, receiver_email, expectedDelivery, status, date, item, history) VALUES (:tcode, :origin, :destination, :sname, :sphone, :semail, :rname, :rphone, :remail, :exp, :status, :date, :item, :history)');
      $stmt->execute([
        ':tcode' => $_POST['tcode'],
        ':origin' => $_POST['origin'] ?? null,
        ':destination' => $_POST['destination'] ?? null,
        ':sname' => $_POST['sender_name'] ?? null,
        ':sphone' => $_POST['sender_phone'] ?? null,
        ':semail' => $_POST['sender_email'] ?? null,
        ':rname' => $_POST['receiver_name'] ?? null,
        ':rphone' => $_POST['receiver_phone'] ?? null,
        ':remail' => $_POST['receiver_email'] ?? null,
        ':exp' => $_POST['expectedDelivery'] ?? null,
        ':status' => $_POST['status'] ?? null,
        ':date' => $_POST['date'] ?? null,
        ':item' => $_POST['item'] ?? null,
        ':history' => json_encode($historyArr),
      ]);
    } else {
      $arr = read_json_records($jsonFile);
      $arr[$_POST['tcode']] = [
        'tcode' => $_POST['tcode'],
        'origin' => $_POST['origin'] ?? null,
        'destination' => $_POST['destination'] ?? null,
        'sender' => ['name' => $_POST['sender_name'] ?? null, 'phone' => $_POST['sender_phone'] ?? null, 'email' => $_POST['sender_email'] ?? null],
        'receiver' => ['name' => $_POST['receiver_name'] ?? null, 'phone' => $_POST['receiver_phone'] ?? null, 'email' => $_POST['receiver_email'] ?? null],
        'expectedDelivery' => $_POST['expectedDelivery'] ?? null,
        'status' => $_POST['status'] ?? null,
        'date' => $_POST['date'] ?? null,
        'item' => $_POST['item'] ?? null,
        'history' => $historyArr,
      ];
      write_json_records($jsonFile, $arr);
    }
    header('Location: admin.php'); exit;
  }
}

// Read all records (DB or JSON)
if ($use_db) {
  $rows = $db->query('SELECT tcode, origin, destination, status, date FROM tracking ORDER BY date DESC')->fetchAll(PDO::FETCH_ASSOC);
} else {
  $arr = read_json_records($jsonFile);
  $rows = [];
  foreach ($arr as $t => $rec) {
    $rows[] = ['tcode' => $t, 'origin' => $rec['origin'] ?? '', 'destination' => $rec['destination'] ?? '', 'status' => $rec['status'] ?? '', 'date' => $rec['date'] ?? ''];
  }
}

function esc($s){ return htmlspecialchars($s ?? ''); }

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tracking Admin</title>
  <style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
  </head>
<body>
  <h1>Tracking Admin</h1>
  <p><a href="admin.php?logout=1">Log out</a></p>

  <h2>Create / Update Record</h2>
  <form method="post">
    <input type="hidden" name="action" value="create">
    <label>TCode: <input name="tcode"></label><br>
    <label>Origin: <input name="origin"></label>
    <label>Destination: <input name="destination"></label><br>
    <label>Sender Name: <input name="sender_name"></label>
    <label>Sender Phone: <input name="sender_phone"></label>
    <label>Sender Email: <input name="sender_email"></label><br>
    <label>Receiver Name: <input name="receiver_name"></label>
    <label>Receiver Phone: <input name="receiver_phone"></label>
    <label>Receiver Email: <input name="receiver_email"></label><br>
    <label>Expected Delivery: <input name="expectedDelivery"></label>
    <label>Status: <input name="status"></label>
    <label>Date: <input name="date"></label>
    <label>Item: <input name="item"></label><br>
    <label>History (JSON array): <textarea name="history" rows="4" cols="80">[]</textarea></label><br>
    <button type="submit">Save</button>
  </form>

  <h2>Admin Account</h2>
  <p>Change admin username or password (will update `data/admin.json`).</p>
  <form method="post" onsubmit="return confirm('Change credentials?')">
    <input type="hidden" name="action" value="change_credentials">
    <label>New username: <input name="new_user" value="<?php echo esc($creds['user'] ?? 'admin') ?>" required></label><br>
    <label>New password: <input type="password" name="new_pass" placeholder="Enter new password" required></label><br>
    <button type="submit">Change credentials</button>
  </form>

  <h2>Existing Records</h2>
  <table>
    <thead><tr><th>TCode</th><th>Origin</th><th>Destination</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?php echo esc($r['tcode']) ?></td>
        <td><?php echo esc($r['origin']) ?></td>
        <td><?php echo esc($r['destination']) ?></td>
        <td><?php echo esc($r['status']) ?></td>
        <td><?php echo esc($r['date']) ?></td>
        <td>
          <a href="admin_edit.php?tcode=<?php echo urlencode($r['tcode']) ?>">Edit</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="tcode" value="<?php echo esc($r['tcode']) ?>">
            <button>Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p>Tip: Use <strong>admin_edit.php</strong> to load a record into the form quickly.</p>
</body>
</html>
