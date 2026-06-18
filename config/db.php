<?php
// config/db.php — Veritabanı bağlantısı (Local XAMPP)

define('DB_HOST',    'localhost');
define('DB_NAME',    'wild_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// Otomatik URL tespiti (XAMPP alt klasörleri için)
$base_dir = str_replace('\\', '/', dirname(__DIR__));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$url_path = str_replace($doc_root, '', $base_dir);
define('URL_ROOT', rtrim($url_path, '/'));

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {
    http_response_code(500);
    die('
        <div style="font-family:sans-serif;max-width:600px;margin:80px auto;padding:30px;
                    border:2px solid #dc2626;border-radius:10px;background:#fff5f5;color:#991b1b;">
            <h2>⚠️ Veritabanı Bağlantı Hatası</h2>
            <p>Veritabanı bilgilerini kontrol edin.</p>
            <pre style="background:#fee2e2;padding:12px;border-radius:6px;font-size:13px;">'
            . htmlspecialchars($e->getMessage()) .
            '</pre>
            <p style="font-size:13px;">config/db.php dosyasındaki bilgileri kontrol edin.</p>
        </div>
    ');
}
