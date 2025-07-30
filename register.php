<?php
if (headers_sent($file, $line)) {
    die("Hata: Çıktı daha önce gönderilmiş! $file:$line");
}

require_once 'config.php';
require_once 'csrf.php';
require_once 'lang_init.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$error = '';
$success = '';
$show_code_form = false;
$email = $_SESSION['pending_email'] ?? '';

// GET ile açılışta CSRF token üret
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    csrf_token();
}

// Çoklu dil destekli form/metinler
$form_lang = [
    "register" => "Kayıt Ol",
    "already_account" => "Zaten hesabın var mı?",
    "login" => "Giriş Yap",
    "username" => "Kullanıcı Adı",
    "email" => "E-posta",
    "password" => "Şifre",
    "password_repeat" => "Şifre Tekrar",
    "terms" => "Kullanım şartlarını kabul ediyorum",
    "register_btn" => "Kayıt Ol",
    "verify_code" => "Doğrulama Kodu",
    "verify_btn" => "Kodu Doğrula",
    "resend_code" => "Kodu Yeniden Gönder",
    "success" => "Kayıt başarılı!",
    "code_success" => "Doğrulama başarılı, giriş yapabilirsiniz!",
    "forgot" => "Şifremi Unuttum",
    "accept_terms_msg" => "Kullanım koşullarını kabul etmelisiniz!",
    "username_error" => "Kullanıcı adı 3-20 karakter ve sadece harf/rakam/altçizgi olmalı!",
    "email_error" => "Geçerli bir e-posta girin!",
    "password_error" => "Şifre en az 8 karakter, büyük/küçük harf ve rakam içermeli!",
    "password_match_error" => "Şifreler uyuşmuyor!",
    "user_exists" => "Bu kullanıcı adı veya e-posta zaten kayıtlı!",
    "missing_info" => "Eksik bilgi!",
    "invalid_code_format" => "Kod formatı geçersiz!",
    "invalid_code" => "Kod hatalı veya süresi dolmuş.",
    "verify_error" => "Doğrulama sırasında hata oluştu.",
    "resend_failed" => "Yeniden gönderme başarısız.",
    "resend_success" => "Kod yeniden gönderildi, e-postanızı kontrol edin.",
    "resend_not_sent" => "Kod gönderilemedi. Lütfen daha sonra tekrar deneyin.",
    "resend_already_verified" => "Bu e-posta zaten doğrulanmış veya kayıt süresi dolmuş.",
    "register_mail_subject" => "Affiluxe Doğrulama Kodu",
    "register_mail_subject_resend" => "Affiluxe Doğrulama Kodu (Yeniden)",
    "register_mail_body" => '
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Affiluxe Doğrulama Kodu</title>
        </head>
        <body>
            <h1>Merhaba {username},</h1>
            <p>Affiluxe hesabınızı doğrulamak için aşağıdaki kodu kullanabilirsiniz:</p>
            <div style="font-size:24px;font-weight:bold;">{code}</div>
            <p>Bu kod 24 saat boyunca geçerlidir. Eğer bu işlemi siz yapmadıysanız, lütfen bu e-postayı dikkate almayın.</p>
            <p>Teşekkür ederiz,<br><strong>Affiluxe Ekibi</strong></p>
        </body>
        </html>',
    "register_mail_body_resend" => '
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Affiluxe Doğrulama Kodu</title>
        </head>
        <body>
            <h1>Merhaba {username},</h1>
            <p>Yeni doğrulama kodunuz aşağıda yer almaktadır:</p>
            <div style="font-size:24px;font-weight:bold;">{code}</div>
            <p>Bu kod 24 saat boyunca geçerlidir. Eğer bu işlemi siz yapmadıysanız, lütfen bu e-postayı dikkate almayın.</p>
            <p>Teşekkür ederiz,<br><strong>Affiluxe Ekibi</strong></p>
        </body>
        </html>',
];

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = envv('SMTP_HOST', 'mail.affiluxe.com.tr');
        $mail->SMTPAuth = true;
        $mail->Username = envv('SMTP_USER');
        $mail->Password = envv('SMTP_PASS');
        $mail->SMTPSecure = envv('SMTP_SECURE', 'ssl');
        $mail->Port = (int)envv('SMTP_PORT', 465);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(envv('SMTP_USER'), 'Affiluxe');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        // SSL sertifika problemi için açabilirsin:
        // $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        return $mail->send();
    } catch (Exception $e) {
        echo '<pre>Mail gönderilemedi: ' . $mail->ErrorInfo . "\nException: " . $e->getMessage() . '</pre>';
        return false;
    }
}

// Kullanıcı giriş yaptıysa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    // Doğrulama kodu girildi mi?
    if (isset($_POST['verify_code_input'])) {
        $email = $_SESSION['pending_email'] ?? '';
        $code = trim($_POST['verify_code_input'] ?? '');

        if (!$email || !$code) {
            $error = $form_lang['missing_info'];
            $show_code_form = true;
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $error = $form_lang['invalid_code_format'];
            $show_code_form = true;
        } else {
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email=? AND verify_code=? AND is_verified=0 AND created_at > NOW() - INTERVAL 24 HOUR");
            $stmt->execute([$email, $code]);
            $user = $stmt->fetch();

            if ($user) {
                $pdo->beginTransaction();
                try {
                    $pdo->prepare("UPDATE users SET is_verified=1, verify_code=NULL, verified_at=NOW() WHERE id=?")->execute([$user['id']]);
                    $pdo->commit();
                    unset($_SESSION['pending_email']);
                    $success = $form_lang['code_success'];
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $form_lang['verify_error'];
                }
            } else {
                $error = $form_lang['invalid_code'];
                $show_code_form = true;
            }
        }
    }
    // Kod yeniden gönderilsin mi?
    elseif (isset($_POST['resend_code'])) {
        $email = $_SESSION['pending_email'] ?? '';
        if (!$email) {
            $error = $form_lang['resend_failed'];
        } else {
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email=? AND is_verified=0 AND created_at > NOW() - INTERVAL 24 HOUR");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $code = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
                $pdo->prepare("UPDATE users SET verify_code=? WHERE email=? AND is_verified=0")->execute([$code, $email]);
                $subject = $form_lang['register_mail_subject_resend'];
                $body = str_replace(['{username}', '{code}'], [$user['username'], $code], $form_lang['register_mail_body_resend']);
                $sent = sendMail($email, $subject, $body);
                if ($sent) {
                    $success = $form_lang['resend_success'];
                } else {
                    $error = $form_lang['resend_not_sent'];
                }
            } else {
                $error = $form_lang['resend_already_verified'];
            }
            $show_code_form = true;
        }
    }
    // Kayıt formu gönderildi
    else {
        $username   = trim($_POST['username'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $password2  = $_POST['password2'] ?? '';
        $terms      = isset($_POST['terms']);
        $errors = [];

        if (!$username) $errors[] = $form_lang['username']." ".$form_lang['missing_info'];
        elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors[] = $form_lang['username_error'];
        if (!$email) $errors[] = $form_lang['email']." ".$form_lang['missing_info'];
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $form_lang['email_error'];
        if (!$password) $errors[] = $form_lang['password']." ".$form_lang['missing_info'];
        elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password))
            $errors[] = $form_lang['password_error'];
        elseif ($password !== $password2) $errors[] = $form_lang['password_match_error'];
        if (!$terms) $errors[] = $form_lang['accept_terms_msg'];

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = $form_lang['user_exists'];
            } else {
                $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $code  = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verify_code, created_at, is_verified) VALUES (?, ?, ?, ?, NOW(), 0)");
                    $stmt->execute([$username, $email, $hash, $code]);
                    $subject = $form_lang['register_mail_subject'];
                    $body = str_replace(['{username}', '{code}'], [$username, $code], $form_lang['register_mail_body']);
                    $sent = sendMail($email, $subject, $body);
                    if ($sent) {
                        $pdo->commit();
                        $_SESSION['pending_email'] = $email;
                        $show_code_form = true;
                        $success = $form_lang['success'] . ' Doğrulama kodu e-posta adresinize gönderildi.';
                    } else {
                        $pdo->rollBack();
                        $error = $form_lang['resend_not_sent'];
                        // Hata detayını görmek istersen:
                        // echo '<pre>Mail gönderilemedi: ' . $mail->ErrorInfo . '</pre>';
                    }
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = "Kayıt sırasında hata oluştu.";
                }
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

$pageTitle = $form_lang['register']." | Affiluxe";
include "header.php";
?>
<main class="flex-fill d-flex align-items-center justify-content-center" style="min-height:70vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                <div class="card shadow p-4 rounded-4 border-0" style="background: var(--bg, #fff);">
                    <h2 class="mb-4 text-center" style="color:var(--primary, #6C5CE7);font-weight:800;"><?= $form_lang['register'] ?></h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($show_code_form): ?>
                        <form method="post" class="mt-3" autocomplete="off" id="code-form">
                            <?= csrf_input() ?>
                            <div class="mb-3">
                                <label class="form-label"><?= $form_lang['verify_code'] ?></label>
                                <input type="text" name="verify_code_input" class="form-control" maxlength="6" required pattern="\d{6}" autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" style="background:var(--primary, #6C5CE7);border:none;"><?= $form_lang['verify_btn'] ?></button>
                            <button type="submit" name="resend_code" value="1" class="btn btn-link w-100"><?= $form_lang['resend_code'] ?></button>
                        </form>
                    <?php else: ?>
                        <form method="post" class="mt-3 needs-validation" novalidate autocomplete="off" id="register-form">
                            <?= csrf_input() ?>
                            <div class="mb-3">
                                <label class="form-label"><?= $form_lang['username'] ?></label>
                                <input type="text" name="username" class="form-control" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="off">
                                <div class="invalid-feedback"><?= $form_lang['username_error'] ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $form_lang['email'] ?></label>
                                <input type="email" name="email" class="form-control" required maxlength="64" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="off">
                                <div class="invalid-feedback"><?= $form_lang['email_error'] ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $form_lang['password'] ?></label>
                                <input type="password" name="password" id="password" class="form-control" required minlength="8" autocomplete="off">
                                <div class="invalid-feedback"><?= $form_lang['password_error'] ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $form_lang['password_repeat'] ?></label>
                                <input type="password" name="password2" id="password2" class="form-control" required minlength="8" autocomplete="off">
                                <div class="invalid-feedback"><?= $form_lang['password_match_error'] ?></div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label" for="terms"><?= $form_lang['terms'] ?></label>
                                <div class="invalid-feedback"><?= $form_lang['accept_terms_msg'] ?></div>
                            </div>
                            <button type="submit" class="btn btn-success w-100" style="background:var(--secondary,#00B894);border:none;"><?= $form_lang['register_btn'] ?></button>
                        </form>
                    <?php endif; ?>
                    <div class="mt-3 text-center small">
                        <?= $form_lang['already_account'] ?>
                        <a href="login.php"><?= $form_lang['login'] ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
// Bootstrap validation
document.querySelectorAll('form').forEach(f=>f.addEventListener('submit',function(e){
    if(!f.checkValidity()){e.preventDefault();f.classList.add('was-validated');}
}));

// Şifreler uyuşmazsa invalid göster
document.getElementById('register-form')?.addEventListener('submit', function(e) {
    var pwd = document.getElementById('password');
    var pwd2 = document.getElementById('password2');
    if (pwd && pwd2 && pwd.value !== pwd2.value) {
        pwd2.setCustomValidity('Passwords do not match');
        pwd2.classList.add('is-invalid');
        e.preventDefault();
        e.stopPropagation();
    } else if (pwd2) {
        pwd2.setCustomValidity('');
        pwd2.classList.remove('is-invalid');
    }
});

// Enter ile yanlışlıkla resend olmaması için:
document.getElementById('code-form')?.addEventListener('keydown', function(e){
    if(e.key === "Enter" && document.activeElement.name === "verify_code_input") {
        setTimeout(()=>{document.activeElement.blur();},100);
    }
});
</script>
<?php include "footer.php"; ?>