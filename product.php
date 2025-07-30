<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'lang_init.php';

// *** Yönlendirme ve id kontrolü ***
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    header("Location: index.php");
    exit;
}

// Ürün detayına giriş logu (product_click_logs tablosuna 'detail' kaydı ekle)
try {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO product_click_logs (product_id, user_id, click_type, ip, user_agent, clicked_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$product_id, $user_id, 'detail', $ip, $ua]);
} catch (Exception $e) { /* sessizce geç */ }


// Ürün çek
$cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
$desc_field = $lang == 'tr' ? 'short_description_tr' : 'short_description_en';
$stmt = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$urun) {
    echo '<div style="padding:48px;text-align:center;font-size:1.2em;">' . ($lang=='en' ? 'Product not found.' : 'Ürün bulunamadı.') . '</div>';
    include "footer.php";
    exit;
}

// SEO & AdSense için dinamik meta başlık, açıklama ve görsel
$page_title = ($lang == 'tr' ? ($urun['name'] . ' - En İyi Fiyat ve Kampanya | Affiluxe') : ($urun['name'] . ' - Best Price & Deal | Affiluxe'));
$page_desc = !empty($urun['short_description'])
    ? $urun['short_description']
    : ($lang == 'tr' ? 'Bu ürünün en güncel fiyatı, kampanyası ve detayları Affiluxe ile hemen keşfet.' : 'Discover the latest price, deal and details of this product on Affiluxe.');
$product_desc = '';
if ($lang === 'tr') {
    if (!empty($urun['description_tr'])) {
        $product_desc = $urun['description_tr'];
    } elseif (!empty($urun['description'])) {
        $product_desc = $urun['description'];
    }
    if (!isset($meta_title)) {
        $meta_title = !empty($urun['meta_title_tr']) ? $urun['meta_title_tr'] : $urun['name_tr'];
    }
    if (!isset($meta_desc)) {
        $meta_desc = !empty($urun['meta_desc_tr']) ? $urun['meta_desc_tr'] : mb_substr(strip_tags($product_desc), 0, 160, 'UTF-8');
    }
} else {
    if (!empty($urun['description_en'])) {
        $product_desc = $urun['description_en'];
    } elseif (!empty($urun['description'])) {
        $product_desc = $urun['description'];
    }
    if (!isset($meta_title)) {
        $meta_title = !empty($urun['meta_title_en']) ? $urun['meta_title_en'] : $urun['name_en'];
    }
    if (!isset($meta_desc)) {
        $meta_desc = !empty($urun['meta_desc_en']) ? $urun['meta_desc_en'] : mb_substr(strip_tags($product_desc), 0, 160, 'UTF-8');
    }
}
$meta_img = '';
if (!empty($urun['images'])) {
    $imgList = array_map('trim', explode(',', $urun['images']));
    $firstImg = trim($imgList[0]);
    if ($firstImg) {
        if (strpos($firstImg, 'http') === 0) {
            $meta_img = $firstImg;
        } else {
            $meta_img = 'https://affiluxe.com.tr' . (strpos($firstImg, '/') === 0 ? $firstImg : '/uploads/' . $firstImg);
        }
    }
}
if (!$meta_img) $meta_img = 'https://affiluxe.com.tr/assets/images/og-default.webp';


// SEO: Dinamik meta başlık, açıklama, canonical, Open Graph, Twitter Card
?><title><?= htmlspecialchars($meta_title ?? $page_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($meta_desc ?? $page_desc) ?>">
<link rel="canonical" href="https://affiluxe.com.tr/product.php?id=<?= $urun['id'] ?>">
<meta property="og:title" content="<?= htmlspecialchars($meta_title ?? $page_title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($meta_desc ?? $page_desc) ?>">
<meta property="og:image" content="<?= htmlspecialchars($meta_img) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php
include "header.php";

// Benzer ürünler
$relatedProducts = [];
if (!empty($urun['category_id'])) {
    $relStmt = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products WHERE category_id = ? AND id != ? ORDER BY id DESC LIMIT 8");
    $relStmt->execute([$urun['category_id'], $urun['id']]);
    $relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Görsel ve video (video varsa başa al, açıklama/caption ve thumb desteği ile)
function get_product_img($img) {
    $img = trim($img);
    if (!$img) return '/assets/images/default-product.webp';
    if (strpos($img, 'http') === 0) return $img;
    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$img)) return '/'.$img;
    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/'.$img)) return '/uploads/'.$img;
    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/assets/images/'.$img)) return '/assets/images/'.$img;
    return '/assets/images/default-product.webp';
}
$imgList = !empty($urun['images']) ? array_map('trim', explode(',', $urun['images'])) : [];
$mediaList = [];

// Video önce gelecek, thumb ve caption desteğiyle
if (!empty($urun['video_src'])) {
    $mediaList[] = [
        'type' => 'video',
        'src'  => $urun['video_src'],
        'thumb'=> !empty($urun['video_thumb']) ? $urun['video_thumb'] : '/assets/images/video-thumb.webp',
        'caption' => !empty($urun['video_caption']) ? $urun['video_caption'] : ($lang=='en' ? 'Product video' : 'Ürün videosu'),
    ];
}
foreach($imgList as $img) {
    if ($img) {
        $mediaList[] = [
            'type' => 'img',
            'src'  => get_product_img($img),
            'thumb'=> get_product_img($img), // thumb ayrı kaydediliyorsa burayı değiştir
            'caption' => '', // ileride admin panelinden doldurulabilir
        ];
    }
}
if (!$mediaList) $mediaList[] = ['type'=>'img','src'=>'/assets/images/default-product.webp','thumb'=>'/assets/images/default-product.webp','caption'=>''];


// Fiyat ve platform detayları
$price = isset($urun['price']) && $urun['price'] > 0 ? $urun['price'] : ($urun['original_price'] ?? 0);
$oldPrice = $urun['original_price'] ?? null;
$discountPercent = ($oldPrice > $price && $price > 0) ? round(100 - ($price / $oldPrice * 100)) : 0;
$platform = strtolower($urun['platform'] ?? '');

// Favori kontrolü (giriş yaptıysa)
$is_fav = false;
if (!empty($_SESSION['user_id'])) {
    $favCheck = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?");
    $favCheck->execute([$_SESSION['user_id'], $urun['id']]);
    $is_fav = $favCheck->fetchColumn() ? true : false;
}

// Temu kampanya: ilk indirme fiyatı ve aktiflik
$isTemu = ($platform === 'temu');
$firstOrderActive = !empty($urun['first_order_active']);
$firstOrderPrice  = !empty($urun['first_order_price']) ? (float)$urun['first_order_price'] : null;

// Platform logosu
function getPlatformLogo($platform) {
    $platform = strtolower(trim($platform));
    foreach(['svg','webp','png'] as $ext) {
        $file = "assets/platform-logos/{$platform}.{$ext}";
        if (file_exists($file)) return $file;
    }
    return '';
}
$platformLogo = $platform ? getPlatformLogo($platform) : '';

// Renk varyantları (örnek veri; ileride admin panelde dinamik gelecek)
$variants = [];
if (!empty($urun['variant_json'])) {
    $variants = json_decode($urun['variant_json'], true); // admin panelinde kaydedeceksin!
}
if (!$variants) {
    $variants[] = [
        'name' => $lang=='en' ? 'Default' : 'Varsayılan',
        'price' => $price,
        'img' => $mediaList[0]['src'] ?? '/assets/images/default-product.webp'
    ];
}

// Yorumları çek (pagination)
$page = isset($_GET['comment_page']) ? max(1, intval($_GET['comment_page'])) : 1;
$comments_per_page = 10;
$offset = ($page - 1) * $comments_per_page;
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE product_id = ? AND status = 'approved'");
$totalStmt->execute([$urun['id']]);
$total_comments = $totalStmt->fetchColumn();
$commentsStmt = $pdo->prepare("SELECT c.*, u.username FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.product_id = ? AND c.status = 'approved' ORDER BY c.created_at DESC LIMIT ? OFFSET ?");
$commentsStmt->execute([$urun['id'], $comments_per_page, $offset]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
/* Responsive ürün grid ve kartı */
.product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px 12px; }
@media (max-width: 900px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .product-grid { grid-template-columns: 1fr; } }
.product-card { background: #fff; border-radius: 13px; box-shadow: 0 2px 10px 0 rgba(108,92,231,0.04); transition: box-shadow .13s, transform .13s, border-color .13s; overflow: hidden; display: flex; flex-direction: column; position: relative; padding: 0; }
.product-card:hover { box-shadow: 0 6px 18px 0 rgba(108,92,231,0.13); transform: scale(1.02); border-color: #5649c0; }
.product-card img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 13px 13px 0 0; background: #f8f8ff; display: block; transition: transform .18s; }
.product-card:hover img { transform: scale(1.045); }
.product-info { padding: 13px 13px 14px 13px; }
.product-title { font-size: 1.1em; font-weight: 600; margin-bottom: 6px; }
.product-price { color: #00b894; font-weight: 700; font-size: 1.13em; }
.product-desc { font-size: .98em; color: #636e72; margin-bottom: 8px; }
.product-actions { display: flex; gap: 8px; margin-top: 10px; }
.product-btn { background: #6c5ce7; color: #fff; border: none; border-radius: 7px; padding: 8px 18px; font-weight: 700; cursor: pointer; }
.product-btn:hover { background: #5649c0; }
.fav-btn, .share-btn { background: #f8f8ff; color: #6c5ce7; border: none; border-radius: 7px; padding: 8px; cursor: pointer; }
.fav-btn.active, .fav-btn:hover { background: #ff7675; color: #fff; }
.skeleton { background: #ececf7; min-height: 180px; border-radius: 13px; animation: skeleton 1.2s infinite linear alternate; }
@keyframes skeleton { 0% { opacity: .7; } 100% { opacity: 1; } }
/* Yorum kutusu */
.comment-box { background: #f8f8fb; border-radius: 10px; margin-bottom: 14px; padding: 13px 15px; }
.comment-header { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.comment-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.comment-user { font-weight: 700; }
.comment-date { color: #888; font-size: .95em; }
.comment-actions { margin-left: auto; display: flex; gap: 8px; }
.comment-content { font-size: 1.04em; line-height: 1.6; }
</style>
<?php
global $isMobile;
if (!isset($isMobile)) {
  if (!function_exists('isMobile')) {
    function isMobile() {
      return preg_match('/(android|iphone|ipad|ipod|opera mini|iemobile|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
    }
  }
  $isMobile = isMobile();
}
?>
<main class="af-container" style="background:#f7f8fa;min-height:calc(100vh - 220px);padding:32px 0 0 0;">
  <?php if ($isMobile): ?>
    <!-- MOBİL: Daha kompakt, üstte büyük görsel, altında başlık, fiyat, butonlar -->
    <div class="af-card" style="max-width:99vw;margin:0 auto 24px auto;background:#fff;border-radius:16px;box-shadow:0 2px 12px #ececec;padding:18px 8px 18px 8px;display:flex;flex-direction:column;align-items:center;">
      <div style="width:100%;max-width:340px;">
        <?php if (!empty($mediaList)): ?>
          <?php $media = $mediaList[0]; ?>
          <?php if($media['type']=='video'): ?>
            <video src="<?= htmlspecialchars($media['src']) ?>" controls preload="none" poster="<?= htmlspecialchars($media['thumb']) ?>" style="width:100%;height:220px;object-fit:cover;border-radius:13px;background:#111;cursor:pointer;"></video>
          <?php else: ?>
            <img src="<?= htmlspecialchars($media['src']) ?>" alt="<?= htmlspecialchars($urun['name']) ?>" loading="lazy" style="width:100%;height:220px;object-fit:cover;border-radius:13px;cursor:zoom-in;"/>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <div style="width:100%;max-width:340px;margin-top:13px;">
        <div style="font-size:1.18em;font-weight:800;color:#23244a;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"> <?= htmlspecialchars($urun['name']) ?> </div>
        <?php if(!empty($urun['short_description'])): ?>
          <div style="font-size:.99em;color:#636e72;margin-bottom:8px;"> <?= htmlspecialchars($urun['short_description']) ?> </div>
        <?php endif; ?>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:8px;">
          <span style="font-size:1.19em;font-weight:700;color:#00b894;" id="product-price"> <?= number_format($price,2,',','.') ?> ₺ </span>
          <?php if($oldPrice > $price): ?>
            <span style="text-decoration:line-through;color:#b2bec3;font-size:1.03em;" id="product-oldprice"> <?= number_format($oldPrice,2,',','.') ?> ₺ </span>
          <?php endif; ?>
        </div>
        <?php if($isTemu && $firstOrderActive && $firstOrderPrice): ?>
        <div style="margin-bottom:7px;">
          <span style="display:inline-block;background:#00b894;color:#fff;padding:5px 10px;border-radius:7px;font-size:.97em;font-weight:600;margin-right:7px;"> <?= $lang=='en' ? 'First Order Price:' : 'İlk Temu Fiyatı:' ?> <?= number_format($firstOrderPrice,2,',','.') ?> ₺ </span>
          <span style="display:inline-block;background:#f1f2fa;color:#5649C0;padding:5px 9px;border-radius:7px;font-size:.93em;"> <i class="fa fa-info-circle"></i> <?= $lang=='en' ? 'First Temu App Order Only' : 'Sadece ilk Temu siparişi' ?> </span>
        </div>
        <?php endif; ?>
        <?php if($discountPercent): ?>
          <div style="color:#00b894;font-weight:600;margin-bottom:6px;"> <?= $lang=='en' ? '%'.$discountPercent.' discount' : '%'.$discountPercent.' indirim' ?> </div>
        <?php endif; ?>
        <?php if($platform): ?>
          <div style="margin-bottom:8px;font-size:.99em;color:#5649C0;font-weight:600;"> Platform: <?= ucfirst($platform) ?> </div>
        <?php endif; ?>
        <?php if(!empty($urun['affiliate_link'])): ?>
          <a href="redirect.php?id=<?= $urun['id'] ?>&type=buy" target="_blank" rel="nofollow noopener" class="af-btn" style="margin-top:10px;display:inline-block;padding:11px 0;width:100%;background:linear-gradient(90deg,#6C5CE7 0%,#00b894 100%);color:#fff;border-radius:8px;font-weight:700;font-size:1.13em;text-decoration:none;transition:background 0.2s,box-shadow 0.2s,transform 0.13s;box-shadow:0 2px 9px 0 rgba(45,75,255,0.08);text-align:center;"> <?= $lang=='en' ? 'Go to Seller' : 'Satıcıya Git' ?> </a>
        <?php endif; ?>
        <?php if(isset($_SESSION['user_id'])): ?>
          <button id="fav-btn-detail" class="mini-btn fav-btn<?= $is_fav ? ' active' : '' ?>" data-product-id="<?= $urun['id'] ?>" aria-label="<?= $is_fav ? ($lang=='en'?'Remove from favorites':'Favorilerden çıkar') : ($lang=='en'?'Add to favorites':'Favorilere ekle') ?>" style="font-size:1.25em;padding:7px 15px;border-radius:8px;border:1.5px solid #e0e0f7;background:#fff;cursor:pointer;transition:background .13s;margin-top:8px;"> <i class="fa<?= $is_fav ? 's' : 'r' ?> fa-heart"></i> </button>
        <?php else: ?>
          <button class="mini-btn fav-btn" style="font-size:1.25em;padding:7px 15px;border-radius:8px;border:1.5px solid #e0e0f7;background:#fff;cursor:not-allowed;opacity:.7;margin-top:8px;" title="<?= $lang=='en'?'Login to add favorites':'Favori için giriş yap' ?>"><i class="far fa-heart"></i></button>
        <?php endif; ?>
        <?php if(!empty($product_desc)): ?>
          <div style="margin-top:13px;font-size:1.05em;line-height:1.6;color:#2c2c2c;"> <?= nl2br(htmlspecialchars($product_desc)) ?> </div>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="af-card" style="max-width:830px;margin:0 auto 40px auto;background:#fff;border-radius:18px;box-shadow:0 4px 24px 0 rgba(108,92,231,0.09);padding:38px 38px 34px 38px;display:flex;gap:36px;align-items:stretch;flex-wrap:wrap;">
      <!-- GELİŞMİŞ GALERİ (kaydırılabilir, thumb'lu, caption'lı, ok'lu) -->
      <div style="min-width:320px;max-width:340px;width:100%;display:flex;flex-direction:column;align-items:center;">
        <div class="product-media-main" style="position:relative;width:100%;height:340px;border-radius:13px;background:#f2f2fa;display:flex;align-items:center;justify-content:center;overflow:hidden;">
          <?php foreach($mediaList as $mi=>$media): ?>
            <?php if($media['type']=='video'): ?>
              <video src="<?= htmlspecialchars($media['src']) ?>" controls preload="none"
                poster="<?= htmlspecialchars($media['thumb']) ?>"
                style="display:<?= $mi==0?'block':'none' ?>;width:100%;height:340px;object-fit:cover;border-radius:13px;background:#111;cursor:pointer;"
                onclick="openMediaModal('video','<?= htmlspecialchars($media['src']) ?>')"></video>
              <?php if(!empty($media['caption'])): ?>
              <div class="media-caption" style="position:absolute;bottom:12px;left:13px;right:13px;background:rgba(20,20,20,0.76);color:#fff;padding:8px 13px;border-radius:8px;font-size:.98em;"><?= htmlspecialchars($media['caption']) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <img src="<?= htmlspecialchars($media['src']) ?>" alt="<?= htmlspecialchars($media['caption'] ?? $urun['name']) ?>" loading="lazy"
                style="display:<?= $mi==0?'block':'none' ?>;width:100%;height:340px;object-fit:cover;border-radius:13px;cursor:zoom-in;"
                onclick="openMediaModal('img','<?= htmlspecialchars($media['src']) ?>')"/>
              <?php if(!empty($media['caption'])): ?>
              <div class="media-caption" style="position:absolute;bottom:12px;left:13px;right:13px;background:rgba(20,20,20,0.76);color:#fff;padding:8px 13px;border-radius:8px;font-size:.98em;"><?= htmlspecialchars($media['caption']) ?></div>
              <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if($discountPercent): ?>
          <span style="position:absolute;top:13px;left:13px;background:#00b894;color:#fff;padding:6px 14px 5px 14px;border-radius:8px;font-weight:700;font-size:1.04em;">-%<?= $discountPercent ?></span>
          <?php endif; ?>
          <button type="button" class="media-thumb-arrow media-thumb-arrow-left" onclick="moveMediaThumb(-1)" style="position:absolute;left:0;top:50%;transform:translateY(-50%);background:#fff;border:none;width:32px;height:44px;border-radius:0 22px 22px 0;box-shadow:0 1px 7px #bbb;z-index:3;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="fa fa-chevron-left"></i></button>
          <button type="button" class="media-thumb-arrow media-thumb-arrow-right" onclick="moveMediaThumb(1)" style="position:absolute;right:0;top:50%;transform:translateY(-50%);background:#fff;border:none;width:32px;height:44px;border-radius:22px 0 0 22px;box-shadow:0 1px 7px #bbb;z-index:3;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="fa fa-chevron-right"></i></button>
        </div>
        <div class="product-media-thumbs-scroll" id="mediaThumbsScroll" style="margin-top:12px;overflow-x:auto;white-space:nowrap;width:100%;display:flex;gap:8px;align-items:center;justify-content:center;padding-bottom:2px;">
          <?php foreach($mediaList as $mi=>$media): ?>
            <div class="thumb-box" style="display:inline-block;">
              <?php if($media['type'] == 'img'): ?>
                <img src="<?= htmlspecialchars($media['thumb']) ?>" alt="<?= htmlspecialchars($media['caption'] ?? $urun['name']) ?>"
                  class="product-thumb"
                  style="width:44px;height:44px;border-radius:50%;object-fit:cover;box-shadow:0 1px 4px #e2e2f5;cursor:pointer;border:2.5px solid <?= $mi==0?'#6c5ce7':'#ececec' ?>;background:#fff;"
                  onclick="setMediaSlide(<?= $mi ?>)">
              <?php else: ?>
                <span class="product-thumb product-thumb-video" style="width:44px;height:44px;border-radius:50%;background:#e6e8ff;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2.5px solid <?= $mi==0?'#6c5ce7':'#ececec' ?>;" onclick="setMediaSlide(<?= $mi ?>)">
                  <i style="font-size:1.3em;color:#6c5ce7;" class="fa fa-play"></i>
                </span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- DETAY KISMI (ESKİ HALİ) -->
      <div style="flex:1;min-width:220px;display:flex;flex-direction:column;justify-content:center;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <span style="font-size:1.45rem;font-weight:800;color:#23244a;line-height:1.1;max-width:calc(100% - 60px);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"> <?= htmlspecialchars($urun['name']) ?> </span>
          <?php if(isset($_SESSION['user_id'])): ?>
            <button id="fav-btn-detail" class="mini-btn fav-btn<?= $is_fav ? ' active' : '' ?>" data-product-id="<?= $urun['id'] ?>" aria-label="<?= $is_fav ? ($lang=='en'?'Remove from favorites':'Favorilerden çıkar') : ($lang=='en'?'Add to favorites':'Favorilere ekle') ?>" style="font-size:1.35em;padding:7px 15px;border-radius:8px;border:1.5px solid #e0e0f7;background:#fff;cursor:pointer;transition:background .13s;"> <i class="fa<?= $is_fav ? 's' : 'r' ?> fa-heart"></i> </button>
          <?php else: ?>
            <button class="mini-btn fav-btn" style="font-size:1.35em;padding:7px 15px;border-radius:8px;border:1.5px solid #e0e0f7;background:#fff;cursor:not-allowed;opacity:.7;" title="<?= $lang=='en'?'Login to add favorites':'Favori için giriş yap' ?>"><i class="far fa-heart"></i></button>
          <?php endif; ?>
        </div>
        <?php if(!empty($urun['short_description'])): ?>
          <div style="font-size:.99em;color:#636e72;margin-bottom:14px;"> <?= htmlspecialchars($urun['short_description']) ?> </div>
        <?php endif; ?>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:11px;margin-bottom:13px;">
          <span style="font-size:1.38em;font-weight:700;color:#00b894;" id="product-price"> <?= number_format($price,2,',','.') ?> ₺ </span>
          <?php if($oldPrice > $price): ?>
            <span style="text-decoration:line-through;color:#b2bec3;font-size:1.03em;" id="product-oldprice"> <?= number_format($oldPrice,2,',','.') ?> ₺ </span>
          <?php endif; ?>
        </div>
        <?php if($isTemu && $firstOrderActive && $firstOrderPrice): ?>
        <div style="margin-bottom:9px;">
          <span style="display:inline-block;background:#00b894;color:#fff;padding:7px 12px;border-radius:7px;font-size:.97em;font-weight:600;margin-right:7px;"> <?= $lang=='en' ? 'First Order Price:' : 'İlk Temu Fiyatı:' ?> <?= number_format($firstOrderPrice,2,',','.') ?> ₺ </span>
          <span style="display:inline-block;background:#f1f2fa;color:#5649C0;padding:7px 11px;border-radius:7px;font-size:.93em;"> <i class="fa fa-info-circle"></i> <?= $lang=='en' ? 'First Temu App Order Only' : 'Sadece ilk Temu siparişi' ?> </span>
        </div>
        <?php endif; ?>
        <?php if($discountPercent): ?>
          <div style="color:#00b894;font-weight:600;margin-bottom:6px;"> <?= $lang=='en' ? '%'.$discountPercent.' discount' : '%'.$discountPercent.' indirim' ?> </div>
        <?php endif; ?>
        <?php if($platform): ?>
          <div style="margin-bottom:12px;font-size:.99em;color:#5649C0;font-weight:600;"> Platform: <?= ucfirst($platform) ?> </div>
        <?php endif; ?>
        <?php if(!empty($urun['affiliate_link'])): ?>
          <a href="redirect.php?id=<?= $urun['id'] ?>&type=buy" target="_blank" rel="nofollow noopener" class="af-btn" style="margin-top:10px;display:inline-block;padding:13px 40px;background:linear-gradient(90deg,#6C5CE7 0%,#00b894 100%);color:#fff;border-radius:8px;font-weight:700;font-size:1.13em;text-decoration:none;transition:background 0.2s,box-shadow 0.2s,transform 0.13s;box-shadow:0 2px 9px 0 rgba(45,75,255,0.08);"> <?= $lang=='en' ? 'Go to Seller' : 'Satıcıya Git' ?> </a>
        <?php endif; ?>
        <?php if(!empty($product_desc)): ?>
          <div style="margin-top:19px;font-size:1.09em;line-height:1.6;color:#2c2c2c;"> <?= nl2br(htmlspecialchars($product_desc)) ?> </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  <!-- Ürün görselinin hemen altına AdSense reklamı -->
  <div class="adsense-product-top" style="width:100%;text-align:center;margin:18px 0 18px 0;">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-XXXXXXXXXXXXXXX"
         data-ad-slot="YYYYYYYYYYYYY"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
  </div>
  <!-- Yorumlar Card -->
  <div class="af-card" id="comments" style="background:#f6f7fa;border-radius:17px;box-shadow:0 2px 11px 0 rgba(108,92,231,0.07);padding:24px 18px;max-width:830px;margin:0 auto 36px auto;">
    <h2 style="font-size:1.35rem;font-weight:800;color:#5649C0;margin-bottom:13px;text-align:left;letter-spacing:-1px;">Kullanıcı Yorumları</h2>
    <div style="font-size:1.05em;color:#888;margin-bottom:10px;">Bu ürünle ilgili gerçek kullanıcı deneyimlerini ve soruları aşağıda bulabilirsiniz. Yorum eklemek için giriş yapmalısınız.</div>
    <?php if(isset($_SESSION['user_id'])): ?>
      <form id="commentForm" style="margin-bottom:15px;">
        <!-- Flood/spam önleme: 1dk rate limit ve basit captcha -->
        <?php if(isset($_SESSION['last_comment_time']) && (time() - $_SESSION['last_comment_time'] < 60)): ?>
          <div style="color:#d63031;font-size:.98em;margin-bottom:8px;">Çok sık yorum ekliyorsunuz. Lütfen 1 dakika bekleyin.</div>
        <?php endif; ?>
        <div class="mb-2">
          <label for="captcha" style="font-weight:600;">Spam Önleme: 3 + 4 = ?</label>
          <input type="text" name="captcha" id="captcha" class="form-control" required style="max-width:80px;display:inline-block;">
        </div>
        <input type="hidden" name="product_id" value="<?= $urun['id'] ?>">
        <div class="mb-2">
          <label for="rating" style="font-weight:600;">Puanınız:</label>
          <select name="rating" id="rating" class="form-select" required style="max-width:120px;display:inline-block;">
            <option value="">Seçiniz</option>
            <?php for($i=5;$i>=1;$i--): ?>
              <option value="<?= $i ?>"><?= $i ?> Yıldız</option>
            <?php endfor; ?>
          </select>
        </div>
        <textarea name="comment" rows="3" class="form-control mb-2" placeholder="Yorumunuzu yazın..." required style="width:100%;min-height:60px;border-radius:8px;padding:10px 13px;"></textarea>
        <button type="submit" class="af-btn" style="background:#6C5CE7;color:#fff;padding:7px 22px;font-size:.99em;border-radius:8px;margin-top:7px;">Yorum Gönder</button>
      </form>
      <div id="commentToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position:fixed;top:30px;right:30px;z-index:9999;display:none;min-width:220px;">
        <div class="d-flex">
          <div class="toast-body" id="commentToastMsg">Yorumunuz eklendi.</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="document.getElementById('commentToast').style.display='none';"></button>
        </div>
      </div>
    <?php else: ?>
      <div style="background:#ffeaa7;color:#8a6d3b;padding:11px 15px;border-radius:6px;margin-bottom:13px;">Yorum eklemek için giriş yapmalısınız.</div>
    <?php endif; ?>
    <?php if($comments): ?>
      <!-- Yorumlarda beğeni/dislike ve filtreleme -->
      <script>
      function likeComment(id) {
        fetch('comment_like.php', {method:'POST',body:'id='+id+'&action=like',headers:{'Content-Type':'application/x-www-form-urlencoded'}})
          .then(r=>r.json()).then(res=>{if(res.success) location.reload();});
      }
      function dislikeComment(id) {
        fetch('comment_like.php', {method:'POST',body:'id='+id+'&action=dislike',headers:{'Content-Type':'application/x-www-form-urlencoded'}})
          .then(r=>r.json()).then(res=>{if(res.success) location.reload();});
      }
      function filterComments(type) {
        // type: 'all', 'liked', 'disliked', 'recent'
        window.location.href = '?id=<?= $urun['id'] ?>&comment_filter='+type+'#comments';
      }
      </script>
      <div style="margin-bottom:14px;display:flex;gap:12px;align-items:center;">
        <button onclick="filterComments('all')" class="btn btn-sm btn-outline-primary">Tümü</button>
        <button onclick="filterComments('liked')" class="btn btn-sm btn-outline-success">Beğenilenler</button>
        <button onclick="filterComments('disliked')" class="btn btn-sm btn-outline-danger">Beğenilmeyenler</button>
        <button onclick="filterComments('recent')" class="btn btn-sm btn-outline-secondary">En Yeniler</button>
      </div>
      <?php foreach($comments as $comment): ?>
        <?php
          // Kullanıcı profilinden avatar, username, admin
          $userStmt = $pdo->prepare("SELECT username, avatar, is_admin FROM users WHERE id = ?");
          $userStmt->execute([$comment['user_id']]);
          $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
          $avatar_dir = "assets/avatars/";
          $avatar_file = !empty($userData['avatar']) ? $userData['avatar'] : 'default.png';
          $avatar_path = file_exists($avatar_dir . $avatar_file) ? $avatar_dir . $avatar_file : $avatar_dir . 'default.png';
          $is_admin = !empty($userData['is_admin']);
          $username = $userData['username'] ?? ($lang=='en'?'User':'Kullanıcı');
          $comment_date = date('d.m.Y H:i', strtotime($comment['created_at']));
          $rating = isset($comment['rating']) ? intval($comment['rating']) : 0;
          $stars = '';
          for($i=1;$i<=5;$i++) {
            $stars .= '<i class="fa fa-star" style="color:'.($i<=$rating?'#FFD700':'#ececec').';font-size:1.1em;"></i>';
          }
        ?>
<div class="comment-card" style="background:#fff;border-radius:13px;box-shadow:0 1px 8px #ececec;padding:18px 16px;margin-bottom:18px;display:flex;align-items:flex-start;gap:18px;">
  <img src="<?= htmlspecialchars($avatar_path) ?>" alt="avatar" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2.5px solid #e6e8ff;box-shadow:0 1px 6px #e2e2f5;">
  <div style="flex:1;">
    <div style="display:flex;align-items:center;gap:8px;">
      <span style="font-weight:700;color:#5649C0;font-size:1.08em;">@<?= htmlspecialchars($username) ?></span>
      <?php if($is_admin): ?><span class="badge bg-danger ms-1">Admin</span><?php endif; ?>
      <span style="font-size:0.97em;color:#888;"><?= $comment_date ?></span>
    </div>
    <div style="margin-top:7px;font-size:1.07em;color:#23244a;line-height:1.6;"><?= htmlspecialchars($comment['comment']) ?></div>
    <div style="margin-top:7px;display:flex;gap:10px;align-items:center;">
      <button onclick="likeComment(<?= $comment['id'] ?>)" class="btn btn-sm btn-outline-success"><i class="fa fa-thumbs-up"></i> <?= $comment['likes'] ?? 0 ?></button>
      <button onclick="dislikeComment(<?= $comment['id'] ?>)" class="btn btn-sm btn-outline-danger"><i class="fa fa-thumbs-down"></i> <?= $comment['dislikes'] ?? 0 ?></button>
      <!-- Sosyal paylaşım butonları -->
      <a href="https://twitter.com/intent/tweet?text=<?= urlencode($urun['name'].' - '.$urun['affiliate_link']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fab fa-twitter"></i></a>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($urun['affiliate_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fab fa-facebook"></i></a>
    </div>
  </div>
  <button class="btn btn-outline-danger btn-sm report-btn" data-comment-id="<?= $comment['id'] ?>" style="margin-left:10px;align-self:flex-start;">
    <i class="fa fa-flag"></i> <?= $lang=='en' ? 'Report' : 'Raporla' ?>
  </button>
</div>
      <?php endforeach; ?>
      <?php
        $total_pages = ceil($total_comments / $comments_per_page);
        if($total_pages > 1):
      ?>
        <nav aria-label="Yorum Sayfalama" style="margin-top:18px;">
          <ul class="pagination justify-content-center">
            <?php for($i=1;$i<=$total_pages;$i++): ?>
              <li class="page-item<?= $i==$page?' active':'' ?>">
                <a class="page-link" href="?id=<?= $urun['id'] ?>&comment_page=<?= $i ?>#comments"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <div style="color:#888;font-size:1em;">Henüz yorum yok. İlk yorumu sen ekle!</div>
    <?php endif; ?>
  </div>
  <!-- Ürün açıklamasının ortasına AdSense reklamı -->
  <?php if(!empty($product_desc)): ?>
  <div class="adsense-product-desc" style="width:100%;text-align:center;margin:18px 0 18px 0;">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-XXXXXXXXXXXXXXX"
         data-ad-slot="YYYYYYYYYYYYY"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
  </div>
  <?php endif; ?>
  <!-- Yorumlar alanının üstüne AdSense reklamı -->
  <div class="adsense-comments-top" style="width:100%;text-align:center;margin:18px 0 18px 0;">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-XXXXXXXXXXXXXXX"
         data-ad-slot="YYYYYYYYYYYYY"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
  </div>
</main>
<script>
document.getElementById('commentForm').addEventListener('submit', function(e) {
  e.preventDefault();
  var form = e.target;
  var fd = new FormData(form);
  fetch('comment_add.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(function(res) {
    var toast = document.getElementById('commentToast');
    var msg = document.getElementById('commentToastMsg');
    if(res.success) {
      msg.innerText = res.message || 'Yorumunuz eklendi.';
      toast.classList.remove('text-bg-danger');
      toast.classList.add('text-bg-success');
      form.reset();
    } else {
      msg.innerText = res.message || 'Yorum eklenemedi.';
      toast.classList.remove('text-bg-success');
      toast.classList.add('text-bg-danger');
    }
    toast.style.display = 'block';
    setTimeout(function(){ toast.style.display = 'none'; }, 3000);
  })
  .catch(function(){
    var toast = document.getElementById('commentToast');
    var msg = document.getElementById('commentToastMsg');
    msg.innerText = 'Bir hata oluştu.';
    toast.classList.remove('text-bg-success');
    toast.classList.add('text-bg-danger');
    toast.style.display = 'block';
    setTimeout(function(){ toast.style.display = 'none'; }, 3000);
  });
});

document.querySelectorAll('.report-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var commentId = btn.getAttribute('data-comment-id');
    var reason = prompt('Lütfen şikayet sebebinizi yazın:');
    if(!reason || reason.length < 3) {
      alert('Şikayet sebebi en az 3 karakter olmalı.');
      return;
    }
    var fd = new FormData();
    fd.append('comment_id', commentId);
    fd.append('reason', reason);
    fetch('comment_report.php', {
      method: 'POST',
      body: fd
    })
    .then(r => r.json())
    .then(function(res){
      var toast = document.getElementById('commentToast');
      var msg = document.getElementById('commentToastMsg');
      if(res.success) {
        msg.innerText = res.message || 'Şikayetiniz iletildi.';
        toast.classList.remove('text-bg-danger');
        toast.classList.add('text-bg-success');
      } else {
        msg.innerText = res.message || 'Şikayet iletilemedi.';
        toast.classList.remove('text-bg-success');
        toast.classList.add('text-bg-danger');
      }
      toast.style.display = 'block';
      setTimeout(function(){ toast.style.display = 'none'; }, 3000);
    })
    .catch(function(){
      var toast = document.getElementById('commentToast');
      var msg = document.getElementById('commentToastMsg');
      msg.innerText = 'Bir hata oluştu.';
      toast.classList.remove('text-bg-success');
      toast.classList.add('text-bg-danger');
      toast.style.display = 'block';
      setTimeout(function(){ toast.style.display = 'none'; }, 3000);
    });
  });
});

document.querySelectorAll('.replyForm').forEach(function(form){
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var fd = new FormData(form);
    fetch('comment_reply_add.php', {
      method: 'POST',
      body: fd
    })
    .then(r => r.json())
    .then(function(res){
      if(res.success){
        location.reload();
      } else {
        alert(res.message || 'Cevap eklenemedi.');
      }
    })
    .catch(function(){
      alert('Bir hata oluştu.');
    });
  });
});
</script>
</main>
<!-- Modal Lightbox -->
<div id="productMediaModal" class="product-media-modal" onclick="closeMediaModal(event)" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(40,40,40,0.98);justify-content:center;align-items:center;">
  <span style="position:absolute;top:36px;right:48px;font-size:2em;color:#fff;cursor:pointer;" onclick="closeMediaModal(event)">&times;</span>
  <div id="productMediaModalBox"></div>
</div>
<script>
let currentMediaIndex = 0;
function setMediaSlide(idx) {
    currentMediaIndex = idx;
    const main = document.querySelector('.product-media-main');
    if (!main) return;
    let i = 0;
    Array.from(main.children).forEach(el => {
        if(el.tagName === 'IMG' || el.tagName === 'VIDEO')
            el.style.display = (i++ === idx) ? 'block' : 'none';
        // caption için block
        if(el.classList && el.classList.contains('media-caption')) {
            el.style.display = (i-1 === idx && el.previousElementSibling && (el.previousElementSibling.tagName === 'IMG' || el.previousElementSibling.tagName === 'VIDEO')) ? 'block' : 'none';
        }
    });
    // thumb border
    document.querySelectorAll('.product-thumb').forEach((thumb, i) =>
        thumb.style.borderColor = (i === idx) ? '#6c5ce7' : '#ececec'
    );
}
function moveMediaThumb(dir) {
    const total = <?= count($mediaList) ?>;
    let next = currentMediaIndex + dir;
    if(next < 0) next = total-1;
    if(next >= total) next = 0;
    setMediaSlide(next);
    // thumb kaydır
    const scrollArea = document.getElementById('mediaThumbsScroll');
    const thumbs = scrollArea.querySelectorAll('.product-thumb, .product-thumb-video');
    if(thumbs[next]) {
        thumbs[next].scrollIntoView({behavior:'smooth',inline:'center'});
    }
}
function openMediaModal(type, src) {
    var modal = document.getElementById("productMediaModal");
    var box = document.getElementById("productMediaModalBox");
    box.innerHTML = '';
    if(type === 'img') {
        var img = document.createElement('img');
        img.src = src;
        img.style.maxWidth = '90vw';
        img.style.maxHeight = '90vh';
        img.style.borderRadius = '18px';
        box.appendChild(img);
    } else {
        var vid = document.createElement('video');
        vid.src = src;
        vid.controls = true;
        vid.style.maxWidth = '90vw';
        vid.style.maxHeight = '90vh';
        vid.style.borderRadius = '18px';
        box.appendChild(vid);
    }
    modal.style.display = "flex";
}
function closeMediaModal(e) {
    if (!e || e.target === document.getElementById("productMediaModal") || e.target.tagName === 'SPAN') {
        document.getElementById("productMediaModal").style.display = "none";
        document.getElementById("productMediaModalBox").innerHTML = '';
    }
}
window.addEventListener('keydown', function(e){
  if(e.key === "Escape") closeMediaModal();
});
document.addEventListener('DOMContentLoaded', function(){
    setMediaSlide(0); // İlk slide aktif
});
// Favori butonu AJAX + toast
function showFavToast(msg) {
  let toast = document.getElementById('fav-toast');
  if(!toast) {
    toast = document.createElement('div');
    toast.id = 'fav-toast';
    toast.style.position = 'fixed';
    toast.style.top = '30px';
    toast.style.right = '30px';
    toast.style.zIndex = 9999;
    toast.style.background = '#6C5CE7';
    toast.style.color = '#fff';
    toast.style.padding = '13px 28px';
    toast.style.borderRadius = '9px';
    toast.style.fontWeight = '600';
    toast.style.boxShadow = '0 2px 12px #6c5ce766';
    document.body.appendChild(toast);
  }
  toast.innerText = msg;
  toast.style.display = 'block';
  setTimeout(()=>{ toast.style.display = 'none'; }, 1800);
}
document.addEventListener('DOMContentLoaded', function() {
  var favBtn = document.getElementById('fav-btn-detail');
  if(favBtn) {
    favBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var pid = this.getAttribute('data-product-id');
      var btn = this;
      fetch('favorites_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=toggle&product_id=' + encodeURIComponent(pid)
      })
      .then(r => r.json())
      .then(data => {
        if(data.success) {
          btn.classList.toggle('active', data.fav);
          var icon = btn.querySelector('i');
          if(icon) icon.className = data.fav ? 'fas fa-heart' : 'far fa-heart';
          btn.setAttribute('aria-label', data.fav ? (btn.lang=='en'?'Remove from favorites':'Favorilerden çıkar') : (btn.lang=='en'?'Add to favorites':'Favorilere ekle'));
          showFavToast(data.fav ? (btn.lang=='en'?'Added to favorites!':'Favorilere eklendi!') : (btn.lang=='en'?'Removed from favorites!':'Favorilerden çıkarıldı!'));
        } else if(data.error === 'login_required') {
          alert(data.message);
        }
      });
    });
  }
});
</script>

<!-- Satın alma sonrası yönlendirme ve teşekkür -->
<?php if(isset($_GET['success'])): ?>
  <div class="alert alert-success" style="margin:24px 0;">Satın alma işleminiz başarıyla tamamlandı. Teşekkür ederiz!</div>
<?php endif; ?>

<?php include "footer.php"; ?>