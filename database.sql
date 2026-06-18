-- ============================================================
-- WILD E-TICARET PLATFORMU - TAM VERİTABANI KURULUM DOSYASI
-- Versiyon: 1.0 | Oluşturma: 2026
-- (Veritabanı config/db.php tarafından oluşturulur)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLO: users
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: addresses
-- ============================================================
DROP TABLE IF EXISTS addresses;
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100),
    full_address TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: categories
-- ============================================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('adult', 'child', 'home', 'general') DEFAULT 'general',
    icon VARCHAR(100),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: products
-- ============================================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    brand VARCHAR(150),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    composition VARCHAR(255),
    gender ENUM('male', 'female', 'unisex', 'child') DEFAULT 'unisex',
    model_size VARCHAR(50),
    model_height VARCHAR(50),
    return_policy TEXT,
    stock INT NOT NULL DEFAULT 100,
    rating DECIMAL(2,1) DEFAULT 4.5,
    seller_rating DECIMAL(2,1) DEFAULT 4.7,
    delivery_days INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: product_variants
-- ============================================================
DROP TABLE IF EXISTS product_variants;
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    color_name VARCHAR(50),
    color_hex VARCHAR(10),
    main_image VARCHAR(500) COMMENT 'Real external URL (Unsplash/Picsum)',
    price_override DECIMAL(10,2) DEFAULT NULL,
    sku_suffix VARCHAR(20),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: wishlist
-- ============================================================
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    size_selected VARCHAR(20),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE(user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: reviews
-- ============================================================
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment_text TEXT,
    photo_url VARCHAR(500) DEFAULT NULL COMMENT 'Fotoğraflı yorum için URL',
    is_verified_buy TINYINT(1) DEFAULT 1,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: orders
-- ============================================================
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('hazirlanıyor', 'kargoda', 'ulasti', 'iptal', 'tamamlandi') DEFAULT 'hazirlanıyor',
    cargo_tracking_no VARCHAR(100) DEFAULT NULL,
    delivery_address_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_address_id) REFERENCES addresses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: order_items
-- ============================================================
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NULL,
    size_selected VARCHAR(20),
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: cart_items (Oturum + Kullanıcı bazlı sepet)
-- ============================================================
DROP TABLE IF EXISTS cart_items;
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) DEFAULT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    size_selected VARCHAR(20),
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLO: password_reset_requests
-- ============================================================
DROP TABLE IF EXISTS password_reset_requests;
CREATE TABLE password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    status ENUM('pending', 'done') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ============================================================
-- MOCK DATA (SEED) - BÜYÜK ÇAPLI VERİ EKLEME STRATEJİSİ
-- ============================================================
-- ============================================================

-- Kullanıcılar (Admin + Normal kullanıcılar)
INSERT INTO users (id, first_name, last_name, email, phone, password_hash, role) VALUES
(1, 'Admin', 'Wild', 'admin@wild.com', '5550000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Ayşe', 'Kaya', 'ayse@example.com', '5551112233', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(3, 'Mert', 'Demir', 'mert@example.com', '5552223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(4, 'Zeynep', 'Arslan', 'zeynep@example.com', '5553334455', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(5, 'Can', 'Yıldız', 'can@example.com', '5554445566', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(6, 'Selin', 'Çelik', 'selin@example.com', '5555556677', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(7, 'Emre', 'Şahin', 'emre@example.com', '5556667788', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(8, 'Büşra', 'Kurt', 'busra@example.com', '5557778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(9, 'Hüseyin', 'Aydın', 'huseyin@example.com', '5558889900', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(10, 'Fatma', 'Güneş', 'fatma@example.com', '5559990011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
-- Şifre: "password" (tüm kullanıcılar için)

-- Adresler
INSERT INTO addresses (user_id, city, district, full_address) VALUES
(2, 'İstanbul', 'Kadıköy', 'Moda Caddesi No: 45 Daire: 3'),
(3, 'Ankara', 'Çankaya', 'Tunalı Hilmi Caddesi No: 12'),
(4, 'İzmir', 'Konak', 'Kemeralı Caddesi No: 78'),
(5, 'Bursa', 'Nilüfer', 'Atatürk Caddesi No: 33');

-- ============================================================
-- KATEGORİLER (Ana + Alt)
-- ============================================================
INSERT INTO categories (id, parent_id, name, type) VALUES
-- Ana kategoriler
(1, NULL, 'Kadın', 'adult'),
(2, NULL, 'Erkek', 'adult'),
(3, NULL, 'Çocuk', 'child'),
(4, NULL, 'Ev', 'general'),
(5, NULL, 'Güzellik', 'general'),
(6, NULL, 'Elektronik', 'general'),
(7, NULL, 'Spor', 'general'),
(8, NULL, 'Kitap', 'general'),

-- Kadın alt kategorileri
(10, 1, 'Elbise', 'adult'),
(11, 1, 'Bluz ve Gömlek', 'adult'),
(12, 1, 'Dış Giyim', 'adult'),
(13, 1, 'Tişört', 'adult'),
(14, 1, 'Jeans ve Pantolon', 'adult'),
(15, 1, 'Etek', 'adult'),
(16, 1, 'Ayakkabı', 'adult'),
(17, 1, 'Takım Elbise', 'adult'),

-- Erkek alt kategorileri
(20, 2, 'Kazak ve Sweatshirt', 'adult'),
(21, 2, 'Tişört', 'adult'),
(22, 2, 'Pantolon ve Jeans', 'adult'),
(23, 2, 'Mont ve Kaban', 'adult'),
(24, 2, 'Ayakkabı', 'adult'),

-- Çocuk alt kategorileri
(30, 3, 'Kız Çocuk Giyim', 'child'),
(31, 3, 'Erkek Çocuk Giyim', 'child'),
(32, 3, 'Bebek Bezi', 'child'),
(33, 3, 'Bebek Maması', 'child'),
(34, 3, 'Emzik ve Biberon', 'child');

-- ============================================================
-- ÜRÜNLER (20 ADET - Gerçekçi Mock Veriler)
-- ============================================================
INSERT INTO products (id, category_id, brand, title, description, price, original_price, sku, composition, gender, model_size, model_height, return_policy, stock, rating, seller_rating, delivery_days) VALUES

-- Kadın Elbise
(1, 10, 'LuxStyle', 'Yazlık Çiçek Desenli Midi Elbise', 'Şık ve rahat yazlık elbise. Düz kesimli, çiçek desenli midi boy elbise. Özel günler ve günlük kullanım için idealdir.', 349.00, 499.00, 'ELB-1001', '%100 Viskon', 'female', 'S/M', '175 cm', '14 gün içinde koşulsuz iade. Ürün etiketleri sökülmemiş olmalıdır.', 85, 4.7, 4.8, 2),

(2, 10, 'ModaHouse', 'Saten Gece Elbisesi - V Yaka', 'Şık kesimi ve yumuşak saten kumaşı ile özel geceler için tasarlanmış elbise. Tam boy, ince askılı model.', 679.00, 899.00, 'ELB-1002', '%100 Polyester Saten', 'female', 'S', '178 cm', '14 gün iade garantisi. Hijyenik ürün, ilk etiketi üzerinde olmalı.', 42, 4.9, 4.9, 3),

(3, 10, 'UrbanChic', 'Yazlık Keten Elbise - Bej', 'Doğal keten kumaştan üretilen, nefes alan ve serin tutan midi boy elbise. Minimal çizgili tasarım.', 289.00, NULL, 'ELB-1003', '%55 Keten, %45 Pamuk', 'female', 'M', '172 cm', '14 gün iade hakkı.', 120, 4.5, 4.6, 2),

-- Kadın Tişört
(4, 13, 'BasicCo', 'Basic Oversize Pamuk Tişört - Beyaz', 'Yumuşak \%100 pamuktan yapılmış oversize kesimli, uzun kollu tişört. Her kombin ile uyum sağlar.', 149.00, 199.00, 'TSR-2001', '%100 Organik Pamuk', 'female', 'M/L', '170 cm', '14 gün iade garantisi.', 250, 4.6, 4.7, 1),

(5, 13, 'TrendWear', 'Crop Top Baskılı Tişört - Pembe', 'Şık grafik baskılı, kısa kesimli crop tişört. Yüksek bel pantolonlar ile mükemmel uyum.', 199.00, NULL, 'TSR-2002', '%95 Pamuk, %5 Elastan', 'female', 'S', '165 cm', '14 gün iade.', 180, 4.4, 4.5, 2),

-- Kadın Dış Giyim
(6, 12, 'WarmStyle', 'Kaşmir Karışımlı Uzun Kaban - Camel', 'Şık ve ısı yalıtımı yüksek, diz altı uzunluğunda kadın kabası. Şık düğme kapama ve derin cepli tasarım.', 1299.00, 1799.00, 'DGY-3001', '%30 Kaşmir, %70 Yün', 'female', 'S/M', '173 cm', '14 gün iade. Kürk ve yün ürünlerde özel temizlik şartı.', 35, 4.8, 4.8, 4),

(7, 12, 'SportLine', 'Mont Puffer - Siyah', 'Hafif ve sıcak tutan şişme dolgu mont. Su itici özellikli, rüzgar kesici kumaş. Kapüşonlu model.', 849.00, NULL, 'DGY-3002', '%100 Naylon (Dış), %100 Polyester Dolgu', 'female', 'L', '168 cm', '14 gün iade garantisi.', 78, 4.5, 4.6, 3),

-- Erkek Kazak
(8, 20, 'MachoBrand', 'Boğazlı Kışlık Kazak - Lacivert', 'Klasik balıkçı yaka kazak. Dokunmatik yumuşak Merino yünü ile üretilmiş, vücudu sıcak tutar.', 459.00, 599.00, 'KZK-4001', '%80 Merino Yünü, %20 Naylon', 'male', 'L', '183 cm', '14 gün iade.', 95, 4.7, 4.8, 2),

(9, 20, 'CasualMan', 'Oversize Hoodie Fermuarlı - Gri', 'Geniş kesimli, fermuarlı kapüşonlu sweatshirt. Rahat günlük kullanım için çift katlı iç astar.', 549.00, NULL, 'KZK-4002', '%60 Pamuk, %40 Polyester', 'male', 'XL', '180 cm', '14 gün iade garantisi.', 140, 4.6, 4.7, 2),

-- Erkek Tişört
(10, 21, 'PremiumBasic', 'Polo Yaka Erkek Tişört - Beyaz', 'Klasik polo yaka, slim fit erkek tişörtü. Nefes alan pique kumaş. Hem resmi hem de günlük kombinlere uygun.', 249.00, 349.00, 'TSR-5001', '%100 Pique Pamuk', 'male', 'M', '182 cm', '14 gün iade.', 200, 4.5, 4.6, 1),

-- Erkek Mont
(11, 23, 'AlpineGear', 'Kışlık Kapitone Mont - Haki', 'Ağır kış şartları için tasarlanmış, su geçirmez ve rüzgar tutmayan kapitone mont. Çıkarılabilir kapüşon.', 1149.00, 1499.00, 'MNT-6001', '%100 Polyester (Dış), %80 Duck Down (Dolgu)', 'male', 'L', '185 cm', '14 gün iade garantisi.', 55, 4.9, 4.9, 4),

-- Çocuk Giyim
(12, 30, 'KidZone', 'Kız Çocuk Baskılı Elbise - Pembe', 'Sevimli çiçek baskılı, fiyonklu kız çocuk elbisesi. 4-14 yaş arası için uygun.', 189.00, 249.00, 'CKZ-7001', '%100 Pamuk', 'child', '120 cm (6 yaş)', '105 cm', '14 gün iade.', 90, 4.7, 4.7, 2),

(13, 31, 'KidZone', 'Erkek Çocuk Spor Takım - Mavi/Beyaz', 'İki parçalı erkek çocuk spor takımı. Eşofman altı ve sweatshirt seti.', 279.00, NULL, 'CKZ-7002', '%80 Pamuk, %20 Polyester', 'child', '128 cm (8 yaş)', '115 cm', '14 gün iade garantisi.', 75, 4.6, 4.7, 2),

-- Güzellik
(14, 5, 'GlowLab', 'C Vitamini Aydınlatıcı Serum 30ml', 'Konsantre C vitamini formülüyle leke azaltma ve cilt aydınlatma. Tüm cilt tiplerine uygun.', 349.00, 449.00, 'GLW-8001', 'Su, Askorbik Asit %15, Hyaluronik Asit, Niasinamid', 'female', NULL, NULL, '30 gün iade garantisi. Ambalaj açılmamış olmalı.', 200, 4.8, 4.9, 2),

(15, 5, 'NaturalCare', 'Argan Yağı Saç Bakım Seti 3lü', 'Şampuan, saç kremi ve serum içeren komple argan yağı bakım seti. Saçlara canlılık ve ışıltı kazandırır.', 289.00, NULL, 'GLW-8002', 'Argan Yağı, Keratin, Panthenol', 'female', NULL, NULL, '14 gün iade.', 150, 4.6, 4.7, 1),

-- Elektronik
(16, 6, 'SoundPro', 'Bluetooth Kulaklık Gürültü Engelleyici ANC', 'Aktif gürültü engelleyici teknolojili premium kulaklık. 40 saat pil ömrü, katlanabilir tasarım.', 1799.00, 2499.00, 'ELK-9001', NULL, 'unisex', NULL, NULL, '14 gün iade. Ambalaj içeriği eksiksiz olmalı.', 45, 4.7, 4.8, 3),

(17, 6, 'ChargeFast', 'Hızlı Şarj Adaptör 65W USB-C', '65W GaN teknolojili, çoklu cihaz şarj desteği sunan kompakt adaptör. 3 çıkış portu.', 399.00, 549.00, 'ELK-9002', NULL, 'unisex', NULL, NULL, '14 gün iade garantisi.', 180, 4.8, 4.8, 2),

-- Spor
(18, 7, 'FitGear', 'Yoga Matı 6mm Kaymaz Yüzey - Mor', '6mm kalınlığında, çift taraflı kaymaz yüzeyli yoga matı. Hafif ve katlanabilir. Taşıma askısı dahil.', 249.00, NULL, 'SPR-0001', 'TPE (Doğal Kauçuk)', 'female', NULL, NULL, '14 gün iade.', 220, 4.5, 4.6, 2),

(19, 7, 'ActiveRun', 'Erkek Koşu Ayakkabısı - Siyah/Neon', 'Hafif taban ve nefes alan örgü kumaşlı koşu ayakkabısı. Yüksek enerji geri dönüşümlü köpük taban.', 899.00, 1199.00, 'SPR-0002', 'Örgü Kumaş Üst, EVA Taban', 'male', '42', '180 cm', '14 gün iade. Ayakkabı kutusunda iade edilmeli.', 60, 4.7, 4.7, 3),

-- Kitap
(20, 8, 'İş Bankası Yayınları', 'Dune - Frank Herbert (Ciltli)', 'Bilim kurgunun başyapıtı. Frank Herbert\'in efsanevi Dune serisi, özel ciltli baskı. Türkçe çeviri.', 199.00, 249.00, 'KTP-1001', NULL, 'unisex', NULL, NULL, '14 gün iade. Kitap deforme olmamış olmalı.', 300, 4.9, 4.9, 1);

-- ============================================================
-- ÜRÜN VARYASYONLARl (Gerçek Unsplash/Picsum URL'leri)
-- ============================================================
INSERT INTO product_variants (product_id, color_name, color_hex, main_image) VALUES

-- Elbise 1 varyasyonları
(1, 'Çiçek Desenli Beyaz', '#FAFAFA', 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600&q=80'),
(1, 'Çiçek Desenli Pembe', '#FFB6C1', 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=600&q=80'),
(1, 'Çiçek Desenli Mavi', '#87CEEB', 'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=600&q=80'),

-- Elbise 2 varyasyonları
(2, 'Siyah', '#1A1A1A', 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=600&q=80'),
(2, 'Kırmızı', '#DC143C', 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&q=80'),
(2, 'Lacivert', '#000080', 'https://images.unsplash.com/photo-1551803091-e20673f15770?w=600&q=80'),

-- Elbise 3 keten
(3, 'Bej', '#F5F5DC', 'https://images.unsplash.com/photo-1502716119720-b23a93e5fe1b?w=600&q=80'),
(3, 'Beyaz', '#FFFFFF', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80'),

-- Tişört 4 - Basic
(4, 'Beyaz', '#FFFFFF', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80'),
(4, 'Siyah', '#000000', 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=600&q=80'),
(4, 'Gri', '#808080', 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=600&q=80'),

-- Tişört 5 - Crop
(5, 'Pembe', '#FFB6C1', 'https://images.unsplash.com/photo-1563889958749-625da3a6a0d2?w=600&q=80'),
(5, 'Sarı', '#FFD700', 'https://images.unsplash.com/photo-1564859228273-274232fdb516?w=600&q=80'),

-- Kaban
(6, 'Camel', '#C19A6B', 'https://images.unsplash.com/photo-1543076447-215ad9ba6923?w=600&q=80'),
(6, 'Siyah', '#1A1A1A', 'https://images.unsplash.com/photo-1548454782-15b189d129ab?w=600&q=80'),

-- Mont - Puffer
(7, 'Siyah', '#1A1A1A', 'https://images.unsplash.com/photo-1608063615781-e2ef8c73d114?w=600&q=80'),
(7, 'Bej', '#F5F5DC', 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600&q=80'),

-- Erkek Kazak
(8, 'Lacivert', '#000080', 'https://images.unsplash.com/photo-1620012253295-c15cc3e65df4?w=600&q=80'),
(8, 'Gri', '#808080', 'https://images.unsplash.com/photo-1519058082700-08a0b56da9b4?w=600&q=80'),
(8, 'Bordo', '#800020', 'https://images.unsplash.com/photo-1614093302611-8efc4b92f1fe?w=600&q=80'),

-- Hoodie
(9, 'Gri', '#808080', 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=600&q=80'),
(9, 'Siyah', '#000000', 'https://images.unsplash.com/photo-1578768079052-aa76e52ff3ea?w=600&q=80'),

-- Polo tişört
(10, 'Beyaz', '#FFFFFF', 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=600&q=80'),
(10, 'Lacivert', '#000080', 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&q=80'),

-- Mont
(11, 'Haki', '#8B7355', 'https://images.unsplash.com/photo-1544923246-77307dd654cb?w=600&q=80'),
(11, 'Siyah', '#1A1A1A', 'https://images.unsplash.com/photo-1509551388413-e18d0ac5d495?w=600&q=80'),

-- Çocuk Elbise
(12, 'Pembe', '#FFB6C1', 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=600&q=80'),
(12, 'Lila', '#C8A2C8', 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=600&q=80'),

-- Erkek Çocuk Takım
(13, 'Mavi/Beyaz', '#4169E1', 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&q=80'),
(13, 'Gri/Siyah', '#808080', 'https://images.unsplash.com/photo-1503944168849-8bf86875bbd8?w=600&q=80'),

-- Serum
(14, 'Standart', '#FFF8DC', 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'),

-- Saç Bakım Seti
(15, 'Standart', '#F0FFF0', 'https://images.unsplash.com/photo-1526045431048-f857369baa09?w=600&q=80'),

-- Kulaklık
(16, 'Siyah', '#1A1A1A', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80'),
(16, 'Beyaz', '#FFFFFF', 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600&q=80'),

-- Şarj
(17, 'Beyaz', '#FFFFFF', 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=600&q=80'),

-- Yoga Matı
(18, 'Mor', '#9370DB', 'https://images.unsplash.com/photo-1601925228523-5eb60e12fd93?w=600&q=80'),
(18, 'Mavi', '#4169E1', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=80'),

-- Koşu Ayakkabısı
(19, 'Siyah/Neon Sarı', '#000000', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80'),
(19, 'Beyaz/Gri', '#FFFFFF', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&q=80'),

-- Kitap
(20, 'Ciltli', '#8B4513', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&q=80');

-- ============================================================
-- YORUMLAR (50+ Gerçekçi Müşteri Yorumları)
-- ============================================================
INSERT INTO reviews (product_id, user_id, rating, comment_text, photo_url, is_verified_buy) VALUES

-- Ürün 1: Çiçek Elbise (12 yorum)
(1, 2, 5, 'Harika bir elbise! Kumaşı çok kaliteli, vücudu nefes aldırıyor. Boyum 168cm, M beden aldım, tam oturdu. Kesinlikle tavsiye ederim.', 'https://images.unsplash.com/photo-1488161628813-04466f872be2?w=300&q=70', 1),
(1, 3, 4, 'Genel olarak güzel ama renk fotoğraftakinden biraz daha soluk. Yine de kalitesi için fiyatı uygun sayılır.', NULL, 1),
(1, 4, 5, 'Düğün törenine giydim, çok beğendiler! Kumaş saten gibi kayıyor, çok şık.', 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=300&q=70', 1),
(1, 5, 5, 'İkinci kez sipariş veriyorum. Yıkandıktan sonra şekli bozulmuyor, renk atmıyor. Süper!', NULL, 1),
(1, 6, 3, 'Beden tablosuna göre S aldım ama biraz bol geldi. Bir küçük alsaydım daha iyi olurdu. Kumaş kaliteli.', NULL, 1),
(1, 7, 5, 'Eşime aldım doğum günü hediyesi olarak. Çok mutlu oldu! Paketleme de çok özenli.', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=300&q=70', 1),
(1, 8, 4, 'Güzel elbise, kargo hızlı geldi. Sadece fermuarın kalitesi daha iyi olabilirdi.', NULL, 1),
(1, 9, 5, 'Her dem taze bir his veriyor. Viskon kumaş gerçekten nefes aldırıyor. Yazın terletmiyor.', NULL, 1),
(1, 10, 5, 'Mükemmel bir ürün! Fiyat kalite dengesi harika. 3 renk birden aldım.', 'https://images.unsplash.com/photo-1485518882345-15568b007407?w=300&q=70', 1),
(1, 2, 4, 'Kuru temizleme denemedim ama makine yıkamada sorun yaşamadım. Önlü arkası farklı desende, bir de tam anlamıyla tanımıyorsunuz.', NULL, 1),
(1, 3, 5, 'Bu markayı ilk kez deniyorum, çok memnun kaldım. Artık sadık bir müşterim.', NULL, 1),
(1, 4, 4, 'Omuz kısmı biraz dar ama genel olarak çok iyi. Kaliteli işçilik.', NULL, 1),

-- Ürün 2: Saten Gece Elbisesi (8 yorum)
(2, 5, 5, 'Mezuniyet gecesinde giydim, herkes hangi markaya ait diye sordu. Çok şık ve kaliteli.', 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=300&q=70', 1),
(2, 6, 5, 'Kumaşın dokusu gerçekten premium. Bu fiyata bu kaliteyi başka yerde bulamazsınız.', NULL, 1),
(2, 7, 4, 'Nişan törenimde giydim. Çok beğendiler. Sadece arka yırtmaç biraz fazla uzun.', NULL, 1),
(2, 8, 5, 'Harika! Oturduğumda kırışmıyor, saten düz duruyor. Tavsiye ederim.', 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=300&q=70', 1),
(2, 9, 5, 'Düğün davetiyesinde giydim. Tam bir prenses gibi hissettim!', NULL, 1),
(2, 10, 3, 'Elbise güzel ama teslimat geç geldi. 7 günde gelmesi gerekiyordu 10 günde geldi.', NULL, 1),
(2, 2, 5, 'Renk tam istediğim gibi. Koyu lacivert, çok zarif görünüyor.', NULL, 1),
(2, 3, 4, 'Bedenim küçüktü iade ettim, yeni beden çok güzeldi. İade süreci kolaydı.', NULL, 1),

-- Ürün 4: Basic Tişört (7 yorum)
(4, 2, 5, 'Kalitesi çok çok iyi. Yıkadıkça yumuşuyor, bozulmuyor. Gardrobumdaki en çok sevdiğim basic.', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=300&q=70', 1),
(4, 4, 4, 'Oversize beden tam beden uyumlu yani. Ben 65 kilo boyum 170 M aldım biraz serbest durdu.', NULL, 1),
(4, 5, 5, '4 adet aldım, her rengi bir. Hiç pişman değilim. Fiyat kalite dengesi mükemmel!', NULL, 1),
(4, 6, 5, 'Organik pamuk gerçekten fark yaratıyor. Cilde sürttüğünde hiç tahriş gelmiyor.', NULL, 1),
(4, 7, 4, 'Baskılı model değil ama sade şıklığı var. Herşeyle uyum sağlıyor.', NULL, 1),
(4, 8, 5, 'Mükemmel kumaş kalitesi. Hapsetmez, nefes aldırır. Kesinlikle tekrar alacağım.', 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=300&q=70', 1),
(4, 9, 3, 'Kargo gelirken biraz ezilmiş geldi ama ürünün kendisi güzel.', NULL, 1),

-- Ürün 8: Erkek Kazak (6 yorum)
(8, 3, 5, 'Merino yünü gerçekten fark yaratıyor. Kaşıntı yapmıyor, çok sıcak tutuyor. L beden tam oldu.', 'https://images.unsplash.com/photo-1620012253295-c15cc3e65df4?w=300&q=70', 1),
(8, 5, 5, 'İş hayatım için çok uygun. Pantolon üstü ile çok şık görünüyor.', NULL, 1),
(8, 7, 4, 'Güzel kazak, tek sorun yıkamada biraz çekiyor. Soğuk suda yıkamanızı tavsiye ederim.', NULL, 1),
(8, 9, 5, 'Kışın olmazsa olmaz. İçine tişört giymeden üst tek başına sıcak tutuyor.', NULL, 1),
(8, 2, 4, 'Renk çok güzel, tam istediğim lacivert. Kumaşı biraz kalın ama kışın bu iyi.', NULL, 1),
(8, 4, 5, 'Erkek kardeşime hediye aldım, çok beğendi. Paketleme de çok güzeldi.', NULL, 1),

-- Ürün 11: Mont (5 yorum)
(11, 3, 5, 'Eksi 10 derecede dışarı çıktım, montun içinde terledim! Bu kadar sıcak tutmasını beklemiyordum.', 'https://images.unsplash.com/photo-1544923246-77307dd654cb?w=300&q=70', 1),
(11, 5, 5, 'Çok kaliteli bir mont. Doğaç yürüyüşlerimde vazgeçilmezim oldu.', NULL, 1),
(11, 7, 4, 'Biraz ağır ama bu kadar sıcaklık için normal. Fiyat kaliteye değiyor.', NULL, 1),
(11, 9, 5, 'XL beden aldım, altına kalın kazak giyebiliyorum. Mükemmel!', NULL, 1),
(11, 2, 5, 'Rengi ve kesimi harika. Çok şık görünüyor. Kapüşonun kürk kısmı yumuşak.', NULL, 1),

-- Ürün 14: Serum (6 yorum)
(14, 4, 5, 'C vitamini serumunun en iyisi bu! 2 ayda lekelerim belirgin şekilde azaldı.', 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=300&q=70', 1),
(14, 2, 5, 'Sabah rutinimde kullanıyorum. Cildim çok parlak ve aydınlık görünüyor.', NULL, 1),
(14, 6, 4, 'Güzel serum, sadece biraz yağlı his bırakıyor. Kombine cilt tipleri için.', NULL, 1),
(14, 8, 5, 'Bu fiyata bu kalite inanılmaz! Köklü markalardan daha etkili.', NULL, 1),
(14, 10, 5, '3 adet aldım. Bir tanesini annem için. İkimiz de çok memnunuz.', NULL, 1),
(14, 3, 3, 'Sonuçları görmek için birkaç hafta kullanmak gerekiyor, sabırsız kişilere tavsiye etmem.', NULL, 1),

-- Ürün 16: Kulaklık (6 yorum)
(16, 5, 5, 'Gürültü engelleme mükemmel! Metro ve uçakta kullandım, fark yaratıyor. Bas sesi çok derin.', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&q=70', 1),
(16, 7, 5, '40 saat pil ömrü gerçek! Bir haftada bir sarj ediyor insan.', NULL, 1),
(16, 9, 4, 'Ses kalitesi çok iyi ama uzun süre takmak biraz rahatsız ediyor. Belki alışırsınız.', NULL, 1),
(16, 3, 5, 'Bu fiyata bu kalite! Çift kat daha pahalı markaları tavsiye etmem artık.', NULL, 1),
(16, 6, 5, 'Bluetooth bağlantısı çok stabil. Sinyal kesilmesi hiç olmadı.', NULL, 1),
(16, 8, 4, 'Güzel ürün, tek sorun kasa biraz ince görünüyor. Düşürmemek lazım.', NULL, 1),

-- Ürün 20: Kitap (5 yorum)
(20, 2, 5, 'Bu kitap bir başyapıt! Türkçe çevirisi de çok başarılı. Ciltli baskısı da çok güzel.', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=300&q=70', 1),
(20, 4, 5, 'Bilimkurgu okuyucuları için mutlak okuma listesinde olmalı. Harika!', NULL, 1),
(20, 6, 5, 'Film izlemeden önce mutlaka okunmalı. Kitap çok daha derin.', NULL, 1),
(20, 8, 4, 'Anlatım biraz ağır başlı ama sonunda çok güzel. Sabırla okunmalı.', NULL, 1),
(20, 10, 5, 'Ailenin diğer üyeleri de istedi, 3 tane birden sipariş verdim.', NULL, 1);
