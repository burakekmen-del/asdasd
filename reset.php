<?php
require_once 'config.php';
require_once 'csrf.php'; // << EKLENDİ
$pdo = $GLOBALS['pdo'];
$error = '';
$success = '';
$showForm = true;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // Token geçerli mi?
    $stmt = $pdo->prepare("SELECT id, username, reset_expiry FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user || strtotime($user['reset_expiry']) < time()) {
        $error = "This reset link is invalid or has expired.";
        $showForm = false;
    }
} else {
    $error = "Invalid link.";
    $showForm = false;
}

// Form POST edildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['password'])) {
    check_csrf(); // << EKLENDİ
    $token = $_POST['token'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        // Şifreyi güncelle
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);
        $success = "Your password has been reset. You may now <a href='login.php'>log in</a>.";
        $showForm = false;
    } else {
        $error = "This reset link is invalid or has expired.";
        $showForm = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset | Affiluxe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f1f3f8;">
<div class="container" style="max-width:420px;margin:50px auto;">
    <div class="card shadow mt-5">
        <div class="card-body">
            <h3 class="mb-3 text-center">Reset Password</h3>
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if($showForm): ?>
            <form method="post">
                <?= csrf_input() ?> <!-- EKLENDİ -->
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required minlength="6">
                </div>
                <button class="btn btn-primary w-100" type="submit">Set New Password</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>