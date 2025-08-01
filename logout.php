<?php
require_once 'config.php';

// Tüm session değişkenlerini temizle
$_SESSION = [];

// Oturum çerezini sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session'ı tamamen sonlandır
session_destroy();

header("Location: index.php");
exit;