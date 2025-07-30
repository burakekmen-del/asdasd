<?php
require_once 'config.php';
require_once 'csrf.php';
require_once __DIR__ . '/vendor/autoload.php';

$error = $success = '';
$email = $_SESSION['reset_email'] ?? '';

if (!isset($_SESSION['reset_verified']) || !$_SESSION['reset_verified'] || !$email) {
    header("Location: forgot-password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $new_pass = $_POST['new_pass'] ?? '';
    $new_pass2 = $_POST['new_pass2'] ?? '';

    if (!$new_pass || !$new_pass2) {
        $error = "Tüm alanları doldurun!";
    } elseif ($new_pass !== $new_pass2) {
        $error = "Şifreler eşleşmiyor!";
    } elseif (strlen($new_pass) < 8) {
        $error = "Şifre en az 8 karakter olmalı!";
    } elseif (!preg_match('/[A-Z]/', $new_pass) || !preg_match('/[a-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
        $error = "Şifre büyük harf, küçük harf ve rakam içermeli!";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=?, verify_code=NULL WHERE email=?");
        $stmt->execute([$hash, $email]);
        unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
        $success = "Şifreniz başarıyla sıfırlandı! <a href='login.php'>Giriş yap</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Şifre | Affiluxe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .box { max-width: 400px; margin: 100px auto; background: #fff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 2.5rem 2rem; }
        .box h2 { font-weight: 700; color: #6C5CE7; margin-bottom: 2rem;}
        .error-msg { color: #e63946; font-weight:600; margin-bottom:1rem;}
        .success-msg { color: #2ec4b6; font-weight:600; margin-bottom:1rem;}
        .btn-main { background:#6C5CE7; color:#fff; }
        .btn-main:hover { background:#5649C0; color:#fff; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Yeni Şifre Oluştur</h2>
        <?php if($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="success-msg"><?= $success ?></div><?php endif; ?>

        <?php if(!$success): ?>
        <form method="post" autocomplete="off">
            <?= csrf_input() ?>
            <div class="mb-3">
                <label class="form-label">Yeni Şifre</label>
                <input type="password" name="new_pass" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Yeni Şifre (Tekrar)</label>
                <input type="password" name="new_pass2" class="form-control" required minlength="8">
            </div>
            <button class="btn btn-main w-100" type="submit">Şifremi Sıfırla</button>
        </form>
        <?php endif; ?>
        <div class="mt-3 text-center">
            <a href="login.php" style="color:#6C5CE7;">Giriş Ekranına Dön</a>
        </div>
    </div>
</body>
</html>