<?php
require_once 'config.php';

header('Content-Type: application/json');

$username = trim($_GET['username'] ?? '');
$isAvailable = false;

if ($username && preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $isAvailable = !$stmt->fetch();
}

echo json_encode(['available' => $isAvailable]);