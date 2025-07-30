<?php
require_once '../config.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if (!isset($_SESSION['admin_login'])) { header("Location: login.php"); exit; }

$info = "";
if(isset($_POST['send_campaign_mail'])) {
    $url = "http://localhost/burak_vip/deals.php";
    $website = "http://localhost/burak_vip/";
    $users = $pdo->query("SELECT id, email FROM users WHERE campaign_notify=1")->fetchAll(PDO::FETCH_ASSOC);

    // Kampanyalı ürünleri çek
    $products = $pdo->query("SELECT * FROM products WHERE campaign_active=1 ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

    // Ürünleri mail HTML'ine ekle
    $productsHtml = "";
    foreach($products as $urun) {
        $imgList = !empty($urun['images']) ? explode(',', $urun['images']) : [];
        $img = (!empty($imgList[0])) ? trim($imgList[0]) : '';
        $imgPath = ($img && file_exists('../uploads/' . $img)) ? $website.'uploads/' . htmlspecialchars($img) : 'https://via.placeholder.com/200x120?text=Görsel+Yok';
        $name = htmlspecialchars($urun['name']);
        $price = number_format($urun['price'], 0, ',', '.');
        $desc = htmlspecialchars(substr(strip_tags($urun['description'] ?? ''),0,80)) . '...';
        $productsHtml .= "
        <tr>
            <td style='padding:10px 0 18px 0; border-bottom:1px solid #eee;'>
                <img src='{$imgPath}' alt='{$name}' style='width:100px; height:70px; object-fit:cover; border-radius:6px; box-shadow:0 2px 8px #0001; vertical-align:middle; margin-right:18px;'>
                <strong style='font-size:15px;'>{$name}</strong><br>
                <span style='font-size:13px; color:#2ec4b6;'>₺{$price}</span>
                <div style='font-size:12px;color:#666;margin-top:4px'>{$desc}</div>
            </td>
        </tr>";
    }

    $count = 0;
    foreach ($users as $user) {
        $mailobj = new PHPMailer(true);
        try {
            $mailobj->isSMTP();
            $mailobj->Host       = 'smtp.gmail.com';
            $mailobj->SMTPAuth   = true;
            $mailobj->Username   = 'burakekmen722134@gmail.com';
            $mailobj->Password   = 'gyhj xmve aarw xtox';
            $mailobj->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailobj->Port       = 587;
            $mailobj->setFrom('burakekmen722134@gmail.com', 'Affiluxe Kampanya');
            $mailobj->addAddress($user['email']);
            $mailobj->CharSet = 'UTF-8';
            $mailobj->Encoding = 'base64';
            $mailobj->isHTML(true);
            $mailobj->Subject = 'Yeni Kampanyalı Ürünler Sizi Bekliyor!';
            // Abonelikten çıkma linki (örnek endpoint)
            $unsubscribe = $website . "unsubscribe.php?uid=" . $user['id'] . "&token=" . md5($user['email'].'-secret');
            $mailobj->Body = "
            <div style='font-family:Arial,sans-serif; background:#f8f9fa; padding:20px; border-radius:12px;'>
                <h2 style='color:#e63946;'>Yeni Kampanyalı Ürünler!</h2>
                <p>Sizin için seçilmiş kampanyalı ürünlerden bazıları:</p>
                <table style='width:100%; max-width:500px; border-collapse:collapse;'>{$productsHtml}</table>
                <div style='text-align:center; margin:30px 0 0 0;'>
                    <a href='{$url}' style='display:inline-block; padding:12px 26px; background:#e63946; color:#fff; border-radius:32px; text-decoration:none; font-weight:bold; font-size:16px;'>Kampanyalı Ürünleri Hemen İncele</a>
                </div>
                <hr style='margin:36px 0;'>
                <div style='font-size:13px; color:#999;'>
                  Affiluxe - <a href='{$website}' style='color:#2ec4b6;'>Websitemiz</a> | Destek: info@burak_vip.com<br>
                  <a href='{$unsubscribe}' style='color:#e63946;'>Kampanya bildirim aboneliğinden çıkmak için tıklayın</a>
                </div>
            </div>
            ";
            $mailobj->AltBody = "Kampanyalı ürünler için: $url\n\nAbonelikten çıkmak için: $unsubscribe";
            $mailobj->send();
            $count++;
        } catch (Exception $e) {
            // echo "Mail gönderilemedi: {$mailobj->ErrorInfo}";
        }
    }
    $info = "$count kişiye başarıyla kampanya maili gönderildi!";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><title>Kampanya Maili Gönder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container" style="max-width:520px;margin:60px auto;">
    <h2>Kampanya Maili Gönder</h2>
    <?php if(!empty($info)): ?><div class="alert alert-success"><?= $info ?></div><?php endif; ?>
    <form method="post">
        <button name="send_campaign_mail" class="btn btn-warning">Kampanya Sayfasını Tüm Abonelere Gönder</button>
    </form>
    <a href="dashboard.php" class="btn btn-link mt-3">Panele Dön</a>
</div>
</body>
</html>