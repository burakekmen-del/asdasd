<?php
require_once 'config.php';
require_once 'lang_init.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { header("Location: /blog.php"); exit; }

// SEO meta başlık ve açıklama

// SEO & AdSense için dinamik meta başlık, açıklama ve görsel
if ($lang === 'en') {
    $page_title = !empty($post['meta_title_en']) ? $post['meta_title_en'] : (!empty($post['title_en']) ? $post['title_en'] . ' - Affiluxe' : $post['title'] . ' - Affiluxe');
    $page_desc  = !empty($post['meta_desc_en'])  ? $post['meta_desc_en']  : (!empty($post['excerpt_en']) ? $post['excerpt_en'] : $post['excerpt']);
} else {
    $page_title = !empty($post['meta_title']) ? $post['meta_title'] : ($post['title'] . ' - Affiluxe');
    $page_desc  = !empty($post['meta_desc'])  ? $post['meta_desc']  : $post['excerpt'];
}
$meta_img = !empty($post['image']) ? (strpos($post['image'], 'http') === 0 ? $post['image'] : 'https://affiluxe.com.tr' . $post['image']) : 'https://affiluxe.com.tr/assets/images/og-default.webp';

// Detay URL'si (paylaşım ve SEO için)
$detail_url = "https://" . $_SERVER['HTTP_HOST'] . "/blog/" . htmlspecialchars($post['slug']);

include 'header.php';
?>
<main class="flex-fill">
    <div class="container py-4" style="max-width:800px;">
        <?php if(!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="mb-3" style="width:100%;max-width:620px;display:block;border-radius:12px;">
        <?php endif; ?>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div style="color:#aaa;font-size:.96em;"><?= date('d.m.Y', strtotime($post['created_at'])) ?></div>
        <?php if(!empty($post['editor_note'])): ?>
            <div class="alert alert-info mb-4" style="font-size:1.02em;"><b>📝 <?= $lang === 'en' ? "Editor's Note:" : "Editör Notu:" ?></b> <?= htmlspecialchars($post['editor_note']) ?></div>
        <?php endif; ?>
        <div class="mt-3"><?= ($lang === 'en' && !empty($post['content_en'])) ? $post['content_en'] : $post['content'] ?></div>

        <!-- Sosyal paylaşım butonları -->
        <div class="mt-4 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span style="font-weight:600;"><?= $lang === 'en' ? "Share:" : "Paylaş:" ?></span>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title'] . ' - ' . $detail_url) ?>"
                   target="_blank" rel="nofollow noopener" title="Twitter" style="color:#1da1f2;">
                  <i class="fab fa-twitter fa-lg"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($detail_url) ?>"
                   target="_blank" rel="nofollow noopener" title="Facebook" style="color:#1877f3;">
                  <i class="fab fa-facebook fa-lg"></i>
                </a>
                <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' - ' . $detail_url) ?>"
                   target="_blank" rel="nofollow noopener" title="WhatsApp" style="color:#25d366;">
                  <i class="fab fa-whatsapp fa-lg"></i>
                </a>
                <a href="mailto:?subject=<?= rawurlencode($post['title']) ?>&body=<?= rawurlencode($post['excerpt'] . "\n" . $detail_url) ?>"
                   title="Mail" style="color:#e17055;">
                  <i class="fa fa-envelope fa-lg"></i>
                </a>
                <button type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($detail_url) ?>');this.innerText='✔️';"
                  class="btn btn-light btn-sm" style="border:1px solid #eee;" title="<?= $lang === 'en' ? 'Copy Link' : 'Bağlantıyı Kopyala' ?>">
                  <i class="fa fa-link"></i>
                </button>
            </div>
        </div>

        <!-- Editörün Seçimi / Benzer Yazılar -->
        <?php
        $editorPicks = [];
        $editorPickStmt = $pdo->prepare("
            SELECT bp.slug, bp.title, bp.title_en, bp.excerpt, bp.excerpt_en, bp.image
            FROM blog_editor_picks bep
            INNER JOIN blog_posts bp ON bp.id = bep.picked_blog_id
            WHERE bep.blog_id = ?
            GROUP BY bp.id
            ORDER BY bep.sort_order ASC, bp.created_at DESC
        ");
        $editorPickStmt->execute([$post['id']]);
        $editorPicks = $editorPickStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="container mb-5 mt-5 px-0">
            <div class="en-destek-box p-4 rounded-4 shadow-sm" style="background:#f7f6fd;">
                <h3 style="color:#6c5ce7;font-weight:800;font-size:1.35em;margin-bottom:18px;">
                    <?= $lang === 'tr' ? "Editörün Seçimi" : "Editor's Picks" ?>
                </h3>
                <div class="row g-2">
                <?php if(count($editorPicks)): ?>
                    <?php foreach($editorPicks as $pick): ?>
                        <div class="col-12 col-md-4">
                            <a href="/blog/<?= htmlspecialchars($pick['slug']) ?>" class="en-destek-link d-block p-3 rounded-3 text-dark" style="background:#fff;transition:.13s;">
                                <?php if(!empty($pick['image'])): ?>
                                    <img src="<?= htmlspecialchars($pick['image']) ?>" alt="<?= htmlspecialchars($pick['title']) ?>" style="width:100%;max-height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
                                <?php endif; ?>
                                <div class="fw-bold mb-1" style="color:#6c5ce7;">
                                    <?= ($lang === 'en' && !empty($pick['title_en'])) ? htmlspecialchars($pick['title_en']) : htmlspecialchars($pick['title']) ?>
                                </div>
                                <div style="color:#333;font-size:.97em">
                                    <?= ($lang === 'en' && !empty($pick['excerpt_en'])) ? htmlspecialchars($pick['excerpt_en']) : htmlspecialchars($pick['excerpt']) ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <?= $lang === 'tr' ? "Henüz bu yazı için editör seçimi önerimiz yok." : "No editor's picks for this post yet." ?>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="/blog.php" class="btn btn-outline-secondary"><?= $lang === 'en' ? 'Back to Blog' : 'Tüm Bloglar' ?></a>
        </div>
    </div>
</main>
<style>
.en-destek-box { border:2px solid #ece9ff;}
.en-destek-link:hover { background:#f1f0ff; color:#6c5ce7 !important; text-decoration:none;}
</style>
<?php include 'footer.php'; ?>