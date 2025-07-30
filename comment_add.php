<?php
// Güvenli oturum başlat
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Bilinmeyen hata'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Giriş yapmalısınız.';
    echo json_encode($response); exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$comment = trim($_POST['comment'] ?? '');
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

if (!$product_id || $comment === '' || $rating < 1 || $rating > 5) {
    $response['message'] = 'Eksik veya hatalı veri.';
    echo json_encode($response); exit;
}

// Ürün var mı kontrolü
$stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
$stmt->execute([$product_id]);
if (!$stmt->fetchColumn()) {
    $response['message'] = 'Ürün bulunamadı.';
    echo json_encode($response); exit;
}

// Aynı kullanıcı aynı ürüne kısa sürede tekrar yorum yapmasın (örnek: 1dk)
$stmt = $pdo->prepare('SELECT created_at FROM comments WHERE user_id = ? AND product_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$user_id, $product_id]);
$last = $stmt->fetch(PDO::FETCH_ASSOC);
if ($last && strtotime($last['created_at']) > time() - 60) {
    $response['message'] = 'Çok sık yorum yapıyorsunuz. Lütfen biraz bekleyin.';
    echo json_encode($response); exit;
}

// Yorum ekle
$stmt = $pdo->prepare('INSERT INTO comments (user_id, product_id, comment, rating, created_at) VALUES (?, ?, ?, ?, NOW())');
$ok = $stmt->execute([$user_id, $product_id, $comment, $rating]);
if ($ok) {
    $response['success'] = true;
    $response['message'] = 'Yorumunuz eklendi.';
} else {
    $response['message'] = 'Yorum eklenemedi.';
}
echo json_encode($response);
