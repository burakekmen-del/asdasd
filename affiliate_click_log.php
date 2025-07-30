<?php
// Affiliate tıklama loglama endpointi
require_once 'config.php';
header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$platform = trim($_POST['platform'] ?? '');
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!$product_id || $platform === '') {
    echo json_encode(['success' => false, 'message' => 'Eksik veri']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO affiliate_clicks (product_id, platform, user_id, ip, user_agent, clicked_at) VALUES (?, ?, ?, ?, ?, NOW())');
$stmt->execute([$product_id, $platform, $user_id, $ip, $ua]);

echo json_encode(['success' => true]);
