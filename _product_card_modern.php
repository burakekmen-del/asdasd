<?php
/**
 * Modern ve Düzenli Ürün Kartı - Tam Responsive
 * 5'li ürün grid görünümü: Masaüstünde 5 ürün yan yana, responsive olarak 3-2-1 grid uyumu.
 * Kartlar daha büyük, aralarındaki mesafe daha az!
 */

if (!isset($urun) || !is_array($urun)) return;

if (!isset($lang)) {
  if (isset($_GET['lang'])) $lang = $_GET['lang'];
  elseif (isset($_SESSION['lang'])) $lang = $_SESSION['lang'];
  else $lang = 'tr';
}
$lang = strtolower($lang);

// Temel veriler
$id = (int)$urun['id'];
$name = htmlspecialchars($urun['name'] ?? ($lang == 'en' ? 'Product' : 'Ürün'));
$platform = strtolower($urun['platform'] ?? 'amazon');
$price = (float)($urun['current_price'] ?? $urun['price'] ?? 0);
$oldPrice = (float)($urun['original_price'] ?? 0);
$discount = ($oldPrice > 0 && $price < $oldPrice) ? round(100 - ($price / $oldPrice * 100)) : 0;
$isFav = isset($_SESSION['user_id']) && isset($urun['is_fav']) && $urun['is_fav'];
$affiliateUrl = $urun['affiliate_url'] ?? $urun['affiliate_link'] ?? $urun['product_link'] ?? "#";
$detailUrl = "/product.php?id=" . $id . (isset($lang) && $lang ? "&lang=" . $lang : "");

// Temu ilk indirme fiyatı
$isTemu = ($platform === 'temu');
$firstOrderActive = !empty($urun['first_order_active']);
$firstOrderPrice  = !empty($urun['first_order_price']) ? (float)$urun['first_order_price'] : null;

// Görseller (images: virgülle ayrılmış, image: tekli)
$images = [];
if (!empty($urun['images'])) {
    $images = array_filter(array_map('trim', explode(',', $urun['images'])));
}
$mainImage = '';
if (!empty($images)) {
    $mainImage = $images[0];
} elseif (!empty($urun['image'])) {
    $mainImage = trim($urun['image']);
} else {
    $mainImage = 'default-product.webp'; // default asset
}

// Görsel yolu düzeltme (sadece dosya adıysa uploads klasörüne ekle, URL ise aynen al)
if ($mainImage && strpos($mainImage, '/') === false && strpos($mainImage, 'http') !== 0) {
    $mainImage = '/uploads/' . $mainImage;
} elseif ($mainImage && strpos($mainImage, '/') !== 0 && strpos($mainImage, 'http') !== 0) {
    $mainImage = '/' . $mainImage;
}
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $mainImage)) {
    $mainImage = '/assets/images/default-product.webp';
}

$platformBorders = [
  'amazon' => '#ff9900',
  'temu' => '#f60',
  'trendyol' => '#f27a1a',
  'hepsiburada' => '#0073ea',
  'n11' => '#d70f64',
  'ebay' => '#3665f3'
];
$borderColor = $platformBorders[$platform] ?? '#6c5ce7';

$platformNames = [
  "amazon"      => ["Amazon'da Gör", "View on Amazon"],
  "temu"        => ["Temu'da Gör", "View on Temu"],
  "trendyol"    => ["Trendyol'da Gör", "View on Trendyol"],
  "hepsiburada" => ["Hepsiburada'da Gör", "View on Hepsiburada"],
  "n11"         => ["N11'de Gör", "View on N11"],
  "ebay"        => ["eBay'de Gör", "View on eBay"]
];
if (isset($platformNames[$platform])) {
    $platformBtnText = $lang == 'en' ? $platformNames[$platform][1] : $platformNames[$platform][0];
} else {
    $platformBtnText = $lang == 'en' ? "View on Platform" : "Platformda Gör";
}

$commentText = $lang == 'en' ? "comments" : "yorum";
$starText = $lang == 'en' ? "stars" : "puan";
$favAddText = $lang == 'en' ? 'Add to favorites' : 'Favorilere ekle';
$favRemoveText = $lang == 'en' ? 'Remove from favorites' : 'Favorilerden çıkar';

// KART GÖSTERİLMESİNİN ENGELLENMESİ: Boş ya da 0 fiyatlı ürünler veya isimsiz ürünler kartta görünmesin
if (!$id || !$name || $price <= 0) return;
global $isMobile;
if (!isset($isMobile)) {
  if (!function_exists('isMobile')) {
    function isMobile() {
      return preg_match('/(android|iphone|ipad|ipod|opera mini|iemobile|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
    }
  }
  $isMobile = isMobile();
}

if ($isMobile) {
    // MOBİL KART TASARIMI (Trendyol benzeri, daha kompakt, tek satır başlık, büyük butonlar)
    echo '<div class="mini-product-card mini-mobile-card" data-id="'.$id.'" style="--platform-border: '.$borderColor.';max-width:100%;border-radius:13px;box-shadow:0 1.5px 8px #ececec33;padding:7px 5px 10px 5px;background:#fff;margin:0;">';
    echo '<a href="'.$detailUrl.'" class="mini-img-link" tabindex="0" aria-label="'.$name.'" style="display:block;width:100%;aspect-ratio:1/1;" onclick="logDetailClick(event, '.(int)$id.')">';
    echo '<div class="mini-img-box" style="width:100%;display:flex;align-items:center;justify-content:center;margin-bottom:7px;">';
    echo '<img src="'.$mainImage.'" alt="'.$name.'" loading="lazy" class="mini-card-img" width="120" height="120" style="width:120px;height:120px;object-fit:contain;border-radius:10px;background:#fafbfc;box-shadow:0 1px 4px #ececec22;" onerror="this.src=\'/assets/images/default-product.webp\'">';
    if($discount) echo '<span class="mini-badge discount" style="top:7px;left:7px;font-size:.93em;padding:2px 7px;border-radius:7px;">-%'.$discount.'</span>';
    echo '</div></a>';
    echo '<div class="mini-info" style="padding:0 2px;">';
    echo '<div class="mini-title small" style="font-size:1.01em;font-weight:700;color:#232343;margin-bottom:2px;min-height:36px;line-height:1.18;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><a href="'.$detailUrl.'" style="color:inherit;text-decoration:none;" onclick="logDetailClick(event, '.(int)$id.')">'.$name.'</a></div>';
    echo '<div class="mini-price-row" style="font-size:1.09em;font-weight:800;color:#f27a1a;margin-bottom:2px;">';
    if($oldPrice > 0 && $price < $oldPrice) echo '<span class="mini-oldprice" style="font-size:.97em;color:#888;text-decoration:line-through;margin-right:4px;">'.number_format($oldPrice,2).' TL</span>';
    echo '<span class="mini-price" style="color:#00b894;font-weight:700;font-size:1.13em;">'.number_format($price,2).' TL</span>';
    if($isTemu && $firstOrderActive && $firstOrderPrice) {
        echo '<span class="mini-price mini-firstorder" style="background:#f1f0ff;border-radius:7px;padding:2px 9px;margin-left:4px;font-size:1em;display:flex;align-items:center;gap:4px;"><i class="fa fa-bolt text-warning"></i>'.($lang=='en' ? 'First Order:' : 'İlk Temu İndirimi:').number_format($firstOrderPrice,2).' TL <span style="margin-left:2px;" title="'.($lang=='en' ? 'Valid only for your first Temu order' : 'Sadece Temu uygulamasını ilk kez indirenler için geçerli').'"><i class="fa fa-info-circle"></i></span></span>';
    }
    echo '</div>';
    echo '<div class="mini-actions" style="display:flex;gap:6px;margin-top:9px;align-items:center;">';
    echo '<a href="/redirect.php?id='.$id.'&type=buy" class="mini-btn buy" target="_blank" rel="nofollow noopener" data-aff-external data-platform="'.$platform.'" onclick="logSaleClick('.$id.')" style="background:linear-gradient(90deg,#6c5ce7 0%,#00b894 100%);color:#fff;font-weight:700;min-width:100px;justify-content:center;font-size:1em;box-shadow:0 2px 11px #00b89422;padding:8px 0;border-radius:7px;">'.$platformBtnText.'</a>';
    echo '<button class="mini-btn fav-btn'.($isFav ? ' active' : '').'" data-product-id="'.$id.'" aria-label="'.($isFav ? $favRemoveText : $favAddText).'" style="font-size:1.05em;background:#f8f8ff;border:none;border-radius:7px;padding:9px 0;min-width:38px;min-height:38px;color:#6c5ce7;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 6px #ececec33;"><i class="fa'.($isFav ? 's' : 'r').' fa-heart"></i></button>';
    echo '<button class="mini-btn compare-btn" data-product="'.$id.'" aria-label="'.($lang=='en' ? 'Compare' : 'Karşılaştır').'" style="font-size:1.05em;background:#f8f8ff;border:none;border-radius:7px;padding:9px 0;min-width:38px;min-height:38px;color:#6c5ce7;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 6px #ececec33;"><i class="fa fa-balance-scale"></i></button>';
    echo '</div></div></div>';
} else {
    // MASAÜSTÜ KART TASARIMI (mevcut modern kart)
    echo '<div class="mini-product-card" data-id="'.$id.'" style="--platform-border: '.$borderColor.';">';
    echo '<a href="'.$detailUrl.'" class="mini-img-link" tabindex="0" aria-label="'.$name.'" onclick="logDetailClick(event, '.(int)$id.')">';
    echo '<div class="mini-img-box">';
    echo '<img src="'.$mainImage.'" alt="'.$name.'" loading="lazy" class="mini-card-img" width="220" height="220" onerror="this.src=\'/assets/images/default-product.webp\'">';
    if($discount) echo '<span class="mini-badge discount">-%'.$discount.'</span>';
    echo '</div></a>';
    echo '<div class="mini-info">';
    echo '<div class="mini-title small"><a href="'.$detailUrl.'" onclick="logDetailClick(event, '.(int)$id.')">'.$name.'</a></div>';
    echo '<div class="mini-price-row">';
    if($oldPrice > 0 && $price < $oldPrice) echo '<span class="mini-oldprice">'.number_format($oldPrice,2).' TL</span>';
    echo '<span class="mini-price">'.number_format($price,2).' TL</span>';
    if($isTemu && $firstOrderActive && $firstOrderPrice) {
      echo '<span class="mini-price mini-firstorder"><i class="fa fa-bolt text-warning"></i>'.($lang=='en' ? 'First Order:' : 'İlk Temu İndirimi:').number_format($firstOrderPrice,2).' TL <span style="margin-left:2px;" title="'.($lang=='en' ? 'Valid only for your first Temu order' : 'Sadece Temu uygulamasını ilk kez indirenler için geçerli').'"><i class="fa fa-info-circle"></i></span></span>';
    }
    echo '</div>';
    echo '<div class="mini-actions">';
    echo '<a href="/redirect.php?id='.$id.'&type=buy" class="mini-btn buy" target="_blank" rel="nofollow noopener" data-aff-external data-platform="'.$platform.'" onclick="logSaleClick('.$id.')">'.$platformBtnText.'</a>';
    echo '<button class="mini-btn fav-btn'.($isFav ? ' active' : '').'" data-product-id="'.$id.'" aria-label="'.($isFav ? $favRemoveText : $favAddText).'"><i class="fa'.($isFav ? 's' : 'r').' fa-heart"></i></button>';
    echo '<button class="mini-btn compare-btn" data-product="'.$id.'" aria-label="'.($lang=='en' ? 'Compare' : 'Karşılaştır').'"><i class="fa fa-balance-scale"></i></button>';
    echo '</div></div></div>';
}