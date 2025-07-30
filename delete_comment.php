<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : 0;
$product_id = 0;

if ($comment_id > 0) {
    // Yorumun bağlı olduğu ürünü bul
    $stmt = $pdo->prepare("SELECT product_id FROM product_comments WHERE id=?");
    $stmt->execute([$comment_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) $product_id = intval($row['product_id']);

    // Yorumu sil
    $pdo->prepare("DELETE FROM product_comments WHERE id=?")->execute([$comment_id]);
    // Şikayet kaydını sil
    $pdo->prepare("DELETE FROM comment_reports WHERE comment_id=?")->execute([$comment_id]);
    $msg = "Yorum başarıyla silindi.";
} else {
    $msg = "Hatalı istek.";
}

// Yönlendirme
if ($product_id > 0) {
    header("Location: ../product.php?id=$product_id&msg=" . urlencode($msg));
} else {
    header("Location: dashboard.php?msg=" . urlencode($msg));
}
exit;
?>