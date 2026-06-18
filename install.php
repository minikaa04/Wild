<?php
/**
 * install.php
 * Site ilk açıldığında veritabanını otomatik kurar.
 * KULLANIM: Tarayıcıda localhost/wild/install.php adresini aç.
 * Kurulum tamamlandıktan sonra bu dosyayı silmek veya yeniden adlandırmak güvenli bir pratiktir.
 */

$host    = 'localhost';
$user    = 'root';
$pass    = '';
$db_name = 'wild_db';

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Wild - Veritabanı Kurulumu</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 750px; margin: 50px auto; background: #f0f4f8; }
        .box { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; }
        .ok  { color: #2e7d32; font-weight: bold; }
        .err { color: #c62828; font-weight: bold; }
        .step { border-left: 4px solid #1a73e8; padding: 8px 14px; margin: 8px 0; background: #e8f0fe; border-radius: 4px; }
        .done { border-color: #2e7d32; background: #e8f5e9; }
        .fail { border-color: #c62828; background: #ffebee; }
        pre  { background: #263238; color: #80cbc4; padding: 14px; border-radius: 6px; font-size: 13px; }
    </style>
</head>
<body>
<div class='box'>
    <h1>🛒 Wild E-Ticaret - Veritabanı Kurulum Paneli</h1>
    <p>Aşağıdaki adımlar otomatik olarak gerçekleştirilecek:</p>";

$steps = [];
$has_error = false;

// ─── 1. MySQL'e bağlan (veritabanı olmadan)
try {
    $pdo_root = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $steps[] = ['ok', 'MySQL sunucu bağlantısı başarılı.'];
} catch (PDOException $e) {
    $steps[] = ['err', 'MySQL bağlantı hatası: ' . $e->getMessage()];
    $has_error = true;
}

// ─── 2. Veritabanını oluştur
if (!$has_error) {
    try {
        $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo_root->exec("USE `$db_name`");
        $steps[] = ['ok', "Veritabanı '$db_name' oluşturuldu / zaten mevcut."];
    } catch (PDOException $e) {
        $steps[] = ['err', 'Veritabanı oluşturma hatası: ' . $e->getMessage()];
        $has_error = true;
    }
}

// ─── 3. SQL dosyasını oku ve çalıştır
if (!$has_error) {
    $sql_file = __DIR__ . '/database.sql';
    if (!file_exists($sql_file)) {
        $steps[] = ['err', 'database.sql dosyası bulunamadı!'];
        $has_error = true;
    } else {
        try {
            $pdo_db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $sql_content = file_get_contents($sql_file);

            // Her komutu ayrı çalıştır
            $pdo_db->exec($sql_content);

            $steps[] = ['ok', 'database.sql başarıyla çalıştırıldı. Tablolar ve tüm mock veriler kuruldu.'];
        } catch (PDOException $e) {
            $steps[] = ['err', 'SQL çalıştırma hatası: ' . $e->getMessage()];
            $has_error = true;
        }
    }
}

// ─── 4. Kontrol: Tablo ve kayıt sayıları
if (!$has_error) {
    try {
        $checks = [
            'users'            => 'Kullanıcılar',
            'categories'       => 'Kategoriler',
            'products'         => 'Ürünler',
            'product_variants' => 'Varyasyonlar',
            'reviews'          => 'Yorumlar',
            'orders'           => 'Siparişler',
        ];
        foreach ($checks as $table => $label) {
            $count = $pdo_db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $steps[] = ['ok', "$label tablosu: $count kayıt."];
        }
    } catch (PDOException $e) {
        $steps[] = ['err', 'Kontrol hatası: ' . $e->getMessage()];
    }
}

// ─── Çıktıyı yazdır
foreach ($steps as $step) {
    $cls = ($step[0] === 'ok') ? 'done' : 'fail';
    $icon = ($step[0] === 'ok') ? '✅' : '❌';
    echo "<div class='step $cls'>$icon {$step[1]}</div>";
}

if (!$has_error) {
    echo "<br><div style='background:#e8f5e9;border-radius:8px;padding:20px;margin-top:20px;'>
        <h2 class='ok'>🎉 Kurulum Tamamlandı!</h2>
        <p>Veritabanı ve tüm mock veriler hazır. Siteyi ziyaret edebilirsiniz:</p>
        <p><a href='<?= URL_ROOT ?>/index.php' style='color:#1a73e8;font-size:18px;font-weight:bold;'>→ Wild Ana Sayfasına Git</a></p>
        <p style='color:#666;font-size:13px;'>ℹ️ Güvenlik için bu install.php dosyasını silmeniz önerilir.</p>
        <p style='color:#666;font-size:13px;'>🔑 Admin giriş: <b>admin@wild.com</b> | Şifre: <b>password</b></p>
    </div>";
} else {
    echo "<br><div style='background:#ffebee;border-radius:8px;padding:20px;margin-top:20px;'>
        <h2 class='err'>⚠️ Kurulum Başarısız</h2>
        <p>Yukarıdaki hataları inceleyerek XAMPP'ın çalıştığını ve MySQL'in aktif olduğunu kontrol edin.</p>
    </div>";
}

echo "</div></body></html>";
