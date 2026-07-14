<?php
// auth/callback.php - Production version (user-friendly)

error_reporting(E_ALL);
ini_set('display_errors', 0);   // Hide technical errors from users
ini_set('log_errors', 1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $provider = new GenericProvider([
        'clientId'                => $_ENV['AUTH0_CLIENT_ID'],
        'clientSecret'            => $_ENV['AUTH0_CLIENT_SECRET'],
        'redirectUri'             => $_ENV['AUTH0_CALLBACK_URL'],
        'urlAuthorize'            => 'https://' . $_ENV['AUTH0_DOMAIN'] . '/authorize',
        'urlAccessToken'          => 'https://' . $_ENV['AUTH0_DOMAIN'] . '/oauth/token',
        'urlResourceOwnerDetails' => 'https://' . $_ENV['AUTH0_DOMAIN'] . '/userinfo'
    ]);

    // === Validate state from cookie ===
    if (empty($_GET['state']) || empty($_COOKIE['oauth2state'])) {
        throw new Exception('Login session expired or invalid. Please try again.');
    }

    if ($_GET['state'] !== $_COOKIE['oauth2state']) {
        setcookie('oauth2state', '', time() - 3600, '/');
        throw new Exception('Login session expired or invalid. Please try again.');
    }

    // Exchange code for access token
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Get user profile from Auth0
    $resourceOwner = $provider->getResourceOwner($accessToken);
    $user = $resourceOwner->toArray();

    if (empty($user['sub'])) {
        throw new Exception('Unable to retrieve your profile from the login provider.');
    }

    // === Save or update user in database ===
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('Database connection failed. Please try again later.');
    }

    $stmt = $conn->prepare("
        INSERT INTO users (auth0_sub, email, name, picture, provider, last_login)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            email = VALUES(email),
            name = VALUES(name),
            picture = VALUES(picture),
            last_login = NOW()
    ");

    $providerName = explode('|', $user['sub'])[0] ?? 'auth0';

    $stmt->execute([
        $user['sub'],
        $user['email'] ?? '',
        $user['name'] ?? ($user['nickname'] ?? ''),
        $user['picture'] ?? '',
        $providerName
    ]);

    // Get internal user ID
    $user_id = $conn->lastInsertId();
    if (empty($user_id)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE auth0_sub = ?");
        $stmt->execute([$user['sub']]);
        $user_id = $stmt->fetchColumn();
    }

    // === Set session variables ===
    $_SESSION['user_id']        = $user_id;
    $_SESSION['auth0_sub']      = $user['sub'];
    $_SESSION['user_email']     = $user['email'] ?? '';
    $_SESSION['user_name']      = $user['name'] ?? ($user['nickname'] ?? '');
    $_SESSION['user_picture']   = $user['picture'] ?? '';
    $_SESSION['is_logged_in']   = true;

    // Clean up temporary cookie
    setcookie('oauth2state', '', time() - 3600, '/');

    // Success — redirect to homepage
    header('Location: https://nononsensecocktails.com/');
    exit;

} catch (IdentityProviderException $e) {
    // Log the real error
    error_log('Auth0 IdentityProviderException: ' . $e->getMessage());
    echo "<h2>Login Failed</h2>";
    echo "<p>Sorry, we were unable to complete your login. Please try again.</p>";
    exit;

} catch (Throwable $e) {
    // Log the real error
    error_log('Auth0 Callback Error: ' . $e->getMessage());
    echo "<h2>Login Failed</h2>";
    echo "<p>Sorry, something went wrong during login. Please try again later.</p>";
    exit;
}
