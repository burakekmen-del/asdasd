<?php
require_once '../config.php';
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

// Her ürün için tıklama sayıları
$productDetailClicks = [];
$productAffiliateClicks = [];
$productBuyClicks = [];

// Detay tıklamaları
$detailRows = $pdo->query("SELECT product_id, COUNT(*) AS click_count FROM product_click_logs WHERE click_type='detail' GROUP BY product_id")->fetchAll(PDO::FETCH_ASSOC);
foreach($detailRows as $row) {
    $productDetailClicks[$row['product_id']] = $row['click_count'];
}
// Affiliate tıklamaları
$affiliateRows = $pdo->query("SELECT product_id, COUNT(*) AS click_count FROM product_click_logs WHERE click_type='affiliate' GROUP BY product_id")->fetchAll(PDO::FETCH_ASSOC);
foreach($affiliateRows as $row) {
    $productAffiliateClicks[$row['product_id']] = $row['click_count'];
}
// "Satın Al" (buy) tıklamaları
$buyRows = $pdo->query("SELECT product_id, COUNT(*) AS click_count FROM product_click_logs WHERE click_type='buy' GROUP BY product_id")->fetchAll(PDO::FETCH_ASSOC);
foreach($buyRows as $row) {
    $productBuyClicks[$row['product_id']] = $row['click_count'];
}

// Ürünleri çek
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC")->fetchAll();

$platform_colors = [
    'trendyol' => '#FF9F1C',
    'amazon' => '#232F3E',
    'hepsiburada' => '#FFCB05',
    'temu' => '#FF4800',
    'other' => '#888'
];
$platform_icons = [
    'trendyol' => 'fa-solid fa-bag-shopping',
    'amazon' => 'fa-brands fa-amazon',
    'hepsiburada' => 'fa-solid fa-store',
    'temu' => 'fa-solid fa-globe',
    'other' => 'fa-solid fa-tag'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürünler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { background: #f5f7ff; }
        .products-box { max-width: 1200px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 32px #2563eb13, 0 2px 10px #0002; padding:2.2rem 2rem 2rem 2rem; }
        h2 { color: #2563eb; font-weight:700;}
        table {margin-top:1.4rem;}
        .mini-img-slider { height:42px; max-width: 92px; object-fit:cover; border-radius:5px; transition:box-shadow .16s;}
        .mini-img-slider:hover { box-shadow:0 2px 10px #2563eb33; transform:scale(1.08);}
        .slider-td { min-width: 105px; }
        .mini-carousel .carousel-control-prev-icon,
        .mini-carousel .carousel-control-next-icon {
            background-color: #2563eb; border-radius: 50%; padding: 5px;
        }
        .mini-carousel .carousel-control-prev,
        .mini-carousel .carousel-control-next { filter: drop-shadow(0 0 2px #fff);}
        tr { transition: background .18s; }
        tr:hover { background: #f0f6ff; }
        .platform-badge {
            display:inline-flex; align-items:center; gap:5px;
            font-weight:600; font-size:1em; padding: 3px 12px;
            border-radius:8px;
            color:#fff;
            min-width: 90px; justify-content:center;
            transition: background .18s, transform .12s;
            box-shadow:0 1px 7px #0001;
        }
        .platform-badge i { font-size: 1.1em; }
        .platform-badge:hover { transform:scale(1.07);}
        .btn-sm { font-size:1em; padding:6px 13px;}
        .table td, .table th { vertical-align: middle;}
        .detail-badge {
            display:inline-block;
            background: #6c5ce7;
            color: #fff;
            padding: 3px 9px;
            border-radius: 9px;
            font-size: 0.96em;
            font-weight: bold;
            margin-top:4px;
            margin-bottom:2px;
        }
        .click-badge {
            display:inline-block;
            background: #ffeb3b;
            color: #232343;
            padding: 3px 9px;
            border-radius: 9px;
            font-size: 0.96em;
            font-weight: bold;
            margin-top:4px;
            margin-bottom:2px;
        }
        .buy-badge {
            display:inline-block;
            background: #00e676;
            color: #232343;
            padding: 3px 9px;
            border-radius: 9px;
            font-size: 0.96em;
            font-weight: bold;
            margin-top:4px;
        }
        .temu-badge {
            display:inline-block;
            background: #ffe5d2;
            color: #ff4800;
            padding: 3px 9px;
            border-radius: 8px;
            font-size: 0.97em;
            font-weight: bold;
            margin-top:4px;
            margin-bottom:2px;
            border:1px solid #ff4800;
        }
        @media (max-width: 900px) {
            .products-box { padding:1rem 0.2rem;}
            table { font-size: 0.97em;}
            th, td { padding: 6px 3px;}
        }
        @media (max-width:600px) {
            .products-box { padding:0.5rem 0.2rem;}
            table { font-size: 0.92em;}
            .btn { font-size:1em;}
        }
    </style>
</head>
<body>
    <div class="products-box">
        <h2>Ürünler</h2>
        <a href="product_add.php" class="btn btn-success mb-3"><i class="fa fa-plus"></i> Ürün Ekle</a>
        <table class="table table-bordered align-middle table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Adı</th>
                    <th>Kategori</th>
                    <th>Platform</th>
                    <th>Fiyat</th>
                    <th>Kampanya</th>
                    <th>%</th>
                    <th>SEO</th>
                    <th>Temu İlk İndirme</th>
                    <th>Tıklama</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td class="slider-td">
                        <?php
                        $imgs = !empty($p['images']) ? array_map('trim', explode(',', $p['images'])) : [];
                        if (empty($imgs)) $imgs = ['default.jpg'];
                        $carouselId = "carousel_".$p['id'];
                        ?>
                        <div id="<?= $carouselId ?>" class="carousel slide mini-carousel" data-bs-ride="carousel" data-bs-interval="3000" style="width:92px;">
                            <div class="carousel-inner">
                                <?php foreach($imgs as $i => $img):
                                    $imgPath = ($img && file_exists('../uploads/' . $img)) ? '../uploads/' . htmlspecialchars($img) : 'https://via.placeholder.com/80x42?text=Yok';
                                ?>
                                <div class="carousel-item <?= $i==0?'active':'' ?>">
                                    <img src="<?= $imgPath ?>" class="mini-img-slider d-block w-100" alt="foto">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if(count($imgs) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev" style="width:24px;">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next" style="width:24px;">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($p['name_en']) ?></td>
                    <td><?= htmlspecialchars($p['cat_name']) ?></td>
                    <td>
                        <?php
                            $plat = strtolower($p['platform']);
                            $col = $platform_colors[$plat] ?? '#888';
                            $icon = $platform_icons[$plat] ?? 'fa-solid fa-tag';
                        ?>
                        <span class="platform-badge" style="background:<?= $col ?>;">
                            <i class="<?= $icon ?>"></i> <?= ucfirst($plat) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($p['campaign_active'] && $p['price'] > 0): ?>
                            <span style="text-decoration:line-through;color:#e63946;"><?= $p['original_price'] ?> ₺</span><br>
                            <span style="color:#2ec4b6;font-weight:600;"><?= $p['price'] ?> ₺</span><br>
                            <small style="color:#2563eb;">
                                %<?= round((($p['original_price']-$p['price'])/$p['original_price'])*100) ?>
                            </small>
                        <?php else: ?>
                            <span><?= $p['original_price'] ?> ₺</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['campaign_active'] ? '<span style="color:green;font-weight:bold">Aktif</span>' : 'Pasif' ?></td>
                    <td>
                        <?php if($p['campaign_active'] && $p['price'] > 0): ?>
                            %<?= round((($p['original_price']-$p['price'])/$p['original_price'])*100) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong>Title:</strong> <?= htmlspecialchars(mb_strimwidth($p['meta_title'],0,30,'...')) ?><br>
                        <strong>Desc:</strong> <?= htmlspecialchars(mb_strimwidth($p['meta_description'],0,30,'...')) ?>
                    </td>
                    <td>
                        <?php if (strtolower($p['platform']) == 'temu'): ?>
                            <?php if (!empty($p['first_order_active']) && !empty($p['first_order_price'])): ?>
                                <span class="temu-badge">
                                    Aktif - <?= number_format($p['first_order_price'],2) ?> ₺
                                </span>
                            <?php else: ?>
                                <span class="temu-badge" style="background:#fff0f0;color:#888;border:1px solid #f29f99;">
                                    Pasif
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#aaa;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="detail-badge"><?= $productDetailClicks[$p['id']] ?? 0 ?> detay</span><br>
                        <span class="click-badge"><?= $productAffiliateClicks[$p['id']] ?? 0 ?> affiliate</span><br>
                        <span class="buy-badge"><?= $productBuyClicks[$p['id']] ?? 0 ?> satın al</span>
                    </td>
                    <td>
                        <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm mb-1"><i class="fa fa-edit"></i> Düzenle</a>
                        <a href="product_delete.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Emin misiniz?')"><i class="fa fa-trash"></i> Sil</a>
                    </td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-link mt-2">Panele dön</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>