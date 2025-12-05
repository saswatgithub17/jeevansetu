<?php
// handlers/logout_process.php

session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to the login page
require_once __DIR__ . '/../includes/config.php';
header("Location: " . BASE_URL . "views/public/login.php?logout=success");
exit;
?>