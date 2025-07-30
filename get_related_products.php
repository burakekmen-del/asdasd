<?php
require_once 'config.php';
require_once 'lang_init.php';

header('Content-Type: text/html; charset=utf-8');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

if (!$product_id) {
    echo '<div class="col-12 text-center text-muted py-4">'.($lang == 'en' ? 'Product ID is required' : 'Ürün ID gereklidir').'</div>';
    exit;
}

$stmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product || !isset($product['category_id'])) {
    echo '<div class="col-12 text-center text-muted py-4">'.($lang == 'en' ? 'Product not found' : 'Ürün bulunamadı').'</div>';
    exit;
}

$name_field = $lang == 'tr' ? 'name_tr' : 'name_en';

$related_stmt = $pdo->prepare("
    SELECT p.*, $name_field AS name 
    FROM products p
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
    ORDER BY RAND() 
    LIMIT 4
");
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($related_products)) {
    echo '<div class="col-12 text-center text-muted py-4">'.($lang == 'en' ? 'No related products found' : 'Benzer ürün bulunamadı').'</div>';
    exit;
}

// Favoriler (isteğe bağlı)
$fav_ids = [];
if (isset($_SESSION['user_id'])) {
    $favs = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id=?");
    $favs->execute([$_SESSION['user_id']]);
    $fav_ids = $favs->fetchAll(PDO::FETCH_COLUMN);
}


// Her ürüne is_fav ekle
if (!empty($fav_ids)) {
    foreach ($related_products as &$urun) {
        $urun['is_fav'] = in_array($urun['id'], $fav_ids);
    }
    unset($urun);
} else {
    foreach ($related_products as &$urun) {
        $urun['is_fav'] = false;
    }
    unset($urun);
}

?>
<div class="row gx-3 gy-4">
<?php foreach ($related_products as $urun): ?>
    <div class="col-md-3 col-6">
        <?php include '_product_card_snippet.php'; ?>
    </div>
<?php endforeach; ?>
</div>