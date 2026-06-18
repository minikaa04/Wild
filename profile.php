<?php
// profile.php — Kullanıcı Kontrol Kabinesi

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . URL_ROOT . '/index.php?login_required=1');
    exit;
}

$user_id  = $_SESSION['user_id'];
$tab      = $_GET['tab'] ?? 'orders';

// Kullanıcı verisi
$uq = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$uq->execute([$user_id]);
$user = $uq->fetch();

$page_title = 'Profilim';
require_once __DIR__ . '/includes/header.php';

// Profil güncelleme (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fname = trim($_POST['first_name'] ?? '');
    $lname = trim($_POST['last_name']  ?? '');
    $phone = trim($_POST['phone']      ?? '');
    if ($fname && $lname) {
        $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=? WHERE id=?")
            ->execute([$fname, $lname, $phone, $user_id]);
        $_SESSION['user_name'] = "$fname $lname";
        $user['first_name']    = $fname;
        $user['last_name']     = $lname;
        $profile_msg = ['ok', 'Profil bilgileri güncellendi.'];
    }
}
?>

<main>
<div class="container" style="padding-top:26px;">
<div style="display:grid;grid-template-columns:240px 1fr;gap:24px;align-items:start;">

<!-- SOL: Navigasyon -->
<aside style="position:sticky;top:84px;">
    <div style="background:var(--surface);border-radius:var(--radius-lg);overflow:hidden;
                box-shadow:var(--shadow-md);border:1px solid var(--border);">

        <!-- Avatar -->
        <div style="background:linear-gradient(135deg,var(--primary),#1558b0);padding:24px;text-align:center;">
            <div style="width:68px;height:68px;border-radius:50%;background:rgba(255,255,255,0.2);
                        display:flex;align-items:center;justify-content:center;font-size:28px;
                        font-weight:800;color:#fff;margin:0 auto 10px;">
                <?= strtoupper(mb_substr($user['first_name'], 0, 1)) ?>
            </div>
            <p style="color:#fff;font-weight:700;font-size:15px;"><?= htmlspecialchars("{$user['first_name']} {$user['last_name']}") ?></p>
            <p style="color:rgba(255,255,255,0.7);font-size:12px;margin-top:2px;"><?= htmlspecialchars($user['email']) ?></p>
        </div>

        <!-- Menü -->
        <?php
        $nav_items = [
            ['orders',   '📦', 'Siparişlerim'],
            ['purchases','🧾', 'Satın Almalarım'],
            ['wishlist', '❤️', 'Favorilerim'],
            ['settings', '⚙️', 'Hesap Ayarları'],
            ['returns',  '🔄', 'İadelerim'],
        ];
        ?>
        <?php foreach ($nav_items as [$key, $icon, $label]): ?>
        <a href="<?= URL_ROOT ?>/profile.php?tab=<?= $key ?>"
           style="display:flex;align-items:center;gap:12px;padding:13px 18px;font-size:14px;font-weight:500;
                  border-top:1px solid var(--border);color:var(--text);transition:.18s;
                  background:<?= $tab === $key ? 'var(--primary-light)' : 'transparent' ?>;
                  color:<?= $tab === $key ? 'var(--primary)' : 'var(--text)' ?>;font-weight:<?= $tab === $key ? '700' : '500' ?>;">
            <?= $icon ?> <?= $label ?>
        </a>
        <?php endforeach; ?>

        <a href="<?= URL_ROOT ?>/ajax/auth.php?action=logout"
           style="display:flex;align-items:center;gap:12px;padding:13px 18px;font-size:14px;
                  border-top:2px solid var(--border);color:var(--danger);">
            🚪 Çıkış Yap
        </a>
    </div>
</aside>

<!-- SAĞ: İçerik -->
<div>

<?php if ($tab === 'orders'): ?>
<!-- SİPARİŞLERİM -->
<h2 style="font-size:20px;font-weight:700;margin-bottom:18px;">📦 Siparişlerim</h2>
<?php
$orders_q = $pdo->prepare("
    SELECT o.*, a.city, a.full_address,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    LEFT JOIN addresses a ON a.id = o.delivery_address_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$orders_q->execute([$user_id]);
$orders = $orders_q->fetchAll();
?>
<?php if (empty($orders)): ?>
<div style="text-align:center;padding:50px;background:var(--surface);border-radius:var(--radius-lg);">
    <div style="font-size:56px;margin-bottom:12px;">📭</div>
    <h3 style="color:var(--text-muted);">Henüz siparişiniz yok</h3>
    <a href="<?= URL_ROOT ?>/index.php" class="btn btn-primary" style="margin-top:16px;">Alışverişe Başla</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:14px;">
<?php foreach ($orders as $ord):
    $status_map = [
        'hazirlanıyor' => ['🔧','Hazırlanıyor','#f59e0b'],
        'kargoda'      => ['🚚','Kargoda','var(--primary)'],
        'ulasti'       => ['✅','Teslim Edildi','var(--success)'],
        'iptal'        => ['❌','İptal Edildi','var(--danger)'],
        'tamamlandi'   => ['✅','Tamamlandı','var(--success)'],
    ];
    [$st_icon, $st_label, $st_color] = $status_map[$ord['status']] ?? ['❓','Bilinmiyor','gray'];
?>
<div style="background:var(--surface);border-radius:var(--radius-md);padding:18px;
            box-shadow:var(--shadow-sm);border:1px solid var(--border);">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <p style="font-weight:700;font-size:15px;">Sipariş #<?= $ord['id'] ?></p>
            <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
                📅 <?= date('d M Y', strtotime($ord['created_at'])) ?> –
                📦 <?= $ord['item_count'] ?> ürün
            </p>
            <p style="font-size:13px;color:var(--text-muted);margin-top:2px;">
                📍 <?= htmlspecialchars($ord['city'] . ' / ' . $ord['full_address']) ?>
            </p>
        </div>
        <div style="text-align:right;">
            <span style="background:<?= $st_color ?>22;color:<?= $st_color ?>;font-size:13px;font-weight:700;
                         padding:5px 14px;border-radius:20px;display:inline-block;margin-bottom:6px;">
                <?= $st_icon ?> <?= $st_label ?>
            </span>
            <p style="font-size:17px;font-weight:800;color:var(--primary);"><?= number_format($ord['total_amount'],0,',','.') ?> ₺</p>
        </div>
    </div>
    <?php if ($ord['cargo_tracking_no']): ?>
    <p style="font-size:12.5px;color:var(--text-muted);margin-top:8px;">🚛 Kargo Takip: <?= htmlspecialchars($ord['cargo_tracking_no']) ?></p>
    <?php endif; ?>
    <!-- Durum çubuğu -->
    <?php
    $steps = ['hazirlanıyor','kargoda','ulasti'];
    $cur_step = array_search($ord['status'], $steps);
    ?>
    <?php if ($cur_step !== false): ?>
    <div style="margin-top:14px;display:flex;align-items:center;gap:0;">
        <?php foreach ($steps as $si => $skey):
            $done = $si <= $cur_step;
            $labels = ['Hazırlanıyor','Kargoda','Teslim'];
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
            <div style="width:28px;height:28px;border-radius:50%;
                        background:<?= $done ? 'var(--primary)' : 'var(--border)' ?>;
                        display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;">
                <?= $done ? '✓' : ($si+1) ?>
            </div>
            <span style="font-size:11px;color:<?= $done ? 'var(--primary)' : 'var(--text-muted)' ?>;font-weight:<?= $done ? '700' : '400' ?>;">
                <?= $labels[$si] ?>
            </span>
        </div>
        <?php if ($si < count($steps)-1): ?>
        <div style="flex:1;height:2px;background:<?= $si < $cur_step ? 'var(--primary)' : 'var(--border)' ?>;margin-bottom:18px;"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif ($tab === 'purchases'): ?>
<!-- SATIN ALMALARIM -->
<h2 style="font-size:20px;font-weight:700;margin-bottom:18px;">🧾 Satın Almalarım</h2>
<?php
$items_q = $pdo->prepare("
    SELECT oi.*, p.title, p.brand, pv.main_image
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    WHERE o.user_id = ? AND o.status IN ('ulasti','tamamlandi')
    GROUP BY oi.id
    ORDER BY o.created_at DESC
");
$items_q->execute([$user_id]);
$past_items = $items_q->fetchAll();
?>
<div style="display:grid;gap:12px;">
<?php if (empty($past_items)): ?>
<p style="color:var(--text-muted);padding:30px;text-align:center;">Tamamlanan alışveriş bulunamadı.</p>
<?php else: ?>
<?php foreach ($past_items as $pi): ?>
<div style="background:var(--surface);border-radius:var(--radius-md);padding:16px;
            display:flex;gap:14px;align-items:center;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
    <div style="width:68px;height:80px;border-radius:var(--radius-sm);overflow:hidden;flex-shrink:0;">
        <img src="<?= htmlspecialchars($pi['main_image'] ?? '') ?>" alt="" style="width:100%;height:100%;object-fit:cover;"
             onerror="this.src='https://via.placeholder.com/68x80?text=?'">
    </div>
    <div style="flex:1;">
        <p style="font-weight:600;font-size:14px;"><?= htmlspecialchars($pi['brand']?$pi['brand'].' – '.$pi['title']:$pi['title']) ?></p>
        <p style="font-size:13px;color:var(--text-muted);margin-top:2px;"><?= number_format($pi['price'],0,',','.') ?> ₺ × <?= $pi['quantity'] ?></p>
    </div>
    <button onclick="addToCart(<?= $pi['product_id'] ?>, null, null)" class="btn btn-outline" style="font-size:13px;padding:8px 14px;">
        🔁 Yeniden Sipariş
    </button>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php elseif ($tab === 'wishlist'): ?>
<!-- FAVORİLERİM -->
<h2 style="font-size:20px;font-weight:700;margin-bottom:18px;">❤️ Favorilerim</h2>
<?php
$wl_q = $pdo->prepare("
    SELECT w.size_selected, p.id, p.title, p.price, p.brand, pv.main_image
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    WHERE w.user_id = ?
    GROUP BY w.product_id
    ORDER BY w.added_at DESC
");
$wl_q->execute([$user_id]);
$wishlist_items = $wl_q->fetchAll();
?>
<?php if (empty($wishlist_items)): ?>
<div style="text-align:center;padding:50px;background:var(--surface);border-radius:var(--radius-lg);">
    <div style="font-size:56px;margin-bottom:12px;">🤍</div>
    <h3 style="color:var(--text-muted);">Favori listeniz boş</h3>
    <p style="color:var(--text-light);margin-top:6px;">Ürünlerdeki ❤️ ikonuna tıklayarak ekleyin.</p>
</div>
<?php else: ?>
<div class="grid-auto">
<?php foreach ($wishlist_items as $wi): ?>
<div class="product-card" onclick="window.location='<?= URL_ROOT ?>/product.php?id=<?= $wi['id'] ?>'">
    <div class="card-img-wrap">
        <img src="<?= htmlspecialchars($wi['main_image'] ?? '') ?>" alt="<?= htmlspecialchars($wi['title']) ?>"
             loading="lazy" onerror="this.src='https://via.placeholder.com/300x360?text=?'">
        <button class="btn-heart active" onclick="event.stopPropagation();toggleWishlist(this,<?= $wi['id'] ?>,false)">❤️</button>
    </div>
    <div class="card-body">
        <div class="card-price"><span class="price-now"><?= number_format($wi['price'],0,',','.') ?> ₺</span></div>
        <p class="card-title"><?= htmlspecialchars($wi['brand']?$wi['brand'].' – '.$wi['title']:$wi['title']) ?></p>
        <?php if ($wi['size_selected']): ?>
        <p style="font-size:12px;color:var(--text-muted);">Seçili Beden: <?= $wi['size_selected'] ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif ($tab === 'settings'): ?>
<!-- HESAP AYARLARI -->
<h2 style="font-size:20px;font-weight:700;margin-bottom:18px;">⚙️ Hesap Ayarları</h2>

<?php if (isset($profile_msg)): ?>
<div style="background:<?= $profile_msg[0]==='ok'?'#e8f5e9':'#ffebee' ?>;border-radius:var(--radius-sm);
            padding:12px 16px;margin-bottom:14px;color:<?= $profile_msg[0]==='ok'?'#2e7d32':'#c62828' ?>;font-size:14px;">
    <?= $profile_msg[0]==='ok'?'✅':'⚠️' ?> <?= htmlspecialchars($profile_msg[1]) ?>
</div>
<?php endif; ?>

<form method="POST" style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
                            box-shadow:var(--shadow-sm);border:1px solid var(--border);margin-bottom:18px;">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">👤 Kişisel Bilgiler</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
            <label>Ad</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Soyad</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label>E-posta</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly
               style="opacity:.6;cursor:not-allowed;">
    </div>
    <div class="form-group">
        <label>Telefon</label>
        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="05XX XXX XX XX">
    </div>
    <button type="submit" name="update_profile" class="btn btn-primary">💾 Kaydet</button>
</form>

<!-- Tema -->
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
            box-shadow:var(--shadow-sm);border:1px solid var(--border);margin-bottom:18px;">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">🎨 Tema Tercihi</h3>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <?php foreach ([['light','☀️','Açık (Varsayılan)'],['dark','🌙','Koyu'],['system','🖥️','Sistem']] as [$val,$icon,$lbl]): ?>
        <button onclick="setTheme('<?= $val ?>')"
                style="padding:10px 18px;border:2px solid var(--border);border-radius:var(--radius-sm);
                       background:var(--surface);cursor:pointer;font-size:14px;transition:.2s;font-family:inherit;"
                onmouseover="this.style.borderColor='var(--primary)'"
                onmouseout="this.style.borderColor='var(--border)'">
            <?= $icon ?> <?= $lbl ?>
        </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bildirimler -->
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
            box-shadow:var(--shadow-sm);border:1px solid var(--border);">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">🔔 Bildirim Tercileri</h3>
    <?php foreach ([['E-posta Bildirimleri','email_notif'],['SMS Bildirimleri','sms_notif'],['Kampanya ve İndirim Bildirimleri','promo_notif']] as [$lbl,$name]): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);">
        <span style="font-size:14px;"><?= $lbl ?></span>
        <label style="position:relative;width:44px;height:24px;cursor:pointer;">
            <input type="checkbox" checked style="opacity:0;width:0;height:0;">
            <span style="position:absolute;inset:0;background:var(--primary);border-radius:24px;"></span>
            <span style="position:absolute;left:3px;top:3px;width:18px;height:18px;background:#fff;border-radius:50%;"></span>
        </label>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif ($tab === 'returns'): ?>
<!-- İADELER -->
<h2 style="font-size:20px;font-weight:700;margin-bottom:18px;">🔄 İade Taleplerim</h2>
<div style="text-align:center;padding:60px;background:var(--surface);border-radius:var(--radius-lg);">
    <div style="font-size:56px;margin-bottom:12px;">🔄</div>
    <h3 style="color:var(--text-muted);">Aktif iade talebiniz bulunmuyor</h3>
    <p style="color:var(--text-light);margin-top:8px;">Siparişlerim sekmesinden iade başlatabilirsiniz.</p>
</div>
<?php endif; ?>

</div><!-- /sağ içerik -->
</div><!-- /grid -->
</div><!-- /container -->
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>document.body.dataset.loggedIn = '1';</script>
