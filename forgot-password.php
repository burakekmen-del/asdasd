<?php
require_once 'config.php';
require_once 'csrf.php';
require_once __DIR__ . '/vendor/autoload.php';

$error = '';
$success = '';
$show_code_form = false;
$email = $_SESSION['reset_email'] ?? '';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $subject, $body) {
    global $env;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = envv('SMTP_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = envv('SMTP_USER');
        $mail->Password = envv('SMTP_PASS');
        $mail->SMTPSecure = envv('SMTP_SECURE', 'ssl');
        $mail->Port = envv('SMTP_PORT', 465);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(envv('SMTP_USER'), 'Affiluxe');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        if (!$mail->send()) {
            throw new Exception('PHPMailer Error: ' . $mail->ErrorInfo);
        }
        return true;
    } catch (Exception $e) {
        global $error;
        $error = "Mail gönderilemedi: " . $e->getMessage();
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    // 1. Adım: E-posta ile kod iste
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (!$email) {
            $error = "Lütfen e-posta adresinizi girin!";
        } else {
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email=?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user) {
                $error = "Bu e-posta ile kayıtlı kullanıcı bulunamadı!";
            } else {
                $reset_code = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
                // Veritabanına kodu yazarken hata olup olmadığını kontrol et
                $upd = $pdo->prepare("UPDATE users SET verify_code=? WHERE email=?");
                if (!$upd->execute([$reset_code, $email])) {
                    $error = "Veritabanına kod yazılamadı!";
                } else {
                    $_SESSION['reset_email'] = $email;
                    $subject = "Affiluxe Şifre Sıfırlama Kodu";
                    $body = "<div style='font-family:Inter,sans-serif;background:#f5f6fa;padding:30px;'>
                        <div style='max-width:480px;margin:auto;background:#fff;border-radius:10px;padding:32px 28px;'>
                            <h2 style='color:#6C5CE7;margin-bottom:18px;'>Şifre Sıfırlama</h2>
                            <p>Şifre sıfırlama için doğrulama kodunuz:</p>
                            <div style='font-size:2rem;font-weight:bold;letter-spacing:4px;background:#f1f0ff;color:#6C5CE7;border-radius:8px;padding:12px 0;margin:16px 0 24px 0;text-align:center;'>$reset_code</div>
                            <p style='color:#636e72;'>Bu kod 24 saat geçerlidir. Siz talep etmediyseniz dikkate almayınız.</p>
                        </div>
                        <div style='max-width:480px;margin:24px auto 0 auto;font-size:12px;color:#b2bec3;text-align:center;'>Affiluxe &copy;".date('Y')."</div>
                    </div>";
                    if (sendMail($email, $subject, $body)) {
                        $show_code_form = true;
                        $success = 'Kod gönderildi, lütfen e-postanızı kontrol edin.';
                        $error = '';
                    }
                    // Eğer hata olduysa $error yukarıda set edilir ve ekrana basılır!
                }
            }
        }
    }
    // 2. Adım: Kod doğrulama
    elseif (isset($_POST['verify_code_input'])) {
        $email = $_SESSION['reset_email'] ?? '';
        $code = trim($_POST['verify_code_input'] ?? '');
        if (!$email || !$code) {
            $error = "Eksik bilgi!";
            $show_code_form = true;
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $error = "Kod formatı geçersiz!";
            $show_code_form = true;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND verify_code=?");
            $stmt->execute([$email, $code]);
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['reset_verified'] = true;
                header("Location: reset-password.php");
                exit;
            } else {
                $error = "Kod hatalı veya süresi dolmuş!";
                $show_code_form = true;
            }
        }
    }
    // 3. Adım: Kodu tekrar gönder
    elseif (isset($_POST['resend_code'])) {
        $email = $_SESSION['reset_email'] ?? '';
        if (!$email) {
            $error = 'Kodu tekrar gönderme başarısız.';
        } else {
            $reset_code = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $upd = $pdo->prepare("UPDATE users SET verify_code=? WHERE email=?");
            if (!$upd->execute([$reset_code, $email])) {
                $error = "Veritabanına kod yazılamadı!";
            } else {
                $subject = "Affiluxe Şifre Sıfırlama Kodu (Yeniden)";
                $body = "<div style='font-family:Inter,sans-serif;background:#f5f6fa;padding:30px;'>
                    <div style='max-width:480px;margin:auto;background:#fff;border-radius:10px;padding:32px 28px;'>
                        <h2 style='color:#6C5CE7;margin-bottom:18px;'>Şifre Sıfırlama</h2>
                        <p>Yeni doğrulama kodunuz:</p>
                        <div style='font-size:2rem;font-weight:bold;letter-spacing:4px;background:#f1f0ff;color:#6C5CE7;border-radius:8px;padding:12px 0;margin:16px 0 24px 0;text-align:center;'>$reset_code</div>
                        <p style='color:#636e72;'>Bu kod 24 saat geçerlidir.</p>
                    </div>
                    <div style='max-width:480px;margin:24px auto 0 auto;font-size:12px;color:#b2bec3;text-align:center;'>Affiluxe &copy;".date('Y')."</div>
                </div>";
                if (sendMail($email, $subject, $body)) {
                    $success = "Kod tekrar gönderildi. E-postanızı kontrol edin.";
                    $show_code_form = true;
                }
                // Eğer hata olduysa $error yukarıda set edilir ve ekrana basılır!
            }
        }
    }
} else {
    if (isset($_SESSION['reset_email'])) {
        $show_code_form = true;
        $email = $_SESSION['reset_email'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifremi Unuttum | Affiluxe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .forgot-box { max-width: 410px; margin: 100px auto; background: #fff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 2.5rem 2rem; }
        .forgot-box h2 { font-weight: 700; color: #6C5CE7; margin-bottom: 2rem;}
        .error-msg { color: #e63946; font-weight:600; margin-bottom:1rem;}
        .success-msg { color: #2ec4b6; font-weight:600; margin-bottom:1rem;}
        .form-label { color:#6C5CE7; font-weight:600;}
        .btn-main { background:#6C5CE7; color:#fff; }
        .btn-main:hover { background:#5649C0; color:#fff; }
    </style>
</head>
<body>
    <div class="forgot-box">
        <h2>Şifremi Unuttum</h2>
        <?php if($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="success-msg"><?= $success ?></div><?php endif; ?>

        <?php if(!$show_code_form): ?>
        <form method="post" autocomplete="off">
            <?= csrf_input() ?>
            <div class="mb-3">
                <label class="form-label">E-posta adresiniz</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
            </div>
            <button class="btn btn-main w-100" type="submit">Kodu Gönder</button>
        </form>
        <?php else: ?>
        <form method="post" autocomplete="off">
            <?= csrf_input() ?>
            <div class="mb-3">
                <label class="form-label">E-posta adresiniz</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Doğrulama Kodu</label>
                <input type="text" name="verify_code_input" class="form-control" maxlength="6" pattern="\d{6}" autocomplete="one-time-code">
            </div>
            <button class="btn btn-main w-100" type="submit">Kodu Doğrula</button>
            <button class="btn btn-link w-100" type="submit" name="resend_code" value="1">Kodu Yeniden Gönder</button>
        </form>
        <?php endif; ?>
        <div class="mt-3 text-center">
            <a href="login.php" style="color:#6C5CE7;">Giriş Yap</a>
        </div>
    </div>
</body>
</html>