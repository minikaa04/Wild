<?php
// cart.php — Sepet Sayfası

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

$is_logged  = isset($_SESSION['user_id']);
$user_id    = $_SESSION['user_id']  ?? null;
$session_id = $_SESSION['guest_id'] ?? session_id();

// Sepet ürünlerini çek
if ($user_id) {
    $stmt = $pdo->prepare("
        SELECT ci.id as cart_item_id, ci.quantity, ci.size_selected,
               p.id as product_id, p.title, p.price, p.brand, p.delivery_days,
               COALESCE(pv.price_override, p.price) as final_price,
               pv.main_image, pv.color_name, pv.id as variant_id
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        LEFT JOIN product_variants pv ON pv.id = ci.variant_id
        WHERE ci.user_id = ?
        ORDER BY ci.added_at DESC
    ");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT ci.id as cart_item_id, ci.quantity, ci.size_selected,
               p.id as product_id, p.title, p.price, p.brand, p.delivery_days,
               COALESCE(pv.price_override, p.price) as final_price,
               pv.main_image, pv.color_name, pv.id as variant_id
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        LEFT JOIN product_variants pv ON pv.id = ci.variant_id
        WHERE ci.session_id = ?
        ORDER BY ci.added_at DESC
    ");
    $stmt->execute([$session_id]);
}
$items = $stmt->fetchAll();

$total = array_sum(array_map(fn($i) => $i['final_price'] * $i['quantity'], $items));
$page_title = 'Sepetim';

require_once __DIR__ . '/includes/header.php';
?>

<main>
<div class="container" style="padding-top:24px;">
<h1 style="font-size:22px;font-weight:700;margin-bottom:22px;">🛒 Sepetim</h1>

<?php if (empty($items)): ?>
<!-- BOŞ SEPET -->
<div style="text-align:center;padding:80px 20px;background:var(--surface);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
    <div style="font-size:72px;margin-bottom:20px;">🛒</div>
    <h2 style="font-size:20px;margin-bottom:8px;color:var(--text-muted);">Sepetiniz boş</h2>
    <p style="color:var(--text-light);margin-bottom:28px;">Alışverişe başlamak için ürünleri inceleyin.</p>
    <a href="<?= URL_ROOT ?>/index.php" class="btn btn-primary" style="font-size:16px;padding:13px 32px;">
        🛍️ Alışverişe Başla
    </a>
</div>

<?php else: ?>
<div style="display:grid;grid-template-columns:1fr 350px;gap:24px;align-items:start;">

    <!-- SOL: Ürün Listesi -->
    <div style="display:flex;flex-direction:column;gap:14px;" id="cart-items-list">
    <?php foreach ($items as $item): ?>
    <div class="cart-row" id="cart-row-<?= $item['cart_item_id'] ?>"
         style="background:var(--surface);border-radius:var(--radius-md);padding:18px;
                display:flex;gap:16px;align-items:center;box-shadow:var(--shadow-sm);
                border:1px solid var(--border);">

        <!-- Görsel -->
        <a href="<?= URL_ROOT ?>/product.php?id=<?= $item['product_id'] ?>" style="flex-shrink:0;">
            <div style="width:100px;height:120px;border-radius:var(--radius-sm);overflow:hidden;background:var(--surface-2);">
                <img src="<?= htmlspecialchars($item['main_image'] ?? '') ?>"
                     alt="<?= htmlspecialchars($item['title']) ?>"
                     style="width:100%;height:100%;object-fit:cover;"
                     onerror="this.src='https://via.placeholder.com/100x120?text=?'">
            </div>
        </a>

        <!-- Bilgiler -->
        <div style="flex:1;min-width:0;">
            <a href="<?= URL_ROOT ?>/product.php?id=<?= $item['product_id'] ?>"
               style="font-weight:600;font-size:15px;color:var(--text);display:block;margin-bottom:4px;
                      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($item['brand'] ? $item['brand'].' – '.$item['title'] : $item['title']) ?>
            </a>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                <?php if ($item['color_name']): ?>
                <span style="font-size:12.5px;background:var(--surface-2);padding:3px 10px;border-radius:20px;
                             color:var(--text-muted);border:1px solid var(--border);">
                    🎨 <?= htmlspecialchars($item['color_name']) ?>
                </span>
                <?php endif; ?>
                <?php if ($item['size_selected']): ?>
                <span style="font-size:12.5px;background:var(--surface-2);padding:3px 10px;border-radius:20px;
                             color:var(--text-muted);border:1px solid var(--border);">
                    📏 <?= htmlspecialchars($item['size_selected']) ?>
                </span>
                <?php endif; ?>
            </div>
            <p style="font-size:12px;color:var(--success);font-weight:500;">
                📦 <?= date('d M', strtotime("+{$item['delivery_days']} days")) ?>'e kadar teslimat
            </p>
        </div>

        <!-- Fiyat + Miktar + Sil -->
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px;flex-shrink:0;">
            <span style="font-size:20px;font-weight:800;color:var(--primary);">
                <?= number_format($item['final_price'] * $item['quantity'], 0, ',', '.') ?> ₺
            </span>
            <span style="font-size:13px;color:var(--text-muted);">
                (<?= number_format($item['final_price'], 0, ',', '.') ?> ₺ × <?= $item['quantity'] ?>)
            </span>

            <!-- Miktar kontrolü -->
            <div style="display:flex;align-items:center;gap:6px;">
                <button onclick="updateCartQty(<?= $item['cart_item_id'] ?>, <?= $item['quantity'] - 1 ?>)"
                        style="width:30px;height:30px;border:1.5px solid var(--border);border-radius:var(--radius-sm);
                               background:var(--surface);font-size:16px;cursor:pointer;
                               display:flex;align-items:center;justify-content:center;">−</button>
                <span id="qty-<?= $item['cart_item_id'] ?>" style="font-weight:700;min-width:24px;text-align:center;">
                    <?= $item['quantity'] ?>
                </span>
                <button onclick="updateCartQty(<?= $item['cart_item_id'] ?>, <?= $item['quantity'] + 1 ?>)"
                        style="width:30px;height:30px;border:1.5px solid var(--border);border-radius:var(--radius-sm);
                               background:var(--surface);font-size:16px;cursor:pointer;
                               display:flex;align-items:center;justify-content:center;">+</button>
            </div>

            <button onclick="removeCartItem(<?= $item['cart_item_id'] ?>)"
                    style="background:none;border:none;color:var(--danger);font-size:13px;
                           cursor:pointer;display:flex;align-items:center;gap:4px;">
                🗑️ Kaldır
            </button>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- SAĞ: Sipariş Özeti -->
    <div style="position:sticky;top:84px;">
        <div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
                    box-shadow:var(--shadow-lg);border:1.5px solid var(--border);">
            <h3 style="font-size:17px;font-weight:700;margin-bottom:18px;">📋 Sipariş Özeti</h3>

            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;">
                    <span style="color:var(--text-muted);">Ara Toplam (<?= count($items) ?> ürün)</span>
                    <span id="subtotal-val"><?= number_format($total, 0, ',', '.') ?> ₺</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;">
                    <span style="color:var(--text-muted);">Kargo</span>
                    <span style="color:var(--success);font-weight:600;">Ücretsiz</span>
                </div>
                <div style="border-top:2px solid var(--border);padding-top:12px;display:flex;
                            justify-content:space-between;font-size:18px;font-weight:800;">
                    <span>Toplam</span>
                    <span id="total-val" style="color:var(--primary);"><?= number_format($total, 0, ',', '.') ?> ₺</span>
                </div>
            </div>

            <a href="<?= URL_ROOT ?>/checkout.php" class="btn btn-primary btn-full" style="font-size:16px;padding:14px;text-align:center;">
                Siparişi Tamamla →
            </a>
            <a href="<?= URL_ROOT ?>/index.php" style="display:block;text-align:center;margin-top:12px;
               color:var(--text-muted);font-size:13.5px;">← Alışverişe Devam Et</a>
        </div>

        <!-- Güvenli Alışveriş -->
        <div style="margin-top:14px;background:var(--surface);border-radius:var(--radius-md);
                    padding:14px 16px;border:1px solid var(--border);">
            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--text-muted);">
                <span>🔒 256-bit SSL ile güvenli ödeme</span>
                <span>🔄 14 gün iade garantisi</span>
                <span>📦 Hızlı ve güvenilir kargo</span>
            </div>
        </div>
    </div>

</div>
<?php endif; ?>
</div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
document.body.dataset.loggedIn = '<?= $is_logged ? '1' : '0' ?>';

async function updateCartQty(itemId, newQty) {
    const fd = new FormData();
    fd.append('action',   'update');
    fd.append('item_id',  itemId);
    fd.append('quantity', newQty);

    const res  = await fetch(URL_ROOT . '/ajax/cart_actions.php', { method:'POST', body:fd });
    const data = await res.json();

    if (data.success) {
        if (newQty < 1) {
            document.getElementById('cart-row-'+itemId)?.remove();
        } else {
            document.getElementById('qty-'+itemId).textContent = newQty;
        }
        location.reload(); // Toplamı yenile
    }
}

async function removeCartItem(itemId) {
    const fd = new FormData();
    fd.append('action',  'remove');
    fd.append('item_id', itemId);

    const res  = await fetch(URL_ROOT . '/ajax/cart_actions.php', { method:'POST', body:fd });
    const data = await res.json();

    if (data.success) {
        const row = document.getElementById('cart-row-'+itemId);
        if (row) { row.style.opacity='0'; row.style.transform='translateX(30px)'; row.style.transition='.3s'; }
        setTimeout(() => location.reload(), 350);
    }
}
</script>
