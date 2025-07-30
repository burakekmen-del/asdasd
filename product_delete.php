<?php
require_once '../config.php';
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if($id > 0) {
    // Ürün görsellerini sil
    $stmt = $pdo->prepare("SELECT images FROM products WHERE id=?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if($p && !empty($p['images'])) {
        $imgs = array_map('trim', explode(',', $p['images']));
        foreach($imgs as $img) {
            if($img && $img != 'default.jpg' && file_exists("../uploads/$img")) {
                unlink("../uploads/$img");
            }
        }
    }
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
}
header("Location: products.php");
exit;
?>