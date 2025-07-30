<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$lang = $_GET['lang'] ?? 'tr';
$query = trim($_GET['query'] ?? '');
$price = $_GET['price'] ?? '';
$type = $_GET['type'] ?? '';
$category = intval($_GET['category'] ?? 0);

$where = ['is_active=1'];
$params = [];

// Kart başlığı için gösterilecek alan
if ($lang == 'tr') {
    $name_field = 'name_tr';
    $desc_field = 'short_description_tr';
} elseif ($lang == 'en') {
    $name_field = 'name_en';
    $desc_field = 'short_description_en';
} else {
    $name_field = 'name';
    $desc_field = 'short_description';
}

// Ürün adı veya meta title arama: hem Türkçe hem İngilizce + meta_title + açıklama
if ($query !== '') {
    $where[] = "(
        name_tr LIKE :query_tr OR 
        name_en LIKE :query_en OR 
        meta_title LIKE :meta OR
        $desc_field LIKE :desc
    )";
    $params[':query_tr'] = '%' . $query . '%';
    $params[':query_en'] = '%' . $query . '%';
    $params[':meta'] = '%' . $query . '%';
    $params[':desc'] = '%' . $query . '%';
}

// Kategori
if ($category > 0) {
    $where[] = "category_id = :category";
    $params[':category'] = $category;
}

// Fırsat/yeni/indirimli
if ($type === 'firsat')      $where[] = "campaign_active = 1";
if ($type === 'yeni')        $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
if ($type === 'indirimli')   $where[] = "(original_price > price)";

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Sıralama
$order = '';
if ($price === 'asc')  $order = "ORDER BY price ASC";
elseif ($price === 'desc') $order = "ORDER BY price DESC";
else $order = "ORDER BY id DESC";

// Sorgu
$sql = "SELECT *, $name_field AS name, $desc_field AS short_description FROM products $whereSql $order LIMIT 24";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcı favorileri: giriş yaptıysa favori ürün ID'lerini çek
$fav_ids = [];
if (!empty($_SESSION['user_id'])) {
    $favs = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id=?");
    $favs->execute([$_SESSION['user_id']]);
    $fav_ids = $favs->fetchAll(PDO::FETCH_COLUMN);
}

// Her ürüne is_fav ekle
foreach ($products as &$urun) {
    $urun['is_fav'] = in_array($urun['id'], $fav_ids);
}
unset($urun);

if (count($products) > 0) {
    echo '<div class="product-card-grid">';
    foreach ($products as $urun) {
        include '_product_card_snippet.php';
    }
    echo '</div>';
} else {
    echo '<div class="alert alert-warning" style="margin:24px 0;text-align:center;font-size:1.1rem;">'
        . ($lang == 'en' ? 'No products found.' : 'Ürün bulunamadı.')
        . '</div>';
}
?>