<?php
// community.php - Topluluk Sohbet & Yorum Alanı
include 'header.php';
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$msg = '';
// Yorum ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['content'])) {
    $user_id = intval($_SESSION['user_id']);
    $type = 'discussion';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO community (user_id, type, title, content, created_at, status) VALUES (?, ?, ?, ?, NOW(), 1)");
        $stmt->execute([$user_id, $type, $title, $content]);
        $msg = 'Paylaşımınız başarıyla eklendi!';
    } else {
        $msg = 'İçerik boş olamaz!';
    }
}

// Son 30 topluluk paylaşımını çek
$sql = "SELECT c.*, u.username FROM community c LEFT JOIN users u ON c.user_id = u.id WHERE c.status = 1 ORDER BY c.created_at DESC LIMIT 30";
$stmt = $pdo->query($sql);
$result = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
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
<div class="container community-page" style="max-width:<?= $isMobile ? '99vw' : '800px' ?>;padding:<?= $isMobile ? '12px 0' : '32px 0' ?>;">
  <h1 style="font-size:<?= $isMobile ? '1.18em' : '1.5em' ?>;font-weight:800;">Topluluk Sohbet & Paylaşımları</h1>
  <p style="font-size:<?= $isMobile ? '1em' : '1.1em' ?>;">Diğer kullanıcılarla deneyimlerinizi, sorularınızı ve önerilerinizi paylaşın. Katılmak için giriş yapmalısınız.</p>

  <?php if (!empty($msg)): ?>
    <div style="background:#dff9fb;color:#222;padding:10px 16px;border-radius:7px;margin-bottom:14px;"> <?= htmlspecialchars($msg) ?> </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['user_id'])): ?>
    <form method="post" style="background:#f7f7fa;padding:<?= $isMobile ? '12px 8px' : '18px 16px' ?>;border-radius:10px;margin-bottom:<?= $isMobile ? '14px' : '22px' ?>;">
      <input type="text" name="title" maxlength="120" placeholder="Başlık (isteğe bağlı)" style="width:100%;margin-bottom:8px;padding:7px 10px;border-radius:6px;border:1px solid #ddd;">
      <textarea name="content" rows="3" required placeholder="Paylaşmak istediğiniz düşünce, soru veya deneyim..." style="width:100%;min-height:60px;border-radius:8px;padding:10px 13px;margin-bottom:8px;"></textarea>
      <button type="submit" class="af-btn" style="background:#6C5CE7;color:#fff;padding:7px 22px;font-size:.99em;border-radius:8px;">Gönder</button>
    </form>
  <?php else: ?>
    <div style="background:#ffeaa7;color:#8a6d3b;padding:11px 15px;border-radius:6px;margin-bottom:13px;">Yorum eklemek için lütfen giriş yapın.</div>
  <?php endif; ?>

  <div class="community-reviews" style="gap:<?= $isMobile ? '14px' : '24px' ?>;">
    <?php if ($result && count($result) > 0): ?>
      <?php foreach($result as $row): ?>
        <div class="community-review-card" style="background:#fff;border-radius:8px;box-shadow:0 2px 8px #0001;padding:<?= $isMobile ? '13px' : '20px' ?>;">
          <div class="review-header" style="display:flex;align-items:center;gap:12px;font-size:<?= $isMobile ? '1em' : '1.1em' ?>;margin-bottom:8px;">
            <span class="review-user">@<?= htmlspecialchars($row['username'] ?? 'Anonim') ?></span>
            <span style="margin-left:auto;color:#aaa;font-size:.97em;"> <?= date('d M Y H:i', strtotime($row['created_at'])) ?> </span>
          </div>
          <?php if(!empty($row['title'])): ?>
            <div style="font-weight:600;font-size:1.08em;margin-bottom:4px;"> <?= htmlspecialchars($row['title']) ?> </div>
          <?php endif; ?>
          <div class="review-body" style="margin-bottom:8px;"> <?= nl2br(htmlspecialchars($row['content'])) ?> </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Henüz hiç paylaşım yok. İlk yorumu siz bırakın!</p>
    <?php endif; ?>
  </div>
</div>
<style>
.community-page { max-width: 800px; margin: 0 auto; padding: 32px 0; }
.community-reviews { display: flex; flex-direction: column; gap: 24px; }
.community-review-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 20px; }
.review-header { display: flex; align-items: center; gap: 12px; font-size: 1.1em; margin-bottom: 8px; }
.product-link { font-weight: bold; color: #0077cc; text-decoration: none; }
.product-link:hover { text-decoration: underline; }
.review-user { color: #888; font-size: 0.95em; }
.review-rating { background: #f5f5f5; border-radius: 4px; padding: 2px 8px; margin-left: auto; font-size: 0.95em; }
.review-body { margin-bottom: 8px; }
.review-date { color: #aaa; font-size: 0.9em; }
</style>
<?php include 'footer.php'; ?>
