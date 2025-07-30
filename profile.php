<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

// Kullanıcı login değilse login sayfasına yönlendir
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Favorilerden çıkarma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_fav'])) {
    $remove_id = intval($_POST['remove_fav']);
    $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?")->execute([$user_id, $remove_id]);
    header("Location: profile.php#fav");
    exit;
}

// header.php yönlendirme ve işlemlerden SONRA eklenmeli!
include "header.php";

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT id, username, email, created_at, is_verified, is_admin, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Kullanıcı bulunamadı.</div></div>";
    exit;
}

// Avatar yolu ve dosya kontrolü
$avatar_dir = "assets/avatars/";
$avatar_file = !empty($user['avatar']) ? $user['avatar'] : 'default.png';
$check_avatar_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $avatar_dir . $avatar_file;
if (!file_exists($check_avatar_path)) {
    $avatar_file = 'default.png';
}
$avatar_img = $avatar_dir . $avatar_file;

// Favori ürünler
$favStmt = $pdo->prepare("
    SELECT p.id, p.name, p.images, p.price, p.original_price, p.platform
    FROM favorites f
    LEFT JOIN products p ON f.product_id = p.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT 20
");
$favStmt->execute([$user_id]);
$favorites = $favStmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcının yorumları
$commentStmt = $pdo->prepare("
    SELECT pc.comment, pc.created_at, p.name AS product_name, p.id AS product_id
    FROM product_comments pc
    LEFT JOIN products p ON pc.product_id = p.id
    WHERE pc.user_id = ?
    ORDER BY pc.created_at DESC
    LIMIT 10
");
$commentStmt->execute([$user_id]);
$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container py-4">
    <h2 class="mb-4 d-flex align-items-center gap-2">
        <i class="fa fa-user-circle"></i>
        <span><?= htmlspecialchars($user['username']) ?></span>
        <?php if($user['is_admin']): ?>
            <span class="badge bg-danger ms-2">Yönetici</span>
        <?php endif; ?>
        <span class="ms-3" id="compareBadgeWrap">
            <a href="#compareTab" class="btn btn-outline-primary position-relative" id="compareProfileBtn">
                <i class="fa fa-balance-scale"></i>
                <span class="compare-count-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size:0.9em;display:none;">0</span>
                Karşılaştırmalarım
            </a>
        </span>
    </h2>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="<?= htmlspecialchars($avatar_img) ?>" alt="Avatar" class="mb-3 rounded-circle" width="100" height="100" style="object-fit:cover;border:3px solid #eee;">
                    <h5 class="card-title mb-3">Profil Bilgileri</h5>
                    <ul class="list-group list-group-flush text-start" style="margin-bottom:0;">
                        <li class="list-group-item d-flex justify-content-between align-items-center"><b>Kullanıcı Adı:</b> <span><?= htmlspecialchars($user['username']) ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><b>E-Posta:</b> <span><?= htmlspecialchars($user['email']) ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><b>Kayıt Tarihi:</b> <span><?= date("d.m.Y H:i", strtotime($user['created_at'])) ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><b>Doğrulama:</b> 
                            <?= $user['is_verified'] ? '<span class="badge bg-success">Doğrulandı</span>' : '<span class="badge bg-secondary">Doğrulanmadı</span>' ?>
                        </li>

// Kullanıcının topluluk paylaşımları
$communityStmt = $pdo->prepare("SELECT id, title, content, created_at FROM community WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$communityStmt->execute([$user_id]);
$communityPosts = $communityStmt->fetchAll(PDO::FETCH_ASSOC);
                    </ul>
                    <a href="profile_edit.php" class="btn btn-outline-primary mt-3 w-100"><i class="fa fa-edit"></i> Profili Düzenle</a>
                    <a href="logout.php" class="btn btn-outline-danger mt-2 w-100"><i class="fa fa-sign-out-alt"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="fav-tab" data-bs-toggle="tab" data-bs-target="#fav" type="button" role="tab">Favoriler</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab">Yorumlarım</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="community-tab" data-bs-toggle="tab" data-bs-target="#community" type="button" role="tab">Topluluk Paylaşımlarım</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="compareTabBtn" data-bs-toggle="tab" data-bs-target="#compareTab" type="button" role="tab">
                        Karşılaştırmalarım
                        <span id="compareTabBadge" class="badge bg-primary ms-1" style="display:none;">0</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="profileTabsContent">
                <div class="tab-pane fade show active" id="fav" role="tabpanel">
                    <?php if ($favorites): ?>
                        <div class="row g-3">
                        <?php foreach($favorites as $product): 
                            $imgList = !empty($product['images']) ? explode(',', $product['images']) : [];
                            $img = (!empty($imgList[0])) ? trim($imgList[0]) : '';
                            $imgPath = ($img && file_exists('uploads/' . $img)) ? 'uploads/' . htmlspecialchars($img) : 'https://via.placeholder.com/300x180?text=Görsel+Yok';
                            $name = !empty($product['name']) ? $product['name'] : '-';
                            $price = isset($product['price']) ? $product['price'] : 0;
                            $oldPrice = isset($product['original_price']) ? $product['original_price'] : null;
                            $discount = ($oldPrice && $oldPrice > $price) ? round((($oldPrice-$price)/$oldPrice)*100) : 0;

                            // Platform logosu veya adı
                            $platformLogoHtml = '';
                            if (!empty($product['platform'])) {
                                $platform = strtolower($product['platform']);
                                $logoExtensions = ['svg', 'png'];
                                $platformLogo = '';
                                foreach ($logoExtensions as $ext) {
                                    $path = "assets/platform-logos/$platform.$ext";
                                    if (file_exists($path)) {
                                        $platformLogo = $path;
                                        break;
                                    }
                                }
                                if ($platformLogo) {
                                    $platformLogoHtml = '<span class="platform-logo" title="' . htmlspecialchars($platform) . '"><img src="' . $platformLogo . '" alt="' . htmlspecialchars($platform) . '" loading="lazy" style="width:22px;height:22px;"></span>';
                                } else {
                                    $platformLogoHtml = '<span class="platform-logo-text">' . ucfirst($platform) . '</span>';
                                }
                            }
                        ?>
                            <div class="col-sm-6 col-md-4">
                                <div class="card h-100">
                                    <div class="product-img position-relative">
                                        <img src="<?= $imgPath ?>" class="card-img-top" alt="<?= htmlspecialchars($name) ?>">
                                        <?= $platformLogoHtml ?>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($name) ?></h6>
                                        <div>
                                            <?php if ($price > 0): ?>
                                                <span class="text-danger fw-bold">₺<?= number_format($price,2,',','.') ?></span>
                                                <?php if ($oldPrice > $price): ?>
                                                    <span class="text-muted text-decoration-line-through ms-2">₺<?= number_format($oldPrice,2,',','.') ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-secondary">Fiyat Bilgisi Yok</span>
                                            <?php endif; ?>
                                        </div>
                                        <form method="post" onsubmit="return confirm('Favorilerden kaldırmak istediğine emin misin?');">
                                            <input type="hidden" name="remove_fav" value="<?= $product['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm w-100 mb-2"><i class="fa fa-trash"></i> Favorilerden Çıkar</button>
                                        </form>
                                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary w-100">Ürüne Git</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <style>
                            .platform-logo {
                                width: 36px;
                                height: 36px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: #fff;
                                border-radius: 50%;
                                box-shadow: 0 2px 8px #0002;
                                padding: 2px;
                                position: absolute;
                                top: 10px;
                                left: 10px;
                                z-index: 4;
                            }
                            .platform-logo img {
                                width: 22px;
                                height: 22px;
                                object-fit: contain;
                            }
                            .platform-logo-text {
                                font-size: 13px;
                                font-weight: bold;
                                padding: 5px 11px;
                                border-radius: 13px;
                                background: #f2f2f2;
                                color: #444;
                                position: absolute;
                                left: 10px;
                                top: 10px;
                                z-index: 4;
                                box-shadow: 0 2px 8px #0001;
                            }
                        </style>
                    <?php else: ?>
                        <div class="alert alert-secondary d-flex flex-column align-items-center justify-content-center">
                            <div>Henüz favori ürününüz yok.</div>
                            <a href="deals.php" class="btn btn-outline-primary mt-3">
                                <i class="fa fa-search"></i> Ürünleri Keşfet ve Favorilere Ekle!
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="comments" role="tabpanel">
                    <?php if ($comments): ?>
                        <ul class="list-group">
                        <?php foreach($comments as $com): ?>
                            <li class="list-group-item">
                                <b>Ürün:</b> <a href="product.php?id=<?= $com['product_id'] ?>" target="_blank"><?= htmlspecialchars($com['product_name']) ?></a><br>
                                <b>Yorum:</b> <?= nl2br(htmlspecialchars($com['comment'])) ?><br>
                                <span class="text-muted small"><?= date("d.m.Y H:i", strtotime($com['created_at'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary">Henüz yorum yapmadınız.</div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="community" role="tabpanel">
                    <!-- Topluluk Paylaşımlarım -->
                    <?php if($communityPosts): ?>
                        <ul class="list-group">
                        <?php foreach($communityPosts as $p): ?>
                            <li class="list-group-item">
                                <?php if($p['title']): ?><b><?= htmlspecialchars($p['title']) ?></b><br><?php endif; ?>
                                <?= nl2br(htmlspecialchars($p['content'])) ?><br>
                                <span class="text-muted">(<?= date('d.m.Y H:i', strtotime($p['created_at'])) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info">Henüz topluluk paylaşımınız yok.</div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="compareTab" role="tabpanel">
                    <div id="compareTableWrap"></div>
                    <a href="index.php" class="btn btn-outline-primary mt-3"><i class="fa fa-plus"></i> Başka ürün ekle</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bootstrap tab fix
    var triggerTabList = [].slice.call(document.querySelectorAll('#profileTabs button'))
    triggerTabList.forEach(function (triggerEl) {
      var tabTrigger = new bootstrap.Tab(triggerEl)
      triggerEl.addEventListener('click', function (event) {
        event.preventDefault()
        tabTrigger.show()
      })
    })
    // Popover init:
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    popoverTriggerList.forEach(function (popoverTriggerEl) { new bootstrap.Popover(popoverTriggerEl) })

    // Profilde karşılaştırma tabı ve badge
    function renderCompareTable() {
        var ids = JSON.parse(localStorage.getItem('compareList')||'[]').slice(0,4);
        var badge = document.querySelector('#compareTabBadge');
        var badge2 = document.querySelector('#compareBadgeWrap .compare-count-badge');
        if(badge) { badge.innerText = ids.length; badge.style.display = ids.length ? "inline-flex" : "none"; }
        if(badge2) { badge2.innerText = ids.length; badge2.style.display = ids.length ? "inline-flex" : "none"; }
        if(ids.length === 0) {
            document.getElementById('compareTableWrap').innerHTML = '<div class="alert alert-info">Karşılaştırmak için ürün seçiniz.</div>';
            return;
        }
        // AJAX ile ürünleri çek
        fetch('compare_api.php?ids='+ids.join(','))
          .then(r=>r.json())
          .then(function(res){
            if(!res.success) return;
            var html = `<table class="table table-bordered align-middle text-center"><tr>`;
            res.products.forEach(function(p,i){
                html += `<th>
                    <img src="uploads/${p.img}" style="max-width:80px; max-height:80px;"><br>
                    <b>${p.name}</b><br>
                    <button class="btn btn-sm btn-danger compare-remove-btn" data-id="${p.id}"><i class="fa fa-trash"></i></button>
                </th>`;
            });
            html += `</tr><tr>`;
            res.products.forEach(function(p){
                html += `<td>₺${p.price}</td>`;
            });
            html += `</tr><tr>`;
            res.products.forEach(function(p){
                html += `<td>${p.brand||'-'}</td>`;
            });
            html += `</tr></table>`;
            document.getElementById('compareTableWrap').innerHTML = html;
            document.querySelectorAll('.compare-remove-btn').forEach(function(btn){
                btn.onclick = function(){
                    var id = btn.dataset.id;
                    var ids = JSON.parse(localStorage.getItem('compareList')||'[]');
                    ids = ids.filter(x=>x!=id);
                    localStorage.setItem('compareList', JSON.stringify(ids));
                    renderCompareTable();
                }
            });
          });
    }
    renderCompareTable();

    // Profilde hash ile favoriler veya karşılaştırma tabını açma
    if(window.location.hash === "#compareTab") {
        setTimeout(function(){
            document.getElementById('compareTabBtn').click();
        },100);
    }
    if(window.location.hash === "#fav") {
        setTimeout(function(){
            document.getElementById('fav-tab').click();
        },100);
    }
</script>
<?php include "footer.php"; ?>