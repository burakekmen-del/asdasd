<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_POST['comment_id']) || !isset($_POST['reason'])) {
    header("Location: ".$_SERVER['HTTP_REFERER']); exit;
}
$user_id = $_SESSION['user_id'];
$comment_id = intval($_POST['comment_id']);
$reason = trim($_POST['reason']);
if(!$comment_id || $reason=='') { header("Location: ".$_SERVER['HTTP_REFERER']); exit; }

$stmt = $pdo->prepare("INSERT INTO comment_reports (comment_id, user_id, reason, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$comment_id, $user_id, $reason]);
header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
?>