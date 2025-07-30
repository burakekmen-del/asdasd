<?php
require_once 'config.php';
$id = intval($_GET['id'] ?? 0);
if($id<=0) { echo json_encode([]); exit; }
$row = $pdo->query("SELECT id, name_tr, name_en, images FROM products WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
if(!$row) { echo json_encode([]); exit; }
$lang = $_SESSION['lang'] ?? 'tr';
$name = $lang=='en' ? ($row['name_en'] ?: $row['name_tr']) : ($row['name_tr'] ?: $row['name_en']);
$imgList = !empty($row['images']) ? explode(',', $row['images']) : [];
$img = (!empty($imgList[0]) && file_exists('uploads/'.$imgList[0])) ? 'uploads/'.$imgList[0] : 'assets/images/default-product.webp';
echo json_encode(['id'=>$row['id'], 'name'=>$name, 'img'=>$img]);