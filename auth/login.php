<?php
// auth/login.php - Start the Auth0 login flow

// Include the config (which loads the SDK)
$auth0 = require_once __DIR__ . '/config.php';

// Clear any previous session state for a clean login
session_start();
session_regenerate_id(true);

// Redirect to Auth0 Universal Login page
// This page will show Google, Facebook, Amazon, and email/password options
header('Location: ' . $auth0->login()->getLoginUrl([
    'redirect_uri' => $_ENV['AUTH0_CALLBACK_URL']
]));
exit;