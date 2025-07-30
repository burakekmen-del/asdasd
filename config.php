<?php
// Hataları göster (canlıda kapat)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// .env'i oku (parse_ini_file)
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    $env = parse_ini_file($env_path);
} else {
    die('.env dosyası bulunamadı!');
}

function envv($key, $default = null) {
    global $env;
    return isset($env[$key]) ? $env[$key] : (getenv($key) !== false ? getenv($key) : $default);
}

// Session başlat (.env'den ayarlar)
if (session_status() === PHP_SESSION_NONE) {
    $params = [
        'lifetime'  => (int)envv('SESSION_LIFETIME', 86400),
        'path'      => '/',
        'domain'    => $_SERVER['HTTP_HOST'],
        'secure'    => filter_var(envv('SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN),
        'httponly'  => filter_var(envv('SESSION_HTTPONLY', true), FILTER_VALIDATE_BOOLEAN),
    ];
    if (PHP_VERSION_ID >= 70300) {
        $params['samesite'] = 'Strict';
        session_set_cookie_params($params);
    } else {
        session_set_cookie_params(
            $params['lifetime'],
            $params['path'] . '; samesite=Strict',
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_name(envv('SESSION_NAME', 'PHPSESSID'));
    session_start();
}

// PDO bağlantısı (globals ile her dosyada erişilir)
try {
    $dsn = "mysql:host=" . envv('DB_HOST') . ";dbname=" . envv('DB_NAME') . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
    ];
    $GLOBALS['pdo'] = new PDO($dsn, envv('DB_USER'), envv('DB_PASS'), $options);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>