<?php
// index.php — Wild Ana Sayfa

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

// ─── OTOMATİK KURULUM: 'products' tablosu yoksa database.sql çalıştır ───
try {
    $pdo->query("SELECT 1 FROM products LIMIT 1");
} catch (PDOException $e) {
    // Tablo yok → SQL dosyasını çalıştır
    $sql_file = __DIR__ . '/database.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        // Birden fazla komutu ayrı ayrı çalıştır
        $pdo->exec($sql);
    }
    // Kurulum bitti, sayfayı yenile
    header('Location: index.php');
    exit;
}
// ─────────────────────────────────────────────────────────────────────────

$page_title = 'Wild – Alışverişin Yeni Adresi';
$is_logged  = isset($_SESSION['user_id']);

// Carousel için ilk 5 ürünü çek (büyük görsel)
$banner_prods = $pdo->query("
    SELECT p.id, p.title, p.price, p.brand, pv.main_image
    FROM products p
    JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
    ORDER BY p.rating DESC
    LIMIT 5
")->fetchAll();

// Ana ürün listesi (tüm ürünler, rastgele sıralı)
$products = $pdo->query("
    SELECT p.id, p.title, p.price, p.original_price, p.brand,
           p.rating, p.delivery_days, c.name as cat_name, c.type as cat_type,
           pv.main_image, pv.color_name
    FROM products p
    JOIN categories c ON c.id = p.category_id
    JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
    ORDER BY RAND()
    LIMIT 40
")->fetchAll();

// Wishlist (giriş yapmış kullanıcı)
$wishlist_ids = [];
if ($is_logged) {
    $wl = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wl->execute([$_SESSION['user_id']]);
    $wishlist_ids = $wl->fetchAll(PDO::FETCH_COLUMN);
}

// Yardımcı: teslimat tarihi
function deliveryDate(int $days): string {
    return date('d M', strtotime("+{$days} days"));
}

// Yardımcı: yıldız render
function stars(float $r): string {
    $full = floor($r); $half = ($r - $full) >= 0.5 ? 1 : 0; $empty = 5 - $full - $half;
    return str_repeat('★', $full) . str_repeat('⯨', $half) . str_repeat('☆', $empty);
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
<div class="container">

<!-- ═══════ HERO CAROUSEL ═══════ -->
<div class="hero-carousel" id="hero-carousel">
    <div class="slides">
        <?php
        $slide_data = [
            ['title' => 'Yaz Koleksiyonu Burada!',   'sub' => 'Çiçek desenli elbiseler ve serin tişörtlerde büyük fırsatlar.',   'color' => '#1565c0'],
            ['title' => 'Elektronik Şöleni',          'sub' => 'Kulaklık, şarj cihazı ve daha fazlası – sınırlı stok!',          'color' => '#0f172a'],
            ['title' => 'Spor Sezonuna Hazır Ol',     'sub' => 'Koşu ayakkabısı ve spor ekipmanlarında kampanya başladı.',       'color' => '#1a4d2e'],
            ['title' => 'Güzellik & Bakım Dünyası',   'sub' => 'C vitamini serumu ve doğal bakım setleri şimdi indirimde.',      'color' => '#4a044e'],
            ['title' => 'Kış Montu Sezonu Açıldı',    'sub' => 'Sıcak tutan kabanlar ve puffer montlarda büyük fırsat.',         'color' => '#7c2d12'],
        ];
        foreach ($slide_data as $i => $slide):
            $img = $banner_prods[$i]['main_image'] ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&q=80';
            $pid = $banner_prods[$i]['id'] ?? 1;
        ?>
        <div class="slide" style="background-image:url('<?= htmlspecialchars($img) ?>');">
            <div class="slide-overlay" style="background:linear-gradient(90deg,<?= $slide['color'] ?>dd 0%,transparent 70%);">
                <div class="slide-content">
                    <h2><?= htmlspecialchars($slide['title']) ?></h2>
                    <p><?= htmlspecialchars($slide['sub']) ?></p>
                    <a href="<?= URL_ROOT ?>/product.php?id=<?= $pid ?>" class="btn-slide">Hemen Keşfet →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-prev" aria-label="Önceki">‹</button>
    <button class="carousel-next" aria-label="Sonraki">›</button>
    <div class="carousel-dots"></div>
</div>

<!-- ═══════ KATEGORİ HIZLI ERİŞİM ═══════ -->
<div style="display:flex;gap:10px;overflow-x:auto;padding:10px 0 18px;scrollbar-width:none;-ms-overflow-style:none;">
    <?php
    $quick_cats = [
        ['name'=>'Kadın',    'icon'=>'👗', 'id'=>1],
        ['name'=>'Erkek',    'icon'=>'👔', 'id'=>2],
        ['name'=>'Çocuk',    'icon'=>'🧒', 'id'=>3],
        ['name'=>'Elektronik','icon'=>'📱','id'=>6],
        ['name'=>'Güzellik', 'icon'=>'💄', 'id'=>5],
        ['name'=>'Spor',     'icon'=>'⚽', 'id'=>7],
        ['name'=>'Kitap',    'icon'=>'📚', 'id'=>8],
        ['name'=>'Ev',       'icon'=>'🏠', 'id'=>4],
    ];
    foreach ($quick_cats as $qc):
    ?>
    <a href="<?= URL_ROOT ?>/search.php?cat=<?= $qc['id'] ?>"
       style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:88px;
              background:var(--surface);border-radius:var(--radius-md);padding:14px 12px;
              box-shadow:var(--shadow-sm);text-decoration:none;color:var(--text);
              transition:.2s;border:1.5px solid var(--border);"
       onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-light)'"
       onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--surface)'">
        <span style="font-size:28px;"><?= $qc['icon'] ?></span>
        <span style="font-size:12px;font-weight:600;"><?= $qc['name'] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ═══════ ÜRÜN LİSTESİ ═══════ -->
<div class="section-title">
    <h2>🔥 Günün Fırsatları</h2>
    <a href="<?= URL_ROOT ?>/search.php">Tümünü Gör →</a>
</div>

<div class="grid-auto" id="product-grid">
<?php foreach ($products as $p):
    $in_wishlist  = in_array($p['id'], $wishlist_ids);
    $is_cloth     = in_array($p['cat_type'], ['adult','child']);
    $discount_pct = $p['original_price'] ? round((1 - $p['price'] / $p['original_price']) * 100) : 0;
    $delivery     = deliveryDate($p['delivery_days']);
?>
<div class="product-card" onclick="window.location='<?= URL_ROOT ?>/product.php?id=<?= $p['id'] ?>'">
    <div class="card-img-wrap">
        <img
            src="<?= htmlspecialchars($p['main_image']) ?>"
            alt="<?= htmlspecialchars($p['title']) ?>"
            loading="lazy"
            onerror="this.src='https://via.placeholder.com/400x500?text=Görsel+Yok'">

        <?php if ($discount_pct > 0): ?>
        <span class="badge-discount">-<?= $discount_pct ?>%</span>
        <?php endif; ?>

        <button
            class="btn-heart <?= $in_wishlist ? 'active' : '' ?>"
            data-product-id="<?= $p['id'] ?>"
            onclick="event.stopPropagation(); toggleWishlist(this, <?= $p['id'] ?>, <?= $is_cloth ? 'true' : 'false' ?>)"
            aria-label="Favorilere Ekle">
            <?= $in_wishlist ? '❤️' : '🤍' ?>
        </button>
    </div>

    <div class="card-body">
        <div class="card-rating">
            <span class="stars"><?= stars((float)$p['rating']) ?></span>
            <span><?= number_format((float)$p['rating'], 1) ?></span>
        </div>

        <div class="card-price">
            <span class="price-now"><?= number_format($p['price'], 0, ',', '.') ?> ₺</span>
            <?php if ($p['original_price']): ?>
            <span class="price-old"><?= number_format($p['original_price'], 0, ',', '.') ?> ₺</span>
            <?php endif; ?>
        </div>

        <p class="card-title"><?= htmlspecialchars($p['brand'] ? $p['brand'].' – '.$p['title'] : $p['title']) ?></p>

        <div class="card-footer-row">
            <span class="card-delivery">📦 <?= $delivery ?>'e kadar</span>
            <button
                class="btn-add-cart"
                onclick="event.stopPropagation(); addToCart(<?= $p['id'] ?>, null, null)"
                aria-label="Sepete Ekle">
                🛒 <span>Ekle</span>
            </button>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div><!-- /grid-auto -->

</div><!-- /container -->
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
document.body.dataset.loggedIn = '<?= $is_logged ? '1' : '0' ?>';
</script>
