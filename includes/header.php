<?php
// includes/header.php
// Tüm sayfalara include edilen sabit üst menü

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db.php';

// Sepet sayısı
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart_items WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = (int)$stmt->fetchColumn();
} elseif (isset($_SESSION['guest_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart_items WHERE session_id = ?");
    $stmt->execute([$_SESSION['guest_id']]);
    $cart_count = (int)$stmt->fetchColumn();
}

// Giriş yapan kullanıcı
$is_logged   = isset($_SESSION['user_id']);
$is_admin    = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$user_name   = $is_logged ? explode(' ', $_SESSION['user_name'] ?? 'Hesabım')[0] : 'Giriş Yap';

// Tema
$theme = $_COOKIE['wild_theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wild – Türkiye'nin Yeni Nesil E-Ticaret Platformu. Milyonlarca ürün, hızlı teslimat.">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | Wild' : 'Wild – Alışverişin Yeni Adresi' ?></title>
    <link rel="stylesheet" href="<?= URL_ROOT ?>/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛒</text></svg>">
</head>
<body>

<!-- ═══════════════════════════════════
     SABİT HEADER
═══════════════════════════════════ -->
<header id="site-header">
    <div class="container header-inner">

        <!-- Hamburger Menü -->
        <button id="menu-toggle" aria-label="Menü">☰</button>

        <!-- Logo -->
        <a href="<?= URL_ROOT ?>/index.php" class="header-logo">
            <span class="logo-icon">🛒</span>
            <span class="logo-text">Wi<span>ld</span></span>
        </a>

        <!-- Arama Çubuğu -->
        <form class="header-search" action="<?= URL_ROOT ?>/search.php" method="GET" role="search">
            <input type="text" name="q" id="search-input"
                   placeholder="Ürün, marka veya kategori ara..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                   autocomplete="off">
            <button type="submit" aria-label="Ara">🔍</button>
        </form>

        <!-- Sağ Aksiyonlar -->
        <div class="header-actions">

            <!-- Adres / Teslimat -->
            <div style="position:relative;">
                <button class="hdr-btn" id="btn-address" aria-haspopup="true" aria-expanded="false">
                    <span class="hdr-icon">📍</span>
                    <span>Adres</span>
                </button>
                <div class="hdr-dropdown" id="address-dropdown">
                    <h4 style="color:var(--text);margin-bottom:10px;font-size:15px;">📦 Teslimat Bilgisi</h4>
                    <p style="font-size:13.5px;color:var(--text-muted);line-height:1.6;">
                        Teslimat süresi satıcıdan satıcıya farklılık göstermektedir.<br>
                        <strong>Maksimum teslimat süresi 2 haftayı geçmez.</strong>
                    </p>
                    <?php if ($is_logged): ?>
                        <hr style="margin:14px 0;border-color:var(--border);">
                        <p style="font-size:13px;color:var(--text-muted);margin-bottom:8px;">Kayıtlı Adresleriniz:</p>
                        <?php
                        $adr = $pdo->prepare("SELECT city, district, full_address FROM addresses WHERE user_id = ? LIMIT 3");
                        $adr->execute([$_SESSION['user_id']]);
                        $addrs = $adr->fetchAll();
                        if ($addrs): foreach ($addrs as $a): ?>
                            <div style="font-size:13px;padding:8px;background:var(--surface-2);border-radius:6px;margin-bottom:6px;">
                                📍 <?= htmlspecialchars("{$a['city']} / {$a['district']} – {$a['full_address']}") ?>
                            </div>
                        <?php endforeach; else: ?>
                            <p style="font-size:13px;color:var(--text-light);">Kayıtlı adres bulunamadı.</p>
                        <?php endif; ?>
                        <a href="/profile.php#addresses" class="btn btn-outline btn-full" style="margin-top:12px;font-size:13px;">Adres Ekle / Düzenle</a>
                    <?php else: ?>
                        <a href="#" onclick="openModal('modal-login');return false;" class="btn btn-primary btn-full" style="margin-top:14px;font-size:13px;">Giriş Yap</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hesap -->
            <div style="position:relative;">
                <button class="hdr-btn" id="btn-account">
                    <span class="hdr-icon">👤</span>
                    <span><?= htmlspecialchars($user_name) ?></span>
                </button>
                <div class="hdr-dropdown" id="account-dropdown">
                    <?php if ($is_logged): ?>
                        <p style="font-weight:600;font-size:15px;color:var(--text);margin-bottom:4px;">
                            Merhaba, <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?> 👋
                        </p>
                        <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <a href="/profile.php" class="btn btn-outline">👤 Profilim</a>
                            <a href="/profile.php?tab=orders" class="btn btn-outline">📦 Siparişlerim</a>
                            <a href="/profile.php?tab=wishlist" class="btn btn-outline">❤️ Favorilerim</a>
                            <?php if ($is_admin): ?>
                            <a href="/admin/index.php" class="btn btn-danger">⚙️ Yönetim Paneli</a>
                            <?php endif; ?>
                            <a href="/ajax/auth.php?action=logout" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger);">🚪 Çıkış Yap</a>
                        </div>
                    <?php else: ?>
                        <p style="font-size:14px;color:var(--text);margin-bottom:14px;">Hesabınıza giriş yapın veya üye olun.</p>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <button onclick="openModal('modal-login')" class="btn btn-primary">Giriş Yap</button>
                            <button onclick="openModal('modal-login');switchTab('tab-register')" class="btn btn-outline">Üye Ol</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sepet -->
            <a href="<?= URL_ROOT ?>/cart.php" class="hdr-btn" style="text-decoration:none;" id="btn-cart">
                <span class="hdr-icon" style="position:relative;">
                    🛒
                    <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </span>
                <span>Sepet</span>
            </a>
        </div><!-- /header-actions -->
    </div><!-- /header-inner -->
</header>

<!-- ═══════════════════════════════════
     MEGA MENÜ
═══════════════════════════════════ -->
<div id="mega-menu-overlay"></div>
<nav id="mega-menu" aria-label="Kategoriler">
    <?php
    $cats = $pdo->query("SELECT id, parent_id, name, type FROM categories ORDER BY parent_id ASC, id ASC")->fetchAll();
    $main_cats = array_filter($cats, fn($c) => $c['parent_id'] === null);
    $sub_cats  = array_filter($cats, fn($c) => $c['parent_id'] !== null);

    foreach ($main_cats as $cat):
        $subs = array_filter($sub_cats, fn($s) => $s['parent_id'] == $cat['id']);
        $has_subs = !empty($subs);
    ?>
        <div class="mega-cat-item" data-cat-id="<?= $cat['id'] ?>" onclick="toggleSub(<?= $cat['id'] ?>)">
            <a href="/search.php?cat=<?= $cat['id'] ?>" style="flex:1;">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
            <?php if ($has_subs): ?><span class="cat-arrow">›</span><?php endif; ?>
        </div>
        <?php if ($has_subs): ?>
        <div class="mega-sub" id="sub-<?= $cat['id'] ?>">
            <?php foreach ($subs as $sub): ?>
                <a href="/search.php?cat=<?= $sub['id'] ?>">
                    <?= htmlspecialchars($sub['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<!-- Login Modal (her sayfada hazır) -->
<?php require_once __DIR__ . '/../components/login_modal.php'; ?>

<!-- Toast Kap -->
<div id="toast-container"></div>

<!-- Global JS -->
<script>const URL_ROOT = '<?= URL_ROOT ?>';</script>
<script src="<?= URL_ROOT ?>/js/main.js"></script>
