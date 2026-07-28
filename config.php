<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not allowed');
}
$host = 'localhost';
$username = 'root';          // local only
$password = '';             // local only
$database = 'codex01';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);  // Fixed: Use $database
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log(date('Y-m-d H:i:s') . " Connection failed: " . $e->getMessage() . "\n");
    exit;
}
?>
