<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php'; // .env yüklemeniz gerekiyorsa burada dotenv ile yükleyin

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// .env'den SMTP ve DB bilgilerini yükle
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

function redirectWithStatus($status, $lang) {
    header("Location: /?newsletter=$status&lang=$lang");
    exit;
}

// E-posta ve dil kontrolü
if (isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $lang = (isset($_POST['lang']) && $_POST['lang'] === 'en') ? 'en' : 'tr';

    if ($email) {
        // Veritabanına kaydet ve tekrar kayıt kontrolü
        try {
            $pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // Zaten kayıtlı mı kontrol et
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_emails WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                redirectWithStatus('exists', $lang);
            }
            // Kayıt et
            $stmt = $pdo->prepare("INSERT INTO newsletter_emails (email, lang, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$email, $lang]);
        } catch (Exception $e) {
            redirectWithStatus('error', $lang);
        }

        $mail = new PHPMailer(true);
        try {
            // SMTP Ayarları
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'mail.affiluxe.com.tr';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? 'info@affiluxe.com.tr';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? 'exl7hs9nxqpm';
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'ssl';
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 465;

            // Gönderen ve alıcı
            $mail->setFrom($_ENV['SMTP_USER'] ?? 'info@affiluxe.com.tr', 'Affiluxe');
            $mail->addAddress($email);

            // UTF-8 ayarları
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Modern HTML e-posta tasarımı
            $logoUrl = $_ENV['SITE_URL'] . '/assets/images/affiluxe-logo.png';

            if ($lang === 'en') {
                $mail->Subject = 'Welcome to Affiluxe Newsletter!';
                $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Affiluxe Newsletter</title>
</head>
<body style="background:#f8f8fb;margin:0;padding:0;">
  <div style="max-width:480px;margin:22px auto;background:#fff;border-radius:18px;box-shadow:0 4px 24px #6c5ce71c;padding:32px 22px 28px 22px;font-family:'Inter',Arial,sans-serif;">
    <div style="text-align:center;">
      <img src="$logoUrl" alt="Affiluxe" style="height:48px;margin-bottom:12px;">
    </div>
    <h2 style="color:#6c5ce7;font-family:'Plus Jakarta Sans','Inter',Arial,sans-serif;font-weight:800;letter-spacing:-1px;text-align:center;margin-top:0;">
      Welcome to Affiluxe Deals!
    </h2>
    <p style="font-size:1.13em;color:#232343;text-align:center;">
      Thank you for subscribing to our newsletter.<br>
      You’ll now receive the latest <b>deals</b>, <b>discounts</b> and <b>exclusive offers</b> directly to your inbox.
    </p>
    <div style="background:#f1f0ff;border-radius:11px;padding:18px 15px;margin:18px 0 20px 0;text-align:center;">
      <span style="color:#6c5ce7;font-weight:700;font-size:1.09em;">Why Affiluxe?</span><br>
      <span style="color:#636e72;font-size:.99em;">
        <ul style="list-style:none;padding:0;margin:11px 0 0 0;text-align:left;">
          <li>• Hand-picked deals from top marketplaces</li>
          <li>• Verified and trusted shopping links</li>
          <li>• Weekly campaigns and surprise discounts</li>
          <li>• No spam, only real bargains</li>
        </ul>
      </span>
    </div>
    <p style="color:#636e72;font-size:.98em;text-align:center;">If you did not request this, you can safely ignore this e-mail.</p>
    <div style="text-align:center;margin-top:18px;">
      <a href="{$_ENV['SITE_URL']}" style="display:inline-block;background:linear-gradient(90deg,#6c5ce7 0%,#00b894 100%);color:#fff;font-weight:700;border-radius:9px;padding:13px 38px;font-size:1.07em;text-decoration:none;margin-top:10px;">Visit Affiluxe</a>
    </div>
    <div style="margin-top:28px;text-align:center;color:#b2bec3;font-size:.98em;">
      &copy; {date('Y')} Affiluxe.com.tr &bull; All rights reserved.
    </div>
  </div>
</body>
</html>
HTML;
            } else {
                $mail->Subject = 'Affiluxe Bülten Aboneliği Başarılı!';
                $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Affiluxe Bülten</title>
</head>
<body style="background:#f8f8fb;margin:0;padding:0;">
  <div style="max-width:480px;margin:22px auto;background:#fff;border-radius:18px;box-shadow:0 4px 24px #6c5ce71c;padding:32px 22px 28px 22px;font-family:'Inter',Arial,sans-serif;">
    <div style="text-align:center;">
      <img src="$logoUrl" alt="Affiluxe" style="height:48px;margin-bottom:12px;">
    </div>
    <h2 style="color:#6c5ce7;font-family:'Plus Jakarta Sans','Inter',Arial,sans-serif;font-weight:800;letter-spacing:-1px;text-align:center;margin-top:0;">
      Affiluxe Fırsat Bültenine Hoş Geldin!
    </h2>
    <p style="font-size:1.13em;color:#232343;text-align:center;">
      Bültenimize abone olduğun için teşekkürler.<br>
      En yeni <b>fırsatlar</b>, <b>indirimler</b> ve <b>özel kampanyalar</b> artık e-posta kutunda!
    </p>
    <div style="background:#f1f0ff;border-radius:11px;padding:18px 15px;margin:18px 0 20px 0;text-align:center;">
      <span style="color:#6c5ce7;font-weight:700;font-size:1.09em;">Neden Affiluxe?</span><br>
      <span style="color:#636e72;font-size:.99em;">
        <ul style="list-style:none;padding:0;margin:11px 0 0 0;text-align:left;">
          <li>• Piyasanın en iyi fırsatları, tek adreste</li>
          <li>• Güvenilir ve onaylı alışveriş bağlantıları</li>
          <li>• Haftalık kampanyalar ve süpriz indirimler</li>
          <li>• Sadece fırsatlar, kesinlikle spam yok</li>
        </ul>
      </span>
    </div>
    <p style="color:#636e72;font-size:.98em;text-align:center;">Eğer bu işlemi siz yapmadıysanız, lütfen bu postayı dikkate almayınız.</p>
    <div style="text-align:center;margin-top:18px;">
      <a href="{$_ENV['SITE_URL']}" style="display:inline-block;background:linear-gradient(90deg,#6c5ce7 0%,#00b894 100%);color:#fff;font-weight:700;border-radius:9px;padding:13px 38px;font-size:1.07em;text-decoration:none;margin-top:10px;">Affiluxe'u Ziyaret Et</a>
    </div>
    <div style="margin-top:28px;text-align:center;color:#b2bec3;font-size:.98em;">
      &copy; {date('Y')} Affiluxe.com.tr &bull; Tüm hakları saklıdır.
    </div>
  </div>
</body>
</html>
HTML;
            }

            $mail->isHTML(true);

            $mail->send();

            redirectWithStatus('success', $lang);
        } catch (Exception $e) {
            // error_log($mail->ErrorInfo);
            redirectWithStatus('error', $lang);
        }
    }
}

if (isset($_GET['newsletter'])) {
    $popupMessage = '';
    $lang = (isset($_GET['lang']) && $_GET['lang'] === 'en') ? 'en' : 'tr';
    if ($_GET['newsletter'] === 'success') {
        $popupMessage = ($lang === 'en')
            ? "You have successfully subscribed to the newsletter!"
            : "Bültene başarıyla abone oldunuz!";
    } elseif ($_GET['newsletter'] === 'exists') {
        $popupMessage = ($lang === 'en')
            ? "This email address is already subscribed."
            : "Bu e-posta zaten kayıtlı!";
    } elseif ($_GET['newsletter'] === 'error') {
        $popupMessage = ($lang === 'en')
            ? "An error occurred. Please try again later."
            : "Bir hata oluştu, lütfen tekrar deneyin.";
    }

    if ($popupMessage) {
        echo '<div id="newsletter-popup" style="position:fixed;left:0;right:0;top:0;z-index:9999;display:flex;justify-content:center;align-items:center;">
  <div style="background:#fff;border-radius:14px;box-shadow:0 4px 24px #6c5ce733;padding:22px 32px;margin-top:50px;min-width:260px;max-width:90vw;text-align:center;">
    <span style="font-size:1.13em;color:#6c5ce7;font-weight:700;">'.htmlspecialchars($popupMessage).'</span>
    <br>
    <button onclick="document.getElementById(\'newsletter-popup\').remove();" style="margin-top:16px;padding:7px 22px;border:none;border-radius:7px;background:#6c5ce7;color:#fff;font-weight:600;cursor:pointer;">Kapat</button>
  </div>
</div>
<script>
  setTimeout(()=>{ 
    let p=document.getElementById("newsletter-popup"); 
    if(p) p.remove(); 
  },5000);
</script>';
    }
}

?>