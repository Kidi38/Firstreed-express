<?php
$cfg = require __DIR__ . '/config.php';
$dbPath = $cfg['db_path'];

function get_db() {
    global $dbPath;
    static $db = null;
    if ($db) return $db;
    if (!file_exists(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
    }
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (Exception $e) {
        // SQLite/PDO not available on this host. Return null to allow JSON fallback.
        return null;
    }
}
