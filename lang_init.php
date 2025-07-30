<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$supported_langs = ['tr', 'en'];
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'en');
if (!in_array($lang, $supported_langs)) $lang = 'en';
$_SESSION['lang'] = $lang;
$lang_file = __DIR__ . "/lang/$lang.php";
if (file_exists($lang_file)) {
    require_once $lang_file;
} else {
    require_once __DIR__ . "/lang/en.php";
}
function l($key) { global $lang_arr; return $lang_arr[$key] ?? $key; }