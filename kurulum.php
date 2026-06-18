<?php
// kurulum.php - Tüm veritabanını sıfırlayıp en baştan kurar
require_once 'config/db.php';

// Hataları ekrana basması için (hata ayıklama modu)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "<body style='font-family:sans-serif; background:#f4f4f9; padding:40px;'>";
    echo "<div style='background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.1); max-width:600px; margin:auto;'>";
    echo "<h2 style='color:#2563eb;'>🚀 Kurulum Başlatılıyor...</h2>";
    echo "<hr style='border:0; border-top:1px solid #eee; margin-bottom:20px;'>";

    // 1. Yabancı anahtar kontrollerini kapat
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "<p style='color:orange;'>✅ Güvenlik denetimleri geçici olarak kapatıldı.</p>";

    // 2. Mevcut tüm tabloları sil
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if(count($tables) > 0){
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "<p style='font-size:13px;'>🗑️ Eski tablo silindi: <b>$table</b></p>";
        }
    } else {
        echo "<p>ℹ️ Silinecek eski tablo bulunamadı, sıfırdan başlanıyor.</p>";
    }

    // 3. database.sql dosyasını oku ve çalıştır
    $sql_path = __DIR__ . '/database.sql';
    if (file_exists($sql_path)) {
        $sql = file_get_contents($sql_path);
        
        // SQL dosyasını parçalayarak çalıştır (Bazı sunucular tek seferde koca dosyayı kabul etmeyebilir)
        $pdo->exec($sql);
        
        echo "<p style='color:green; font-weight:bold;'>💎 Veritabanı tabloları ve veriler başarıyla yüklendi.</p>";
    } else {
        die("<p style='color:red;'>❌ database.sql dosyası bulunamadı! Lütfen dosyanın klasörde olduğundan emin olun.</p>");
    }

    // 4. Güvenlik kontrollerini geri aç
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p style='color:blue;'>🔒 Güvenlik denetimleri tekrar açıldı.</p>";

    echo "<hr style='border:0; border-top:1px solid #eee; margin-top:20px;'>";
    echo "<h2 style='color:#16a34a;'>✅ KURULUM TAMAMLANDI!</h2>";
    echo "<p>Artık ana sayfanıza gidebilirsiniz: <a href='index.php' style='color:#2563eb; font-weight:bold; text-decoration:none;'>👉 Siteye Git</a></p>";
    echo "<p style='font-size:12px; color:#666;'><i><b>ÖNEMLİ:</b> Güvenlik için kurulum bittikten sonra 'kurulum.php' dosyasını FTP'den silmeyi unutmayın!</i></p>";
    echo "</div></body>";

} catch (Exception $e) {
    echo "<div style='background:#fee2e2; color:#991b1b; padding:20px; border-radius:10px; border:1px solid #f87171;'>";
    echo "<h2>❌ HATA OLUŞTU:</h2>";
    echo "<pre style='background:#fff; padding:10px; border-radius:5px;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
?>
