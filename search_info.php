<?php
$searchCount = isset($opportunityProducts) ? count($opportunityProducts) : 0;
$activeCategory = '';
if (!empty($category_id) && $category_id !== 'populer') {
    // Kategorinin adını veritabanından çekebilirsin
    $catStmt = $pdo->prepare("SELECT $cat_name_field AS name FROM categories WHERE id=?");
    $catStmt->execute([$category_id]);
    $catRow = $catStmt->fetch(PDO::FETCH_ASSOC);
    $activeCategory = $catRow ? $catRow['name'] : '';
}
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
?>
<div class="search-info mb-3" style="font-size:1.05em;color:#5649C0;">
    <?php if ($activeCategory): ?>
        <span><?= $lang == 'tr' ? "Kategori:" : "Category:" ?> <b><?= htmlspecialchars($activeCategory) ?></b></span>
    <?php endif; ?>
    <?php if ($q): ?>
        <span><?= $lang == 'tr' ? "Arama:" : "Search:" ?> <b><?= htmlspecialchars($q) ?></b></span>
    <?php endif; ?>
    <span><?= $lang == 'tr' ? "Toplam sonuç:" : "Total results:" ?> <b><?= $searchCount ?></b></span>
</div>