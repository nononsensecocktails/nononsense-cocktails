<?php
// auth/logout.php

session_start();

// Clear PHP session
$_SESSION = [];
session_destroy();

// Redirect to homepage
header('Location: https://nononsensecocktails.com/');
exit;
