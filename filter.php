<?php
require_once 'db.php';  // Added: To get getDBConnection()
require_once 'filter_functions.php';

header('Content-Type: application/json');
ob_start();

$conn = getDBConnection();
if (!$conn) {
    ob_end_clean();
    exit(json_encode(['error' => 'Database connection failed']));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'getTotalCocktails':
            $user = $_GET['user'] ?? 'All';
            $filters_json = $_GET['filters'] ?? '[]';
            $filters_data = json_decode($filters_json, true);
            $result = getTotalCocktails($conn, $user, $filters_data);
            break;
        case 'getDistinctValues':
            $term = $_GET['term'] ?? '';
            $user = $_GET['user'] ?? 'All';
            $filters_json = $_GET['filters'] ?? '[]';
            $filters_data = json_decode($filters_json, true);
            $result = getDistinctValues($conn, $term, $user, $filters_data);
            break;
        case 'getRandomRecipe':
            $user = $_GET['user'] ?? 'All';
            $filters_json = $_GET['filters'] ?? '[]';
            $filters_data = json_decode($filters_json, true);
            $result = getRandomRecipe($conn, $user, $filters_data);
            break;
        case 'getNames':
            $user = $_GET['user'] ?? 'All';
            $filters_json = $_GET['filters'] ?? '[]';
            $filters_data = json_decode($filters_json, true);
            $result = getNames($conn, $user, $filters_data);
            break;
        case 'getSources':
            $name = $_GET['name'] ?? '';
            $user = $_GET['user'] ?? 'All';
            $filters_json = $_GET['filters'] ?? '[]';
            $filters_data = json_decode($filters_json, true);
            $result = getSources($conn, $name, $user, $filters_data);
            break;
        case 'getRecipeDetails':
            $name = $_GET['name'] ?? '';
            $source = $_GET['source'] ?? '';
            $user = $_GET['user'] ?? 'All';
            $result = getRecipeDetails($conn, $name, $source, $user);
            break;
        case 'saveRating':
            $name = $_POST['name'] ?? '';
            $source = $_POST['source'] ?? '';
            $stars = $_POST['stars'] ?? '';
            $last_date = $_POST['last_date'] ?? '';
            $user_id = $_POST['user_id'] ?? null;
            $username = $_POST['username'] ?? '';
            $result = saveRating($conn, $name, $source, $stars, $last_date, $user_id, $username);
            break;
        case 'getUnitConversions':
            $result = getUnitConversions($conn);
            break;
        case 'updateUserProfile':
            $user_id = $_POST['user_id'] ?? null;
            $new_username = trim($_POST['new_username'] ?? '');
            $do_not_show = isset($_POST['do_not_show_username']) ? (int)$_POST['do_not_show_username'] : 0;
            $result = updateUserProfile($conn, $user_id, $new_username, $do_not_show);
            break;
        default:
            $result = ['error' => 'Invalid action'];
    }
    ob_end_clean();
    exit(json_encode($result));
} catch (Exception $e) {
    error_log(date('Y-m-d H:i:s') . " Error: " . $e->getMessage() . "\n", 3, '/home/m2igrnpfhd75/public_html/php_errors.log');
    ob_end_clean();
    exit(json_encode(['error' => 'Query failed: ' . $e->getMessage()]));
}
?>
