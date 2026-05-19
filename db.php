<?php
require_once 'config.php';
//require_once '/home/m2igrnpfhd75/config/config.php';

function getDBConnection() {
    global $host, $username, $password, $database;
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        error_log(date('Y-m-d H:i:s') . " Connection Error: " . $e->getMessage() . "\n", 3, 'C:\xampp\php_errors.log');
//        error_log(date('Y-m-d H:i:s') . " Connection Error: " . $e->getMessage() . "\n", 3, '/home/m2igrnpfhd75/public_html/php_errors.log');
        return null;
    }
}
?>