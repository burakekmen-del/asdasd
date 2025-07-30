<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$current_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
if (!isset($categories)) {
    $cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
    $categories = $pdo->query("SELECT id, $cat_name_field AS name FROM categories ORDER BY $cat_name_field ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="aff-search-form mb-3" role="search">
    <input type="text"
           id="productSearchInput"
           class="form-control mb-2"
           placeholder="<?= $lang == 'en' ? 'Search products...' : 'Ürün adıyla ara...' ?>"
           aria-label="<?= $lang == 'en' ? 'Search products...' : 'Ürün adıyla ara...' ?>">
    <button type="button"
            class="btn btn-outline-secondary w-100 filter-toggle-btn d-md-none"
            aria-expanded="false"
            aria-controls="mobileFilterExtra">
        <?= $lang == 'en' ? 'Show Filters' : 'Filtreler' ?>
    </button>
    <div class="mobile-filter-extra" id="mobileFilterExtra">
        <select id="productFilterPrice" class="form-select" aria-label="<?= $lang == 'en' ? 'Sort by price' : 'Fiyata göre' ?>">
            <option value=""><?= $lang == 'en' ? 'Sort by price' : 'Fiyata göre' ?></option>
            <option value="asc"><?= $lang == 'en' ? 'Lowest first' : 'Artan Fiyat' ?></option>
            <option value="desc"><?= $lang == 'en' ? 'Highest first' : 'Azalan Fiyat' ?></option>
        </select>
        <select id="productFilterType" class="form-select" aria-label="<?= $lang == 'en' ? 'All types' : 'Hepsi' ?>">
            <option value=""><?= $lang == 'en' ? 'All types' : 'Hepsi' ?></option>
            <option value="firsat"><?= $lang == 'en' ? 'Deals' : 'Fırsat Ürünler' ?></option>
            <option value="yeni"><?= $lang == 'en' ? 'New' : 'Yeni Ürünler' ?></option>
            <option value="indirimli"><?= $lang == 'en' ? 'Discounted' : 'İndirimli Ürünler' ?></option>
        </select>
        <select id="productFilterCategory" class="form-select" aria-label="<?= $lang == 'en' ? 'All categories' : 'Tüm Kategoriler' ?>">
            <option value=""><?= $lang == 'en' ? 'All categories' : 'Tüm Kategoriler' ?></option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $current_category == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-outline-secondary w-100" id="resetFiltersBtn">
            <?= $lang == 'en' ? 'Reset' : 'Sıfırla' ?>
        </button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.aff-search-form');
    const filterBtn = form.querySelector('.filter-toggle-btn');
    const filterExtra = form.querySelector('.mobile-filter-extra');
    filterBtn.addEventListener('click', function() {
        form.classList.toggle('show-filters');
        filterBtn.setAttribute('aria-expanded', form.classList.contains('show-filters'));
        filterBtn.textContent = form.classList.contains('show-filters')
            ? '<?= $lang == 'en' ? "Hide Filters" : "Filtreleri Gizle" ?>'
            : '<?= $lang == 'en' ? "Show Filters" : "Filtreler" ?>';
    });

    const searchInput    = document.getElementById('productSearchInput');
    const filterPrice    = document.getElementById('productFilterPrice');
    const filterType     = document.getElementById('productFilterType');
    const filterCategory = document.getElementById('productFilterCategory');
    const resetBtn       = document.getElementById('resetFiltersBtn');
    const staticProducts = document.getElementById('searchProductsStatic');
    let lastCall = 0;

    // Canlı ürün gridini static gridin hemen üstüne ekle
    let liveProductsContainer = document.getElementById('liveProductsContainer');
    if(!liveProductsContainer) {
        liveProductsContainer = document.createElement('div');
        liveProductsContainer.id = 'liveProductsContainer';
        liveProductsContainer.style.display = 'none';
        if(staticProducts) staticProducts.parentNode.insertBefore(liveProductsContainer, staticProducts);
    }

    function showStaticProducts() {
        if(staticProducts) staticProducts.style.display = '';
        liveProductsContainer.innerHTML = '';
        liveProductsContainer.style.display = 'none';
    }
    function hideStaticProducts() {
        if(staticProducts) staticProducts.style.display = 'none';
        liveProductsContainer.style.display = '';
    }

    function liveSearch() {
        const q        = searchInput.value.trim();
        const price    = filterPrice.value;
        const type     = filterType.value;
        const category = filterCategory.value;
        const lang     = '<?= $lang ?>';

        if (!q && !price && !type && !category) {
            showStaticProducts();
            return;
        }

        hideStaticProducts();
        let thisCall = ++lastCall;

        fetch('product_search.php?query=' + encodeURIComponent(q)
            + '&price=' + encodeURIComponent(price)
            + '&type=' + encodeURIComponent(type)
            + '&category=' + encodeURIComponent(category)
            + '&lang=' + encodeURIComponent(lang)
        )
        .then(resp => resp.text())
        .then(html => {
            if (thisCall !== lastCall) return;
            liveProductsContainer.innerHTML = html;
            liveProductsContainer.style.display = '';
        });
    }

    searchInput.addEventListener('input', liveSearch);
    filterPrice.addEventListener('change', liveSearch);
    filterType.addEventListener('change', liveSearch);
    filterCategory.addEventListener('change', liveSearch);

    resetBtn.addEventListener('click', function(){
        searchInput.value = '';
        filterPrice.selectedIndex = 0;
        filterType.selectedIndex = 0;
        filterCategory.selectedIndex = 0;
        showStaticProducts();
    });

    // Eğer kategori GET ile gelirse ilk açılışta filtreli başlat
    <?php if ($current_category): ?>
    filterCategory.value = "<?= (int)$current_category ?>";
    liveSearch();
    <?php endif; ?>
});
</script>