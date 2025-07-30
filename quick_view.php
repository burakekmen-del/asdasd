<?php
require_once 'config.php';
require_once 'lang_init.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    echo '<div class="alert alert-danger">Ürün bulunamadı.</div>';
    exit;
}

$cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
$desc_field = $lang == 'tr' ? 'short_description_tr' : 'short_description_en';

$stmt = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    echo '<div class="alert alert-danger">'.($lang == 'en' ? "Product not found." : "Ürün bulunamadı.").'</div>';
    exit;
}

if (isset($_SESSION['user_id'])) {
    $isFavQuery = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ?");
    $isFavQuery->execute([$_SESSION['user_id'], $urun['id']]);
    $urun['is_fav'] = $isFavQuery->fetchColumn() ? true : false;
} else {
    $urun['is_fav'] = false;
}
?>
<div class="row g-3 p-3">
    <div class="col-12 col-md-5">
        <img src="<?= htmlspecialchars(get_product_img(explode(',', $urun['images'])[0] ?? '')) ?>"
             alt="<?= htmlspecialchars($urun['name']) ?>"
             class="w-100 rounded"
             style="object-fit:contain;min-height:180px;max-height:300px;">
    </div>
    <div class="col-12 col-md-7">
        <h2 class="mb-1" style="font-size:1.25rem;"><?= htmlspecialchars($urun['name']) ?></h2>
        <div class="mb-2 text-muted"><?= htmlspecialchars($urun['short_description']) ?></div>
        <div class="product-pricing mb-2">
            <?php if($urun['original_price'] > $urun['price']): ?>
                <span class="original-price">₺<?= number_format($urun['original_price'],2,',','.') ?></span>
            <?php endif; ?>
            <span class="current-price" style="font-size:1.15em;">₺<?= number_format($urun['price'],2,',','.') ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
            <button type="button"
                class="action-btn fav-btn<?= $urun['is_fav'] ? ' active' : '' ?>"
                data-product="<?= $urun['id'] ?>"
                aria-pressed="<?= $urun['is_fav'] ? 'true' : 'false' ?>"
                aria-label="<?= $lang == 'en' ? ($urun['is_fav'] ? 'Remove from favorites' : 'Add to favorites') : ($urun['is_fav'] ? 'Favorilerden çıkar' : 'Favorilere ekle') ?>">
                <span class="icon-heart<?= $urun['is_fav'] ? ' fas' : '' ?>" aria-hidden="true"></span>
            </button>
            <?php if(!empty($urun['affiliate_link'])): ?>
                <a href="redirect.php?id=<?= $urun['id'] ?>&type=buy"
                target="_blank"
                rel="nofollow noopener"
                class="aff-card-btn buy"
                style="font-size:1.08em;"
                aria-label="<?= $lang == 'en' ? 'Go to Seller (external)' : 'Satıcıya Git (harici)' ?>">
                    <span class="fa fa-shopping-cart"></span> <?= $lang == 'en' ? 'Buy Now' : 'Satın Al' ?>
                </a>
            <?php endif; ?>
            <a href="product.php?id=<?= $urun['id'] ?>&lang=<?= $lang ?>" class="aff-card-btn" style="font-size:1.08em;">
                <?= $lang == 'en' ? 'View Details' : 'Detaylara Git' ?>
            </a>
        </div>
    </div>
</div>