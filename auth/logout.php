<?php
// auth/logout.php - Handle logout

session_start();

try {
    $auth0 = require_once __DIR__ . '/config.php';

    // Clear PHP session
    $_SESSION = [];
    session_destroy();

    // Redirect to Auth0 logout (clears Auth0 session too)
    header('Location: ' . $auth0->logout($_ENV['AUTH0_LOGOUT_URL']));
    exit;

} catch (Exception $e) {
    error_log('Auth0 Logout Error: ' . $e->getMessage());
    header('Location: https://nononsensecocktails.com/');
    exit;
}