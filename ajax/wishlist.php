<?php
// ajax/wishlist.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$action     = $_POST['action']     ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$size       = trim($_POST['size'] ?? '');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş gerekli.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($action === 'toggle') {
    // Var mı kontrol et
    $chk = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $chk->execute([$user_id, $product_id]);
    $exists = $chk->fetch();

    if ($exists) {
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'added' => false]);
    } else {
        $pdo->prepare("INSERT INTO wishlist (user_id, product_id, size_selected) VALUES (?,?,?)")->execute([$user_id, $product_id, $size ?: null]);
        echo json_encode(['success' => true, 'added' => true]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
