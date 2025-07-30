<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
if (isset($_POST['add_category'])) {
    $name_tr = trim($_POST['name_tr'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    if ($name_tr !== '') {
        $stmt = $pdo->prepare("INSERT INTO categories (name, name_tr, name_en) VALUES (?, ?, ?)");
        $stmt->execute([$name_tr, $name_tr, $name_en]);
        $msg = "Kategori başarıyla eklendi!";
    } else {
        $msg = "Kategori adı (TR) boş olamaz!";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
    $msg = "Kategori silindi!";
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Yönetimi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .container { max-width: 650px; margin: 40px auto; }
        .table td, .table th { vertical-align: middle; }
        .table tr:hover { background: #f7faff; }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2>Kategori Yönetimi</h2>
        <a href="dashboard.php" class="btn btn-secondary">Panele Dön</a>
    </div>
    <?php if(!empty($msg)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 mb-4" style="background:#fafafa; border-radius:12px; padding:1.5rem;">
        <div class="col-md-5">
            <label class="form-label">Kategori Adı (TR)</label>
            <input type="text" name="name_tr" class="form-control" maxlength="100" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">Kategori Adı (EN)</label>
            <input type="text" name="name_en" class="form-control" maxlength="100">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-success w-100" type="submit" name="add_category"><i class="fa fa-plus"></i> Kategori Ekle</button>
        </div>
    </form>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="width:80px;">ID</th>
                <th>Kategori Adı (TR)</th>
                <th>Kategori Adı (EN)</th>
                <th style="width:140px;">İşlemler</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($categories as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['name_tr']) ?></td>
                <td><?= htmlspecialchars($cat['name_en']) ?></td>
                <td>
                    <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kategoriyi silmek istediğine emin misin?')">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>