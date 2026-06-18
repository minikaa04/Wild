<?php
// admin/index.php — Admin Dashboard (İstatistikler)
require_once __DIR__ . '/_auth_check.php';

// İstatistikler
$total_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$total_orders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue  = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'iptal'")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_reviews  = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

// En çok satılan ürünler
$top_sellers = $pdo->query("
    SELECT p.id, p.title, p.brand, SUM(oi.quantity) as total_sold,
           SUM(oi.quantity * oi.price) as revenue, pv.main_image
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 8
")->fetchAll();

// Son siparişler
$recent_orders = $pdo->query("
    SELECT o.id, o.total_amount, o.status, o.created_at,
           CONCAT(u.first_name,' ',u.last_name) as customer
    FROM orders o JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC LIMIT 8
")->fetchAll();

$page_title = 'Admin Paneli';
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?= $_COOKIE['wild_theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Wild – Admin Paneli</title>
    <link rel="stylesheet" href="<?= URL_ROOT ?>/css/style.css">
    <style>
        .admin-layout { display:grid; grid-template-columns:220px 1fr; min-height:100vh; }
        .admin-sidebar { background:var(--header-bg); color:#fff; padding:0; position:sticky; top:0; height:100vh; overflow-y:auto; }
        .admin-sidebar .logo { padding:22px 18px; font-size:18px; font-weight:800; border-bottom:1px solid rgba(255,255,255,.1); }
        .admin-sidebar a { display:flex; align-items:center; gap:10px; padding:13px 18px; color:rgba(255,255,255,.8); font-size:14px; border-bottom:1px solid rgba(255,255,255,.06); transition:.18s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background:rgba(255,255,255,.1); color:#fff; }
        .admin-main { padding:28px; background:var(--bg); }
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:28px; }
        .stat-card { background:var(--surface); border-radius:var(--radius-md); padding:20px; box-shadow:var(--shadow-sm); border:1px solid var(--border); }
        .stat-card .val { font-size:30px; font-weight:800; color:var(--primary); }
        .stat-card .lbl { font-size:13px; color:var(--text-muted); margin-top:4px; }
        .stat-card .icon { font-size:28px; margin-bottom:8px; }
    </style>
</head>
<body>

<div class="admin-layout">
<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="logo">⚙️ Wild Admin</div>
    <a href="<?= URL_ROOT ?>/admin/index.php" class="active">📊 Dashboard</a>
    <a href="<?= URL_ROOT ?>/admin/products_manage.php">🛍️ Ürünler</a>
    <a href="<?= URL_ROOT ?>/admin/users_manage.php">👥 Kullanıcılar</a>
    <a href="<?= URL_ROOT ?>/admin/orders_track.php">📦 Siparişler</a>
    <a href="<?= URL_ROOT ?>/admin/comments_mod.php">💬 Yorumlar</a>
    <a href="<?= URL_ROOT ?>/index.php" style="margin-top:auto;border-top:1px solid rgba(255,255,255,.15);">🌐 Siteye Dön</a>
    <a href="<?= URL_ROOT ?>/ajax/auth.php?action=logout" style="color:#f87171;">🚪 Çıkış</a>
</aside>

<!-- Ana İçerik -->
<main class="admin-main">
    <h1 style="font-size:22px;font-weight:800;margin-bottom:22px;">📊 Dashboard</h1>

    <!-- İstatistik Kartları -->
    <div class="stat-grid">
        <div class="stat-card"><div class="icon">👥</div><div class="val"><?= number_format($total_users) ?></div><div class="lbl">Toplam Kullanıcı</div></div>
        <div class="stat-card"><div class="icon">📦</div><div class="val"><?= number_format($total_orders) ?></div><div class="lbl">Toplam Sipariş</div></div>
        <div class="stat-card"><div class="icon">💰</div><div class="val"><?= number_format($total_revenue,0,',','.') ?> ₺</div><div class="lbl">Toplam Ciro</div></div>
        <div class="stat-card"><div class="icon">🛍️</div><div class="val"><?= number_format($total_products) ?></div><div class="lbl">Toplam Ürün</div></div>
        <div class="stat-card"><div class="icon">💬</div><div class="val"><?= number_format($total_reviews) ?></div><div class="lbl">Toplam Yorum</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

    <!-- En Çok Satılanlar -->
    <div style="background:var(--surface);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">🏆 En Çok Satılan Ürünler</h3>
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <thead><tr style="border-bottom:2px solid var(--border);">
                <th style="text-align:left;padding:8px 6px;color:var(--text-muted);">Ürün</th>
                <th style="text-align:right;padding:8px 6px;color:var(--text-muted);">Satış</th>
                <th style="text-align:right;padding:8px 6px;color:var(--text-muted);">Ciro</th>
            </tr></thead>
            <tbody>
            <?php foreach ($top_sellers as $ts): ?>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px 6px;font-weight:500;"><?= htmlspecialchars(mb_substr($ts['title'],0,28).'...') ?></td>
                <td style="padding:8px 6px;text-align:right;color:var(--primary);font-weight:700;"><?= $ts['total_sold'] ?></td>
                <td style="padding:8px 6px;text-align:right;"><?= number_format($ts['revenue'],0,',','.') ?> ₺</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Son Siparişler -->
    <div style="background:var(--surface);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">🕐 Son Siparişler</h3>
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <thead><tr style="border-bottom:2px solid var(--border);">
                <th style="text-align:left;padding:8px 6px;color:var(--text-muted);">#</th>
                <th style="text-align:left;padding:8px 6px;color:var(--text-muted);">Müşteri</th>
                <th style="text-align:right;padding:8px 6px;color:var(--text-muted);">Tutar</th>
                <th style="text-align:right;padding:8px 6px;color:var(--text-muted);">Durum</th>
            </tr></thead>
            <tbody>
            <?php foreach ($recent_orders as $ro):
                $colors = ['hazirlanıyor'=>'#f59e0b','kargoda'=>'var(--primary)','ulasti'=>'var(--success)','iptal'=>'var(--danger)','tamamlandi'=>'var(--success)'];
                $clr = $colors[$ro['status']] ?? 'gray';
            ?>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px 6px;">#<?= $ro['id'] ?></td>
                <td style="padding:8px 6px;"><?= htmlspecialchars($ro['customer']) ?></td>
                <td style="padding:8px 6px;text-align:right;font-weight:700;"><?= number_format($ro['total_amount'],0,',','.') ?> ₺</td>
                <td style="padding:8px 6px;text-align:right;">
                    <span style="color:<?= $clr ?>;font-weight:700;font-size:12px;"><?= htmlspecialchars($ro['status']) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
</main>
</div>
<script src="<?= URL_ROOT ?>/js/main.js"></script>
</body></html>
