<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not allowed');
}
$host = 'localhost';
$username = 'root'; 
//$username = 'cocktailcodex_user'; 	// cocktailcodex_user
$password = '';
//$password = 'cocktailcodex_%15cpaEL'; 	// cocktailcodex_%15cpaEL
$database = 'codex01'; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);  // Fixed: Use $database
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log(date('Y-m-d H:i:s') . " Connection failed: " . $e->getMessage() . "\n", 3, 'C:\xampp\php_errors.log');
//    error_log(date('Y-m-d H:i:s') . " Connection failed: " . $e->getMessage() . "\n", 3, '/home/m2igrnpfhd75/public_html/php_errors.log');
    exit;
}
?>