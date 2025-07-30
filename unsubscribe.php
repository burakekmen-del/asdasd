<?php
require_once 'config.php';
if(isset($_GET['uid']) && isset($_GET['token'])) {
    $uid = (int)$_GET['uid'];
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $email = $stmt->fetchColumn();

    if($email && md5($email.'-secret') === $_GET['token']) {
        $pdo->prepare("UPDATE users SET campaign_notify=0 WHERE id=?")->execute([$uid]);
        $ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><title>Abonelikten Çık</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container" style="max-width:480px; margin:60px auto;">
    <?php if(!empty($ok)): ?>
        <div class="alert alert-success">Başarıyla kampanya bildirim aboneliğinden çıktınız.</div>
    <?php else: ?>
        <div class="alert alert-danger">Geçersiz bağlantı.</div>
    <?php endif; ?>
    <a href="index.php" class="btn btn-link mt-3">Ana Sayfa</a>
</div>
</body>
</html>
