# Proje Geliştirme Planı (plan.md)

Bu plan, `görev.md` içerisindeki gereksinimleri baz alarak projenin PHP ve XAMPP altyapısı üzerinde nasıl inşa edileceğini, teknik süreçlerini ve aşamalarını tanımlar.

## User Review Required

> [!IMPORTANT]
> Lütfen aşağıdaki aşamaları, veritabanı tablolarını ve sistem yaklaşımını dikkatlice inceleyin. Onay verdiğinizde doğrudan Aşama 1 ile operasyona başlanacaktır.

## Proposed Changes / Geliştirme Aşamaları

### Aşama 1: Veritabanı Mimarisi ve Zengin Mock Data (MySQL & XAMPP)
Projenin beyni olarak tüm statik ve dinamik verilerin tutulacağı bir MySQL şeması (Schema) tasarlanacaktır. Veritabanı oluşturulduğu an **Zengin Mock Data Stratejisi** ile doldurulacaktır:
* **Zengin Mock İçerikleri (Seeding):** Unsplash veya Wildberries gerçek görsel URL'leri kullanılarak detaylı 20+ ürün eklenecek. Manken boyu/bedeni, kumaş özellikleri eksiksiz doldurulacak. İnandırıcılık teşkil etmesi için 50'den fazla, bazıları fotoğraflı gerçekçi kullanıcı yorumu sisteme entegre edilecek.
* **Kullanıcı Yönetimi:** `users` (id, isim, email, tel, şifre_hash), `user_addresses` (id, user_id, sehir, tam_adres)
* **Katalog & Navigasyon:** `categories` (parent_id ile sonsuz hiyerarşi/mega menü sağlama - kategori türü yetişkin/çocuk vs.)
* **Ürün Merkezi:** `products` (id, title, price, sku, donanım/kumaş, model_bedeni, cinsiyet), `product_variants` (id, product_id, color_name, detayli_tasarim_resmi).
* **Etkileşim:** `wishlist` (kalp favorileri), `reviews` (yıldız, tarih, yorum yazısı, fotoğraf_url).
* **Satış / Sepet:** `cart_items` (kullanıcı veya oturum ID ile eşleşen geçici alışveriş aracı), `orders` (sipariş statüsü: kargoda, teslim vs.), `order_items`.

### Aşama 2: Çekirdek Tasarım ve Frontend Sistemi
"Amazon Blue" ve "Wildberries" şablon dinamiklerinin sayfaya oturtulması:
* **HTML & Global CSS:** Grid ve CSS Flexbox sistemleri kurularak Wildberries kart yerleşim düzeni inşa edilecek.
* **Sticky Header:** Menü, arama motoru, logo ve sepetin CSS `position: sticky; z-index: 999;` ile ekran tepesine kazınması.
* **Mega Menü Dinamikleri:** Mouse üzerine geldiğinde (hover) DOM elementlerini gösteren JS kodlarının ve geçiş (transition) animasyonlarının yazılması.
* **Responsive Arayüz:** Masaüstü ve ikincil ekranlara uygun akışkan (fluid) tasarımın yapılması.

### Aşama 3: Backend Altyapısı (PHP PDO & Oturumlar)
* **XAMPP Bağlantısı:** `db.php` dosyası oluşturularak güvenli (Prepared Statements) MySQL bağlantı mekanizmasının hazır hale getirilmesi.
* **Oturum ve Güvenlik:** PHP `session_start()` fonksiyonuyla hesap (Login/Register) süreçleri. Misafir ziyaretçiler için sepetin oturum hafızasında tutulması (Guest Session ID).
* **Zorunlu Modal Pop-up:** Ziyaretçi kalbe tıkladığında veya hemen satın al dediğinde PHP/JS entegrasyonuyla sayfa yenilenmeden Giriş Yap modal'ının asenkron gösterilmesi.

### Aşama 4: Ana Sayfa Vitrini ve Dinamik Listeleme
* **Dönen Vitrin (Carousel):** JS bazlı resim slider'ı içine ürün tablosundaki kampanya resimlerinin PHP ile dökülmesi. Reklam kısıtlamalarına göre yalnızca site içi ürünlerin listelenmesi.
* **Ürün Kartları:** Katalog/Card şablonunun oluşturulup, PHP `while` döngüleriyle veritabanındaki 30-50 ürünün fiyat, resim ve kalp ikonu ile listeye yazdırılması.
* **Arama Motoru (Search):** LIKE sorgusu temelli aktif filtre-arama sayfasının oluşturulması. Açılır menüden gelen "Adres Kargo Teslimat Süresi" bilgilendirmelerinin HTML'e basılması.

### Aşama 5: Ürün Detay Sayfası ve AJAX İnteraktivitesi
Burası en kritik "yenilenmeyen (asycn)" veritabanı yüklemasını içerir:
* **Galeri ve Genel Şablon:** Sol tarafta fotoğrafların, sağ tarafta Fiyat, Seçenek, SKU (Artikul) panellerinin yapısı.
* **Anlık Varyasyon (Prototipler) Mutasyonu:** Kullanıcı bir ürünün "mavi" modelinden "kırmızı" renk ikonuna tıkladığında, JavaScript (AJAX) isteği atılarak PHP'den o ürünün kırmızı verilerinin talep edilmesi. Gelen ürün görselinin, ürün numarasının, fiyatın sayfa (DOM) yenilenmeden anında değiştirilmesi.
* **Beden ve Kalp Seçimi:** Giysi grubunda beden seçimi yapılmadan sepete ekletmeyen uyarı validasyonunun tasarlanması.
* **Kullanıcı Yorumları:** Sayfa aşağı indirildiğinde değerlendirmelerin asenkron listelenmesi.

### Aşama 6: Gelişmiş Sepet ve "Checkout" Süreci
* **Sepet (Cart) Yan Paneli:** Sağdan kayarak veya pop-up olarak açılan genel sepet görünümü. PHP oturumundan eski ürünlerin birleştirilip toplam tutarın yazdırılması.
* **Checkout ve Form Doğrulama (Validation):** Adres / Kart bilgilerinin boş olup olmadığı JavaScript + PHP ile denetlenerek, hata varsa kutucuklara Kırmızı Highlight glow verilmesi.
* **Hemen Satın Al Özelliği:** Tıklanılan spesifik ürün için normal sepeti pas geçerek (`$_SESSION['checkout_single_item']` ile) direkt ödeme onayı sayfasına çıkartması.
* **Şehir Haritası Paneli:** "Adres / Teslimat Seç" alanına tıklandığında Leaflet.js altyapısı veya statik etkileşimli bir harita modeliyle adres tayin panelinin render edilmesi.

### Aşama 7: Kullanıcı Paneli (Profile / Dashboard)
Sadece kayıtlı müşterilere özel profil sayfası.
* **Siparişlerim:** "Yolda", "Ulaştı" isimli statüleriyle PHP ile `orders` tablosundan listeleme.
* **Satın Almalar / Geçmiş:** Önceki yıllara ait siparişleri "Tekrar Satın Al / Sepete Ekle" tuşuyla dizmek.
* **Favoriler / Geri İadeler:** Wishlist tablosundaki kalplenen ürünlerin ve müşteri geri iade taleplerinin listelenmesi.
* **Kişisel Tema Ayarları:** Açık, Koyu ve Varsayılan tema konfigürasyonunun JavaScript Browser Storage'a (veya Cookie) kodlanıp tüm sitede CSS değişkenlerinin (Variable) manipüle edilmesi.

### Aşama 8: Kapsamlı Yönetim Paneli (Admin Control Center)
Arayüz ve kullanıcı deneyimi tamamlandıktan sonra, platformun mutfağı olan ve sadece "root" yetkililerinin girebileceği gizli Admin Paneli inşa edilecektir.
* **Ürün ve Stok Yönetimi (CRUD):** Yeni ürünlerin eklenmesi, görsellerin güncellenmesi, stok durumunun manipülasyonu ve indirim tanımlama işlemleri.
* **Kullanıcı / Güvenlik Modülü:** Kullanıcıları banlama, rol (admin/user) atama ve şifre sıfırlama taleplerini onaylama.
* **İçerik Moderatörlüğü:** Müşteri yorumlarını denetleme, uygunsuz fotoğrafları/yorumları tek tıkla silme ağı.
* **Lojistik ve Siparişler:** Sipariş paketlerinin durumunu (Hazırlanıyor, Kargoda vs.) manuel değiştirme ve fatura dökümlerini takibe alma.
* **İstatistikler (Dashboard):** Site hasılatı, kayıt sayıları ve top-seller ürün verilerinin grafiksel dökümleri.

## Notlar

> [!NOTE]
> Görsel ödeme simülasyonu (POS kullanılmayacak) ve Zengin Mock data stratejisi önceki aşamada teyit edilerek görev sırasına bağlandı.

## Verification Plan
* XAMPP üzerinden her aşamada `localhost/wild/index.php` test edilecek.
* MySQL işlemleri (CRUD) phpMyAdmin ile takip edilip veri tutarlılığı onaylanacak.
* Checkout/Harita aşamasının ve AJAX ürün (renk vs) değişken mutasyonlarının çalışırlığı browser araçları (Network sekmesi) ile denetlenecek.
