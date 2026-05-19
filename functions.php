<?php
require_once 'db.php';

function getUsernames($conn) {
    try {
        $stmt = $conn->prepare("SELECT DISTINCT username FROM user_ratings ORDER BY username ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
        error_log(date('Y-m-d H:i:s') . " Error fetching usernames: " . $e->getMessage() . "\n", 3, 'C:\xampp\php_errors.log');
//        error_log(date('Y-m-d H:i:s') . " Error fetching usernames: " . $e->getMessage() . "\n", 3, '/home/m2igrnpfhd75/public_html/php_errors.log');
        return [];
    }
}
?>