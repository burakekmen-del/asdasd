<?php
// Force UTF-8 output for Turkish character support
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/lang_init.php";
if (session_status() === PHP_SESSION_NONE) session_start();


$cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
$categories = $pdo->query("SELECT id, $cat_name_field AS name, icon, slug_tr, slug_en FROM categories WHERE is_active=1 ORDER BY sort_order ASC, $cat_name_field ASC")->fetchAll(PDO::FETCH_ASSOC);

// Mobil tespiti için fonksiyon
function isMobile() {
    return preg_match('/(android|iphone|ipad|ipod|opera mini|iemobile|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
}
$isMobile = isMobile();

// Kullanıcı avatar ve isim güncelleme
if (isset($_SESSION['user_id'])) {
    if (empty($_SESSION['username']) || !isset($_SESSION['avatar'])) {
        $stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $_SESSION['username'] = $u['username'];
            $_SESSION['avatar']   = $u['avatar'] ?: 'default.png';
        }
    }
}
$avatar_dir = "/assets/avatars/";
$avatar_file = $_SESSION['avatar'] ?? 'default.png';
$check_avatar_path = $_SERVER['DOCUMENT_ROOT'] . $avatar_dir . $avatar_file;
if (!file_exists($check_avatar_path)) {
    $avatar_file = 'default.png';
}
$avatar_src = $avatar_dir . $avatar_file;

function sanitize_category_name($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// SEO meta başlık ve açıklama dinamik desteği
$site_title = "Affiluxe";
$meta_title = isset($page_title) ? $page_title : ($site_title . " - " . ($lang == 'en' ? 'Best Deals & Discounts' : 'En İyi Fırsatlar ve İndirimler'));
$meta_desc = isset($page_desc) ? $page_desc : ($lang == 'en'
    ? 'Discover the best alternative shopping links with exclusive deals.'
    : 'En iyi alternatif alişveriş linklerini ve özel kampanyaları keşfet.');
$meta_img = isset($meta_img) ? $meta_img : 'https://affiluxe.com.tr/assets/images/og-default.webp';
$meta_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'tr' ? 'tr' : 'en' ?>">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id=GTM-PN5LZ9WT'+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PN5LZ9WT');</script>
<!-- End Google Tag Manager -->
<title><?= htmlspecialchars($meta_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
<meta property="og:title" content="<?= htmlspecialchars($meta_title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($meta_desc) ?>">
<meta property="og:image" content="<?= htmlspecialchars($meta_img) ?>">
<meta property="og:url" content="<?= htmlspecialchars($meta_url) ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($meta_title) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($meta_desc) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($meta_img) ?>">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="impact-site-verification" value="7302a49a-bb88-4e1f-a745-2909cdb1e512">
<link rel="icon" type="image/svg" href="/assets/images/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
/* Kategori Dropdown ve Header Fix */
.aff-header-container { position: relative; z-index: 10; }
.aff-nav-dropdown { position: relative; }
.aff-dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 340px;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 8px 32px rgba(60,50,120,0.13);
  border: 1px solid #ececec;
  z-index: 1001;
  padding: 18px 18px 18px 18px;
  display: none;
}
.aff-nav-dropdown:hover .aff-dropdown-menu,
.aff-nav-dropdown:focus-within .aff-dropdown-menu {
  display: block;
}
.aff-logo-img {
  height: 48px;
  max-width: 160px;
  margin-right: 10px;
}
.aff-logo { align-items: center; font-size: 2rem; font-weight: 900; }
.aff-header-container { min-height: 72px; }
.aff-nav-list { align-items: center; }
.aff-nav-link { font-size: 1.08em; }
@media (max-width: 900px) {
  .aff-dropdown-menu { min-width: 220px; padding: 10px; }
  .aff-logo-img { height: 32px; max-width: 90px; }
  .aff-header-container { min-height: 54px; }
}
</style>
html { font-size: 16px; }
body {
  background: #f7f8fa;
  color: #232343;
  margin: 0; padding: 0; min-height: 100vh;
  font-family: 'Poppins', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  line-height: 1.6;
}
*, *::before, *::after { box-sizing: border-box; }
a { color: #6C5CE7; text-decoration: none; transition: color .18s;}
a:hover { color: #5649C0;}
img { max-width: 100%; height: auto; display: block;}
button, input, textarea, select { font-family: inherit; font-size: inherit; }
h1, h2, h3, h4, h5, h6 {
  font-family: 'Poppins', Arial, sans-serif; font-weight: 800; line-height: 1.2; margin-bottom: 0.7em;
}
.container, .af-container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
.site-wrapper { display: flex; flex-direction: column; min-height: 100vh;}
.site-wrapper > main, .site-wrapper > .container { flex: 1 0 auto; }
.aff-header { position: sticky; top: 0; left: 0; right: 0; z-index: 1000; background: #fff; box-shadow: 0 2px 8px rgba(60,50,120,0.07); border-bottom: 1px solid #ececec; }
.aff-header-container { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 0 18px; min-height: 70px; }
.aff-logo { display: flex; align-items: center; font-size: 2.1rem; font-weight: 900; color: #232343; min-width: 0; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aff-logo img, .aff-logo svg { height: 48px; max-width: 60px; margin-right: 10px; }
.aff-search-box { flex: 1 1 320px; max-width: 420px; margin: 0 18px; display: flex; align-items: center; }
.aff-search-box input[type="text"] { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #ececec; font-size: 1.08em; background: #fff; }
.aff-navigation { display: flex; align-items: center; gap: 18px; }
.aff-nav-list { display: flex; gap: 8px; align-items: center; list-style: none; margin: 0; padding: 0; }
.aff-nav-link { display: flex; align-items: center; gap: 7px; font-weight: 600; color: #434953; padding: 8px 16px; border-radius: 7px; text-decoration: none; font-size: 1.08em; transition: background .13s, color .13s; }
.aff-nav-link.active, .aff-nav-link:hover { background: #f1f0ff; color: #6C5CE7; }
.aff-header-actions { display: flex; align-items: center; gap: 10px; }
.aff-header-actions .btn { font-size: 1em; padding: 7px 18px; border-radius: 7px; }
.aff-lang-switcher-modern { display: flex; flex-direction: column; gap: 2px; margin-left: 8px; }
.aff-lang-btn-modern { border: 1.5px solid #232343; background: #fff; color: #232343; font-weight: 700; padding: 2px 8px; font-size: .98em; border-radius: 4px; cursor: pointer; margin-bottom: 2px; }
.aff-lang-btn-modern.active, .aff-lang-btn-modern:hover { background: #6C5CE7; color: #fff; }
@media (max-width: 900px) {
  .aff-header-container { flex-direction: column; align-items: stretch; gap: 8px; min-height: 0; }
  .aff-search-box { max-width: 100%; margin: 8px 0; }
  .aff-navigation { flex-wrap: wrap; gap: 8px; }
  .aff-header-actions { flex-wrap: wrap; gap: 6px; }
}
@media (max-width: 600px) {
  .aff-logo { font-size: 1.3rem; }
  .aff-logo img, .aff-logo svg { height: 32px; max-width: 38px; }
  .aff-header-container { padding: 0 6px; }
  .aff-search-box input[type="text"] { font-size: .98em; padding: 7px 8px; }
}
</style>
<script>
window.addEventListener('scroll', function() {
  var header = document.querySelector('.aff-header');
  if (!header) return;
  if (window.scrollY > 10) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
});
</script>
</head>
<body>
<div class="site-wrapper d-flex flex-column min-vh-100">
  <?php if ($isMobile): ?>
    <!-- Mobil Header ve Kategori -->
    <div class="aff-mobile-header">
      <div class="aff-mobile-header-bar d-flex align-items-center justify-content-between px-2">
        <button class="aff-mobile-menu-btn bg-transparent border-0 fs-2" onclick="document.body.classList.toggle('aff-mobile-menu-open')">
          <i class="fa fa-bars"></i>
        </button>
        <a class="aff-logo" href="/"><img src="/assets/images/affiluxe-logo-header.png" alt="Affiluxe" class="aff-logo-img mx-auto d-block"></a>
        <div class="aff-mobile-icons d-flex gap-3">
          <a href="/profile.php" class="aff-mobile-icon"><i class="fa fa-user"></i></a>
          <a href="/favorites.php" class="aff-mobile-icon"><i class="fa fa-heart"></i></a>
        </div>
      </div>
      <div class="aff-mobile-category-tabs d-flex overflow-auto bg-white border-bottom">
        <?php
        $anaKategoriler = [
          ["id"=>"kadin","name"=>$lang=='en'?"Women":"Kadın"],
          ["id"=>"erkek","name"=>$lang=='en'?"Men":"Erkek"],
          ["id"=>"anne-cocuk","name"=>$lang=='en'?"Mother & Child":"Anne & Çocuk"],
          ["id"=>"ev-yasam","name"=>$lang=='en'?"Home & Living":"Ev & Yaşam"],
          ["id"=>"elektronik","name"=>$lang=='en'?"Electronics":"Elektronik"],
          ["id"=>"supermarket","name"=>$lang=='en'?"Supermarket":"Süpermarket"],
          ["id"=>"kozmetik","name"=>$lang=='en'?"Cosmetics":"Kozmetik"],
          ["id"=>"ayakkabi-canta","name"=>$lang=='en'?"Shoes & Bags":"Ayakkabı & Çanta"],
          ["id"=>"spor-outdoor","name"=>$lang=='en'?"Sports & Outdoor":"Spor & Outdoor"],
          ["id"=>"kitap-hobi","name"=>$lang=='en'?"Books & Hobby":"Kitap & Hobi"],
          ["id"=>"populer","name"=>$lang=='en'?"Popular":"Çok Tıklananlar"],
        ];
        $category_id = $_GET['category'] ?? '';
        foreach($anaKategoriler as $cat): ?>
          <button class="aff-mobile-category-tab<?= $category_id===$cat['id'] ? ' active' : '' ?> px-3 py-2 fw-bold border-0 bg-transparent" onclick="window.location.href='/?category=<?= $cat['id'] ?>&lang=<?= $lang ?>'" style="flex:0 0 auto;<?= $category_id===$cat['id'] ? 'color:#6C5CE7;border-bottom:2.5px solid #6C5CE7;' : '' ?>">
            <?= $cat['name'] ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else: ?>
    <header class="aff-header" style="border-bottom:1.5px solid #ececec;background:#fff;">
      <div class="aff-header-bar-trendyol" style="width:100%;max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:22px 0 12px 0;">
        <a class="aff-logo d-flex align-items-center" href="/index.php" style="flex:0 0 auto;">
          <img src="/assets/images/affiluxe-logo-header.png" alt="Affiluxe" class="aff-logo-img" style="height:70px;max-width:220px;" onerror="this.onerror=null;this.src='/assets/images/logo-default.png';" />
        </a>
        <form action="/search_info.php" method="get" class="trendyol-searchbar" style="flex:1;max-width:600px;margin:0 32px;">
          <div style="display:flex;align-items:center;width:100%;background:#fff;border:2px solid #222;border-radius:24px;overflow:hidden;">
            <span style="padding:0 12px;color:#888;"><i class="fa fa-search"></i></span>
            <input type="text" name="q" placeholder="<?= $lang=='en'?'Search for anything':'Ürün, kategori veya marka ara...' ?>" style="flex:1;border:none;padding:16px 8px;font-size:20px;outline:none;background:transparent;">
            <select name="category" style="border:none;background:transparent;font-size:17px;padding:16px 16px 16px 8px;color:#222;min-width:130px;appearance:none;outline:none;">
              <option value="all"><?= $lang=='en'?'All Categories':'Tüm Kategoriler' ?></option>
              <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <span style="padding:0 12px;color:#888;"><i class="fa fa-chevron-down"></i></span>
          </div>
        </form>
        <div class="header-actions" style="display:flex;gap:18px;align-items:center;">
          <a href="/login.php?lang=<?= $lang ?>" class="header-action-btn" style="display:flex;align-items:center;gap:6px;color:#222;font-weight:500;font-size:18px;text-decoration:none;"><i class="fa fa-user"></i> <?= $lang == 'en' ? 'Login' : 'Giriş' ?></a>
          <a href="/compare.php?lang=<?= $lang ?>" class="header-action-btn" style="color:#222;font-size:22px;"><i class="fa fa-exchange"></i></a>
          <a href="/favorites.php?lang=<?= $lang ?>" class="header-action-btn" style="color:#222;font-size:22px;"><i class="fa fa-heart"></i></a>
        </div>
      </div>
      <style>
        .aff-header-bar-trendyol { width:100%;max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:22px 0 12px 0; }
        .aff-logo img { display:block; height:70px; max-width:220px; }
        .trendyol-searchbar { flex:1; max-width:600px; margin:0 32px; }
        .trendyol-searchbar input[type="text"] { border:none; padding:16px 8px; font-size:20px; outline:none; background:transparent; width:100%; }
        .trendyol-searchbar select { border:none; background:transparent; font-size:17px; padding:16px 16px 16px 8px; color:#222; min-width:130px; appearance:none; outline:none; }
        .header-actions { display:flex; gap:18px; align-items:center; }
        .header-action-btn { display:flex; align-items:center; gap:6px; color:#222; font-weight:500; font-size:18px; text-decoration:none; }
        @media (max-width: 900px) {
            .aff-header-bar-trendyol { flex-direction:column; align-items:stretch; padding:10px 0; }
            .trendyol-searchbar { margin:12px 0; max-width:100%; }
        }
        @media (max-width: 600px) {
            .aff-header-bar-trendyol { max-width:100%; padding:8px 0; }
            .aff-logo img { height:38px; max-width:100px; }
            .trendyol-searchbar { margin:8px 0; }
        }
      </style>
    </header>
  <?php endif; ?>
  <!-- SSS Modal (Header’dan her sayfada açılır) -->
  <div id="faqModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(30,28,50,.22);">
    <div style="background:#fff;max-width:540px;margin:50px auto;border-radius:16px;box-shadow:0 8px 32px -10px #6c5ce7;padding:0 0 28px 0;position:relative;">
      <div style="border-bottom:1px solid #f1f0ff;padding:14px 24px 10px 24px;display:flex;align-items:center;justify-content:space-between;">
        <b style="font-size:1.08em;"><?= $lang == 'en' ? 'Frequently Asked Questions' : 'Sıkça Sorulan Sorular' ?></b>
        <a href="#" onclick="document.getElementById('faqModal').style.display='none';return false;" style="font-size:1.5em;color:#888;">&times;</a>
      </div>
      <div style="padding:18px 22px;max-height:66vh;overflow-y:auto;">
        <?php include __DIR__ . '/footer_faq.php'; ?>
      </div>
    </div>
  </div>
  <script>
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){document.getElementById('faqModal').style.display='none';}
  });
  </script>