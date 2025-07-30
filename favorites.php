<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'lang_init.php';

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: login.php?lang=" . urlencode($lang));
    exit;
}
$cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
$desc_field = $lang == 'tr' ? 'short_description_tr' : 'short_description_en';

$stmt = $pdo->prepare("SELECT p.*, p.$cat_name_field AS name, p.$desc_field AS short_description
    FROM favorites f
    INNER JOIN products p ON f.product_id = p.id
    WHERE f.user_id = ?
    ORDER BY f.id DESC LIMIT 60");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "header.php";
?>
<main class="flex-fill">
    <div class="container py-4">
        <h1 class="mb-4"><?= $lang == 'en' ? 'My Favorites' : 'Favorilerim' ?></h1>
        <?php if (empty($products)): ?>
            <div class="alert alert-info mt-4">
                <?= $lang == 'en'
                    ? 'You have not added any products to your favorites yet.'
                    : 'Henüz hiç ürünü favorilere eklemediniz.' ?>
            </div>
        <?php else: ?>
            <div class="product-card-grid">
                <?php foreach ($products as $urun): ?>
                    <?php $urun['is_fav'] = true; ?>
                    <?php include '_product_card_modern.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include "footer.php"; ?>