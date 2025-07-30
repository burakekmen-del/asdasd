<?php
// Session and config
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? 'buy';

// Get product info
$pdo = $GLOBALS['pdo'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$urun) {
    header('Location: /');
    exit;
}


$platform = strtolower($urun['platform'] ?? '');
$platformName = $platform ? ucfirst($platform) : ($lang == 'en' ? 'Platform' : 'Platform');
$platformLogo = '';
foreach(['svg','webp','png'] as $ext) {
    $path = "assets/platform-logos/{$platform}.{$ext}";
    if ($platform && file_exists($path)) { $platformLogo = $path; break; }
}

$affiliateLink = $urun['affiliate_link'] ?? '';
if (!$affiliateLink) {
    header('Location: /product.php?id=' . $id);
    exit;
}

$siteName = $platformName;
$siteLogo = $platformLogo;
$siteColor = '#6C5CE7';
if ($platform === 'amazon') $siteColor = '#FF9900';
elseif ($platform === 'temu') $siteColor = '#FF6600';
elseif ($platform === 'hepsiburada') $siteColor = '#FF6F00';
elseif ($platform === 'trendyol') $siteColor = '#F27A1A';
elseif ($platform === 'n11') $siteColor = '#E60012';
elseif ($platform === 'ebay') $siteColor = '#0064D2';

$lang = $_SESSION['lang'] ?? 'tr';
$msg = $lang == 'en'
    ? "You are being redirected to <b>$siteName</b>."
    : "<b>$siteName</b> sitesine yönlendiriliyorsunuz.";
$desc = $lang == 'en'
    ? "Affiluxe does not sell products. Please check price, seller, and product details before purchasing. If you are not redirected automatically, <a href='$affiliateLink'>click here</a>."
    : "Affiluxe ürün satışı yapmaz. Satın almadan önce fiyat, satıcı ve ürün özelliklerini kontrol etmeyi unutmayın. Otomatik yönlendirilmezseniz <a href='$affiliateLink'>buraya tıklayın</a>.";

// Auto redirect after 2 seconds
echo "<!DOCTYPE html><html lang='$lang'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Yönlendiriliyor...</title><style>body{font-family:Inter,Arial,sans-serif;background:#fff;margin:0;padding:0;text-align:center;} .redirect-wrap{max-width:480px;margin:80px auto 0 auto;padding:32px 18px;border-radius:18px;background:#f8f8ff;box-shadow:0 4px 18px 0 rgba(108,92,231,0.08);} .redirect-logo{height:44px;margin-bottom:18px;} .redirect-title{font-size:1.35em;font-weight:800;color:$siteColor;margin-bottom:10px;} .redirect-msg{font-size:1.08em;color:#222;margin-bottom:18px;} .redirect-desc{font-size:1em;color:#555;} .redirect-bar{height:6px;background:$siteColor;width:100%;margin-bottom:32px;} a{color:$siteColor;text-decoration:underline;} </style><script>setTimeout(function(){window.location.href='$affiliateLink';},2000);</script></head><body><div class='redirect-bar'></div><div class='redirect-wrap'>";
if ($siteLogo) {
    echo "<img src='/$siteLogo' alt='$siteName' class='redirect-logo'>";
} else {
    // Default icon (SVG)
    echo "<svg class='redirect-logo' width='44' height='44' viewBox='0 0 44 44' fill='none' xmlns='http://www.w3.org/2000/svg'><rect width='44' height='44' rx='12' fill='#e0e0e0'/><text x='50%' y='54%' text-anchor='middle' alignment-baseline='middle' font-size='16' font-family='Arial' fill='#6C5CE7'>P</text></svg>";
}
echo "<div class='redirect-title'>$msg</div><div class='redirect-msg'>$desc</div></div></body></html>";
?>
<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Ürün ID belirtilmedi.');
}
$product_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT affiliate_link FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product || empty($product['affiliate_link'])) {
    die("Ürün veya link bulunamadı.");
}

$affiliate_link = $product['affiliate_link'];

if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$click_type = 'buy';
if (isset($_GET['type']) && in_array($_GET['type'], ['affiliate','buy'])) {
    $click_type = $_GET['type'];
}

$log = $pdo->prepare("INSERT INTO product_click_logs (product_id, user_id, click_type, click_time) VALUES (?, ?, ?, NOW())");
$log->execute([$product_id, $user_id, $click_type]);

header("Location: " . $affiliate_link);
exit;
?>