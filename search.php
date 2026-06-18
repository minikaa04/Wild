<?php
// search.php — Arama Sonuçları ve Kategori Sayfası

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

$q       = trim($_GET['q']   ?? '');
$cat_id  = (int)($_GET['cat'] ?? 0);
$sort    = $_GET['sort']     ?? 'default';
$is_logged = isset($_SESSION['user_id']);

// Kategori adı
$cat_name = '';
if ($cat_id) {
    $cs = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $cs->execute([$cat_id]);
    $cat_name = $cs->fetchColumn() ?: '';
}

$page_title = $q ? "\"$q\" için Sonuçlar" : ($cat_name ?: 'Tüm Ürünler');

// Sorgu oluştur
$params = [];
$where  = ['1=1'];

if ($q) {
    $where[]  = "(p.title LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
    $like     = "%$q%";
    $params   = array_merge($params, [$like, $like, $like]);
}
if ($cat_id) {
    // Hem ana hem alt kategori destekle
    $where[]  = "(p.category_id = ? OR c.parent_id = ?)";
    $params   = array_merge($params, [$cat_id, $cat_id]);
}

$order = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'p.rating DESC',
    'new'        => 'p.created_at DESC',
    default      => 'RAND()',
};

$sql = "
    SELECT p.id, p.title, p.price, p.original_price, p.brand,
           p.rating, p.delivery_days, c.name as cat_name, c.type as cat_type,
           pv.main_image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    JOIN product_variants pv ON pv.product_id = p.id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY p.id
    ORDER BY $order
    LIMIT 60
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Wishlist
$wishlist_ids = [];
if ($is_logged) {
    $wl = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wl->execute([$_SESSION['user_id']]);
    $wishlist_ids = $wl->fetchAll(PDO::FETCH_COLUMN);
}

function deliveryDate(int $days): string { return date('d M', strtotime("+{$days} days")); }
function stars(float $r): string {
    return str_repeat('★', floor($r)) . str_repeat('☆', 5 - floor($r));
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
<div class="container" style="padding-top:20px;">

    <!-- Başlık + Sıralama -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
        <div>
            <h1 style="font-size:22px;font-weight:700;">
                <?php if ($q): ?>🔍 "<?= htmlspecialchars($q) ?>" için sonuçlar
                <?php elseif ($cat_name): ?>📂 <?= htmlspecialchars($cat_name) ?>
                <?php else: ?>🛍️ Tüm Ürünler
                <?php endif; ?>
            </h1>
            <p style="color:var(--text-muted);font-size:14px;margin-top:4px;"><?= count($products) ?> ürün bulundu</p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            <label for="sort-sel" style="font-size:13.5px;font-weight:500;color:var(--text-muted);">Sırala:</label>
            <select id="sort-sel" onchange="applySort(this.value)"
                style="padding:9px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);
                       font-size:14px;background:var(--surface);color:var(--text);cursor:pointer;outline:none;">
                <option value="default"    <?= $sort==='default'    ?'selected':'' ?>>Önerilen</option>
                <option value="price_asc"  <?= $sort==='price_asc'  ?'selected':'' ?>>Fiyat: Düşükten Yükseğe</option>
                <option value="price_desc" <?= $sort==='price_desc' ?'selected':'' ?>>Fiyat: Yüksekten Düşüğe</option>
                <option value="rating"     <?= $sort==='rating'     ?'selected':'' ?>>En Yüksek Puanlı</option>
                <option value="new"        <?= $sort==='new'        ?'selected':'' ?>>En Yeni</option>
            </select>
        </div>
    </div>

    <!-- Ürün Grid -->
    <?php if (empty($products)): ?>
    <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:64px;margin-bottom:16px;">🔍</div>
        <h2 style="font-size:20px;margin-bottom:8px;color:var(--text-muted);">Sonuç bulunamadı</h2>
        <p style="color:var(--text-light);margin-bottom:24px;">Farklı bir arama terimi deneyin veya kategorilere göz atın.</p>
        <a href="<?= URL_ROOT ?>/index.php" class="btn btn-primary">Ana Sayfaya Dön</a>
    </div>
    <?php else: ?>
    <div class="grid-auto">
    <?php foreach ($products as $p):
        $in_wl       = in_array($p['id'], $wishlist_ids);
        $is_cloth    = in_array($p['cat_type'], ['adult','child']);
        $disc        = $p['original_price'] ? round((1 - $p['price'] / $p['original_price']) * 100) : 0;
        $delivery    = deliveryDate($p['delivery_days']);
    ?>
    <div class="product-card" onclick="window.location='<?= URL_ROOT ?>/product.php?id=<?= $p['id'] ?>'">
        <div class="card-img-wrap">
            <img src="<?= htmlspecialchars($p['main_image']) ?>"
                 alt="<?= htmlspecialchars($p['title']) ?>"
                 loading="lazy"
                 onerror="this.src='https://via.placeholder.com/400x500?text=Görsel+Yok'">
            <?php if ($disc > 0): ?><span class="badge-discount">-<?= $disc ?>%</span><?php endif; ?>
            <button class="btn-heart <?= $in_wl ? 'active' : '' ?>"
                    data-product-id="<?= $p['id'] ?>"
                    onclick="event.stopPropagation();toggleWishlist(this,<?= $p['id'] ?>,<?= $is_cloth ? 'true':'false' ?>)"
                    aria-label="Favorilere Ekle"><?= $in_wl ? '❤️' : '🤍' ?></button>
        </div>
        <div class="card-body">
            <div class="card-rating">
                <span class="stars"><?= stars((float)$p['rating']) ?></span>
                <span><?= number_format((float)$p['rating'],1) ?></span>
            </div>
            <div class="card-price">
                <span class="price-now"><?= number_format($p['price'],0,',','.') ?> ₺</span>
                <?php if ($p['original_price']): ?>
                <span class="price-old"><?= number_format($p['original_price'],0,',','.') ?> ₺</span>
                <?php endif; ?>
            </div>
            <p class="card-title"><?= htmlspecialchars($p['brand'] ? $p['brand'].' – '.$p['title'] : $p['title']) ?></p>
            <div class="card-footer-row">
                <span class="card-delivery">📦 <?= $delivery ?>'e kadar</span>
                <button class="btn-add-cart" onclick="event.stopPropagation();addToCart(<?= $p['id'] ?>,null,null)">
                    🛒 <span>Ekle</span>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
document.body.dataset.loggedIn = '<?= $is_logged ? '1' : '0' ?>';
function applySort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
}
</script>
