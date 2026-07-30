<?php
require_once 'db.php';  // Added: To get getDBConnection()
require_once 'filter_functions.php';
session_start();

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
        case 'getUnitConversions':
            $result = getUnitConversions($conn);
            break;
        case 'saveRating':
            // Must be logged in
            if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id']) || empty($_SESSION['user_name'])) {
                $result = ['success' => false, 'error' => 'You must be logged in to save a rating.'];
                break;
            }

            $name       = $_POST['name'] ?? '';
            $source     = $_POST['source'] ?? '';
            $stars      = $_POST['stars'] ?? '';
            $last_date  = $_POST['last_date'] ?? '';

            // Default: always the logged-in user
            $user_id    = (int)$_SESSION['user_id'];
            $username   = $_SESSION['user_name'];

            // Admin override – only the hardcoded Auth0 sub may rate for another user
            $is_admin = (isset($_SESSION['auth0_sub']) && $_SESSION['auth0_sub'] === 'google-oauth2|115671403431087083309');
            $target_username = trim($_POST['target_username'] ?? '');

            if ($is_admin && $target_username !== '' && strcasecmp($target_username, 'All') !== 0) {
                $stmt = $conn->prepare("SELECT id, name FROM users WHERE name = ?");
                $stmt->execute([$target_username]);
                $target = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($target) {
                    $user_id  = (int)$target['id'];
                    $username = $target['name'];
                } else {
                    $result = ['success' => false, 'error' => 'Target user not found.'];
                    break;
                }
            }

            $result = saveRating($conn, $name, $source, $stars, $last_date, $user_id, $username);
            break;

        case 'updateUserProfile':
            // Must be logged in
            if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
                $result = ['success' => false, 'error' => 'You must be logged in to update your profile.'];
                break;
            }

            // NEVER trust client-supplied user_id
            $user_id      = (int)$_SESSION['user_id'];
            $new_username = trim($_POST['new_username'] ?? '');
            $do_not_show  = isset($_POST['do_not_show_username']) ? (int)$_POST['do_not_show_username'] : 0;

            $result = updateUserProfile($conn, $user_id, $new_username, $do_not_show);
            break;
        default:
            $result = ['error' => 'Invalid action'];
    }
    ob_end_clean();
    exit(json_encode($result));
} catch (Exception $e) {
    error_log(date('Y-m-d H:i:s') . " Error: " . $e->getMessage() . "\n");
    ob_end_clean();
    exit(json_encode(['error' => 'An internal error occurred. Please try again later.']));
}
?>
