<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

// Banner ekleme işlemi
if (isset($_POST['add_banner'])) {
    $title = trim($_POST['title'] ?? "");
    $link = trim($_POST['link'] ?? "");
    $active = isset($_POST['active']) ? 1 : 0;
    // Görsel yükleme
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ["jpg","jpeg","png","webp"];
        if (in_array($ext, $allowed)) {
            if (!is_dir(__DIR__ . '/../uploads/banners')) {
                mkdir(__DIR__ . '/../uploads/banners', 0777, true);
            }
            $fname = uniqid("banner_") . "." . $ext;
            $target = __DIR__ . '/../uploads/banners/' . $fname;
            if (move_uploaded_file($f['tmp_name'], $target)) {
                $image = $fname;
            }
        }
    }
    if ($image) {
        $stmt = $pdo->prepare("INSERT INTO campaign_banners (title, link, image, active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $link, $image, $active]);
        $msg = "Banner başarıyla eklendi!";
    } else {
        $msg = "Lütfen geçerli bir görsel seçin!";
    }
}

// Banner silme işlemi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("SELECT image FROM campaign_banners WHERE id=?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();
    if ($banner && $banner['image'] && file_exists(__DIR__ . '/../uploads/banners/'.$banner['image'])) {
        unlink(__DIR__ . '/../uploads/banners/'.$banner['image']);
    }
    $pdo->prepare("DELETE FROM campaign_banners WHERE id=?")->execute([$id]);
    $msg = "Banner silindi!";
}

// Banner aktif/pasif işlemi
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $pdo->query("UPDATE campaign_banners SET active = 1-active WHERE id=$id");
    $msg = "Banner aktiflik durumu değiştirildi!";
}

// Bannerları çek
$banners = $pdo->query("SELECT * FROM campaign_banners ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Banner Yönetimi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .container { max-width: 950px; margin: 40px auto; }
        .banner-img { max-width:120px; max-height:60px; border-radius:9px; box-shadow:0 2px 10px #0002;}
        table tr:hover { background: #f7faff; }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2>Banner Yönetimi</h2>
        <a href="dashboard.php" class="btn btn-secondary">Panele Dön</a>
    </div>
    <?php if(!empty($msg)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3 mb-4" style="background:#fafafa; border-radius:12px; padding:2rem;">
        <div class="col-md-4">
            <label class="form-label">Banner Başlığı</label>
            <input type="text" name="title" class="form-control" maxlength="100" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Bağlantı (Link)</label>
            <input type="url" name="link" class="form-control" maxlength="255">
        </div>
        <div class="col-md-3">
            <label class="form-label">Görsel</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" checked>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-success" type="submit" name="add_banner"><i class="fa fa-plus"></i> Banner Ekle</button>
        </div>
    </form>
    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Görsel</th>
                <th>Başlık</th>
                <th>Link</th>
                <th>Aktif</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($banners as $b): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td>
                    <?php if ($b['image'] && file_exists(__DIR__ . '/../uploads/banners/'.$b['image'])): ?>
                        <img src="../uploads/banners/<?= htmlspecialchars($b['image']) ?>" class="banner-img">
                    <?php else: ?>
                        <span style="color:#aaa;">Yok</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td>
                    <?php if($b['link']): ?>
                        <a href="<?= htmlspecialchars($b['link']) ?>" target="_blank"><?= htmlspecialchars($b['link']) ?></a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($b['active']): ?>
                        <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Pasif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?toggle=<?= $b['id'] ?>" class="btn btn-sm btn-warning"><?= $b['active'] ? 'Pasifleştir' : 'Aktifleştir' ?></a>
                    <a href="?delete=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div><a href="dashboard.php" class="btn btn-outline-primary mt-3">Panele Dön</a></div>
</div>
</body>
</html>