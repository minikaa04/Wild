<?php
// product.php — Ürün Detay Sayfası

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . URL_ROOT . '/index.php'); exit; }

// Ürünü çek
$stmt = $pdo->prepare("
    SELECT p.*, c.name as cat_name, c.type as cat_type, c.parent_id
    FROM products p
    JOIN categories c ON c.id = p.category_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: ' . URL_ROOT . '/index.php'); exit; }

// Varyasyonları çek
$vstmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
$vstmt->execute([$id]);
$variants = $vstmt->fetchAll();
$first_var = $variants[0] ?? null;

// Yorumları çek
$rstmt = $pdo->prepare("
    SELECT r.*, CONCAT(u.first_name,' ',u.last_name) as user_name
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC
    LIMIT 30
");
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll();

// Ortalama puan
$avg_rating = count($reviews) ? round(array_sum(array_column($reviews,'rating')) / count($reviews), 1) : $product['rating'];

// Benzer ürünler
$simstmt = $pdo->prepare("
    SELECT p.id, p.title, p.price, p.brand, p.original_price, pv.main_image
    FROM products p
    JOIN product_variants pv ON pv.product_id = p.id
    WHERE p.category_id = ? AND p.id != ?
    GROUP BY p.id
    ORDER BY RAND()
    LIMIT 10
");
$simstmt->execute([$product['category_id'], $id]);
$similar = $simstmt->fetchAll();

// Wishlist durumu
$is_logged   = isset($_SESSION['user_id']);
$in_wishlist = false;
if ($is_logged) {
    $wchk = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wchk->execute([$_SESSION['user_id'], $id]);
    $in_wishlist = (bool)$wchk->fetch();
}

$is_cloth  = in_array($product['cat_type'], ['adult','child']);
$disc_pct  = $product['original_price'] ? round((1 - $product['price'] / $product['original_price']) * 100) : 0;
$page_title = $product['title'];
$sizes = ['XS','S','M','L','XL','XXL','2XL'];

function stars(float $r, bool $full = false): string {
    $result = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($r >= $i) $result .= '★';
        elseif ($r >= $i - 0.5) $result .= '⯨';
        else $result .= '☆';
    }
    return $result;
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
<div class="container" style="padding-top:24px;">

<!-- ═══════ BREADCRUMB ═══════ -->
<nav style="font-size:13px;color:var(--text-muted);margin-bottom:18px;">
    <a href="<?= URL_ROOT ?>/index.php" style="color:var(--primary);">Ana Sayfa</a> ›
    <a href="<?= URL_ROOT ?>/search.php?cat=<?= $product['category_id'] ?>" style="color:var(--primary);"><?= htmlspecialchars($product['cat_name']) ?></a> ›
    <span><?= htmlspecialchars($product['title']) ?></span>
</nav>

<!-- ═══════ ÜRÜN ANA BLOK ═══════ -->
<div style="display:grid;grid-template-columns:1fr 1fr 320px;gap:28px;align-items:start;" id="product-main">

    <!-- SOL: Galeri -->
    <div style="position:sticky;top:84px;">
        <div id="main-img-wrap" style="border-radius:var(--radius-lg);overflow:hidden;background:var(--surface-2);
             aspect-ratio:3/4;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-md);">
            <img id="main-product-img"
                 src="<?= htmlspecialchars($first_var['main_image'] ?? '') ?>"
                 alt="<?= htmlspecialchars($product['title']) ?>"
                 style="width:100%;height:100%;object-fit:cover;transition:opacity .35s ease;"
                 onerror="this.src='https://via.placeholder.com/600x800?text=Görsel+Yok'">
        </div>
        <!-- Thumbnail bar -->
        <div style="display:flex;gap:8px;margin-top:12px;overflow-x:auto;padding-bottom:4px;">
            <?php foreach ($variants as $v): ?>
            <div onclick="selectVariant(<?= $v['id'] ?>)"
                 data-variant-id="<?= $v['id'] ?>"
                 class="thumb-btn"
                 style="width:72px;height:88px;flex-shrink:0;border-radius:var(--radius-sm);overflow:hidden;
                        cursor:pointer;border:2.5px solid transparent;transition:.2s;">
                <img src="<?= htmlspecialchars($v['main_image']) ?>"
                     alt="<?= htmlspecialchars($v['color_name']) ?>"
                     style="width:100%;height:100%;object-fit:cover;"
                     onerror="this.src='https://via.placeholder.com/72x88?text=?'">
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ORTA: Ürün Bilgileri -->
    <div>
        <!-- Marka -->
        <p style="color:var(--primary);font-weight:600;font-size:14px;margin-bottom:6px;">
            <?= htmlspecialchars($product['brand'] ?? '') ?>
        </p>

        <!-- Başlık -->
        <h1 id="product-title" style="font-size:22px;font-weight:700;line-height:1.35;margin-bottom:12px;">
            <?= htmlspecialchars($product['title']) ?>
        </h1>

        <!-- Puan & Yorum -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
            <span style="color:#f59e0b;font-size:18px;"><?= stars($avg_rating) ?></span>
            <span style="font-weight:700;font-size:15px;"><?= number_format($avg_rating,1) ?></span>
            <a href="#reviews" style="color:var(--text-muted);font-size:13.5px;"><?= count($reviews) ?> değerlendirme</a>
            <span style="color:var(--border);">|</span>
            <a href="#ask" style="color:var(--primary);font-size:13.5px;">Ürün Soruları</a>
        </div>

        <!-- Fiyat (orta panel — mobil görünüm) -->
        <div id="mid-price" style="display:flex;align-items:baseline;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <span id="price-display" style="font-size:28px;font-weight:800;color:var(--primary);">
                <?= number_format($product['price'],0,',','.') ?> ₺
            </span>
            <?php if ($product['original_price']): ?>
            <span id="price-old" style="font-size:16px;color:var(--text-light);text-decoration:line-through;">
                <?= number_format($product['original_price'],0,',','.') ?> ₺
            </span>
            <span style="background:var(--danger);color:#fff;font-size:13px;font-weight:700;
                         padding:3px 9px;border-radius:4px;">-%<?= $disc_pct ?></span>
            <?php endif; ?>
        </div>

        <!-- Renk Varyasyonları -->
        <?php if (count($variants) > 1): ?>
        <div style="margin-bottom:22px;">
            <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:10px;">
                RENK: <span id="selected-color" style="color:var(--text);font-weight:700;"><?= htmlspecialchars($first_var['color_name'] ?? '') ?></span>
            </p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ($variants as $v): ?>
                <div onclick="selectVariant(<?= $v['id'] ?>)"
                     data-variant-id="<?= $v['id'] ?>"
                     class="color-swatch"
                     title="<?= htmlspecialchars($v['color_name']) ?>"
                     style="width:44px;height:44px;border-radius:50%;cursor:pointer;
                            background-color:<?= htmlspecialchars($v['color_hex'] ?? '#ccc') ?>;
                            border:3px solid transparent;box-shadow:0 0 0 1px var(--border);
                            transition:.2s;overflow:hidden;position:relative;">
                    <img src="<?= htmlspecialchars($v['main_image']) ?>" alt=""
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <input type="hidden" id="selected-color" value="<?= htmlspecialchars($first_var['color_name'] ?? '') ?>">
        <?php endif; ?>

        <!-- Beden Seçimi -->
        <?php if ($is_cloth): ?>
        <div style="margin-bottom:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <p style="font-size:13px;font-weight:600;color:var(--text-muted);">BEDEN SEÇİN:</p>
                <a href="#" style="font-size:12.5px;color:var(--primary);">📏 Beden Tablosu</a>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;" id="size-grid">
                <?php foreach ($sizes as $sz): ?>
                <button onclick="selectSize(this,'<?= $sz ?>')"
                        data-size="<?= $sz ?>"
                        style="min-width:52px;padding:10px 8px;border:2px solid var(--border);
                               border-radius:var(--radius-sm);background:var(--surface);
                               font-size:14px;font-weight:600;cursor:pointer;transition:.2s;"
                        class="size-pick-btn">
                    <?= $sz ?>
                </button>
                <?php endforeach; ?>
            </div>
            <p id="size-error" style="display:none;color:var(--danger);font-size:12.5px;margin-top:8px;">
                ⚠️ Lütfen bir beden seçin.
            </p>
        </div>
        <?php endif; ?>

        <!-- Teknik Özellikler -->
        <div style="background:var(--surface-2);border-radius:var(--radius-md);padding:18px;margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">📋 Ürün Bilgileri</h3>
            <table style="width:100%;font-size:13.5px;border-collapse:collapse;">
                <tr>
                    <td style="padding:7px 0;color:var(--text-muted);width:50%;">Stok Kodu (SKU)</td>
                    <td id="sku-val" style="padding:7px 0;font-weight:500;"><?= htmlspecialchars($product['sku']) ?></td>
                </tr>
                <?php if ($product['composition']): ?>
                <tr style="border-top:1px solid var(--border);">
                    <td style="padding:7px 0;color:var(--text-muted);">Malzeme / Bileşim</td>
                    <td style="padding:7px 0;font-weight:500;"><?= htmlspecialchars($product['composition']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($product['gender']): ?>
                <tr style="border-top:1px solid var(--border);">
                    <td style="padding:7px 0;color:var(--text-muted);">Cinsiyet</td>
                    <td style="padding:7px 0;font-weight:500;"><?= htmlspecialchars(match($product['gender']) {
                        'male' => 'Erkek', 'female' => 'Kadın', 'child' => 'Çocuk', default => 'Unisex'
                    }) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($product['model_size']): ?>
                <tr style="border-top:1px solid var(--border);">
                    <td style="padding:7px 0;color:var(--text-muted);">Modelin Bedeni</td>
                    <td style="padding:7px 0;font-weight:500;"><?= htmlspecialchars($product['model_size']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($product['model_height']): ?>
                <tr style="border-top:1px solid var(--border);">
                    <td style="padding:7px 0;color:var(--text-muted);">Modelin Boyu</td>
                    <td style="padding:7px 0;font-weight:500;"><?= htmlspecialchars($product['model_height']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- İade Politikası -->
        <?php if ($product['return_policy']): ?>
        <div style="border:1.5px solid var(--success);border-radius:var(--radius-md);padding:14px 16px;
                    background:#f0fdf4;display:flex;gap:10px;align-items:flex-start;">
            <span style="font-size:20px;">🔄</span>
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--success);margin-bottom:2px;">İade Politikası</p>
                <p style="font-size:13px;color:#166534;"><?= htmlspecialchars($product['return_policy']) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- SAĞ: Yapışık Sipariş Paneli -->
    <div style="position:sticky;top:84px;">
        <div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
                    box-shadow:var(--shadow-lg);border:1.5px solid var(--border);">

            <!-- Fiyat -->
            <div style="margin-bottom:16px;">
                <span id="sidebar-price" style="font-size:32px;font-weight:800;color:var(--primary);">
                    <?= number_format($product['price'],0,',','.') ?> ₺
                </span>
                <?php if ($product['original_price']): ?>
                <div style="font-size:13px;color:var(--text-light);text-decoration:line-through;margin-top:2px;">
                    <?= number_format($product['original_price'],0,',','.') ?> ₺
                </div>
                <?php endif; ?>
            </div>

            <!-- Teslimat -->
            <div style="background:var(--primary-light);border-radius:var(--radius-sm);padding:12px 14px;margin-bottom:18px;">
                <p style="font-size:12.5px;color:var(--primary);font-weight:600;">📦 Tahmini Teslimat</p>
                <p style="font-size:14px;font-weight:700;color:var(--text);margin-top:2px;">
                    <?= date('d M', strtotime("+{$product['delivery_days']} days")) ?>'e kadar
                </p>
            </div>

            <!-- Butonlar -->
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                <button onclick="handleAddToCart()" class="btn btn-primary btn-full" style="font-size:16px;padding:14px;">
                    🛒 Sepete Ekle
                </button>
                <a href="<?= URL_ROOT ?>/checkout.php?buy_now=<?= $id ?>" onclick="return handleBuyNow(event)"
                   class="btn btn-accent btn-full" style="font-size:16px;padding:14px;text-align:center;">
                    ⚡ Hemen Satın Al
                </a>
            </div>

            <!-- Favori -->
            <button onclick="toggleWishlist(this, <?= $id ?>, <?= $is_cloth ? 'true':'false' ?>)"
                    class="btn btn-outline btn-full <?= $in_wishlist ? 'active' : '' ?>"
                    id="sidebar-heart" style="margin-bottom:18px;">
                <?= $in_wishlist ? '❤️ Favorilerde' : '🤍 Favorilere Ekle' ?>
            </button>

            <!-- Satıcı Bilgisi -->
            <div style="border-top:1px solid var(--border);padding-top:14px;">
                <p style="font-size:13px;font-weight:700;margin-bottom:6px;">🏪 <?= htmlspecialchars($product['brand'] ?? 'Satıcı') ?></p>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="color:#f59e0b;font-size:14px;">★</span>
                    <span style="font-weight:600;font-size:14px;"><?= number_format($product['seller_rating'],1) ?></span>
                    <span style="color:var(--text-muted);font-size:12.5px;">satıcı puanı</span>
                </div>
            </div>
        </div>
    </div>

</div><!-- /product-main -->

<!-- GİZLİ VERİLER -->
<input type="hidden" id="current-product-id" value="<?= $id ?>">
<input type="hidden" id="current-variant-id" value="<?= $first_var['id'] ?? '' ?>">
<input type="hidden" id="current-size" value="">
<input type="hidden" id="is-cloth" value="<?= $is_cloth ? '1' : '0' ?>">

<!-- ═══════ YORUMLAR ═══════ -->
<div id="reviews" style="margin-top:50px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:8px;">💬 Müşteri Değerlendirmeleri</h2>

    <!-- Özet -->
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="text-align:center;">
            <div style="font-size:48px;font-weight:800;color:var(--primary);"><?= number_format($avg_rating,1) ?></div>
            <div style="color:#f59e0b;font-size:20px;"><?= stars($avg_rating) ?></div>
            <div style="color:var(--text-muted);font-size:13px;"><?= count($reviews) ?> değerlendirme</div>
        </div>
        <div style="flex:1;min-width:200px;">
            <?php foreach ([5,4,3,2,1] as $star):
                $cnt = count(array_filter($reviews, fn($r) => $r['rating'] == $star));
                $pct = count($reviews) ? round($cnt / count($reviews) * 100) : 0;
            ?>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                <span style="font-size:13px;width:12px;"><?= $star ?></span>
                <span style="color:#f59e0b;font-size:13px;">★</span>
                <div style="flex:1;height:8px;background:var(--border);border-radius:4px;overflow:hidden;">
                    <div style="width:<?= $pct ?>%;height:100%;background:#f59e0b;border-radius:4px;"></div>
                </div>
                <span style="font-size:12px;color:var(--text-muted);width:30px;"><?= $pct ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Yorum Kartları -->
    <div style="display:grid;gap:16px;">
    <?php foreach ($reviews as $rv): ?>
    <div style="background:var(--surface);border-radius:var(--radius-md);padding:20px;
                box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--primary);
                        color:#fff;display:flex;align-items:center;justify-content:center;
                        font-weight:700;font-size:16px;flex-shrink:0;">
                <?= strtoupper(substr($rv['user_name'], 0, 1)) ?>
            </div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
                    <span style="font-weight:600;font-size:14px;"><?= htmlspecialchars($rv['user_name']) ?></span>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="color:#f59e0b;font-size:15px;"><?= str_repeat('★',$rv['rating']) ?><?= str_repeat('☆',5-$rv['rating']) ?></span>
                        <span style="font-size:12px;color:var(--text-muted);"><?= date('d M Y', strtotime($rv['created_at'])) ?></span>
                        <?php if ($rv['is_verified_buy']): ?>
                        <span style="background:#e8f5e9;color:#2e7d32;font-size:11px;padding:2px 7px;border-radius:20px;font-weight:600;">✓ Doğrulanmış Alım</span>
                        <?php endif; ?>
                    </div>
                </div>
                <p style="font-size:14px;color:var(--text);margin-top:8px;line-height:1.6;">
                    <?= htmlspecialchars($rv['comment_text']) ?>
                </p>
                <?php if ($rv['photo_url']): ?>
                <div style="margin-top:10px;">
                    <img src="<?= htmlspecialchars($rv['photo_url']) ?>" alt="Yorum fotoğrafı"
                         style="width:100px;height:100px;object-fit:cover;border-radius:var(--radius-sm);
                                border:1px solid var(--border);cursor:pointer;"
                         onclick="window.open(this.src,'_blank')"
                         onerror="this.style.display='none'">
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- ═══════ BENZERürünLER ═══════ -->
<?php if ($similar): ?>
<div style="margin-top:50px;">
    <div class="section-title">
        <h2>🔗 Benzer Ürünler</h2>
    </div>
    <div style="display:flex;gap:16px;overflow-x:auto;padding-bottom:12px;scrollbar-width:thin;">
        <?php foreach ($similar as $s):
            $disc2 = $s['original_price'] ? round((1 - $s['price'] / $s['original_price']) * 100) : 0;
        ?>
        <div class="product-card" style="min-width:200px;max-width:200px;flex-shrink:0;"
             onclick="window.location='<?= URL_ROOT ?>/product.php?id=<?= $s['id'] ?>'">
            <div class="card-img-wrap" style="padding-top:110%;">
                <img src="<?= htmlspecialchars($s['main_image']) ?>"
                     alt="<?= htmlspecialchars($s['title']) ?>"
                     loading="lazy"
                     onerror="this.src='https://via.placeholder.com/200x220?text=?'">
                <?php if ($disc2): ?><span class="badge-discount">-<?= $disc2 ?>%</span><?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-price">
                    <span class="price-now"><?= number_format($s['price'],0,',','.') ?> ₺</span>
                </div>
                <p class="card-title"><?= htmlspecialchars($s['brand'] ? $s['brand'].' – '.$s['title'] : $s['title']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- /container -->
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="<?= URL_ROOT ?>/js/product_mutator.js"></script>
<script>
document.body.dataset.loggedIn = '<?= $is_logged ? '1' : '0' ?>';

// Beden seçim fonksiyonu (ürün detay sayfasına özel)
function selectSize(btn, size) {
    document.querySelectorAll('.size-pick-btn').forEach(b => {
        b.style.borderColor = 'var(--border)';
        b.style.background  = 'var(--surface)';
        b.style.color       = 'var(--text)';
    });
    btn.style.borderColor = 'var(--primary)';
    btn.style.background  = 'var(--primary-light)';
    btn.style.color       = 'var(--primary)';
    document.getElementById('current-size').value = size;
    document.getElementById('size-error').style.display = 'none';
}

function handleAddToCart() {
    const isCloth = document.getElementById('is-cloth').value === '1';
    const size    = document.getElementById('current-size').value;
    if (isCloth && !size) {
        document.getElementById('size-error').style.display = 'block';
        document.getElementById('size-grid').scrollIntoView({ behavior:'smooth', block:'center' });
        return;
    }
    const productId = document.getElementById('current-product-id').value;
    const variantId = document.getElementById('current-variant-id').value;
    addToCart(productId, variantId, size);
}

function handleBuyNow(e) {
    const isCloth = document.getElementById('is-cloth').value === '1';
    const size    = document.getElementById('current-size').value;
    if (isCloth && !size) {
        e.preventDefault();
        document.getElementById('size-error').style.display = 'block';
        document.getElementById('size-grid').scrollIntoView({ behavior:'smooth', block:'center' });
        return false;
    }
    // Hemen satın al için product ve variant id'yi URL'e ekle
    const pid = document.getElementById('current-product-id').value;
    const vid = document.getElementById('current-variant-id').value;
    e.target.href = URL_ROOT + `/checkout.php?buy_now=${pid}&variant_id=${vid}&size=${encodeURIComponent(size)}`;
    return true;
}
</script>
