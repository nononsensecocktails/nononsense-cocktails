<?php

// Auth0 Configuration Loader
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Auth0 SDK Configuration
$config = new SdkConfiguration(
    domain: $_ENV['AUTH0_DOMAIN'],
    clientId: $_ENV['AUTH0_CLIENT_ID'],
    clientSecret: $_ENV['AUTH0_CLIENT_SECRET'],
    redirectUri: $_ENV['AUTH0_CALLBACK_URL'],
    cookieSecret: $_ENV['AUTH0_COOKIE_SECRET']
);

$auth0 = new Auth0($config);

return $auth0;
