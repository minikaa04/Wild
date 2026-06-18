<?php
// ajax/cart_actions.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$action     = $_POST['action']     ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$variant_id = (int)($_POST['variant_id'] ?? 0) ?: null;
$size       = trim($_POST['size']       ?? '') ?: null;
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));

$user_id    = $_SESSION['user_id']  ?? null;
$session_id = $_SESSION['guest_id'] ?? null;

// Misafir session oluştur
if (!$user_id && !$session_id) {
    $session_id = session_id();
    $_SESSION['guest_id'] = $session_id;
}

function cartCount($pdo, $user_id, $session_id): int {
    if ($user_id) {
        $s = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id = ?");
        $s->execute([$user_id]);
    } else {
        $s = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE session_id = ?");
        $s->execute([$session_id]);
    }
    return (int)$s->fetchColumn();
}

// ─── EKLE
if ($action === 'add') {
    if ($user_id) {
        $chk = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=? AND (variant_id<=>?) AND (size_selected<=>?)");
        $chk->execute([$user_id, $product_id, $variant_id, $size]);
    } else {
        $chk = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE session_id=? AND product_id=? AND (variant_id<=>?) AND (size_selected<=>?)");
        $chk->execute([$session_id, $product_id, $variant_id, $size]);
    }
    $existing = $chk->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?")->execute([$quantity, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO cart_items (user_id, session_id, product_id, variant_id, size_selected, quantity) VALUES (?,?,?,?,?,?)")
            ->execute([$user_id, $user_id ? null : $session_id, $product_id, $variant_id, $size, $quantity]);
    }

    echo json_encode(['success' => true, 'cart_count' => cartCount($pdo, $user_id, $session_id)]);
    exit;
}

// ─── SİL
if ($action === 'remove') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item_id]);
    echo json_encode(['success' => true, 'cart_count' => cartCount($pdo, $user_id, $session_id)]);
    exit;
}

// ─── MİKTAR GÜNCELLE
if ($action === 'update') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    if ($quantity < 1) {
        $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item_id]);
    } else {
        $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?")->execute([$quantity, $item_id]);
    }
    echo json_encode(['success' => true, 'cart_count' => cartCount($pdo, $user_id, $session_id)]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
