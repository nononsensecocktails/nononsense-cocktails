<?php
require_once 'config.php';

function getDBConnection() {
    global $host, $username, $password, $database;
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        error_log(date('Y-m-d H:i:s') . " Connection Error: " . $e->getMessage() . "\n");
        return null;
    }
}
?>
