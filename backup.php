<?php
require_once '../config.php';
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) { http_response_code(403); exit('Yetkisiz!'); }

$filename = 'affiluxe_backup_' . date('Ymd_His') . '.sql';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$filename.'"');

// .env bilgilerinden çek
$db = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$host = $env['DB_HOST'];

// Mysqldump komutu (sunucuda mysqldump kurulu olmalı)
$cmd = "mysqldump --user=" . escapeshellarg($user) .
       " --password=" . escapeshellarg($pass) .
       " --host=" . escapeshellarg($host) .
       " --single-transaction --skip-lock-tables --add-drop-table --databases " . escapeshellarg($db);

passthru($cmd);
exit;
?>