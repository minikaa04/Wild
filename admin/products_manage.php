<?php
// admin/products_manage.php
require_once __DIR__ . '/_auth_check.php';

$msg = '';

// Silme
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_GET['delete']]);
    $msg = ['ok','Ürün silindi.'];
    header('Location: ' . URL_ROOT . '/admin/products_manage.php?deleted=1'); exit;
}

// Stok toggle
if (isset($_GET['toggle_stock'])) {
    $pid = (int)$_GET['toggle_stock'];
    $cur = $pdo->prepare("SELECT stock FROM products WHERE id=?"); $cur->execute([$pid]);
    $stock = (int)$cur->fetchColumn();
    $pdo->prepare("UPDATE products SET stock=? WHERE id=?")->execute([$stock > 0 ? 0 : 100, $pid]);
    header('Location: ' . URL_ROOT . '/admin/products_manage.php'); exit;
}

// İndirim uygula
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['apply_discount'])) {
    $pid = (int)$_POST['product_id'];
    $disc = (float)$_POST['discount_pct'];
    $cur_p = $pdo->prepare("SELECT price, original_price FROM products WHERE id=?");
    $cur_p->execute([$pid]);
    $row = $cur_p->fetch();
    $orig = $row['original_price'] ?: $row['price'];
    $new_price = round($orig * (1 - $disc/100), 2);
    $pdo->prepare("UPDATE products SET price=?, original_price=? WHERE id=?")->execute([$new_price, $orig, $pid]);
    $msg = ['ok', "%{$disc} indirim uygulandı."];
}

// Ürünleri listele
$products = $pdo->query("
    SELECT p.*, c.name as cat_name,
           (SELECT main_image FROM product_variants WHERE product_id=p.id LIMIT 1) as img
    FROM products p JOIN categories c ON c.id=p.category_id
    ORDER BY p.id DESC
")->fetchAll();

$page_title = 'Ürün Yönetimi';
?>
<!DOCTYPE html><html lang="tr" data-theme="<?= $_COOKIE['wild_theme']??'light' ?>">
<head><meta charset="UTF-8"><title>Wild Admin – Ürünler</title>
<link rel="stylesheet" href="<?= URL_ROOT ?>/css/style.css">
<style>
.admin-layout{display:grid;grid-template-columns:220px 1fr;min-height:100vh}
.admin-sidebar{background:var(--header-bg);color:#fff;position:sticky;top:0;height:100vh;overflow-y:auto}
.admin-sidebar .logo{padding:22px 18px;font-size:18px;font-weight:800;border-bottom:1px solid rgba(255,255,255,.1)}
.admin-sidebar a{display:flex;align-items:center;gap:10px;padding:13px 18px;color:rgba(255,255,255,.8);font-size:14px;border-bottom:1px solid rgba(255,255,255,.06);transition:.18s}
.admin-sidebar a:hover,.admin-sidebar a.active{background:rgba(255,255,255,.1);color:#fff}
.admin-main{padding:28px;background:var(--bg)}
table{width:100%;border-collapse:collapse;font-size:13.5px}
th{text-align:left;padding:11px 10px;background:var(--surface-2);color:var(--text-muted);font-weight:600;border-bottom:2px solid var(--border)}
td{padding:10px;border-bottom:1px solid var(--border);vertical-align:middle}
tr:hover{background:var(--surface-2)}
</style></head><body>
<div class="admin-layout">
<aside class="admin-sidebar">
    <div class="logo">⚙️ Wild Admin</div>
    <a href="<?= URL_ROOT ?>/admin/index.php">📊 Dashboard</a>
    <a href="<?= URL_ROOT ?>/admin/products_manage.php" class="active">🛍️ Ürünler</a>
    <a href="<?= URL_ROOT ?>/admin/users_manage.php">👥 Kullanıcılar</a>
    <a href="<?= URL_ROOT ?>/admin/orders_track.php">📦 Siparişler</a>
    <a href="<?= URL_ROOT ?>/admin/comments_mod.php">💬 Yorumlar</a>
    <a href="<?= URL_ROOT ?>/index.php">🌐 Siteye Dön</a>
    <a href="<?= URL_ROOT ?>/ajax/auth.php?action=logout" style="color:#f87171;">🚪 Çıkış</a>
</aside>
<main class="admin-main">
    <h1 style="font-size:22px;font-weight:800;margin-bottom:18px;">🛍️ Ürün Yönetimi</h1>

    <?php if (isset($_GET['deleted'])): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:6px;margin-bottom:14px;">✅ Ürün silindi.</div>
    <?php endif; ?>
    <?php if ($msg): ?>
    <div style="background:<?= $msg[0]==='ok'?'#e8f5e9':'#ffebee' ?>;color:<?= $msg[0]==='ok'?'#2e7d32':'#c62828' ?>;padding:12px 16px;border-radius:6px;margin-bottom:14px;">
        <?= $msg[0]==='ok'?'✅':'⚠️' ?> <?= htmlspecialchars($msg[1]) ?></div>
    <?php endif; ?>

    <!-- İndirim Formu (Popup) -->
    <div id="discount-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:var(--surface);border-radius:var(--radius-lg);padding:28px;max-width:360px;width:100%;">
            <h3 style="margin-bottom:14px;">💸 İndirim Uygula</h3>
            <form method="POST">
                <input type="hidden" name="product_id" id="disc-pid">
                <div class="form-group">
                    <label>İndirim Oranı (%)</label>
                    <input type="number" name="discount_pct" class="form-control" min="1" max="90" value="10" required>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" name="apply_discount" class="btn btn-danger btn-full">Uygula</button>
                    <button type="button" onclick="document.getElementById('discount-modal').style.display='none'" class="btn btn-outline btn-full">İptal</button>
                </div>
            </form>
        </div>
    </div>

    <div style="background:var(--surface);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:14px;color:var(--text-muted);"><?= count($products) ?> ürün listeleniyor</span>
        </div>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr>
                <th>Görsel</th><th>Ürün Adı</th><th>SKU</th><th>Fiyat</th><th>Stok</th><th>Kategori</th><th>İşlemler</th>
            </tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <div style="width:48px;height:56px;border-radius:6px;overflow:hidden;background:var(--surface-2);">
                        <img src="<?= htmlspecialchars($p['img']??'') ?>" alt="" style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/48x56?text=?'">
                    </div>
                </td>
                <td>
                    <p style="font-weight:600;font-size:13.5px;"><?= htmlspecialchars($p['title']) ?></p>
                    <p style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($p['brand']??'') ?></p>
                </td>
                <td style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($p['sku']) ?></td>
                <td>
                    <span style="font-weight:700;color:var(--primary);"><?= number_format($p['price'],0,',','.') ?> ₺</span>
                    <?php if ($p['original_price']): ?>
                    <br><span style="font-size:11px;text-decoration:line-through;color:var(--text-light);"><?= number_format($p['original_price'],0,',','.') ?> ₺</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="color:<?= $p['stock']>0?'var(--success)':'var(--danger)' ?>;font-weight:700;">
                        <?= $p['stock']>0 ? '✅ Stokta ('.$p['stock'].')' : '❌ Tükendi' ?>
                    </span>
                </td>
                <td style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($p['cat_name']) ?></td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="<?= URL_ROOT ?>/product.php?id=<?= $p['id'] ?>" target="_blank"
                           style="padding:5px 10px;background:var(--primary-light);color:var(--primary);border-radius:4px;font-size:12px;font-weight:600;">👁️ Görüntüle</a>
                        <a href="?toggle_stock=<?= $p['id'] ?>"
                           style="padding:5px 10px;background:#fff3e0;color:#e56200;border-radius:4px;font-size:12px;font-weight:600;">🔄 Stok</a>
                        <button onclick="openDiscount(<?= $p['id'] ?>)"
                                style="padding:5px 10px;background:#fce4ec;color:#c62828;border-radius:4px;font-size:12px;font-weight:600;border:none;cursor:pointer;">💸 İndirim</button>
                        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')"
                           style="padding:5px 10px;background:#ffebee;color:var(--danger);border-radius:4px;font-size:12px;font-weight:600;">🗑️ Sil</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>
</div>
<script>
function openDiscount(pid) {
    document.getElementById('disc-pid').value = pid;
    document.getElementById('discount-modal').style.display = 'flex';
}
</script>
</body></html>
