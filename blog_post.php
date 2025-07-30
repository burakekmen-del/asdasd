<?php
define('BLOG_PAGE', true);
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'lang_init.php';

$pdo = $GLOBALS['pdo'];
$pdo->exec("SET NAMES 'utf8mb4'");
$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug=? LIMIT 1");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

include "header.php";
?>

<div class="container py-4">
    <?php if (!$post): ?>
        <div class="alert alert-warning"><?= $lang == 'tr' ? 'Blog yazısı bulunamadı.' : 'Blog post not found.' ?></div>
    <?php else: ?>
        <article itemscope itemtype="https://schema.org/Article" class="blog-detail-article" style="max-width:780px;margin:auto;padding:28px 0;">
            <?php if (!empty($post['image'])): ?>
                <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width:100%;max-width:650px;display:block;border-radius:12px;margin-bottom:18px;">
            <?php endif; ?>

            <h1 class="mb-2" itemprop="headline" style="font-size:2em;color:#6c5ce7;font-weight:900;">
                <?= ($lang == 'en' && !empty($post['title_en'])) ? htmlspecialchars($post['title_en']) : htmlspecialchars($post['title']) ?>
            </h1>
            <div class="text-muted mb-3" style="font-size:1.05em;">
                <time itemprop="datePublished" datetime="<?= htmlspecialchars($post['created_at']) ?>">
                    <?= date('d M Y', strtotime($post['created_at'])) ?>
                </time>
            </div>

            <?php if (!empty($post['editor_note'])): ?>
                <div class="alert alert-info mb-3" style="font-size:1.02em;">
                    <b>📝 <?= $lang == 'en' ? "Editor's Note:" : "Editör Notu:" ?></b> <?= htmlspecialchars($post['editor_note']) ?>
                </div>
            <?php endif; ?>

            <?php
            $showExcerpt = ($lang == 'en' && !empty($post['excerpt_en'])) ? $post['excerpt_en'] : $post['excerpt'];
            if (!empty($showExcerpt)): ?>
            <div class="lead" style="font-size:1.14em;color:#444;margin-bottom:18px;">
                <?= htmlspecialchars($showExcerpt) ?>
            </div>
            <?php endif; ?>

            <div itemprop="articleBody" style="font-size:1.14em;line-height:1.8;color:#232343;">
                <?= ($lang == 'en' && !empty($post['content_en'])) ? $post['content_en'] : $post['content'] ?>
            </div>

            <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "Article",
              "headline": "<?= addslashes(($lang == 'en' && !empty($post['title_en'])) ? $post['title_en'] : $post['title']) ?>",
              "datePublished": "<?= $post['created_at'] ?>",
              "description": "<?= addslashes(($lang == 'en' && !empty($post['excerpt_en'])) ? $post['excerpt_en'] : $post['excerpt']) ?>",
              "image": "<?= addslashes($post['image']) ?>",
              "author": {
                "@type": "Organization",
                "name": "Affiluxe"
              }
            }
            </script>
        </article>
    <?php endif; ?>
</div>

<?php
// --- O Bloga Özel Editör Seçimleri (blog_editor_picks tablosu ile) ---
$editorPicks = [];
if ($post) {
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
}
?>
<div class="container mb-5 mt-5">
    <div class="en-destek-box p-4 rounded-4 shadow-sm" style="background:#f7f6fd;">
        <h3 style="color:#6c5ce7;font-weight:800;font-size:1.35em;margin-bottom:18px;">
            <?= $lang == 'tr' ? "Editörün Seçimi" : "Editor's Picks" ?>
        </h3>
        <div class="row g-2">
        <?php if(count($editorPicks)): ?>
            <?php foreach($editorPicks as $pick): ?>
                <div class="col-12 col-md-4">
                    <a href="/blog/<?= htmlspecialchars($pick['slug']) ?>" class="en-destek-link d-block p-3 rounded-3 text-dark" style="background:#fff;transition:.13s;">
                        <?php if(!empty($pick['image'])): ?>
                            <img src="<?= htmlspecialchars($pick['image']) ?>" alt="<?= htmlspecialchars($pick['title']) ?>" style="width:100%;max-height:90px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
                        <?php endif; ?>
                        <div class="fw-bold mb-1" style="color:#6c5ce7;">
                            <?= ($lang == 'en' && !empty($pick['title_en'])) ? htmlspecialchars($pick['title_en']) : htmlspecialchars($pick['title']) ?>
                        </div>
                        <div style="color:#333;font-size:.97em">
                            <?= ($lang == 'en' && !empty($pick['excerpt_en'])) ? htmlspecialchars($pick['excerpt_en']) : htmlspecialchars($pick['excerpt']) ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <?= $lang == 'tr' ? "Henüz bu yazı için editör seçimi önerimiz yok." : "No editor's picks for this post yet." ?>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<style>
.en-destek-box { border:2px solid #ece9ff;}
.en-destek-link:hover { background:#f1f0ff; color:#6c5ce7 !important; text-decoration:none;}
</style>

<?php include "footer.php"; ?>