<?php
// ajax/get_product_variant.php
// Renk varyasyonu değiştiğinde JSON ile ürün verisi döner

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$variant_id = (int)($_GET['variant_id'] ?? 0);
if (!$variant_id) { echo json_encode(['success'=>false]); exit; }

$stmt = $pdo->prepare("
    SELECT pv.id, pv.product_id, pv.color_name, pv.color_hex, pv.main_image,
           COALESCE(pv.price_override, p.price) as price,
           p.original_price, p.title, p.brand, p.sku, p.rating, p.seller_rating,
           CONCAT(pv.sku_suffix,'') as sku_suffix
    FROM product_variants pv
    JOIN products p ON p.id = pv.product_id
    WHERE pv.id = ?
");
$stmt->execute([$variant_id]);
$data = $stmt->fetch();

if (!$data) { echo json_encode(['success'=>false]); exit; }

echo json_encode([
    'success'       => true,
    'variant_id'    => $data['id'],
    'color_name'    => $data['color_name'],
    'main_image'    => $data['main_image'],
    'price'         => (float)$data['price'],
    'price_fmt'     => number_format($data['price'], 0, ',', '.'),
    'original_price'=> $data['original_price'] ? (float)$data['original_price'] : null,
    'original_fmt'  => $data['original_price'] ? number_format($data['original_price'], 0, ',', '.') : null,
    'title'         => $data['title'],
    'brand'         => $data['brand'],
    'sku'           => $data['sku'] . ($data['sku_suffix'] ? '-'.$data['sku_suffix'] : ''),
    'rating'        => (float)$data['rating'],
    'seller_rating' => (float)$data['seller_rating'],
]);
