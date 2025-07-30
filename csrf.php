<?php
// BURAYI SİL: if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

if (!defined('CSRF_TOKEN_EXPIRE')) define('CSRF_TOKEN_EXPIRE', 3600);

function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (
            empty($_POST['csrf_token']) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            die("Invalid request, CSRF protection.");
        }
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRE)) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            die("CSRF token expired, please refresh the form.");
        }
        // Her başarılı POST'tan sonra token yenile:
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
}
?>