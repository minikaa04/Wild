<?php
// admin/users_manage.php
require_once __DIR__ . '/_auth_check.php';

// Ban/Unban
if (isset($_GET['toggle_ban'])) {
    $uid = (int)$_GET['toggle_ban'];
    $cur = $pdo->prepare("SELECT is_banned FROM users WHERE id=?"); $cur->execute([$uid]);
    $banned = (int)$cur->fetchColumn();
    $pdo->prepare("UPDATE users SET is_banned=? WHERE id=?")->execute([$banned ? 0 : 1, $uid]);
    header('Location: ' . URL_ROOT . '/admin/users_manage.php'); exit;
}

// Rol Değiştir
if (isset($_GET['toggle_role'])) {
    $uid = (int)$_GET['toggle_role'];
    $cur = $pdo->prepare("SELECT role FROM users WHERE id=?"); $cur->execute([$uid]);
    $role = $cur->fetchColumn();
    $new_role = $role === 'admin' ? 'user' : 'admin';
    $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$new_role, $uid]);
    header('Location: ' . URL_ROOT . '/admin/users_manage.php'); exit;
}

$users = $pdo->query("
    SELECT u.*, COUNT(o.id) as order_count
    FROM users u LEFT JOIN orders o ON o.user_id = u.id
    GROUP BY u.id ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html><html lang="tr" data-theme="<?= $_COOKIE['wild_theme']??'light' ?>">
<head><meta charset="UTF-8"><title>Wild Admin – Kullanıcılar</title>
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
    <a href="<?= URL_ROOT ?>/admin/products_manage.php">🛍️ Ürünler</a>
    <a href="<?= URL_ROOT ?>/admin/users_manage.php" class="active">👥 Kullanıcılar</a>
    <a href="<?= URL_ROOT ?>/admin/orders_track.php">📦 Siparişler</a>
    <a href="<?= URL_ROOT ?>/admin/comments_mod.php">💬 Yorumlar</a>
    <a href="<?= URL_ROOT ?>/index.php">🌐 Siteye Dön</a>
    <a href="<?= URL_ROOT ?>/ajax/auth.php?action=logout" style="color:#f87171;">🚪 Çıkış</a>
</aside>
<main class="admin-main">
    <h1 style="font-size:22px;font-weight:800;margin-bottom:18px;">👥 Kullanıcı Yönetimi</h1>
    <div style="background:var(--surface);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;">
        <div style="overflow-x:auto;">
        <table>
            <thead><tr>
                <th>#</th><th>Ad Soyad</th><th>E-posta</th><th>Telefon</th><th>Rol</th><th>Sipariş</th><th>Durum</th><th>İşlemler</th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td style="font-size:12px;color:var(--text-muted);"><?= $u['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);color:#fff;
                                    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(mb_substr($u['first_name'],0,1)) ?>
                        </div>
                        <span style="font-weight:600;"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></span>
                    </div>
                </td>
                <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
                <td style="font-size:13px;"><?= htmlspecialchars($u['phone']??'-') ?></td>
                <td>
                    <span style="background:<?= $u['role']==='admin'?'#fce4ec':'var(--primary-light)' ?>;
                                 color:<?= $u['role']==='admin'?'var(--danger)':'var(--primary)' ?>;
                                 font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                        <?= $u['role'] === 'admin' ? '👑 Admin' : '👤 User' ?>
                    </span>
                </td>
                <td style="text-align:center;font-weight:700;"><?= $u['order_count'] ?></td>
                <td>
                    <?php if ($u['is_banned']): ?>
                    <span style="background:#ffebee;color:var(--danger);font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">🚫 Banlı</span>
                    <?php else: ?>
                    <span style="background:#e8f5e9;color:var(--success);font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">✅ Aktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="?toggle_ban=<?= $u['id'] ?>"
                           style="padding:5px 10px;background:<?= $u['is_banned']?'#e8f5e9':'#ffebee' ?>;
                                  color:<?= $u['is_banned']?'var(--success)':'var(--danger)' ?>;
                                  border-radius:4px;font-size:12px;font-weight:600;">
                            <?= $u['is_banned'] ? '✅ Aktif Et' : '🚫 Banla' ?>
                        </a>
                        <a href="?toggle_role=<?= $u['id'] ?>"
                           onclick="return confirm('Rolü değiştirmek istediğinize emin misiniz?')"
                           style="padding:5px 10px;background:#e3f2fd;color:var(--primary);border-radius:4px;font-size:12px;font-weight:600;">
                            <?= $u['role']==='admin' ? '👤 User Yap' : '👑 Admin Yap' ?>
                        </a>
                        <?php else: ?>
                        <span style="font-size:12px;color:var(--text-light);">(Sen)</span>
                        <?php endif; ?>
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
</body></html>
