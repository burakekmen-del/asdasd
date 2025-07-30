<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'csrf.php';
require_once 'lang_init.php';

// Kullanıcı login değilse login sayfasına yönlendir
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT username, email, lang, campaign_notify, avatar, password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Kullanıcı bulunamadı.</div></div>";
    exit;
}

// CSRF token üretimi
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_check() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

$error = '';
$success = '';
$avatar_dir = 'assets/avatars/';
$default_avatar = $avatar_dir . ($user['avatar'] ?: 'default.png');

// Hazır avatarlar otomatik olarak listelenir (default.png hariç!)
$avatar_options = [];
foreach (glob($avatar_dir . "*.png") as $file) {
    $base = basename($file);
    if ($base !== 'default.png') $avatar_options[] = $base;
}

// POST işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = "Güvenlik hatası! Lütfen tekrar deneyin.";
    } else {
        // Formdan değerleri al
        $avatar = in_array($_POST['selected_avatar'], $avatar_options) ? $_POST['selected_avatar'] : 'default.png';
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $lang = in_array($_POST['lang'], ['tr', 'en']) ? $_POST['lang'] : 'tr';
        $campaign_notify = isset($_POST['campaign_notify']) ? 1 : 0;

        // Şifre değiştirme isteği geldiyse kontrol et
        $password_sql = "";
        $params = [$username, $email, $lang, $avatar, $campaign_notify, $user_id];
        if (!empty($_POST['password'])) {
            // Mevcut şifreyi doğrula
            if (empty($_POST['current_password']) || !password_verify($_POST['current_password'], $user['password'])) {
                $error = "Mevcut şifreniz yanlış!";
            } else {
                $new_password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $password_sql = ", password = ?";
                $params[] = $new_password_hash;
            }
        }

        if (!$error) {
            // Veritabanına güncelle
            $sql = "UPDATE users SET username=?, email=?, lang=?, avatar=?, campaign_notify=? $password_sql WHERE id=?";
            if (!empty($password_sql)) {
                $params[] = $user_id;
            }
            $pdo->prepare($sql)->execute($params);

            // Session'ı güncelle
            $_SESSION['avatar'] = $avatar;
            $_SESSION['username'] = $username;
            $_SESSION['lang'] = $lang;

            // Formun tekrar dolması için kullanıcı bilgilerini güncelle
            $user['avatar'] = $avatar;
            $user['username'] = $username;
            $user['email'] = $email;
            $user['lang'] = $lang;
            $user['campaign_notify'] = $campaign_notify;
            $default_avatar = $avatar_dir . $avatar;

            $success = "Profiliniz başarıyla güncellendi!";
        }
    }
}

$langs = ['tr' => 'Türkçe', 'en' => 'English'];
?>
<?php include 'header.php'; ?>
<?php if (!defined('BLOG_PAGE')): ?>
<div class="category-strip-outer">
  <div class="container">
    <nav class="category-strip-swiper" aria-label="Kategoriler">
      <div class="swiper categorySwiper">
        <div class="swiper-wrapper">
          <?php
          $anaKategoriler = [
              ["id"=>"kadin","name"=>$lang=='en'?"Women":"Kadın Moda","icon"=>"fa-person-dress"],
              ["id"=>"erkek","name"=>$lang=='en'?"Men":"Erkek Moda","icon"=>"fa-person"],
              ["id"=>"anne-cocuk","name"=>$lang=='en'?"Mother & Child":"Anne & Çocuk","icon"=>"fa-child"],
              ["id"=>"ev-yasam","name"=>$lang=='en'?"Home & Living":"Ev & Yaşam","icon"=>"fa-couch"],
              ["id"=>"elektronik","name"=>$lang=='en'?"Electronics":"Elektronik","icon"=>"fa-plug-circle-bolt"],
              ["id"=>"supermarket","name"=>$lang=='en'?"Supermarket":"Süpermarket","icon"=>"fa-basket-shopping"],
              ["id"=>"kozmetik","name"=>$lang=='en'?"Cosmetics":"Kozmetik","icon"=>"fa-pump-soap"],
              ["id"=>"ayakkabi-canta","name"=>$lang=='en'?"Shoes & Bags":"Ayakkabı & Çanta","icon"=>"fa-bag-shopping"],
              ["id"=>"spor-outdoor","name"=>$lang=='en'?"Sports & Outdoor":"Spor & Outdoor","icon"=>"fa-person-running"],
              ["id"=>"kitap-hobi","name"=>$lang=='en'?"Books & Hobby":"Kitap & Hobi","icon"=>"fa-book-open"],
              ["id"=>"populer","name"=>$lang=='en'?"Popular":"Çok Tıklananlar","icon"=>"fa-fire-flame-curved"],
          ];
          $category_id = $_GET['category'] ?? '';
          foreach($anaKategoriler as $cat): ?>
          <div class="swiper-slide" style="position:relative;">
            <a href="/?category=<?= $cat['id'] ?>&lang=<?= $lang ?>" class="category-link<?php if($category_id===$cat['id']) echo ' active'; ?>" 
               data-category="<?= $cat['id'] ?>" style="border:none !important;box-shadow:none !important;text-decoration:none !important;background:none;">
              <i class="fa <?= $cat['icon'] ?>"></i>
              <span><?= $cat['name'] ?></span>
            </a>
<style>
.category-link, .category-link.active, .category-link:focus, .category-link:visited, .category-link:hover {
  border: none !important;
  border-bottom: none !important;
  box-shadow: none !important;
  text-decoration: none !important;
  outline: none !important;
  background: none !important;
}
</style>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="swiper-button-prev category-swiper-arrow"></div>
        <div class="swiper-button-next category-swiper-arrow"></div>
      </div>
    </nav>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  new Swiper('.categorySwiper', {
    slidesPerView: 'auto',
    spaceBetween: 2,
    freeMode: true,
    grabCursor: true,
    navigation: {
      nextEl: '.category-swiper-arrow.swiper-button-next',
      prevEl: '.category-swiper-arrow.swiper-button-prev'
    }
  });
});
</script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($user['lang']) ?>">
<head>
    <meta charset="UTF-8">
    <title>Profilimi Düzenle</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f5f7ff; }
        .container { max-width: 520px; margin: 60px auto; }
        .avatar-preview { width:100px; height:100px; border-radius:50%; object-fit:cover; margin-bottom:10px; border:3px solid #eee; }
        .modal-avatar-list { display:flex; flex-wrap:wrap; gap:14px; justify-content:center; }
        .modal-avatar-item { border:2px solid #eee; border-radius:50%; padding:3px; cursor:pointer; transition: border-color .15s; }
        .modal-avatar-item.selected, .modal-avatar-item:hover { border-color:#2563eb; }
        .lang-select { width:100%; }
        .form-switch .form-check-input { width: 2.5em; height: 1.4em; }
        .form-switch .form-check-input:checked { background-color: #2563eb; border-color: #2563eb; }
        .lang-select option[selected], .lang-select:focus option:checked { font-weight: bold; background: #2563eb22; }
        .info-icon { cursor:pointer; color:#2563eb; margin-left:5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Profilimi Düzenle</h2>
        <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-3 text-center">
                <img src="<?= htmlspecialchars($default_avatar) ?>" class="avatar-preview" id="avatarPreview" alt="Avatar">
                <div>
                    <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#avatarModal">
                        Avatar Seç
                    </button>
                </div>
                <input type="hidden" name="selected_avatar" id="selectedAvatar" value="<?= htmlspecialchars($user['avatar']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-posta</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Sistem Dili</label>
                <select name="lang" class="form-select lang-select">
                    <?php foreach($langs as $k=>$v): ?>
                        <option value="<?= $k ?>"<?= $user['lang'] == $k ? ' selected style="font-weight:bold;background:#2563eb22;"' : '' ?>><?= htmlspecialchars($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Yeni Şifre <small class="text-muted">(Değiştirmek istemiyorsanız boş bırakın)</small></label>
                <input type="password" name="password" class="form-control" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Mevcut Şifre <small class="text-muted">(Yeni şifre belirlemek için zorunlu)</small></label>
                <input type="password" name="current_password" class="form-control" autocomplete="current-password">
            </div>
            <div class="mb-3 form-switch">
                <input type="checkbox" class="form-check-input" name="campaign_notify" id="campaign_notify" value="1" <?= $user['campaign_notify'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="campaign_notify">
                    Kampanya Bildirimlerini Aç
                    <i class="fa fa-info-circle info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Kampanya ve fırsat bildirimlerini e-posta veya site üzerinden almak isterseniz açık bırakın."></i>
                </label>
            </div>
            <button type="submit" class="btn btn-success">Güncelle</button>
            <a href="profile.php" class="btn btn-link">İptal</a>
        </form>
    </div>

    <!-- Avatar Modal -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="avatarModalLabel">Avatar Seç</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
          </div>
          <div class="modal-body">
            <div class="modal-avatar-list">
                <?php foreach($avatar_options as $avatar): ?>
                    <div class="modal-avatar-item<?= ($user['avatar'] === $avatar) ? ' selected' : '' ?>" 
                         onclick="selectAvatar('<?= htmlspecialchars($avatar) ?>', this)">
                        <img src="<?= $avatar_dir . $avatar ?>" width="70" height="70" style="border-radius:50%;" alt="">
                    </div>
                <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
   function selectAvatar(name, el) {
    document.querySelectorAll('.modal-avatar-item').forEach(function(a){ a.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('selectedAvatar').value = name;
    document.getElementById('avatarPreview').src = "<?= $avatar_dir ?>" + name;
    var modalEl = document.getElementById('avatarModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) modal = new bootstrap.Modal(modalEl);
    modal.hide();
}

// Modal erişilebilirlik ve backdrop temizleme
document.getElementById('avatarModal').addEventListener('shown.bs.modal', function () {
    this.setAttribute('aria-hidden', 'false');
});
document.getElementById('avatarModal').addEventListener('hidden.bs.modal', function () {
    this.setAttribute('aria-hidden', 'true');
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop){
        if (backdrop && backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
    });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
});
    </script>
<?php include 'footer.php'; ?>
</body>
</html>