<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'csrf.php';
require_once 'lang_init.php';

$pdo = $GLOBALS['pdo'];
$cat_name_field = $lang == 'tr' ? 'name_tr' : 'name_en';
$desc_field = $lang == 'tr' ? 'short_description_tr' : 'short_description_en';

$category_id = isset($_GET['category']) ? $_GET['category'] : '';
$user_id = $_SESSION['user_id'] ?? 0;

$today = date('Y-m-d');
$bannerStmt = $pdo->prepare("SELECT * FROM campaign_banners WHERE active=1 AND (expire_date IS NULL OR expire_date >= ?) ORDER BY id DESC LIMIT 5");
$bannerStmt->execute([$today]);
$banners = $bannerStmt->fetchAll(PDO::FETCH_ASSOC);

$amazonProducts = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products WHERE campaign_active=1 AND platform='amazon' ORDER BY id DESC LIMIT 20");
$amazonProducts->execute();
$amazonProducts = $amazonProducts->fetchAll(PDO::FETCH_ASSOC);

$temuProducts = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products WHERE campaign_active=1 AND platform='temu' ORDER BY id DESC LIMIT 20");
$temuProducts->execute();
$temuProducts = $temuProducts->fetchAll(PDO::FETCH_ASSOC);

$popularProducts = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products WHERE is_active=1 ORDER BY click_count DESC LIMIT 20");
$popularProducts->execute();
$popularProducts = $popularProducts->fetchAll(PDO::FETCH_ASSOC);

$where = ["campaign_active=1"];
$params = [];
if (!empty($category_id) && $category_id !== 'populer') {
    $where[] = 'category_id = ?';
    $params[] = intval($category_id);
}
if (isset($_GET['q']) && $_GET['q'] !== '') {
    $where[] = "($cat_name_field LIKE ? OR $desc_field LIKE ?)";
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}
$whereSql = 'WHERE '.implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT *, $cat_name_field AS name, $desc_field AS short_description FROM products $whereSql GROUP BY id ORDER BY id DESC LIMIT 30");
$stmt->execute($params);
$opportunityProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
$totalActiveCampaigns = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE campaign_active=1")->fetchColumn();

$heroFeaturedStmt = $pdo->prepare(
    "SELECT p.*, $cat_name_field AS name, $desc_field AS short_description
     FROM featured_hero_products f
     INNER JOIN products p ON f.product_id = p.id
     WHERE p.is_active=1
     ORDER BY f.display_order ASC, f.id ASC
     LIMIT 5"
);
$heroFeaturedStmt->execute();
$heroFeaturedProducts = $heroFeaturedStmt->fetchAll(PDO::FETCH_ASSOC);

// SEO & AdSense için dinamik meta başlık, açıklama ve görsel
$page_title = $lang == 'tr'
    ? "Türkiye'nin En Büyük Fırsat ve Kampanya Platformu - Affiluxe"
    : "Turkey's Biggest Deals & Campaigns Platform - Affiluxe";
$page_desc = $lang == 'tr'
    ? "Amazon, Temu ve daha fazlasından binlerce güncel kampanya, indirim ve fırsat. En iyi ürünler, bloglar ve alışveriş rehberleri Affiluxe'ta!"
    : "Thousands of daily deals, discounts and shopping guides from Amazon, Temu and more. Best products, blogs and shopping tips on Affiluxe!";
$meta_img = 'https://affiluxe.com.tr/assets/images/og-default.webp';
// Eğer öne çıkan bir ürün veya blog varsa, onun görselini kullanabilirsin
if (!empty($heroFeaturedProducts) && !empty($heroFeaturedProducts[0]['image'])) {
    $meta_img = (strpos($heroFeaturedProducts[0]['image'], 'http') === 0)
        ? $heroFeaturedProducts[0]['image']
        : 'https://affiluxe.com.tr' . $heroFeaturedProducts[0]['image'];
}
include "header.php";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<style>
/* HERO YÜZEN ÜRÜNLER GRID */
.hero-featured-products {
    display: flex;
    flex-direction: column;
    gap: 22px;
    margin-left: 60px;
    z-index: 3;
}
@media (max-width: 1000px) {
    .home-hero-banner-inner { flex-direction: column; align-items: flex-start; }
    .hero-featured-products { flex-direction: row; margin-left: 0; margin-top: 15px; gap: 11px; width: 100%; justify-content: center; }
}
@media (max-width: 700px) {
    .hero-featured-products { gap: 6px; margin-top: 10px; }
}
.hero-featured-product-card {
    background: rgba(255,255,255,0.88);
    border-radius: 16px;
    box-shadow: 0 7px 24px #6c5ce722, 0 2px 10px #00b89415;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: transform .19s, box-shadow .17s;
    position: relative;
    backdrop-filter: blur(9px);
}
.hero-featured-product-card:hover {
    transform: scale(1.06) rotate(-2deg);
    box-shadow: 0 12px 36px #6c5ce733;
}
.hero-featured-product-card img {
    width: 90px;
    height: 90px;
    object-fit: contain;
    border-radius: 13px;
    filter: drop-shadow(0 3px 11px #d3d3ff66);
}
.hero-featured-product-card.rotate-left { transform: rotate(-8deg);}
.hero-featured-product-card.rotate-right { transform: rotate(7deg);}

/* HERO BANNER MODERN */
.home-hero-banner-modern {
    background: radial-gradient(circle at 40% 30%, #e7e1fc 0%, #f0f9ff 100%) !important;
    border-radius: 0 0 32px 32px;
    box-shadow: 0 6px 18px #6c5ce722;
    padding: 50px 0 40px 0;
    position: relative;
    overflow: hidden;
    transition: background 2s;
}
@keyframes heroBgAnim {
    0%   { background: radial-gradient(circle at 40% 30%, #e7e1fc 0%, #f0f9ff 100%) }
    50%  { background: radial-gradient(circle at 60% 63%, #f1e3ff 0%, #d7eaff 100%) }
    100% { background: radial-gradient(circle at 40% 30%, #e7e1fc 0%, #f0f9ff 100%) }
}
.home-hero-banner-modern { animation: heroBgAnim 12s infinite; }
.home-hero-banner-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    position: relative;
    z-index: 2;
    max-width: 1240px;
    margin: 0 auto;
    padding: 38px 18px 30px 18px;
    min-height: 320px;
}
.home-hero-banner-content {
    text-align: left;
    color: #232343;
    max-width: 620px;
    z-index: 2;
}
@media(max-width: 700px){
    .home-hero-banner-modern { padding: 26px 0 18px 0; border-radius: 0 0 18px 18px;}
    .home-hero-banner-inner { flex-direction: column; align-items: flex-start; padding: 17px 4px 13px 4px;}
    .home-hero-banner-content { max-width: 98vw; margin-bottom: 18px;}
}
.hero-animated-bg .hero-ellipse {
    position: absolute;
    border-radius: 50%;
    filter: blur(16px);
    opacity: .13;
}
.hero-ellipse-1 { width: 220px; height: 220px; top: 18px; left: -80px; background: #6c5ce7; }
.hero-ellipse-2 { width: 160px; height: 160px; top: 110px; right: -48px; background: #00b894; }
.hero-ellipse-3 { width: 110px; height: 110px; bottom: -40px; left: 33%; background: #f27a1a; }

/* HERO İSTATİSTİK KARTLARI */
.hero-stats-row { display: flex; gap: 14px; margin-top: 13px;}
.hero-stat-card {
    display: flex; flex-direction: column; align-items: center;
    background: #fff; border-radius: 13px; padding: 10px 23px;
    box-shadow: 0 2px 12px #6c5ce71a; min-width: 110px;
}
.hero-stat-val {
    font-size: 1.23em; font-weight: 800; color: #6c5ce7; margin-bottom: 3px;
}
.hero-stat-label { font-size: 1em; color: #5649c0; font-weight:600;}
.hero-badge-row { margin-top: 16px;}
.hero-badge-safe {
    background: linear-gradient(90deg,#00b894 0%,#6c5ce7 100%);
    color: #fff;
    border-radius: 14px;
    padding: 7px 18px 7px 13px;
    font-weight: 700;
    font-size: .99em;
    box-shadow: 0 3px 12px #00b89422;
}

.why-affiluxe-box {
    background: #F1F0FF;
    border-radius: 18px;
    padding: 24px 18px;
    box-shadow: 0 4px 18px 0 rgba(108,92,231,0.06);
    text-align: center;
}
#liveSearchResults { margin-top: 32px; }
</style>

<div class="container" style="max-width:700px;margin:0 auto;">
    <div class="aff-search-form mb-4 mt-4" role="search">
        <input type="text" id="productSearchInput" class="form-control mb-2" placeholder="<?= $lang == 'en' ? 'Search products...' : 'Ürün adıyla ara...' ?>" aria-label="<?= $lang == 'en' ? 'Search products...' : 'Ürün adıyla ara...' ?>">
    </div>
</div>
<div id="liveSearchResults" style="display:none"></div>

<!-- ANA SAYFA BLOKLARI: header, hero, güven rozeti, why affiluxe, platform slider, kategori stripi, fırsatlar, blog, sosyal kanıt, footer -->
<div id="mainContent">

  <!-- HERO ALANI: Video + Başlık + Açıklama + Sayaçlar + Güven Rozeti -->
  <section class="home-hero-banner home-hero-banner-modern position-relative overflow-hidden">
    <video autoplay loop muted playsinline class="position-absolute w-100 h-100" style="object-fit:cover;z-index:0;pointer-events:none;opacity:0.28;top:0;left:0;">
      <source src="assets/images/hero_video.mp4" type="video/mp4">
    </video>
    <div class="home-hero-banner-inner position-relative" style="z-index:2;">
      <div class="home-hero-banner-content">
        <h1 class="home-hero-banner-title display-5 fw-bold mb-3">
          <i class="fa fa-star text-warning me-2"></i>
          <?= $lang == 'tr' ? "Türkiye'nin En Büyük <span class='text-warning'>Fırsat</span> Platformu" : "Turkey's Biggest <span class='text-warning'>Deals</span> Platform" ?>
        </h1>
        <div class="home-hero-banner-desc mb-3 lead">
          <?= $lang == 'tr' ? "Binlerce güncel kampanya, <b>Amazon</b>, <b>Temu</b> ve daha fazlası tek adreste!<br><span class='text-warning' style='font-size:1.08em;'>Avantajlı alışverişin adresi, her gün yeni fırsatlar!</span>" : "Thousands of daily deals from <b>Amazon</b>, <b>Temu</b> and more!<br><span class='text-warning' style='font-size:1.08em;'>Your address for the best prices, new deals daily!</span>" ?>
        </div>
        <div class="hero-stats-row">
          <div class="hero-stat-card">
            <div class="hero-stat-val"><i class="fa fa-box"></i> <span class="counter" data-count="<?= $totalProducts ?>"><?= $totalProducts ?></span></div>
            <div class="hero-stat-label"><?= $lang == 'tr' ? "Toplam Ürün" : "Products" ?></div>
          </div>
          <div class="hero-stat-card">
            <div class="hero-stat-val"><i class="fa fa-star"></i> <span class="counter" data-count="<?= $totalActiveCampaigns ?>"><?= $totalActiveCampaigns ?></span></div>
            <div class="hero-stat-label"><?= $lang == 'tr' ? "Fırsat/Kampanya" : "Deals" ?></div>
          </div>
        </div>
        <div class="hero-badge-row mt-3">
          <span class="hero-badge hero-badge-safe"><i class="fa fa-lock"></i> <?= $lang == 'tr' ? "Güvenli Alışveriş" : "Safe Shopping" ?></span>
        </div>
      </div>
      <?php if (!empty($heroFeaturedProducts)): ?>
      <div class="hero-featured-products">
        <?php foreach ($heroFeaturedProducts as $i => $urun): ?>
          <div class="hero-featured-product-card <?= $i%2==0 ? 'rotate-left':'rotate-right' ?>">
            <img src="<?= htmlspecialchars($urun['image'] ?? '/assets/images/no-image.png') ?>" alt="<?= htmlspecialchars($urun['name']) ?>" loading="lazy">
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="hero-animated-bg" style="z-index:2;position:relative;">
      <span class="hero-ellipse hero-ellipse-1"></span>
      <span class="hero-ellipse hero-ellipse-2"></span>
      <span class="hero-ellipse hero-ellipse-3"></span>
    </div>
  </section>

  <!-- WHY AFFILUXE? -->
  <div class="why-affiluxe-box mb-4">
    <h2 style="font-size:1.22rem;font-weight:800;color:#6C5CE7;margin-bottom:10px;">Why Affiluxe?</h2>
    <ul style="font-size:1.03rem;color:#5649C0;margin:0;list-style:disc inside;">
      <li><?= $lang == 'tr' ? "Türkiye'nin affiliate fırsat merkezi!" : "Turkey's affiliate deals hub!" ?></li>
      <li><?= $lang == 'tr' ? "Kullanıcıya özel teklif, hızlı ödeme, güvenli link, tarafsız karşılaştırma" : "Personalized offers, fast payout, secure links, unbiased comparison" ?></li>
    </ul>
  </div>

  <!-- AFFILIATE PLATFORM SLIDER -->
  <div class="platform-slider mb-4">
    <div class="swiper platform-swiper">
      <div class="swiper-wrapper">
        <?php
        $platforms = [
          ['amazon', 'Amazon', 'Dünyanın en büyük online alışveriş platformu', '/assets/platform-logos/amazon.svg'],
          ['temu', 'Temu', 'Uygun fiyatlı yeni nesil alışveriş', '/assets/platform-logos/temu.svg'],
          ['trendyol', 'Trendyol', 'Türkiye’nin en popüler pazaryeri', '/assets/platform-logos/trendyol.svg'],
          ['aliexpress', 'AliExpress', 'Global fırsatlar, uygun fiyatlar', '/assets/platform-logos/aliexpress.svg'],
          ['hepsiburada', 'Hepsiburada', 'Türkiye’nin lider e-ticaret sitesi', '/assets/platform-logos/hepsiburada.svg'],
        ];
        foreach($platforms as $p): ?>
          <div class="swiper-slide" style="text-align:center;">
            <img src="<?= $p[3] ?>" alt="<?= $p[1] ?>" style="height:38px;margin-bottom:7px;">
            <div style="font-weight:700;"><?= $p[1] ?></div>
            <div style="font-size:.97em;color:#888;"><?= $p[2] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>

  <!-- KATEGORİ NAVİGASYON STRİPİ -->
  <div class="category-strip mb-4" style="overflow-x:auto;white-space:nowrap;">
    <?php
    $categories = [
      ['Moda', 'fa-tshirt'],
      ['Elektronik', 'fa-tv'],
      ['Ev', 'fa-couch'],
      ['Oyun', 'fa-gamepad'],
      ['Popüler', 'fa-fire'],
    ];
    foreach($categories as $cat): ?>
      <span class="cat-strip-item" style="display:inline-block;padding:8px 18px;margin-right:7px;border-radius:18px;background:#f1f0ff;color:#5649c0;font-weight:600;font-size:1.07em;"><i class="fa <?= $cat[1] ?>"></i> <?= $cat[0] ?></span>
    <?php endforeach; ?>
  </div>

  <!-- FIRSATLAR BLOKLARI: Temu, Amazon, Trendyol (her ürün sadece bir blokta) -->
  <?php
  $usedProductIds = [];
  $dealBlocks = [
    ['Temu', $temuProducts, '/assets/platform-logos/temu.svg'],
    ['Amazon', $amazonProducts, '/assets/platform-logos/amazon.svg'],
    // ['Trendyol', $trendyolProducts, '/assets/platform-logos/trendyol.svg'],
  ];
  foreach($dealBlocks as $block) {
    $blockName = $block[0];
    $blockProducts = $block[1];
    $blockLogo = $block[2];
    $blockRendered = false;
    ob_start();
    echo '<div class="swiper '.$blockName.'-swiper"><div class="swiper-wrapper">';
    foreach($blockProducts as $urun) {
      if(in_array($urun['id'], $usedProductIds)) continue;
      $usedProductIds[] = $urun['id'];
      $blockRendered = true;
      echo '<div class="swiper-slide">';
      include '_product_card_modern.php';
      echo '</div>';
    }
    echo '</div><div class="swiper-pagination"></div></div>';
    $blockHtml = ob_get_clean();
    if ($blockRendered) {
      echo '<h2 class="section-title" style="display:flex;align-items:center;gap:8px;"><img src="'.$blockLogo.'" alt="'.$blockName.'" style="height:28px;"> '.$blockName.' Fırsatları</h2>';
      echo $blockHtml;
    }
  }
  ?>

  <!-- BLOG & KILAVUZLAR BLOĞU -->
  <div class="blog-section mb-5">
    <h2 class="section-title"><i class="fa fa-pen"></i> <?= $lang == 'tr' ? "Affiliate Blog & Kılavuzlar" : "Affiliate Blog & Guides" ?></h2>
    <?php
    $stmt = $pdo->prepare("SELECT slug, title, title_en, excerpt, excerpt_en, image, editor_note, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 4");
    $stmt->execute();
    $homeBlogPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="row">
      <?php foreach($homeBlogPosts as $post): ?>
        <div class="col-12 col-md-6 mb-3">
          <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-card shadow-sm p-3 rounded d-block h-100">
            <?php if(!empty($post['image'])): ?>
              <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width:100%;max-height:180px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
            <?php endif; ?>
            <div class="blog-title fw-bold mb-1">
              <?= ($lang === 'en' && !empty($post['title_en'])) ? htmlspecialchars($post['title_en']) : htmlspecialchars($post['title']) ?>
            </div>
            <div class="blog-excerpt text-muted">
              <?= ($lang === 'en' && !empty($post['excerpt_en'])) ? htmlspecialchars($post['excerpt_en']) : htmlspecialchars($post['excerpt']) ?>
            </div>
            <?php if(!empty($post['editor_note'])): ?>
              <div class="alert alert-info p-2 mt-2 mb-2" style="font-size:.96em;"><b>📝 <?= $lang === 'en' ? "Editor's Note:" : "Editör Notu:" ?></b> <?= htmlspecialchars($post['editor_note']) ?></div>
            <?php endif; ?>
            <div style="font-size:.93em;color:#888;"><?= date('d M Y', strtotime($post['created_at'])) ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-3">
      <a href="/blog" class="btn btn-info"><?= $lang == 'tr' ? "Tüm Yazıları Gör" : "See All Blog Posts" ?></a>
    </div>
    <div class="mt-4" style="font-size:.93em;color:#888;">
      <?= $lang == 'tr' ? "En iyi ürünler, karşılaştırmalar, rehberler, trendler ve hediye fikirleriyle güncel blog içeriklerimizi keşfet! Her hafta yeni SEO odaklı yazılar eklenmektedir." : "Discover up-to-date blog content with best product lists, comparisons, guides, trends, and gift ideas! New SEO-focused articles added weekly." ?>
    </div>
  </div>

  <!-- SOSYAL KANIT & GÜVEN ALANI -->
  <div class="social-proof-box mb-5" style="background:#f1f0ff;border-radius:18px;padding:24px 18px;text-align:center;box-shadow:0 4px 18px 0 rgba(108,92,231,0.06);">
    <div style="font-size:1.18em;font-weight:700;color:#6c5ce7;">1000+ kullanıcı, gerçek yorumlar, Trustpilot ve medya logoları</div>
    <div style="margin:10px 0 0 0;font-size:1.05em;color:#5649c0;">Spam içerik yok • Fırsatlar her gün güncellenir • Bağımsız editörler</div>
    <div style="margin-top:12px;">
      <img src="/assets/images/trustpilot.svg" alt="Trustpilot" style="height:32px;margin-right:12px;">
      <img src="/assets/images/forbes.svg" alt="Forbes" style="height:32px;margin-right:12px;">
      <img src="/assets/images/ntv.svg" alt="NTV" style="height:32px;">
    </div>
  </div>

  <!-- FOOTER -->
  <!-- ...footer zaten include ediliyor... -->


<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('productSearchInput');
    const mainContent = document.getElementById('mainContent');
    const liveSearchResults = document.getElementById('liveSearchResults');
    let searchTimeout = null;
    searchInput.addEventListener('input', function() {
        let val = this.value.trim();
        if (searchTimeout) clearTimeout(searchTimeout);
        if (val.length > 0) {
            mainContent.style.display = 'none';
            liveSearchResults.style.display = '';
            searchTimeout = setTimeout(function() {
                fetch('ajax/live_search.php?q=' + encodeURIComponent(val) + '&lang=<?= $lang ?>')
                .then(r => r.text())
                .then(html => { liveSearchResults.innerHTML = html; });
            }, 260);
        } else {
            liveSearchResults.style.display = 'none';
            mainContent.style.display = '';
        }
    });
});

// Sayaçlar animasyonlu güncelleme
document.addEventListener("DOMContentLoaded",function(){
  fetch('/ajax/home_stats.php').then(r=>r.json()).then(data=>{
    if(data && data.products >= 0 && data.deals >= 0){
      animateCounter(document.querySelector('.hero-stat-val .counter[data-count]'), data.products);
      animateCounter(document.querySelectorAll('.hero-stat-val .counter[data-count]')[1], data.deals);
    }
  });
  function animateCounter(el, to){
    if(!el) return;
    let start = 0, end = parseInt(to,10), dur=800, inc = Math.max(1,Math.ceil(end/50)), now=start;
    let intv = setInterval(()=>{
      now += inc;
      el.textContent = now > end ? end : now;
      if(now >= end) clearInterval(intv);
    }, 18);
  }
});

// Swiper sliderlar
if(document.querySelector('.popular-swiper')) {
    new Swiper('.popular-swiper', {
        loop: false,
        slidesPerView: 2,
        spaceBetween: 8,
        grabCursor: true,
        pagination: { el: '.popular-swiper .swiper-pagination', clickable: true },
        breakpoints: {
            0: { slidesPerView: 1.2 },
            550: { slidesPerView: 2 },
            900: { slidesPerView: 3 },
            1200: { slidesPerView: 4 },
            1450: { slidesPerView: 5 }
        }
    });
}
if(document.querySelector('.amazon-swiper')) {
    new Swiper('.amazon-swiper', {
        loop: false,
        slidesPerView: 2,
        spaceBetween: 8,
        grabCursor: true,
        pagination: { el: '.amazon-swiper .swiper-pagination', clickable: true },
        breakpoints: {
            0: { slidesPerView: 1.2 },
            550: { slidesPerView: 2 },
            900: { slidesPerView: 3 },
            1200: { slidesPerView: 4 },
            1450: { slidesPerView: 5 }
        }
    });
}
if(document.querySelector('.temu-swiper')) {
    new Swiper('.temu-swiper', {
        loop: false,
        slidesPerView: 2,
        spaceBetween: 8,
        grabCursor: true,
        pagination: { el: '.temu-swiper .swiper-pagination', clickable: true },
        breakpoints: {
            0: { slidesPerView: 1.2 },
            550: { slidesPerView: 2 },
            900: { slidesPerView: 3 },
            1200: { slidesPerView: 4 },
            1450: { slidesPerView: 5 }
        }
    });
}

</script>
<?php include "footer.php"; ?>