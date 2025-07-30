<?php
// AJAX ile yorumları JSON olarak döner
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if (!$product_id) {
    echo json_encode(['comments'=>[],'isLoggedIn'=>isset($_SESSION['user_id'])]);
    exit;
}
$stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.product_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$product_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($comments as &$c) {
    $c['comment'] = htmlspecialchars($c['comment']);
    $c['username'] = htmlspecialchars($c['username'] ?? 'Kullanıcı');
    $c['rating'] = (int)($c['rating'] ?? 0);
    $c['id'] = (int)$c['id'];
}
echo json_encode(['comments'=>$comments,'isLoggedIn'=>isset($_SESSION['user_id'])]);
