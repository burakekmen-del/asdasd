<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
require_once '../config.php';
if (!isset($_SESSION['admin_login'])) { header("Location: login.php"); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hero_products'])) {
    $pdo->query("DELETE FROM featured_hero_products");
    $order = 0;
    foreach ($_POST['hero_products'] as $pid) {
        $pid = intval($pid);
        $pdo->prepare("INSERT INTO featured_hero_products (product_id, display_order) VALUES (?, ?)")->execute([$pid, ++$order]);
    }
    $msg = "Hero banner ürünleri güncellendi.";
}

$heroProducts = $pdo->query("SELECT product_id FROM featured_hero_products ORDER BY display_order ASC")->fetchAll(PDO::FETCH_COLUMN);
$allProducts = $pdo->query("SELECT id, name_en FROM products WHERE is_active=1 ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hero Banner Ürünleri Yönetimi - Affiluxe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { background:#f5f7ff; }
        .featured-box { background:#fff; border-radius:16px; box-shadow:0 8px 32px #2563eb13, 0 2px 10px #0002; max-width:600px; margin:40px auto 70px auto; padding:2.7rem 2rem 2.4rem 2rem; }
        h2 { color:#6c5ce7; font-weight:800;}
        .hero-featured-list { display:flex; gap:9px; flex-wrap:wrap; margin-bottom:11px;}
        .hero-featured-list .badge { font-size:1.06em; padding:7px 12px; background:#6C5CE7; color:#fff; border-radius:7px; display:flex; align-items:center; gap:7px;}
        .hero-featured-list .badge .fa-times { cursor:pointer; margin-left:6px; }
    </style>
</head>
<body>
    <div class="featured-box">
        <h2><i class="fa fa-star"></i> Hero Banner Ürünleri Yönetimi</h2>
        <?php if(!empty($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="hero-featured-list mb-2">
                <?php foreach($heroProducts as $hid):
                    $p = array_filter($allProducts, function($item) use ($hid) { return $item['id'] == $hid; });
                    $p = reset($p);
                    if($p):
                ?>
                    <span class="badge">
                        <?= htmlspecialchars($p['name_en']) ?>
                        <a href="#" class="text-light remove-hero-product" data-id="<?= $hid ?>"><i class="fa fa-times"></i></a>
                        <input type="hidden" name="hero_products[]" value="<?= $hid ?>">
                    </span>
                <?php endif; endforeach; ?>
            </div>
            <div class="input-group mb-2">
                <select id="heroProductSelect" class="form-select">
                    <option value="">Ürün Seç...</option>
                    <?php foreach($allProducts as $p):
                        if(in_array($p['id'],$heroProducts)) continue; ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name_en']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-info" id="addHeroProductBtn"><i class="fa fa-plus"></i> Ekle</button>
            </div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
        <div class="form-text mt-2">Buradan seçili ürünler ana sayfanın üst kısmında "öne çıkan" olarak gözükecektir. Sürükle-bırak sıralama için ek geliştirme yapılabilir.</div>
        <a href="dashboard.php" class="btn btn-secondary mt-4"><i class="fa fa-arrow-left"></i> Panele Dön</a>
    </div>
    <script>
    // Hero ürünleri ekle/çıkar (JS ile)
    document.getElementById('addHeroProductBtn').onclick = function(){
        var sel = document.getElementById('heroProductSelect');
        var val = sel.value, txt = sel.options[sel.selectedIndex].text;
        if(!val) return;
        var current = document.querySelectorAll('.hero-featured-list input[name="hero_products[]"]').length;
        if(current >= 4) { alert('En fazla 4 ürün seçebilirsiniz!'); return; }
        if(document.querySelector('.hero-featured-list input[value="'+val+'"]')) return;
        var badge = document.createElement('span');
        badge.className = 'badge';
        badge.innerHTML = txt+' <a href="#" class="text-light remove-hero-product" data-id="'+val+'"><i class="fa fa-times"></i></a>' +
            '<input type="hidden" name="hero_products[]" value="'+val+'">';
        document.querySelector('.hero-featured-list').appendChild(badge);
    };
    document.addEventListener('click', function(e){
        if(e.target.closest('.remove-hero-product')){
            e.preventDefault();
            var badge = e.target.closest('.badge');
            if(badge) badge.remove();
        }
    });
    </script>
</body>
</html>