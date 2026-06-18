<?php
// checkout.php — Sipariş Tamamlama Sayfası

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

$is_logged  = isset($_SESSION['user_id']);
$user_id    = $_SESSION['user_id']  ?? null;
$session_id = $_SESSION['guest_id'] ?? session_id();

// "Hemen Satın Al" mı?
$buy_now_id  = (int)($_GET['buy_now']    ?? 0);
$variant_id  = (int)($_GET['variant_id'] ?? 0);
$buy_now_size = trim($_GET['size'] ?? '');
$is_buy_now  = $buy_now_id > 0;

// Ürün / Sepet verisi çek
if ($is_buy_now) {
    // Sadece tek ürün
    $stmt = $pdo->prepare("
        SELECT p.id as product_id, p.title, p.price, p.brand, p.delivery_days,
               COALESCE(pv.price_override, p.price) as final_price,
               pv.main_image, pv.color_name
        FROM products p
        LEFT JOIN product_variants pv ON pv.id = ?
        WHERE p.id = ?
    ");
    $stmt->execute([$variant_id ?: null, $buy_now_id]);
    $buy_item = $stmt->fetch();
    $items    = $buy_item ? [array_merge($buy_item, ['quantity'=>1,'size_selected'=>$buy_now_size,'cart_item_id'=>0])] : [];
} else {
    // Tüm sepet
    if ($user_id) {
        $stmt = $pdo->prepare("
            SELECT ci.id as cart_item_id, ci.quantity, ci.size_selected,
                   p.id as product_id, p.title, p.price, p.brand, p.delivery_days,
                   COALESCE(pv.price_override, p.price) as final_price,
                   pv.main_image, pv.color_name
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            LEFT JOIN product_variants pv ON pv.id = ci.variant_id
            WHERE ci.user_id = ?
            ORDER BY ci.added_at DESC
        ");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT ci.id as cart_item_id, ci.quantity, ci.size_selected,
                   p.id as product_id, p.title, p.price, p.brand, p.delivery_days,
                   COALESCE(pv.price_override, p.price) as final_price,
                   pv.main_image, pv.color_name
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            LEFT JOIN product_variants pv ON pv.id = ci.variant_id
            WHERE ci.session_id = ?
            ORDER BY ci.added_at DESC
        ");
        $stmt->execute([$session_id]);
    }
    $items = $stmt->fetchAll();
}

$total = array_sum(array_map(fn($i) => $i['final_price'] * $i['quantity'], $items));

// Kullanıcı kayıtlı adresleri
$saved_addresses = [];
if ($is_logged) {
    $addr_stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ?");
    $addr_stmt->execute([$user_id]);
    $saved_addresses = $addr_stmt->fetchAll();
}

// Sipariş oluşturma (POST)
$order_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!$is_logged) {
        // Kayıt gerektir
        $_SESSION['checkout_redirect'] = URL_ROOT . '/checkout.php' . ($is_buy_now ? "?buy_now=$buy_now_id&variant_id=$variant_id&size=$buy_now_size" : '');
        header('Location: ' . URL_ROOT . '/index.php?login_required=1');
        exit;
    }

    $city    = trim($_POST['city']    ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($city && $address && !empty($items)) {
        // Adres kaydet
        $ins_addr = $pdo->prepare("INSERT INTO addresses (user_id, city, full_address) VALUES (?,?,?)");
        $ins_addr->execute([$user_id, $city, $address]);
        $addr_id  = $pdo->lastInsertId();

        // Sipariş oluştur
        $ins_ord = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, delivery_address_id) VALUES (?,?,'hazirlanıyor',?)");
        $ins_ord->execute([$user_id, $total, $addr_id]);
        $order_id = $pdo->lastInsertId();

        // Ürünleri ekle
        $ins_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, size_selected, quantity, price) VALUES (?,?,?,?,?)");
        foreach ($items as $item) {
            $ins_item->execute([$order_id, $item['product_id'], $item['size_selected'], $item['quantity'], $item['final_price']]);
        }

        // Sepeti temizle
        if (!$is_buy_now) {
            $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$user_id]);
        }

        $order_success = true;
        $success_order_id = $order_id;
    }
}

$page_title = $is_buy_now ? 'Hemen Satın Al' : 'Siparişi Tamamla';
require_once __DIR__ . '/includes/header.php';
?>

<main>
<div class="container" style="padding-top:24px;max-width:980px;">

<?php if ($order_success): ?>
<!-- ─── SİPARİŞ BAŞARILI ─── -->
<div style="text-align:center;padding:60px 20px;background:var(--surface);border-radius:var(--radius-lg);
            box-shadow:var(--shadow-lg);">
    <div style="font-size:72px;margin-bottom:16px;">🎉</div>
    <h2 style="font-size:26px;font-weight:800;color:var(--success);margin-bottom:8px;">Siparişiniz Alındı!</h2>
    <p style="color:var(--text-muted);font-size:15px;margin-bottom:8px;">
        Sipariş No: <strong>#<?= $success_order_id ?></strong>
    </p>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:28px;">
        Siparişiniz hazırlanmaya başlandı. İlerlemeyi profil sayfanızdan takip edebilirsiniz.
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= URL_ROOT ?>/profile.php?tab=orders" class="btn btn-primary">📦 Siparişlerimi Gör</a>
        <a href="<?= URL_ROOT ?>/index.php" class="btn btn-outline">🏠 Ana Sayfaya Dön</a>
    </div>
</div>

<?php elseif (empty($items)): ?>
<div style="text-align:center;padding:80px 20px;">
    <div style="font-size:64px;margin-bottom:16px;">🛒</div>
    <h2 style="color:var(--text-muted);">Sepetiniz boş</h2>
    <a href="<?= URL_ROOT ?>/index.php" class="btn btn-primary" style="margin-top:20px;">Alışverişe Başla</a>
</div>

<?php else: ?>

<!-- ─── SİPARİŞ FORMU ─── -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

<!-- SOL: Form -->
<form method="POST" id="checkout-form">

    <!-- 1) Teslimat Adresi -->
    <div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
                box-shadow:var(--shadow-sm);border:1px solid var(--border);margin-bottom:18px;">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">📍 Teslimat Adresi</h3>

        <?php if (!$is_logged): ?>
        <div style="background:var(--accent-light);border:1.5px solid var(--accent);border-radius:var(--radius-sm);
                    padding:14px 16px;margin-bottom:14px;">
            <p style="font-size:13.5px;color:#92400e;">
                ⚠️ Sipariş verebilmek için giriş yapmanız gerekmektedir.
                <a href="#" onclick="openModal('modal-login')" style="color:var(--primary);font-weight:700;">Giriş Yap →</a>
            </p>
        </div>
        <?php endif; ?>

        <!-- Kayıtlı Adresler -->
        <?php if (!empty($saved_addresses)): ?>
        <div style="margin-bottom:14px;">
            <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">Kayıtlı Adresleriniz:</p>
            <?php foreach ($saved_addresses as $adr): ?>
            <label style="display:flex;align-items:flex-start;gap:10px;padding:10px;border:1.5px solid var(--border);
                          border-radius:var(--radius-sm);margin-bottom:8px;cursor:pointer;transition:.2s;"
                   onmouseover="this.style.borderColor='var(--primary)'"
                   onmouseout="this.style.borderColor='var(--border)'">
                <input type="radio" name="saved_addr_id" value="<?= $adr['id'] ?>"
                       onclick="fillAddress('<?= addslashes($adr['city']) ?>','<?= addslashes($adr['full_address']) ?>')"
                       style="margin-top:3px;">
                <span style="font-size:13.5px;">
                    <strong><?= htmlspecialchars($adr['city']) ?></strong> –
                    <?= htmlspecialchars($adr['full_address']) ?>
                </span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Manuel Adres -->
        <div style="position:relative;background:var(--surface-2);border-radius:var(--radius-md);
                    padding:16px;border:1px solid var(--border);">
            <!-- Harita Simülasyonu -->
            <div id="map-placeholder" onclick="activateMap()"
                 style="width:100%;height:160px;background:linear-gradient(135deg,#e8f0fe,#c5d8f5);
                        border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;
                        cursor:pointer;margin-bottom:14px;border:1px solid var(--border);
                        flex-direction:column;gap:8px;transition:.2s;"
                 onmouseover="this.style.background='linear-gradient(135deg,#d2e3fc,#a8c4f0)'"
                 onmouseout="this.style.background='linear-gradient(135deg,#e8f0fe,#c5d8f5)'">
                <span style="font-size:36px;">🗺️</span>
                <p style="font-size:14px;font-weight:600;color:var(--primary);">Haritadan Adres Seç</p>
                <p style="font-size:12px;color:var(--text-muted);">Konumunuzu işaretleyin</p>
            </div>

            <!-- Adres Giriş Alanları -->
            <div class="form-group">
                <label for="addr-city">Şehir <span style="color:var(--danger);">*</span></label>
                <input type="text" id="addr-city" name="city" class="form-control"
                       placeholder="İstanbul" required
                       oninput="document.getElementById('addr-city').classList.remove('is-invalid')">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label for="addr-full">Tam Adres <span style="color:var(--danger);">*</span></label>
                <textarea id="addr-full" name="address" class="form-control" rows="3"
                          placeholder="Mahalle, Cadde, Sokak No, Daire..."
                          required style="resize:vertical;"
                          oninput="this.classList.remove('is-invalid')"></textarea>
            </div>
        </div>
    </div>

    <!-- 2) Ödeme Yöntemi (Simülasyon) -->
    <div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;
                box-shadow:var(--shadow-sm);border:1px solid var(--border);margin-bottom:18px;">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">💳 Ödeme Yöntemi</h3>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ([['credit','💳','Kredi / Banka Kartı'],['transfer','🏦','Banka Havalesi'],['door','💰','Kapıda Ödeme']] as [$val,$icon,$lbl]): ?>
            <label style="display:flex;align-items:center;gap:12px;padding:12px 16px;
                          border:1.5px solid var(--border);border-radius:var(--radius-sm);
                          cursor:pointer;transition:.2s;"
                   onmouseover="this.style.borderColor='var(--primary)'"
                   onmouseout="this.style.borderColor='var(--border)'">
                <input type="radio" name="payment_method" value="<?= $val ?>" <?= $val==='credit'?'checked':'' ?>>
                <span style="font-size:18px;"><?= $icon ?></span>
                <span style="font-size:14px;font-weight:500;"><?= $lbl ?></span>
            </label>
            <?php endforeach; ?>
        </div>

        <!-- Kart Bilgileri (Simülasyon) -->
        <div id="card-fields" style="margin-top:16px;background:var(--surface-2);padding:16px;
                                      border-radius:var(--radius-md);border:1px solid var(--border);">
            <div class="form-group">
                <label>Kart Üzerindeki İsim</label>
                <input type="text" class="form-control" placeholder="AD SOYAD" id="card-name">
            </div>
            <div class="form-group">
                <label>Kart Numarası</label>
                <input type="text" class="form-control" placeholder="•••• •••• •••• ••••"
                       id="card-number" maxlength="19" oninput="formatCardNumber(this)">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Son Kullanma Tarihi</label>
                    <input type="text" class="form-control" placeholder="AA/YY" id="card-expiry" maxlength="5">
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="text" class="form-control" placeholder="•••" id="card-cvv" maxlength="4">
                </div>
            </div>
            <p style="font-size:12px;color:var(--text-light);">🔒 Bilgileriniz 256-bit SSL ile şifrelenir. (Simülasyon)</p>
        </div>
    </div>

    <button name="place_order" type="submit" class="btn btn-accent btn-full"
            style="font-size:17px;padding:16px;" id="btn-place-order">
        ✅ Siparişi Tamamla – <?= number_format($total,0,',','.') ?> ₺
    </button>
</form>

<!-- SAĞ: Özet -->
<div style="position:sticky;top:84px;">
    <div style="background:var(--surface);border-radius:var(--radius-lg);padding:22px;
                box-shadow:var(--shadow-lg);border:1.5px solid var(--border);">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">
            <?= $is_buy_now ? '⚡ Hemen Satın Al' : '📋 Sipariş Özeti' ?>
        </h3>

        <!-- Ürünler -->
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;
                    max-height:280px;overflow-y:auto;">
        <?php foreach ($items as $item): ?>
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="width:52px;height:62px;border-radius:var(--radius-sm);overflow:hidden;flex-shrink:0;">
                <img src="<?= htmlspecialchars($item['main_image'] ?? '') ?>"
                     alt="" style="width:100%;height:100%;object-fit:cover;"
                     onerror="this.src='https://via.placeholder.com/52x62?text=?'">
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:12.5px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?= htmlspecialchars($item['title']) ?>
                </p>
                <?php if ($item['size_selected']): ?>
                <p style="font-size:11.5px;color:var(--text-muted);">Beden: <?= $item['size_selected'] ?></p>
                <?php endif; ?>
                <p style="font-size:12px;color:var(--text-muted);"><?= $item['quantity'] ?> adet</p>
            </div>
            <span style="font-size:13px;font-weight:700;white-space:nowrap;color:var(--primary);">
                <?= number_format($item['final_price'] * $item['quantity'],0,',','.') ?> ₺
            </span>
        </div>
        <?php endforeach; ?>
        </div>

        <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;
                    justify-content:space-between;align-items:center;font-size:18px;font-weight:800;">
            <span>Toplam</span>
            <span style="color:var(--primary);"><?= number_format($total,0,',','.') ?> ₺</span>
        </div>

        <div style="margin-top:14px;font-size:12.5px;color:var(--text-muted);display:flex;flex-direction:column;gap:5px;">
            <span>✅ KDV dahildir</span>
            <span>📦 Kargo ücretsizdir</span>
            <span>🔄 14 gün iade garantisi</span>
        </div>
    </div>
</div>

</div><!-- /grid -->
<?php endif; ?>
</div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= URL_ROOT ?>/js/checkout.js"></script>
<script>
document.body.dataset.loggedIn = '<?= $is_logged ? '1' : '0' ?>';

function fillAddress(city, address) {
    document.getElementById('addr-city').value = city;
    document.getElementById('addr-full').value = address;
}

function activateMap() {
    const map = document.getElementById('map-placeholder');
    map.innerHTML = `
        <span style="font-size:36px;">📍</span>
        <p style="font-size:14px;font-weight:600;color:var(--success);">Konum işaretlendi!</p>
        <p style="font-size:12px;color:var(--text-muted);">Formu doldurun ve devam edin</p>
    `;
}

document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const cardFields = document.getElementById('card-fields');
        cardFields.style.display = radio.value === 'credit' ? 'block' : 'none';
    });
});
</script>
