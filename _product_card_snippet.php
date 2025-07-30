<?php
if (!isset($urun) || !is_array($urun)) {
    echo '<!-- Hata: $urun dizisi tanımlı değil -->';
    return;
}
if (!function_exists('get_product_img')) {
    function get_product_img($img) {
        $img = trim($img);
        if (!$img) return 'assets/images/default-product.webp';
        if (file_exists($img)) return $img;
        if (file_exists('uploads/'.$img)) return 'uploads/'.$img;
        if (file_exists('assets/images/'.$img)) return 'assets/images/'.$img;
        return 'assets/images/default-product.webp';
    }
}
if (!function_exists('getPlatformLogo')) {
    function getPlatformLogo($platform) {
        $platform = strtolower(trim($platform));
        $formats = ['webp', 'svg', 'png'];
        foreach ($formats as $ext) {
            $file = "assets/platform-logos/{$platform}.{$ext}";
            if (file_exists($file)) return $file;
        }
        return '';
    }
}

$categoryId = isset($urun['category_id']) ? (int)$urun['category_id'] : '';
$platformLogo = !empty($urun['platform']) ? getPlatformLogo($urun['platform']) : '';
$imgList = !empty($urun['images']) ? array_map('trim', explode(',', $urun['images'])) : [];
$img = (!empty($imgList[0])) ? $imgList[0] : '';
$imgPath = get_product_img($img);

$name = '';
if (!empty($urun['name'])) $name = $urun['name'];
elseif (!empty($urun['name_en'])) $name = $urun['name_en'];
elseif (!empty($urun['name_tr'])) $name = $urun['name_tr'];
elseif (!empty($urun['meta_title'])) $name = $urun['meta_title'];
else $name = 'Ürün';
$name = htmlspecialchars($name);

$price = isset($urun['price']) && $urun['price'] > 0 ? $urun['price'] : ($urun['original_price'] ?? 0);
$oldPrice = $urun['original_price'] ?? null;
$discountPercent = ($oldPrice > $price && $price > 0) ? round(100 - ($price / $oldPrice * 100)) : 0;
$lang = $lang ?? ($_SESSION['lang'] ?? 'tr');
$isLoggedIn = !empty($_SESSION['user_id']);
$isFav = ($isLoggedIn && isset($urun['is_fav']) && $urun['is_fav']) ? true : false;
$is_firsat = !empty($urun['campaign_active']);
$is_indirimli = ($oldPrice > $price && $price > 0);

// SEO dostu ürün linki (slug varsa)
$slug = isset($urun['slug']) && $urun['slug'] ? $urun['slug'] : null;
$detail_url = $slug
    ? ($lang == 'en' ? "/product/$slug" : "/urun/$slug")
    : "product.php?id={$urun['id']}&lang=$lang";

// Affiliate yönlendirme linki (modal için data attribute)
$aff_link = "redirect.php?id={$urun['id']}&type=buy";
?>
<?php
// Modern ürün kartı kodunu ekle
include '_product_card_modern.php';
?>