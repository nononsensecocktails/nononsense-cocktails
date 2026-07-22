<?php
require_once 'db.php';

function getUsernames($conn) {
    try {
        // Only return usernames of users who have not opted out of public display
        $stmt = $conn->prepare("
            SELECT DISTINCT ur.username
            FROM user_ratings ur
            INNER JOIN users u ON u.id = ur.user_id
            WHERE COALESCE(u.do_not_show_username, 0) = 0
            ORDER BY ur.username ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
        error_log(date('Y-m-d H:i:s') . " Error fetching usernames: " . $e->getMessage() . "\n", 3, '/home/m2igrnpfhd75/public_html/php_errors.log');
        return [];
    }
}
?>
