<?php
// admin/orders_track.php
require_once __DIR__ . '/_auth_check.php';

// Durum & Kargo Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $cargo  = trim($_POST['cargo_tracking'] ?? '');
    $pdo->prepare("UPDATE orders SET status=?, cargo_tracking_no=? WHERE id=?")
        ->execute([$status, $cargo ?: null, $oid]);
    header('Location: ' . URL_ROOT . '/admin/orders_track.php?updated=1'); exit;
}

$orders = $pdo->query("
    SELECT o.*, CONCAT(u.first_name,' ',u.last_name) as customer, u.email,
           a.city, a.full_address,
           COUNT(oi.id) as item_count
    FROM orders o
    JOIN users u ON u.id = o.user_id
    LEFT JOIN addresses a ON a.id = o.delivery_address_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html><html lang="tr" data-theme="<?= $_COOKIE['wild_theme']??'light' ?>">
<head><meta charset="UTF-8"><title>Wild Admin – Siparişler</title>
<link rel="stylesheet" href="<?= URL_ROOT ?>/css/style.css">
<style>
.admin-layout{display:grid;grid-template-columns:220px 1fr;min-height:100vh}
.admin-sidebar{background:var(--header-bg);color:#fff;position:sticky;top:0;height:100vh;overflow-y:auto}
.admin-sidebar .logo{padding:22px 18px;font-size:18px;font-weight:800;border-bottom:1px solid rgba(255,255,255,.1)}
.admin-sidebar a{display:flex;align-items:center;gap:10px;padding:13px 18px;color:rgba(255,255,255,.8);font-size:14px;border-bottom:1px solid rgba(255,255,255,.06);transition:.18s}
.admin-sidebar a:hover,.admin-sidebar a.active{background:rgba(255,255,255,.1);color:#fff}
.admin-main{padding:28px;background:var(--bg)}
</style></head><body>
<div class="admin-layout">
<aside class="admin-sidebar">
    <div class="logo">⚙️ Wild Admin</div>
    <a href="<?= URL_ROOT ?>/admin/index.php">📊 Dashboard</a>
    <a href="<?= URL_ROOT ?>/admin/products_manage.php">🛍️ Ürünler</a>
    <a href="<?= URL_ROOT ?>/admin/users_manage.php">👥 Kullanıcılar</a>
    <a href="<?= URL_ROOT ?>/admin/orders_track.php" class="active">📦 Siparişler</a>
    <a href="<?= URL_ROOT ?>/admin/comments_mod.php">💬 Yorumlar</a>
    <a href="<?= URL_ROOT ?>/index.php">🌐 Siteye Dön</a>
    <a href="<?= URL_ROOT ?>/ajax/auth.php?action=logout" style="color:#f87171;">🚪 Çıkış</a>
</aside>
<main class="admin-main">
    <h1 style="font-size:22px;font-weight:800;margin-bottom:18px;">📦 Sipariş ve Lojistik Yönetimi</h1>
    <?php if (isset($_GET['updated'])): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:6px;margin-bottom:14px;">✅ Sipariş güncellendi.</div>
    <?php endif; ?>

    <!-- Güncelleme Modal -->
    <div id="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:var(--surface);border-radius:var(--radius-lg);padding:28px;max-width:420px;width:100%;box-shadow:var(--shadow-lg);">
            <h3 style="margin-bottom:16px;">📦 Sipariş Güncelle</h3>
            <form method="POST">
                <input type="hidden" name="order_id" id="modal-order-id">
                <div class="form-group">
                    <label>Sipariş Durumu</label>
                    <select name="status" id="modal-status" class="form-control">
                        <option value="hazirlanıyor">🔧 Hazırlanıyor</option>
                        <option value="kargoda">🚚 Kargoda</option>
                        <option value="ulasti">✅ Teslim Edildi</option>
                        <option value="iptal">❌ İptal Edildi</option>
                        <option value="tamamlandi">✅ Tamamlandı</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kargo Takip Numarası</label>
                    <input type="text" name="cargo_tracking" id="modal-cargo" class="form-control" placeholder="Takip No">
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" name="update_order" class="btn btn-primary btn-full">Kaydet</button>
                    <button type="button" onclick="document.getElementById('order-modal').style.display='none'" class="btn btn-outline btn-full">İptal</button>
                </div>
            </form>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($orders as $o):
        $status_cfg = [
            'hazirlanıyor' => ['🔧','Hazırlanıyor','#f59e0b','#fff8e1'],
            'kargoda'      => ['🚚','Kargoda','var(--primary)','#e8f0fe'],
            'ulasti'       => ['✅','Teslim Edildi','var(--success)','#e8f5e9'],
            'iptal'        => ['❌','İptal Edildi','var(--danger)','#ffebee'],
            'tamamlandi'   => ['✅','Tamamlandı','var(--success)','#e8f5e9'],
        ];
        [$ico,$lbl,$clr,$bg] = $status_cfg[$o['status']] ?? ['❓','Bilinmiyor','gray','#f5f5f5'];
    ?>
    <div style="background:var(--surface);border-radius:var(--radius-md);padding:18px;
                box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                    <span style="font-weight:800;font-size:15px;">#<?= $o['id'] ?></span>
                    <span style="background:<?= $bg ?>;color:<?= $clr ?>;font-size:12px;font-weight:700;padding:3px 12px;border-radius:20px;">
                        <?= $ico ?> <?= $lbl ?>
                    </span>
                </div>
                <p style="font-size:13.5px;font-weight:600;"><?= htmlspecialchars($o['customer']) ?> – <span style="color:var(--text-muted);font-weight:400;"><?= htmlspecialchars($o['email']) ?></span></p>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px;">📍 <?= htmlspecialchars($o['city'].' / '.$o['full_address']) ?></p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">📅 <?= date('d M Y H:i', strtotime($o['created_at'])) ?> – <?= $o['item_count'] ?> ürün</p>
                <?php if ($o['cargo_tracking_no']): ?>
                <p style="font-size:12px;color:var(--info);margin-top:2px;">🚛 Kargo: <?= htmlspecialchars($o['cargo_tracking_no']) ?></p>
                <?php endif; ?>
            </div>
            <div style="text-align:right;">
                <p style="font-size:20px;font-weight:800;color:var(--primary);"><?= number_format($o['total_amount'],0,',','.') ?> ₺</p>
                <button onclick="openOrderModal(<?= $o['id'] ?>,'<?= $o['status'] ?>','<?= addslashes($o['cargo_tracking_no']??'') ?>')"
                        class="btn btn-primary" style="font-size:13px;padding:8px 16px;margin-top:8px;">
                    ✏️ Güncelle
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</main>
</div>
<script>
function openOrderModal(id, status, cargo) {
    document.getElementById('modal-order-id').value = id;
    document.getElementById('modal-status').value   = status;
    document.getElementById('modal-cargo').value    = cargo;
    document.getElementById('order-modal').style.display = 'flex';
}
</script>
</body></html>
