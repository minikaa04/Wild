<?php
// admin/comments_mod.php
require_once __DIR__ . '/_auth_check.php';

// Yorum Sil
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: ' . URL_ROOT . '/admin/comments_mod.php?deleted=1'); exit;
}

// Onay Durumu Toggle
if (isset($_GET['toggle'])) {
    $rid = (int)$_GET['toggle'];
    $cur = $pdo->prepare("SELECT is_approved FROM reviews WHERE id=?"); $cur->execute([$rid]);
    $ap = (int)$cur->fetchColumn();
    $pdo->prepare("UPDATE reviews SET is_approved=? WHERE id=?")->execute([$ap ? 0 : 1, $rid]);
    header('Location: ' . URL_ROOT . '/admin/comments_mod.php'); exit;
}

$reviews = $pdo->query("
    SELECT r.*, CONCAT(u.first_name,' ',u.last_name) as user_name, u.email,
           p.title as product_title
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    JOIN products p ON p.id = r.product_id
    ORDER BY r.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html><html lang="tr" data-theme="<?= $_COOKIE['wild_theme']??'light' ?>">
<head><meta charset="UTF-8"><title>Wild Admin – Yorumlar</title>
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
    <a href="<?= URL_ROOT ?>/admin/orders_track.php">📦 Siparişler</a>
    <a href="<?= URL_ROOT ?>/admin/comments_mod.php" class="active">💬 Yorumlar</a>
    <a href="<?= URL_ROOT ?>/index.php">🌐 Siteye Dön</a>
    <a href="<?= URL_ROOT ?>/ajax/auth.php?action=logout" style="color:#f87171;">🚪 Çıkış</a>
</aside>
<main class="admin-main">
    <h1 style="font-size:22px;font-weight:800;margin-bottom:18px;">💬 Yorum Moderasyonu</h1>
    <?php if (isset($_GET['deleted'])): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:6px;margin-bottom:14px;">✅ Yorum silindi.</div>
    <?php endif; ?>

    <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($reviews as $r): ?>
    <div style="background:var(--surface);border-radius:var(--radius-md);padding:18px;
                box-shadow:var(--shadow-sm);border:1px solid var(--border);
                <?= !$r['is_approved'] ? 'border-left:4px solid var(--danger);' : '' ?>">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;">
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                    <span style="font-weight:700;font-size:14px;"><?= htmlspecialchars($r['user_name']) ?></span>
                    <span style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($r['email']) ?></span>
                    <span style="color:#f59e0b;"><?= str_repeat('★',$r['rating']) ?><?= str_repeat('☆',5-$r['rating']) ?></span>
                    <span style="font-size:12px;color:var(--text-muted);"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                    <?php if (!$r['is_approved']): ?>
                    <span style="background:#ffebee;color:var(--danger);font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">❌ Onaysız</span>
                    <?php else: ?>
                    <span style="background:#e8f5e9;color:var(--success);font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">✅ Onaylı</span>
                    <?php endif; ?>
                </div>
                <p style="font-size:12.5px;color:var(--primary);margin-bottom:6px;">🛍️ <?= htmlspecialchars($r['product_title']) ?></p>
                <p style="font-size:14px;color:var(--text);line-height:1.6;"><?= htmlspecialchars($r['comment_text']) ?></p>
                <?php if ($r['photo_url']): ?>
                <div style="margin-top:8px;">
                    <img src="<?= htmlspecialchars($r['photo_url']) ?>" alt="Yorum görseli"
                         style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--border);cursor:pointer;"
                         onclick="window.open(this.src,'_blank')"
                         onerror="this.style.display='none'">
                </div>
                <?php endif; ?>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;">
                <a href="?toggle=<?= $r['id'] ?>"
                   style="padding:6px 12px;background:<?= $r['is_approved']?'#fff3e0':'#e8f5e9' ?>;
                          color:<?= $r['is_approved']?'#e56200':'var(--success)' ?>;
                          border-radius:6px;font-size:12.5px;font-weight:700;text-align:center;">
                    <?= $r['is_approved'] ? '🚫 Gizle' : '✅ Onayla' ?>
                </a>
                <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Bu yorumu silmek istediğinize emin misiniz?')"
                   style="padding:6px 12px;background:#ffebee;color:var(--danger);border-radius:6px;font-size:12.5px;font-weight:700;text-align:center;">
                    🗑️ Sil
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</main>
</div>
</body></html>
