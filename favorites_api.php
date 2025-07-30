<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// Debug için, geçici olarak açabilirsin
// file_put_contents('fav-debug.log', date("Y-m-d H:i:s").' - '.json_encode($_SESSION)."\n", FILE_APPEND);

$supported_langs = ['tr', 'en'];
$lang = $_POST['lang'] ?? $_GET['lang'] ?? ($_SESSION['lang'] ?? 'tr');
if (!in_array($lang, $supported_langs)) $lang = 'tr';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    $msg = $lang === 'en'
        ? 'You must be logged in to add to favorites.'
        : 'Favorilere eklemek için giriş yapmalısınız.';
    echo json_encode(['success'=>false, 'error'=>'login_required', 'message'=>$msg]);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($action === "toggle" && $product_id) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id=? AND product_id=?");
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND product_id=?")->execute([$user_id, $product_id]);
        echo json_encode(['success'=>true, 'fav'=>false]);
    } else {
        $pdo->prepare("INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?,?)")->execute([$user_id, $product_id]);
        echo json_encode(['success'=>true, 'fav'=>true]);
    }
    exit;
}

if ($action === "list") {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id=?");
    $stmt->execute([$user_id]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success'=>true, 'favorites'=>$ids]);
    exit;
}

echo json_encode(['success'=>false, 'error'=>'unknown_action']);