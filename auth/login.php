<?php
// auth/login.php - Start the Auth0 login flow

session_start();
session_regenerate_id(true);

try {
    $auth0 = require __DIR__ . '/config.php';

    // Generate the Auth0 login URL (correct method for current SDK version)
    $loginUrl = $auth0->getLoginLink([
        'redirect_uri' => $_ENV['AUTH0_CALLBACK_URL']
    ]);

    // Redirect the user to Auth0
    header('Location: ' . $loginUrl);
    exit;

} catch (Throwable $e) {
    // Show the real error for debugging (remove this block later)
    echo "<h2>Login Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}
