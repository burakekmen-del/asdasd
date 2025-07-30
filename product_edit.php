<?php
require_once '../config.php';
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { echo "Product not found."; exit; }

$imgList = !empty($product['images']) ? array_map('trim', explode(',', $product['images'])) : [];
if (isset($_POST['delete_imgs'])) {
    $delete_imgs = $_POST['delete_imgs'];
    foreach ($delete_imgs as $del_img) {
        $del_img = trim($del_img);
        if ($del_img && file_exists("../uploads/$del_img")) unlink("../uploads/$del_img");
        $imgList = array_diff($imgList, [$del_img]);
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name_en'])) {
    $name_en = trim($_POST['name_en'] ?? '');
    $desc_en = trim($_POST['description_en'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $original_price = floatval($_POST['original_price'] ?? 0);
    $campaign_active = isset($_POST['campaign_active']) ? 1 : 0;
    $campaign_price = floatval($_POST['campaign_price'] ?? 0);
    $affiliate_link = trim($_POST['affiliate_link'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');

    // Temu ilk indirme alanları
    $first_order_active = isset($_POST['first_order_active']) ? 1 : 0;
    $first_order_price = floatval($_POST['first_order_price'] ?? 0);

    $price = $original_price;
    $campaign_percent = 0;
    if ($campaign_active && $campaign_price > 0 && $campaign_price < $original_price) {
        $price = $campaign_price;
        $campaign_percent = round((($original_price - $campaign_price) / $original_price) * 100);
    }

    $images = $imgList;
    if (isset($_FILES['images']) && $_FILES['images']['error'][0] == 0) {
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
            if ($_FILES['images']['error'][$k] != 0) continue;
            $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg','jpeg','png','gif','webp','svg','bmp','tiff'];
            $mime = mime_content_type($tmp_name);
            $allowed_mimes = [
                'image/jpeg','image/png','image/gif','image/webp','image/svg+xml','image/bmp','image/tiff'
            ];
            if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mimes)) {
                $original_name = basename($_FILES['images']['name'][$k]);
                $save_path = "../uploads/$original_name";
                if (move_uploaded_file($tmp_name, $save_path)) {
                    $images[] = $original_name;
                }
            }
        }
    }
    $images = array_map('trim', $images);
    $images_str = $images ? implode(',', $images) : 'default.jpg';

    // Validasyonlar
    if (!$name_en || !$original_price) {
        $error = "Product name and original price are required!";
    } elseif ($campaign_active && (!$campaign_price || $campaign_price >= $original_price)) {
        $error = "Campaign price must be less than original price!";
    } elseif (strtolower($platform) === 'temu' && $first_order_active && (!$first_order_price || $first_order_price >= $original_price)) {
        $error = "Temu ilk indirme fiyatı, orijinal fiyattan küçük ve boş olmamalıdır!";
    } elseif (!$error) {
        $stmt = $pdo->prepare("UPDATE products SET name_en=?, description_en=?, category_id=?, price=?, original_price=?, affiliate_link=?, images=?, campaign_active=?, campaign_percent=?, platform=?, meta_title=?, meta_description=?, first_order_active=?, first_order_price=? WHERE id=?");
        $stmt->execute([
            $name_en, $desc_en, $category_id, $price, $original_price,
            $affiliate_link, $images_str, $campaign_active, $campaign_percent,
            $platform, $meta_title, $meta_description, $first_order_active, $first_order_price, $id
        ]);
        header("Location: products.php");
        exit;
    }

    $product = array_merge($product, [
        'name_en' => $name_en,
        'description_en' => $desc_en,
        'category_id' => $category_id,
        'original_price' => $original_price,
        'price' => $price,
        'affiliate_link' => $affiliate_link,
        'campaign_active' => $campaign_active,
        'campaign_percent' => $campaign_percent,
        'images' => $images_str,
        'platform' => $platform,
        'meta_title' => $meta_title,
        'meta_description' => $meta_description,
        'first_order_active' => $first_order_active,
        'first_order_price' => $first_order_price
    ]);
    $imgList = $images;
}
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7ff; }
        .container { max-width:520px;margin:60px auto; background:#fff; border-radius:13px; box-shadow:0 4px 18px #2563eb13, 0 2px 10px #0002; padding:2.3rem 2rem;}
        h2 { color:#2563eb; font-weight:700;}
        .edit-img {height: 60px; margin-right:10px; object-fit:cover; border-radius:7px;}
        .img-del-checkbox {vertical-align:middle; margin-right:4px;}
        .temu-fields { background:#f8f0ff;border-radius:8px;padding:10px 13px; margin-bottom:15px;}
    </style>
    <script>
      function toggleCampaign() {
        const isChecked = document.getElementById('campaign_active').checked;
        document.getElementById('campaign_price_group').style.display = isChecked ? 'block' : 'none';
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
      function toggleTemuFields() {
        var platform = document.querySelector('[name="platform"]').value;
        var box = document.getElementById('temu_fields_box');
        box.style.display = (platform === 'temu') ? 'block' : 'none';
      }
    </script>
</head>
<body>
<div class="container">
    <h2>Edit Product</h2>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3"><label class="form-label">Name (English)</label>
            <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($product['name_en']) ?>" required>
        </div>
        <div class="mb-3"><label class="form-label">Description</label>
            <textarea name="description_en" class="form-control" required><?= htmlspecialchars($product['description_en']) ?></textarea>
        </div>
        <div class="mb-3"><label class="form-label">Category</label>
            <select name="category_id" class="form-control">
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $product['category_id']==$cat['id']?'selected':'' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Original Price (₺)</label>
            <input type="number" step="0.01" name="original_price" class="form-control" value="<?= htmlspecialchars($product['original_price']) ?>" required oninput="calculatePercent()">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="campaign_active" id="campaign_active" value="1" <?= !empty($product['campaign_active'])?'checked':'' ?> onchange="toggleCampaign();calculatePercent();">
            <label class="form-check-label" for="campaign_active">Campaign Active</label>
        </div>
        <div class="mb-3" id="campaign_price_group" style="display:<?= !empty($product['campaign_active'])?'block':'none' ?>;">
            <label class="form-label">Campaign Price (₺)</label>
            <input type="number" step="0.01" name="campaign_price" class="form-control" value="<?= htmlspecialchars($product['campaign_active'] ? $product['price'] : '') ?>" oninput="calculatePercent()">
            <div class="form-text"><span id="campaign_percent_display"><?= $product['campaign_percent'] ? '%'.$product['campaign_percent'] : '' ?></span></div>
        </div>
        <div class="mb-3"><label class="form-label">Affiliate Link</label>
            <input type="url" name="affiliate_link" class="form-control" value="<?= htmlspecialchars($product['affiliate_link']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Platform</label>
            <select name="platform" class="form-control" required onchange="toggleTemuFields()">
                <option value="">Select</option>
                <option value="trendyol" <?= $product['platform']=='trendyol'?'selected':'' ?>>Trendyol</option>
                <option value="amazon" <?= $product['platform']=='amazon'?'selected':'' ?>>Amazon</option>
                <option value="temu" <?= $product['platform']=='temu'?'selected':'' ?>>Temu</option>
                <option value="hepsiburada" <?= $product['platform']=='hepsiburada'?'selected':'' ?>>Hepsiburada</option>
                <option value="other" <?= $product['platform']=='other'?'selected':'' ?>>Other</option>
            </select>
        </div>
        <!-- Temu ilk indirme kampanyası alanları -->
        <div class="mb-3 temu-fields" id="temu_fields_box" style="display:<?= $product['platform']=='temu'?'block':'none' ?>;">
          <label class="form-label">
            <input type="checkbox" name="first_order_active" id="first_order_active" value="1" <?= (!empty($product['first_order_active']) ? 'checked' : '') ?>> Temu İlk İndirme Fiyatı Aktif
          </label>
          <div style="margin-top:7px;">
            <label class="form-label">Temu İlk İndirme Fiyatı (₺)</label>
            <input type="number" step="0.01" name="first_order_price" class="form-control" value="<?= htmlspecialchars($product['first_order_price'] ?? '') ?>">
            <div class="form-text" style="font-size:0.98em;">
              Bu alanı doldurursanız, ürün kartlarında <b>sadece Temu'da ilk kez sipariş verenler için geçerli</b> fiyat etiketi gösterilecektir.<br>
              Fiyat, orijinal fiyattan küçük olmalıdır.
            </div>
          </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Current Images</label><br>
            <?php foreach($imgList as $img): ?>
                <?php if($img && $img != 'default.jpg'): ?>
                <label>
                    <input type="checkbox" name="delete_imgs[]" value="<?= htmlspecialchars($img) ?>" class="img-del-checkbox"> 
                    <img src="../uploads/<?= htmlspecialchars($img) ?>" class="edit-img" alt="photo">
                    Delete
                </label>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="mb-3"><label class="form-label">Add Images</label>
            <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.bmp,.tiff" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
        <a href="products.php" class="btn btn-link">Back</a>
    </form>
</div>
<script>
    toggleCampaign();
    calculatePercent();
    toggleTemuFields();
    document.querySelector('[name="platform"]').addEventListener('change', toggleTemuFields);
</script>
</body>
</html>