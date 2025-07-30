<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_login'])) { header("Location: login.php"); exit; }

// Yorum onaylama/silme işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $comment_id = intval($_POST['comment_id']);
    if (isset($_POST['approve'])) {
        $pdo->prepare("UPDATE product_comments SET status='approved' WHERE id=?")->execute([$comment_id]);
    }
    if (isset($_POST['reject'])) {
        $pdo->prepare("DELETE FROM product_comments WHERE id=?")->execute([$comment_id]);
    }
}

// Kullanıcıdan gelen hızlı beğeni (like/dislike)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_action']) && isset($_POST['product_id'])) {
    if (empty($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
    $product_id = intval($_POST['product_id']);
    $liked = isset($_POST['liked']) && ($_POST['liked'] === "1" || $_POST['liked'] === "0") ? intval($_POST['liked']) : null;
    if ($liked !== null) {
        $stmt = $pdo->prepare("INSERT INTO product_comments (product_id, user_id, liked, status) VALUES (?, ?, ?, 'approved')
            ON DUPLICATE KEY UPDATE liked=VALUES(liked)");
        $stmt->execute([$product_id, $_SESSION['user_id'], $liked]);
        header("Location: ../product.php?id=" . $product_id . "&like=success");
        exit;
    }
}

// Kullanıcıdan gelen yorum ekleme (yorum formu)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_action']) && isset($_POST['product_id'])) {
    if (empty($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
    $product_id = intval($_POST['product_id']);
    $comment = trim($_POST['comment'] ?? '');
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
    if ($comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO product_comments (product_id, user_id, comment, rating, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$product_id, $_SESSION['user_id'], $comment, $rating]);
        header("Location: ../product.php?id=" . $product_id . "&comment=success");
        exit;
    }
}

// Onay bekleyen yorumlar
$pending = $pdo->query("
    SELECT pc.*, u.username AS user, p.name AS product_name 
    FROM product_comments pc
    JOIN users u ON pc.user_id = u.id
    JOIN products p ON pc.product_id = p.id
    WHERE pc.status='pending'
    ORDER BY pc.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Şikayetli yorumlar
$raporlar = $pdo->query("
    SELECT 
        cr.id AS report_id, cr.reason, cr.created_at, 
        c.comment, c.id AS comment_id, c.product_id, 
        u.username AS user, 
        ru.username AS reporter
    FROM comment_reports cr
    JOIN product_comments c ON cr.comment_id = c.id
    JOIN users u ON c.user_id = u.id
    JOIN users ru ON cr.user_id = ru.id
    ORDER BY cr.created_at DESC LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yorum Yönetimi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background:#f5f7ff; }
        .container { margin-top:40px; margin-bottom:40px; }
        .shikayet-comment { color:#444; background:#f7f7fa; border-radius:8px; padding:6px 10px;}
        .shikayet-reason { font-weight:600; color:#b71c1c;}
        .table tr:hover { background: #f7faff; }
        .btn-sm { font-size:0.95em; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Yorum Yönetimi</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Geri Dön</a>

        <!-- Onay Bekleyen Yorumlar -->
        <h4>🕓 Onay Bekleyen Yorumlar</h4>
        <?php if (empty($pending)): ?>
            <div class="alert alert-info">Onay bekleyen yorum yok.</div>
        <?php else: ?>
        <table class="table table-bordered align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Kullanıcı</th>
                    <th>Ürün</th>
                    <th>Yorum</th>
                    <th>Puan</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($pending as $y): ?>
                <tr>
                    <td><?= $y['id'] ?></td>
                    <td><?= htmlspecialchars($y['user']) ?></td>
                    <td><?= htmlspecialchars($y['product_name']) ?></td>
                    <td><span class="shikayet-comment"><?= htmlspecialchars($y['comment']) ?></span></td>
                    <td><?= intval($y['rating']) ?> ★</td>
                    <td><?= date('d.m.Y H:i', strtotime($y['created_at'])) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $y['id'] ?>">
                            <button class="btn btn-success btn-sm" name="approve" onclick="return confirm('Yorumu onaylamak istiyor musunuz?')">Onayla</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $y['id'] ?>">
                            <button class="btn btn-danger btn-sm" name="reject" onclick="return confirm('Yorumu silmek istiyor musunuz?')">Sil</button>
                        </form>
                        <a href="../product.php?id=<?= $y['product_id'] ?>" target="_blank" class="btn btn-info btn-sm">Üründe Gör</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <hr class="my-5">

        <!-- Şikayetli Yorumlar -->
        <h4>🚨 Şikayet Edilen Yorumlar</h4>
        <?php if(empty($raporlar)): ?>
            <div class="alert alert-info">Hiç şikayet yok.</div>
        <?php else: ?>
        <table class="table table-bordered align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Şikayet Eden</th>
                    <th>Yorum Sahibi</th>
                    <th>Yorum</th>
                    <th>Sebep</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($raporlar as $r): ?>
                <tr>
                    <td><?= $r['report_id'] ?></td>
                    <td><?= htmlspecialchars($r['reporter']) ?></td>
                    <td><?= htmlspecialchars($r['user']) ?></td>
                    <td><span class="shikayet-comment"><?= htmlspecialchars($r['comment']) ?></span></td>
                    <td><span class="shikayet-reason"><?= htmlspecialchars($r['reason']) ?></span></td>
                    <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                    <td>
                        <a href="../product.php?id=<?= $r['product_id'] ?>" target="_blank" class="btn btn-sm btn-info mb-1">Üründe Gör</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $r['comment_id'] ?>">
                            <button class="btn btn-sm btn-danger" name="reject" onclick="return confirm('Yorumu silmek istiyor musunuz?')">Yorumu Sil</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $r['comment_id'] ?>">
                            <button class="btn btn-sm btn-success" name="approve" onclick="return confirm('Yorumu onaylamak istiyor musunuz?')">Onayla</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <hr class="my-5">

        <!-- Kullanıcıdan gelen demo yorum ve hızlı beğeni formları (geliştiricinin testine özel) -->
        <h4>Test: Kullanıcıdan Gelen Yorum/Beğeni (Demo Formlar)</h4>
        <form method="post" class="mb-3">
            <input type="hidden" name="product_id" value="1">
            <input type="hidden" name="like_action" value="1">
            <button type="submit" name="liked" value="1" class="btn btn-outline-success btn-sm">👍 Beğendim</button>
            <button type="submit" name="liked" value="0" class="btn btn-outline-danger btn-sm">👎 Beğenmedim</button>
        </form>
        <form method="post" class="mb-3">
            <input type="hidden" name="product_id" value="1">
            <input type="hidden" name="comment_action" value="1">
            <textarea name="comment" class="form-control mb-1" placeholder="Yorum ekle..." rows="2"></textarea>
            <input type="number" name="rating" min="1" max="5" class="form-control mb-1" placeholder="Puan (1-5)">
            <button type="submit" class="btn btn-primary btn-sm">Yorumu Gönder</button>
        </form>
        <div class="alert alert-warning">
            Gerçek yorum/like formları <b>product.php</b> içinde gösterilmelidir. Buradaki örnekler entegrasyon içindir.
        </div>
    </div>
</body>
</html>