<?php
// auth/callback.php - Handle return from Auth0 + save user

session_start();

try {
    $auth0 = require_once __DIR__ . '/config.php';

    // Get the authenticated user from Auth0
    $user = $auth0->getUser();

    if (!$user || empty($user['sub'])) {
        throw new Exception('No valid user returned from Auth0');
    }

    // Include your existing database configuration and connection
    require_once __DIR__ . '/../db.php';
    $conn = getDBConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Upsert user into the users table
    $stmt = $conn->prepare("
        INSERT INTO users (auth0_sub, email, name, picture, provider, last_login)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            email = VALUES(email),
            name = VALUES(name),
            picture = VALUES(picture),
            last_login = NOW()
    ");

    $provider = $user['sub'] ? explode('|', $user['sub'])[0] : 'auth0';

    $stmt->execute([
        $user['sub'],
        $user['email'] ?? '',
        $user['name'] ?? '',
        $user['picture'] ?? '',
        $provider
    ]);

    // Get the internal user ID (optional but useful)
    $user_id = $conn->lastInsertId();
    if (empty($user_id)) {
        // If it was an update, fetch existing ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE auth0_sub = ?");
        $stmt->execute([$user['sub']]);
        $user_id = $stmt->fetchColumn();
    }

    // Set PHP session variables (this makes the rest of your site aware of the logged-in user)
    $_SESSION['user_id'] = $user_id;
    $_SESSION['auth0_sub'] = $user['sub'];
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['user_name'] = $user['name'] ?? '';
    $_SESSION['user_picture'] = $user['picture'] ?? '';
    $_SESSION['is_logged_in'] = true;

    // Redirect to homepage (or you can use a return-to URL later)
    header('Location: https://nononsensecocktails.com/');
    exit;

} catch (Exception $e) {
    error_log('Auth0 Callback Error: ' . $e->getMessage());
    header('Location: https://nononsensecocktails.com/?error=login_failed');
    exit;
}