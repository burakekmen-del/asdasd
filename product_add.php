<?php
require_once '../config.php';
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

// CSRF token oluşturma
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Session timeout. Please try again.";
    } else {
        $name_en = trim($_POST['name_en'] ?? '');
        $desc_en = trim($_POST['description_en'] ?? '');
        $name_tr = trim($_POST['name_tr'] ?? '');
        $desc_tr = trim($_POST['description_tr'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $original_price = floatval($_POST['original_price'] ?? 0);
        $campaign_active = isset($_POST['campaign_active']) ? 1 : 0;
        $campaign_price = floatval($_POST['campaign_price'] ?? 0);
        $affiliate_link = trim($_POST['affiliate_link'] ?? '');
        $platform = trim($_POST['platform'] ?? '');

        // Meta alanları TR ve EN (veritabanına uygun isim!)
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_title_tr = trim($_POST['meta_title_tr'] ?? '');
        $meta_desc_tr = trim($_POST['meta_description_tr'] ?? '');

        // Temu İlk İndirme Alanları
        $first_order_active = isset($_POST['first_order_active']) ? 1 : 0;
        $first_order_price = floatval($_POST['first_order_price'] ?? 0);

        // Video alanları
        $video_type = trim($_POST['video_type'] ?? '');
        $video_src = '';

        if ($video_type === 'mp4' && isset($_FILES['video_src']) && $_FILES['video_src']['error'] === 0) {
            $allowed_mp4 = ['mp4','webm'];
            $ext = strtolower(pathinfo($_FILES['video_src']['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($_FILES['video_src']['tmp_name']);
            if (in_array($ext, $allowed_mp4) && ($mime === 'video/mp4' || $mime === 'video/webm')) {
                $unique_name = uniqid('vid_') . '.' . $ext;
                if (move_uploaded_file($_FILES['video_src']['tmp_name'], "../uploads/$unique_name")) {
                    $video_src = "/uploads/$unique_name";
                }
            }
        } elseif ($video_type && isset($_POST['video_src_text'])) {
            $video_src = trim($_POST['video_src_text']);
        }

        $price = $original_price;
        $campaign_percent = 0;
        if ($campaign_active && $campaign_price > 0 && $campaign_price < $original_price) {
            $price = $campaign_price;
            $campaign_percent = round((($original_price - $campaign_price) / $original_price) * 100);
        }

        // Çoklu görsel yükleme
        $images = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
            foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
                if ($_FILES['images']['error'][$k] != 0) continue;
                $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg','jpeg','png','gif','webp','svg','bmp','tiff'];
                $mime = mime_content_type($tmp_name);
                $allowed_mimes = [
                    'image/jpeg','image/png','image/gif','image/webp','image/svg+xml','image/bmp','image/tiff'
                ];
                if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mimes)) {
                    $unique_name = uniqid('img_').'.'.$ext;
                    if (move_uploaded_file($tmp_name, "../uploads/$unique_name")) {
                        $images[] = $unique_name;
                    }
                }
            }
        }
        $images = array_map('trim', $images);
        $images_str = $images ? implode(',', $images) : 'default.jpg';

        // Validasyon
        if (!$name_en) {
            $error = "Product name (English) is required!";
        } elseif (!$original_price) {
            $error = "Original price is required!";
        } elseif ($campaign_active && (!$campaign_price || $campaign_price >= $original_price)) {
            $error = "Campaign price must be less than original price!";
        } elseif ($video_type && !$video_src) {
            $error = "Video kaynağı eksik!";
        } elseif ($platform === 'temu' && $first_order_active && (!$first_order_price || $first_order_price >= $original_price)) {
            $error = "Temu ilk indirme fiyatı, orijinal fiyattan küçük ve boş olmamalıdır!";
        }

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO products (
                name_en, description_en,
                name_tr, description_tr,
                category_id, price, original_price, affiliate_link, images, campaign_active, campaign_percent,
                platform, meta_title, meta_description, meta_title_tr, meta_desc_tr, video_type, video_src, 
                first_order_active, first_order_price,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $name_en, $desc_en,
                $name_tr, $desc_tr,
                $category_id, $price, $original_price, $affiliate_link, $images_str, $campaign_active, $campaign_percent,
                $platform, $meta_title, $meta_description, $meta_title_tr, $meta_desc_tr, $video_type, $video_src, 
                $first_order_active, $first_order_price
            ]);
            header("Location: product_add.php?success=1");
            exit;
        }
    }
}

if (isset($_GET['success'])) $success = true;

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .container { max-width:620px;margin:60px auto; background:#fff; border-radius:13px; box-shadow:0 4px 18px #2563eb13, 0 2px 10px #0002; padding:2.3rem 2rem;}
        h2 { color:#2563eb; font-weight:700;}
        #preview img { width:64px; margin:3px; border-radius:5px; border:1px solid #eee;}
        .temu-fields { background:#f8f0ff;border-radius:8px;padding:10px 13px;}
    </style>
    <script>
      function toggleCampaign() {
        var c = document.getElementById('campaign_active').checked;
        document.getElementById('campaign_price_group').style.display = c ? 'block' : 'none';
      }
      function calculatePercent() {
        var org = parseFloat(document.querySelector('[name="original_price"]').value.replace(',', '.')) || 0;
        var camp = parseFloat(document.querySelector('[name="campaign_price"]').value.replace(',', '.')) || 0;
        var per = 0;
        if(org > 0 && camp > 0 && camp < org) {
          per = Math.round(((org - camp) / org) * 100);
        }
        document.getElementById('campaign_percent_display').innerText = per > 0 ? ("%"+per) : "";
      }
      function previewImages(input) {
        const preview = document.getElementById('preview');
        preview.innerHTML = '';
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                let img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
      }
      function toggleVideoInput() {
        var type = document.getElementById('video_type').value;
        document.getElementById('video_src_file').style.display = (type === 'mp4') ? 'block' : 'none';
        document.getElementById('video_src_text').style.display = (type && type !== 'mp4') ? 'block' : 'none';
      }
      function toggleTemuFields() {
        var platform = document.querySelector('[name="platform"]').value;
        var box = document.getElementById('temu_fields_box');
        box.style.display = (platform === 'temu') ? 'block' : 'none';
      }
    </script>
</head>
<body>
<div class="container">
    <h2>Add Product</h2>
    <?php if($success): ?>
        <div class="alert alert-success">Product added successfully!</div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="mb-3"><label class="form-label">Name (English) *</label>
            <input type="text" name="name_en" class="form-control" required value="<?= htmlspecialchars($_POST['name_en'] ?? '') ?>">
        </div>
        <div class="mb-3"><label class="form-label">Description (EN) *</label>
            <textarea name="description_en" class="form-control" required><?= htmlspecialchars($_POST['description_en'] ?? '') ?></textarea>
        </div>
        <div class="mb-3"><label class="form-label">Adı (Türkçe)</label>
            <input type="text" name="name_tr" class="form-control" value="<?= htmlspecialchars($_POST['name_tr'] ?? '') ?>">
        </div>
        <div class="mb-3"><label class="form-label">Açıklama (TR)</label>
            <textarea name="description_tr" class="form-control"><?= htmlspecialchars($_POST['description_tr'] ?? '') ?></textarea>
        </div>
        <div class="mb-3"><label class="form-label">Category</label>
            <select name="category_id" class="form-control">
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id']==$cat['id']) ? 'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Original Price (₺) *</label>
            <input type="number" step="0.01" name="original_price" class="form-control" required oninput="calculatePercent()" value="<?= htmlspecialchars($_POST['original_price'] ?? '') ?>">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="campaign_active" id="campaign_active" value="1" onchange="toggleCampaign();calculatePercent();" <?= (isset($_POST['campaign_active']) && $_POST['campaign_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="campaign_active">Campaign Active</label>
        </div>
        <div class="mb-3" id="campaign_price_group" style="display:none;">
            <label class="form-label">Campaign Price (₺)</label>
            <input type="number" step="0.01" name="campaign_price" class="form-control" oninput="calculatePercent()" value="<?= htmlspecialchars($_POST['campaign_price'] ?? '') ?>">
            <div class="form-text"><span id="campaign_percent_display"></span></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Affiliate Link</label>
            <input type="url" name="affiliate_link" class="form-control" autocomplete="off" value="<?= htmlspecialchars($_POST['affiliate_link'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Platform</label>
            <select name="platform" class="form-control" required onchange="toggleTemuFields()">
                <option value="">Select</option>
                <option value="trendyol" <?= (isset($_POST['platform']) && $_POST['platform']=='trendyol')?'selected':''; ?>>Trendyol</option>
                <option value="amazon" <?= (isset($_POST['platform']) && $_POST['platform']=='amazon')?'selected':''; ?>>Amazon</option>
                <option value="temu" <?= (isset($_POST['platform']) && $_POST['platform']=='temu')?'selected':''; ?>>Temu</option>
                <option value="hepsiburada" <?= (isset($_POST['platform']) && $_POST['platform']=='hepsiburada')?'selected':''; ?>>Hepsiburada</option>
                <option value="other" <?= (isset($_POST['platform']) && $_POST['platform']=='other')?'selected':''; ?>>Other</option>
            </select>
        </div>
        <!-- Temu ilk indirme kampanyası alanları -->
        <div class="mb-3 temu-fields" id="temu_fields_box" style="display:none;">
          <label class="form-label">
            <input type="checkbox" name="first_order_active" id="first_order_active" value="1" <?= (isset($_POST['first_order_active']) && $_POST['first_order_active'])?'checked':''; ?>> Temu İlk İndirme Fiyatı Aktif
          </label>
          <div style="margin-top:7px;">
            <label class="form-label">Temu İlk İndirme Fiyatı (₺)</label>
            <input type="number" step="0.01" name="first_order_price" class="form-control" value="<?= htmlspecialchars($_POST['first_order_price'] ?? '') ?>">
            <div class="form-text" style="font-size:0.98em;">
              Bu alanı doldurursanız, ürün kartlarında <b>sadece Temu'da ilk kez sipariş verenler için geçerli</b> fiyat etiketi gösterilecektir.<br>
              Fiyat, orijinal fiyattan küçük olmalıdır.
            </div>
          </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Product Images (multiple)</label>
            <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.bmp,.tiff" class="form-control" onchange="previewImages(this)">
            <div id="preview"></div>
        </div>
        <!-- Video Alanı Başlangıç -->
        <div class="mb-3">
            <label class="form-label">Video Türü</label>
            <select name="video_type" id="video_type" class="form-control" onchange="toggleVideoInput()">
                <option value="">Yok</option>
                <option value="mp4" <?= (isset($_POST['video_type']) && $_POST['video_type']=='mp4')?'selected':''; ?>>MP4 Dosya</option>
                <option value="youtube" <?= (isset($_POST['video_type']) && $_POST['video_type']=='youtube')?'selected':''; ?>>YouTube</option>
                <option value="vimeo" <?= (isset($_POST['video_type']) && $_POST['video_type']=='vimeo')?'selected':''; ?>>Vimeo</option>
                <option value="tiktok" <?= (isset($_POST['video_type']) && $_POST['video_type']=='tiktok')?'selected':''; ?>>TikTok</option>
            </select>
        </div>
        <div class="mb-3" id="video_src_file" style="display:none;">
            <label class="form-label">Video Dosyası (MP4/WebM)</label>
            <input type="file" name="video_src" accept="video/mp4,video/webm" class="form-control">
        </div>
        <div class="mb-3" id="video_src_text" style="display:none;">
            <label class="form-label">Video Kaynağı (YouTube ID, Vimeo ID veya TikTok ID/URL)</label>
            <input type="text" name="video_src_text" class="form-control" value="<?= htmlspecialchars($_POST['video_src_text'] ?? '') ?>">
            <div class="form-text">
                YouTube için sadece video ID (örn: dQw4w9WgXcQ),<br>
                Vimeo için sadece video ID,<br>
                TikTok için video ID veya tam embed URL giriniz.
            </div>
        </div>
        <!-- Video Alanı Bitiş -->
        <div class="mb-3">
            <label class="form-label">Meta Title (EN)</label>
            <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Meta Description (EN)</label>
            <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Meta Title (TR)</label>
            <input type="text" name="meta_title_tr" class="form-control" value="<?= htmlspecialchars($_POST['meta_title_tr'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Meta Description (TR)</label>
            <textarea name="meta_description_tr" class="form-control" rows="2"><?= htmlspecialchars($_POST['meta_description_tr'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
        <a href="products.php" class="btn btn-link">Back</a>
    </form>
    <script>
      toggleCampaign();
      calculatePercent();
      toggleVideoInput();
      toggleTemuFields();
      document.querySelector('[name="platform"]').addEventListener('change', toggleTemuFields);
    </script>
</div>
</body>
</html>