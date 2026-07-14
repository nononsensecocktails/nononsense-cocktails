<?php
// auth/login.php - Start login with Auth0 (cookie-based state)

session_start();
session_regenerate_id(true);

require_once __DIR__ . '/../vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$provider = new GenericProvider([
    'clientId'                => $_ENV['AUTH0_CLIENT_ID'],
    'clientSecret'            => $_ENV['AUTH0_CLIENT_SECRET'],
    'redirectUri'             => $_ENV['AUTH0_CALLBACK_URL'],
    'urlAuthorize'            => 'https://' . $_ENV['AUTH0_DOMAIN'] . '/authorize',
    'urlAccessToken'          => 'https://' . $_ENV['AUTH0_DOMAIN'] . '/oauth/token',
    'urlResourceOwnerDetails' => 'https://' . $_ENV['AUTH0_DOMAIN'] . '/userinfo'
]);

$authUrl = $provider->getAuthorizationUrl([
    'scope' => 'openid profile email'
]);

$state = $provider->getState();

// Store state in a short-lived, secure cookie (more reliable on GoDaddy than session)
setcookie('oauth2state', $state, [
    'expires'  => time() + 300,           // 5 minutes
    'path'     => '/',
    'domain'   => 'nononsensecocktails.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

header('Location: ' . $authUrl);
exit;
