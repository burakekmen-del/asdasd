<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['admin_user'] ?? '');
    $pass = $_POST['admin_pass'] ?? '';

    // Sadece admin girişine izin ver
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND is_admin=1 LIMIT 1");
    $stmt->execute([$user]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_user'] = $admin['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Hatalı giriş!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Giriş</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .login-box { max-width: 340px; margin: 100px auto; background: #fff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 2.5rem 2rem; }
        .login-box h2 { font-weight: 700; color: #2563eb; margin-bottom: 2rem;}
        .error-msg { color: #e63946; font-weight:600; margin-bottom:1rem;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Giriş</h2>
        <?php if($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="admin_user" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input type="password" name="admin_pass" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit"><i class="fa fa-sign-in-alt"></i> Giriş Yap</button>
        </form>
    </div>
</body>
</html>