
<?php
// Hata ayıklama aktif (canlıda kapatılmalı)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'lang_init.php';

// Kategori açıklama array dosyasını dahil et
include_once 'category_info.php';

// Fırsat ürünler sorgusu için gerekli alanlar (kategori ve dil)
$cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
$desc_field = $lang == 'tr' ? 'short_description_tr' : 'short_description_en';
$current_category = isset($_GET['category']) ? intval($_GET['category']) : 0;

$where = ["campaign_active=1"];
$params = [];
if ($current_category > 0) {
    $where[] = "category_id = ?";
    $params[] = $current_category;
}
if (!empty($_GET['q'])) {
    $where[] = "($cat_name_field LIKE ? OR $desc_field LIKE ?)";
    $query = '%' . $_GET['q'] . '%';
    $params[] = $query;
    $params[] = $query;
}
$whereSql = 'WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products $whereSql ORDER BY id DESC LIMIT 40");
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header'ı ana sayfadakiyle tamamen aynı şekilde dahil et
include 'header.php';
?>
<style>
.product-card-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px 6px;
    margin-bottom: 32px;
    background: transparent;
    box-shadow: none;
    border-radius: 0;
}
@media (max-width: 1100px) {
    .product-card-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px 4px;
    }
}
@media (max-width: 700px) {
    .product-card-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 6px 2px;
    }
}
@media (max-width: 500px) {
    .product-card-grid {
        grid-template-columns: 1fr;
        gap: 5px 2px;
    }
}
.product-card-modern {
    transition: box-shadow .18s, transform .18s;
    min-height: 340px;
}
.product-card-modern:hover {
    box-shadow: 0 8px 32px -8px #6c5ce744;
    transform: translateY(-2px) scale(1.012);
}
</style>

<main class="flex-fill">
    <div class="container py-4">
        <h1 class="mb-4"><?php echo $lang === 'en' ? 'Deals & Campaigns' : 'Fırsat & Kampanya Ürünleri'; ?></h1>
        <?php include 'search_form.php'; ?>

        <!-- Kategori Açıklama Kutusu -->
        <?php
        $slugKey = '';
        // Slug'ı belirle
        if ($current_category) {
            foreach ($categories as $cat) {
                if ((int)$cat['id'] === $current_category) {
                    $slugKey = strtolower(
                        $lang === 'tr'
                            ? ($cat['slug_tr'] ?? preg_replace('/[^a-z0-9-]/', '', strtolower($cat['name'])))
                            : ($cat['slug_en'] ?? preg_replace('/[^a-z0-9-]/', '', strtolower($cat['name'])))
                    );
                    break;
                }
            }
        }
        if (!empty($slugKey) && isset($categoryExplanations[$slugKey])) {
            $catExp = $categoryExplanations[$slugKey];
            echo '<section class="category-info-box" style="background:#f1f0ff;border-radius:12px;padding:16px 18px;margin-bottom:18px;">
                <h2 style="font-size:1.18em;font-weight:600;">' . $catExp["title"] . '</h2>
                <div style="font-size:1em;color:#5649c0;">' . $catExp["desc"] . '</div>
                <meta name="keywords" content="' . htmlspecialchars($catExp["keywords"]) . '">
            </section>';
        }
        ?>

        <!-- Ürünler -->
        <?php if (empty($products)): ?>
            <div class="alert alert-warning mt-4">
                <?php echo $lang === 'en' ? 'No products found for selected criteria.' : 'Seçilen kriterlere uygun ürün bulunamadı.'; ?>
            </div>
        <?php else: ?>
            <div class="product-card-grid">
                <?php foreach ($products as $urun): ?>
                    <?php
                    // Favori kontrolü için optimizasyon: Tek sorgu ile topluca çekmek daha iyi olur.
                    if (!isset($urun['is_fav'])) {
                        if (!empty($_SESSION['user_id'])) {
                            $isFavQuery = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ?");
                            $isFavQuery->execute([$_SESSION['user_id'], $urun['id']]);
                            $urun['is_fav'] = $isFavQuery->fetchColumn() ? true : false;
                        } else {
                            $urun['is_fav'] = false;
                        }
                    }
                    ?>
                    <?php include '_product_card_modern.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- SSS Bölümü (Footer üstünde veya burada) -->
        <?php include 'footer_faq.php'; ?>

    </div>
</main>
<?php include 'footer.php'; ?>