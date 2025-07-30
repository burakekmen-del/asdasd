<?php
require_once '../config.php';
if (!isset($_SESSION['admin_login'])) { header("Location: login.php"); exit; }

// Analizler
$top_details = $pdo->query("
    SELECT p.name AS product_name, COUNT(*) AS detail_count
    FROM product_click_logs pcl
    JOIN products p ON pcl.product_id = p.id
    WHERE pcl.click_type='detail'
    GROUP BY p.id
    ORDER BY detail_count DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
$top_buys = $pdo->query("
    SELECT p.name AS product_name, COUNT(*) AS buy_count
    FROM product_click_logs pcl
    JOIN products p ON pcl.product_id = p.id
    WHERE pcl.click_type='buy'
    GROUP BY p.id
    ORDER BY buy_count DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
$top_users = $pdo->query("
    SELECT IFNULL(u.username,'Ziyaretçi') AS username,
        SUM(pcl.click_type='detail') AS detail_count,
        SUM(pcl.click_type='buy') AS buy_count,
        COUNT(*) AS total
    FROM product_click_logs pcl
    LEFT JOIN users u ON pcl.user_id = u.id
    GROUP BY pcl.user_id
    ORDER BY total DESC
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);
$trend_daily = $pdo->query("
    SELECT DATE(click_time) AS tarih,
        SUM(click_type='detail') AS detay,
        SUM(click_type='buy') AS satin_al
    FROM product_click_logs
    WHERE click_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(click_time)
    ORDER BY tarih ASC
")->fetchAll(PDO::FETCH_ASSOC);
$category_stats = $pdo->query("
    SELECT c.name AS kategori,
        SUM(pcl.click_type='detail') AS detay,
        SUM(pcl.click_type='buy') AS satin_al
    FROM product_click_logs pcl
    JOIN products p ON pcl.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY SUM(pcl.click_type='detail') + SUM(pcl.click_type='buy') DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Zaman filtresi desteği
$date_filter = $_GET['filter'] ?? '30d';
$filter_sql = "WHERE 1";
if ($date_filter == "today") {
    $filter_sql = "WHERE DATE(click_time) = CURDATE()";
} elseif ($date_filter == "week") {
    $filter_sql = "WHERE click_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($date_filter == "month") {
    $filter_sql = "WHERE click_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}
// Filtreli toplam tıklama/satın alma
$summary = $pdo->query("
    SELECT
        SUM(click_type='detail') AS total_detail,
        SUM(click_type='buy') AS total_buy,
        COUNT(*) AS total_clicks
    FROM product_click_logs
    $filter_sql
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Analizler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background:#f5f7ff; }
        .container { background: #fff; border-radius: 14px; box-shadow: 0 8px 32px #2563eb13, 0 2px 10px #0002; margin-top:40px;padding:2.2rem 2rem;}
        h2 { color: #2563eb; font-weight:700;}
        table th, table td { vertical-align:middle;}
        table tr:hover { background: #f7faff; }
        .filter-btns { margin-bottom: 1.5rem; }
        .filter-btns .btn { margin-right: 7px; }
        .summary-box { background: #f1f6fb; border-radius: 9px; padding: 15px 18px; margin-bottom: 24px; font-size: 1.09em;}
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Tıklama ve Satın Alma Analizleri</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Geri Dön</a>
        <div class="filter-btns mb-4">
            <b>Filtrele:</b>
            <a href="?filter=today" class="btn btn-sm <?= $date_filter=='today'?'btn-primary':'btn-outline-primary' ?>">Bugün</a>
            <a href="?filter=week" class="btn btn-sm <?= $date_filter=='week'?'btn-primary':'btn-outline-primary' ?>">Bu Hafta</a>
            <a href="?filter=month" class="btn btn-sm <?= $date_filter=='month'?'btn-primary':'btn-outline-primary' ?>">Bu Ay</a>
            <a href="?" class="btn btn-sm <?= ($date_filter=='30d'||!isset($_GET['filter']))?'btn-primary':'btn-outline-primary' ?>">Son 30 Gün</a>
        </div>
        <div class="summary-box">
            <b>Toplam Detay Tıklama:</b> <?= number_format($summary['total_detail']) ?> &nbsp;|&nbsp;
            <b>Toplam Satın Al Tıklama:</b> <?= number_format($summary['total_buy']) ?> &nbsp;|&nbsp;
            <b>Toplam Tıklama:</b> <?= number_format($summary['total_clicks']) ?>
        </div>
        <h5>En Çok Detay Görüntülenen İlanlar (Top 10)</h5>
        <table class="table table-bordered mb-4">
            <tr><th>İlan</th><th>Detay Görüntüleme</th></tr>
            <?php foreach($top_details as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['detail_count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h5>En Çok 'Satın Al' Tıklanan İlanlar (Top 10)</h5>
        <table class="table table-bordered mb-4">
            <tr><th>İlan</th><th>Satın Al Tıklaması</th></tr>
            <?php foreach($top_buys as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['buy_count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h5>En Aktif Kullanıcılar</h5>
        <table class="table table-bordered mb-4">
            <tr><th>Kullanıcı</th><th>Detay</th><th>Satın Al</th><th>Toplam</th></tr>
            <?php foreach($top_users as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['detail_count'] ?></td>
                <td><?= $row['buy_count'] ?></td>
                <td><?= $row['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h5>Günlük Trend (Son 30 Gün)</h5>
        <table class="table table-bordered mb-4">
            <tr><th>Tarih</th><th>Detay</th><th>Satın Al</th></tr>
            <?php foreach($trend_daily as $row): ?>
            <tr>
                <td><?= $row['tarih'] ?></td>
                <td><?= $row['detay'] ?></td>
                <td><?= $row['satin_al'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h5>Kategori Bazında Tıklama/Satın Alma</h5>
        <table class="table table-bordered mb-4">
            <tr><th>Kategori</th><th>Detay</th><th>Satın Al</th></tr>
            <?php foreach($category_stats as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['kategori']) ?></td>
                <td><?= $row['detay'] ?></td>
                <td><?= $row['satin_al'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>