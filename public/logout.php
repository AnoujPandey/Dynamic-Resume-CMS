<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Logout changes session state, so use a POST form with a token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}
verifyCsrfToken();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'],
        $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: login.php');
exit;
