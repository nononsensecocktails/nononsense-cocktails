<?php
// auth/login.php - Start the Auth0 login flow

session_start();
session_regenerate_id(true);

try {
    $auth0 = require __DIR__ . '/config.php';

    // This is the correct method in the current SDK version
    $loginUrl = $auth0->login($_ENV['AUTH0_CALLBACK_URL']);

    header('Location: ' . $loginUrl);
    exit;

} catch (Throwable $e) {
    echo "<h2>Login Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}
