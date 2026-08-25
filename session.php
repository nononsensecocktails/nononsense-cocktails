<?php
// session.php
// Keeps users logged in for 30 days

$lifetime = 60 * 60 * 24 * 30; // 30 days in seconds

ini_set('session.gc_maxlifetime', (string)$lifetime);
ini_set('session.cookie_lifetime', (string)$lifetime);

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => '/',
    'domain'   => 'nononsensecocktails.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}