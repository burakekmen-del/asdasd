<?php
define('BLOG_PAGE', true);
require_once 'config.php';
require_once 'lang_init.php';
$pdo = $GLOBALS['pdo'];

// Blog yazılarını çek
$stmt = $pdo->prepare(
    "SELECT slug, title, title_en, excerpt, excerpt_en, image, editor_note, meta_title, meta_desc, category_id, created_at 
     FROM blog_posts 
     ORDER BY created_at DESC 
     LIMIT 20"
);
$stmt->execute();
$blogPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "header.php";
?>
<style>
.blog-list { display: grid; gap: 18px 12px; grid-template-columns: repeat(2,1fr);}
@media (max-width: 700px) { .blog-list { grid-template-columns: 1fr; } }
.blog-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #ececf7; padding: 0; overflow: hidden; }
.blog-card img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
.blog-info { padding: 14px; }
.blog-title { font-size: 1.08em; font-weight: 700; }
.blog-excerpt { font-size: .98em; color: #636e72; margin-bottom: 8px; }
.blog-btn { background: #6c5ce7; color: #fff; border: none; border-radius: 7px; padding: 7px 16px; font-weight: 700; cursor: pointer; }
</style>
<div class="container py-4">
    <h1 class="section-title mb-4"><?= $lang === 'tr' ? "Blog & Rehberler" : "Blog & Guides" ?></h1>
    <div class="blog-list">
        <?php foreach($blogPosts as $post): ?>
            <div class="blog-card">
                <?php if(!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                <?php endif; ?>
                <div class="blog-info">
                    <div class="blog-title">
                        <?= ($lang === 'en' && !empty($post['title_en'])) ? htmlspecialchars($post['title_en']) : htmlspecialchars($post['title']) ?>
                    </div>
                    <div class="blog-excerpt">
                        <?= ($lang === 'en' && !empty($post['excerpt_en'])) ? htmlspecialchars($post['excerpt_en']) : htmlspecialchars($post['excerpt']) ?>
                    </div>
                    <?php if(!empty($post['editor_note'])): ?>
                        <div class="alert alert-info p-2 mt-2 mb-2" style="font-size:.96em;"><b>📝 <?= $lang === 'en' ? "Editor's Note:" : "Editör Notu:" ?></b> <?= htmlspecialchars($post['editor_note']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:.93em;color:#888;"><?= date('d M Y', strtotime($post['created_at'])) ?></div>
                    <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-btn mt-2">Devamını Oku</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Editörün seçimi (ön plana çıkarılmış bloglar)
$enDestekler = $pdo->query(
    "SELECT slug, title, title_en, excerpt, excerpt_en, image 
     FROM blog_posts 
     WHERE en_destek=1 
     ORDER BY created_at DESC 
     LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if(count($enDestekler)): ?>
<div class="container mb-5 mt-5">
    <div class="en-destek-box p-4 rounded-4 shadow-sm" style="background:#f7f6fd;">
        <h3 style="color:#6c5ce7;font-weight:800;font-size:1.35em;margin-bottom:18px;">
            <?= $lang === 'tr' ? "Editörün Seçimi" : "Editor's Picks" ?>
        </h3>
        <div class="row g-2">
        <?php foreach($enDestekler as $destek): ?>
            <div class="col-12 col-md-4">
                <a href="/blog/<?= htmlspecialchars($destek['slug']) ?>" class="en-destek-link d-block p-3 rounded-3 text-dark" style="background:#fff;transition:.13s;">
                    <?php if(!empty($destek['image'])): ?>
                        <img src="<?= htmlspecialchars($destek['image']) ?>" alt="<?= htmlspecialchars($destek['title']) ?>" style="width:100%;max-height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
                    <?php endif; ?>
                    <div class="fw-bold mb-1" style="color:#6c5ce7;">
                        <?= ($lang === 'en' && !empty($destek['title_en'])) ? htmlspecialchars($destek['title_en']) : htmlspecialchars($destek['title']) ?>
                    </div>
                    <div style="color:#333;font-size:.97em">
                        <?= ($lang === 'en' && !empty($destek['excerpt_en'])) ? htmlspecialchars($destek['excerpt_en']) : htmlspecialchars($destek['excerpt']) ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<style>
.en-destek-box { border:2px solid #ece9ff;}
.en-destek-link:hover { background:#f1f0ff; color:#6c5ce7 !important; text-decoration:none;}
</style>
<?php include "footer.php"; ?>