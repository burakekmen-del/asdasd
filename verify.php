<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$msg = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verify_token=? AND is_verified=0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $pdo->prepare("UPDATE users SET is_verified=1, verify_token=NULL WHERE id=?")->execute([$user['id']]);
        $msg = "Tebrikler! Hesabınız doğrulandı. Giriş yapabilirsiniz.";
    } else {
        $msg = "Doğrulama başarısız! Token geçersiz veya zaten doğrulanmış.";
    }
} else {
    $msg = "Doğrulama tokeni eksik!";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hesap Doğrulama</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>body{background:#f5f7ff;} .verify-box{max-width:410px;margin:100px auto;background:#fff;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);padding:2.5rem 2rem;text-align:center;}</style>
</head>
<body>
    <div class="verify-box">
        <h2>Hesap Doğrulama</h2>
        <div><?= htmlspecialchars($msg) ?></div>
        <div class="mt-3 text-center">
            <a href="login.php" style="color:#e63946;">Giriş Yap</a>
        </div>
    </div>
</body>
</html>
