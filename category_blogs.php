<?php
// $current_category ile ilgili kategoriye özel bloglar
$catBlogStmt = $pdo->prepare("SELECT slug, title, excerpt, image FROM blog_posts WHERE category_id=? ORDER BY created_at DESC LIMIT 3");
$catBlogStmt->execute([$current_category]);
$catBlogs = $catBlogStmt->fetchAll(PDO::FETCH_ASSOC);
if($catBlogs): ?>
<div class="mb-4">
    <h2 class="section-title"><i class="fa fa-pen"></i> Bu Kategori İçin Bloglar</h2>
    <div class="row">
        <?php foreach($catBlogs as $blog): ?>
            <div class="col-12 col-md-6 mb-3">
                <a href="/blog/<?= htmlspecialchars($blog['slug']) ?>" class="blog-card shadow-sm p-3 rounded d-block h-100">
                    <?php if(!empty($blog['image'])): ?>
                        <img src="<?= htmlspecialchars($blog['image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                    <?php endif; ?>
                    <div class="blog-title fw-bold mb-1"><?= htmlspecialchars($blog['title']) ?></div>
                    <div class="blog-excerpt text-muted"><?= htmlspecialchars($blog['excerpt']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>