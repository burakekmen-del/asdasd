<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "header.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Form auto-fill for user info
$adsoyad = "";
$email = "";
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $adsoyad = $user['username'];
        $email = $user['email'];
    }
}

$success = $error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $adsoyad = trim($_POST['adsoyad'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $mesaj = trim($_POST['mesaj'] ?? "");

    if (strlen($adsoyad) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($mesaj) < 10) {
        $error = $lang == 'en'
            ? "Please fill in all fields correctly."
            : "Lütfen tüm alanları doğru şekilde doldurun.";
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "mail.affiluxe.com.tr";
            $mail->SMTPAuth = true;
            $mail->Username = "support@affiluxe.com.tr";
            $mail->Password = "rZz3A+Kr=u8y";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom("support@affiluxe.com.tr", "Affiluxe Contact");
            $mail->addAddress("burakekmen72@gmail.com", "Site Admin");
            $mail->addReplyTo($email, $adsoyad);
            $mail->Subject = "Affiluxe Contact Message";
            $mail->Body = "Name: ".htmlspecialchars($adsoyad)."\nEmail: ".htmlspecialchars($email)."\n\nMessage:\n".htmlspecialchars($mesaj);

            $mail->send();
            $success = $lang == 'en'
                ? "Your message has been sent successfully. You will be contacted as soon as possible!"
                : "Mesajınız başarıyla gönderildi. En kısa sürede dönüş yapılacaktır!";
        } catch (Exception $e) {
            $error = $lang == 'en'
                ? "Mail could not be sent. Error: {$mail->ErrorInfo} - {$e->getMessage()}"
                : "Mail gönderilemedi. Hata: {$mail->ErrorInfo} - {$e->getMessage()}";
        }
    }
}
?>
<div class="site-wrapper d-flex flex-column min-vh-100">
    <div class="container" style="max-width:600px; margin:40px auto;">
        <div class="section-title">
            <h2><?= $lang == 'en' ? 'Contact' : 'İletişim' ?></h2>
            <p>
                <?= $lang == 'en'
                ? 'You can reach us for any questions, opinions, or suggestions.'
                : 'Her türlü soru, görüş ve öneriniz için bize ulaşabilirsiniz.' ?>
            </p>
        </div>
        <?php if($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" style="background:white; border-radius:12px; box-shadow:0 4px 18px #0001; padding:2rem;">
            <div class="mb-3">
                <label for="adsoyad" class="form-label"><?= $lang == 'en' ? 'Name Surname' : 'Ad Soyad' ?></label>
                <input type="text" name="adsoyad" id="adsoyad" class="form-control" required value="<?= htmlspecialchars($_POST['adsoyad'] ?? $adsoyad) ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><?= $lang == 'en' ? 'Email' : 'E-Posta' ?></label>
                <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? $email) ?>">
            </div>
            <div class="mb-3">
                <label for="mesaj" class="form-label"><?= $lang == 'en' ? 'Your Message' : 'Mesajınız' ?></label>
                <textarea name="mesaj" id="mesaj" class="form-control" rows="5" required><?= htmlspecialchars($_POST['mesaj'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-danger"><?= $lang == 'en' ? 'Send' : 'Gönder' ?></button>
        </form>
    </div>
    <?php include "footer.php"; ?>
</div>
<style>
.site-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.site-wrapper > .container {
    flex: 1 0 auto;
}
.footer {
    margin-top: auto;
}
</style>