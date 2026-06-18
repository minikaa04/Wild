# GÖREV PLANI VE TEKNİK ŞARTNAME (görev.md)

## 1. GİRİŞ VE TASARIM STANDARTLARI
**1.1. Proje Kapsamı ve Altyapı:**
Bu projenin temel amacı, yerel (localhost) ortamda XAMPP sunucusu üzerinde koşacak, PHP tabanlı, yüksek etkileşimli ve kapsamlı bir e-ticaret platformu inşa etmektir. 

**1.2. Tasarım Dili ve Renk Paleti:**
Platformun genel arayüz mimarisi ve bileşen yerleşimi tamamen **Wildberries** örnek alınarak kurgulanacaktır. Ancak renk şeması ve tematik yaklaşım olarak, ağırlıklı şekilde mavi ve açık mavi tonların kullanıldığı (Omozon / Amazon benzeri) güven verici, modern, sade ve ferah bir dizayn dili ("Amazon Blue" tonları) benimsenecektir.

**1.3. Sabit Menü (Sticky Header) Kullanımı:**
Kullanıcı deneyiminin (UX) kesintiye uğramaması adına, platformun üst bölümünde yer alan "Arama çubuğu, Adres/Kargo ikonu, Giriş Yap ikonu, Sepet ve Hamburger Menü" bileşenlerini barındıran üst şerit (Header), sayfa aşağı doğru ne kadar kaydırılırsa kaydırılsın ekranın en üstünde sabit (sticky) olarak kalacak ve asla kaybolmayacaktır. Başka bir sayfaya geçiş yapıldığında dahi bu bant, ekranın üst kısmındaki yerini her zaman koruyacaktır.

---

## 2. NAVİGASYON VE MEGA MENÜ MİMARİSİ
**2.1. Üst Panel (Header) Bileşenleri:**
Arayüzün en üstünde daimi olarak bulunacak bileşenler soldan sağa doğru şu şekilde konumlandırılacaktır:
*   **Sol Kısım:** Platformun resmi logosu, site ismi ve hemen yanlarında mega menüyü tetikleyecek üç yatay çizgili menü butonu (Hamburger Menu).
*   **Orta Kısım:** Site içindeki tüm ürünleri ürün adına (isimlendirmesine) göre sorgulayabilen geniş, interaktif arama motoru çubuğu (Search Bar).
*   **Sağ Kısım:** Birbiri peşi sıra yerleştirilmiş bildirim/eylem piktogramları:
    *   **Adres / Kargo İkonu:** Tıklanıldığında bir panel açılır. Bu panelde gönderim sürelerine dair bilgilendirme yer alır (Teslimat süresi satıcıdan satıcıya farklılık göstermekle birlikte, maksimum teslimat süresi "2 haftayı geçmeyecek" şekilde sistemsel bir kural metniyle garanti altına alınmıştır). Ayrıca kullanıcının sistemde saklı, kayıtlı olan teslimat adresleri burada listelenir.
    *   **Kişi (Hesap / Profil) İkonu:** Hesaba giriş portalıdır. Sisteme giriş yapmamış veya kayıt olmamış anonim ziyaretçiler bu ikona tıkladığında "Giriş Yap veya Profil Oluştur" şeklinde bir yetkilendirme penceresi açılır. Zaten giriş yapmış kullanıcılar için "Kullanıcı Paneli" sayfasına yönlendirir.
    *   **Sepet İkonu:** Tıklanıldığında içerisinde daha önce eklenen ürünlerin de listelendiği genel "Sepet" sayfasını direkt olarak açar.

**2.2. Kapsamlı Mega Menü (Kategoriler):**
Logonun yanındaki üç çizgili (Hamburger) menü ikonuna basıldığında ekranın sol tarafından aşağıya doğru kaydırılabilir (scrollable) sistemin tüm yapıtaşlarını barındıran mega departman menüsü ortaya çıkar.
**Ana Departmanlar (Kategoriler):** Kadın, Çocuk, Erkek, Ev, Güzellik, Aksesuarlar, Elektronik, Oyuncak, Mobilya, Gıda (Ürünler), Çiçek, Beyaz Eşya, Ev Gereçleri (Züccaciye / Temizlik), Spor, Evcil Hayvan Ürünleri (Zoo), Otomotiv (Araç) Ürünleri, Kitaplar, Mücevherat, Bahçe ve Yazlık, Sağlık, Kırtasiye.

**2.3. Dinamik Alt Kategoriler (Hover ve Click Tetiği):**
Kullanıcı fare okunu (mouse) "Kadın", "Erkek" veya "Çocuk" gibi ana departmanların üzerine getirdiğinde veya tıkladığında ilgili departmana ait oldukça geniş yan alt menüler listelenir:
*   *Yetişkin Temel Alt Kategorileri:* Bluzlar ve Gömlekler, Dış Giyim, Jumper/Süveter, Balıkçı Yaka (Boğazlı) Kazaklar, Kot Pantolonlar (Jeans), Tişörtler, Elbiseler, Etekler, Ayakkabılar, Takım Elbiseler vb.
*   *Çocuk Kategorisine Özel Ekstralar:* Sistemin çocuklara özel alanında yetişkin kıyafet versiyonlarına ek olarak çocukların biyolojik gereksinimlerine hitap eden ek listeler mevcuttur: Bebek Bezleri, Çocuk Mamaları (Bebek Sütü Formülleri), Emzikler ve Biberonlar.

---

## 3. ZİYARETÇİ (KAYITSIZ KULLANICI) FONKSİYONLARI VE ÖN YÜZ
**3.1. Ana Sayfa ve Dönen Vitrin (Carousel Banner):**
*   Site ana sayfasına giriş yapıldığında, ziyaretçiyi oldukça geniş ekranı kaplayan, etkileşimli, kendi etrafında dönen veya periyodik slayt geçişi yapan büyük bir afiş/reklam bandı karşılayacaktır.
*   **Kesin Kısıtlama:** Bu alanda platform/site harici 3. şahıs şirketlerin reklamlarının döndürülmesi kesinlikle yasaktır. Bu vitrin tamamen e-ticaret sitesinin içinde hali hazırda mağazası bulunan iç satıcıların onaylı kampanyalarına, promosyonlarına ve bizzat platformdaki ürünlere ayrılmıştır.

**3.2. Dinamik Ürün Kartı Şablonu:**
Afiş/Slider bölümü geçildikten hemen sonra ziyaretçilere çok çeşitli, karmakarışık türde ürünleri gösteren ürün listeleme kartları sıralanacaktır. Standart bir ürün kartının mimarisi yukarıdan aşağıya şu şekilde olacaktır:
*   En üstte net çekilmiş büyük bir ürün görseli (Fotoğraf).
*   Görselin hemen altında net ve okunur fontla rakamsal "Fiyat" ibaresi.
*   Fiyatın altında ürünün "Tam Adı (Türü / Modeli)".
*   Sol veya sağ alt kısımda içinde "Sepet İkonu" olan bir eyleme çağrı butonu ve hemen yanında "Tahmini Teslimat (Ulaşma) Tarihi" ibaresi.
*   **Kalp (Favori) İkonu Mantığı:** Kartın ana çerçevesinin "sağ üst" köşesine yapışık olarak favorilere ekleme yapılmasını sağlayan bir Kalp Piktogramı bulunacaktır.
    *   Sistem, kullanıcının seçtiği ürün "Kıyafet veya Ayakkabı" kategorisinde ise tıklandığı an Kalp ikonu bir pencere (modal) fırlatarak ürün bedenini/numarasını seçmesini ister.
    *   Eğer tıklayan kişi sisteme kayıt olmayan veya giriş yapmamış bir ziyaretçi ise; Kalp'e basıldığı an favori işlemi durdurulur ve zorunlu olarak "Kayıt / Oturum Açma" paneli belirecektir.

---

## 4. ÜRÜN SAYFASI MİMARİSİ
Herhangi bir ürün kartına resminden veya isminden basıldığında sistem o ürünü master sayfaya alır. Sayfa Trendyol ve Wildberries harmanı, son derece gelişmiş bir tasarımdır.

**4.1. Sol Platform (Medya Gösterimi):**
Sol çerçeve tamamen büyük ve yakınlaştırılabilir (zoom-in) ürün medya, video ve interaktif fotoğraf galerisine ayrılmıştır.

**4.2. Sağ Panel (Temel Detaylar):**
Sağ alanda geniş puntolarla Ürünün Adı, ürüne verilmiş Ortalama Puan (Yıldız Değerlendirmeleri) ve "Ürün Soruları (Müşteri & Satıcı Soru-Cevap Seksiyonu)" konumlanır.

**4.3. Dinamik Geçişli Varyasyon Sistemi:**
Sağ taraftaki ana bilgilerin hemen altında yatay bir hat üzerinde aynı ürünün farklı prototipleri (örneğin tasarımın başka bir rengi veya türevi) küçük daireler/kutucuklar halinde listelenir.
*   *Çalışma Prensibi:* Eğer kullanıcı o hatta bulunan farklı bir renge tıklarsa; sistem sayfayı "Refresh" atmadan (yenilemeden) var olan ürünün tamamen değişmesini sağlar. Sadece ürün resmi değil; Ürün İsmi, Fiyatı, Ürün Kodu (Artikulu) ve Satıcı Puanı anlık olarak mutasyon geçirip seçilen yeni renge ait data özelliklerine bürünmek zorundadır.

**4.4. Kapsamlı Bilgi ve Spesifikasyon Blokları:**
Varyasyonların hemen altına doğru doğru kıyafetse bir tekstil tablosunu ifade eden "Beden Seçim" kutuları vardır. Bunların altında ise ilgili ürünün veri tabanından tamamen dinamik olarak çekilen bilgi demeti yer alır:
*   Ürün Kodu (Artikul / SKU numarası)
*   Malzeme İçeriği / Formasyon (Örn: %100 Pamuk)
*   Cinsiyet Tipi
*   Fotoğraftaki Modelin Üzerindeki Beden
*   Fotoğraftaki Modelin / Mankenin Boy Ölçüsü (cm)
*   Ürün İade Şartları ve Para İadesi Koşulları Hakkında Bilgilendirme Kutucuğu.

**4.5. Sabit Sipariş Modülü (Sidebar):**
Sayfanın en sağında ürün verilerinden bağımsız, fareyi kaydırsa da inen yapışkan sağ panel sekmesi bulunur.
*   Büyük rakamla "Güncel Fiyat" vurgusu.
*   İki ayrı dev buton: **"Sepete Ekle"** ve **"Hemen Satın Al"**.
*   Sipariş modülünün alt katmanında: Mağazanın/Markanın Genel Adı, Seçili ürünün tahmini paketleme/ulaşma takvimi (Teslimat Tarihi) ve o satıcının sahip olduğu genel Puan Ortalaması.

**4.6. Sosyal Kanıt ve Çapraz Satış Olanakları (Footer Üstü):**
*   **Kullanıcı Yorumları:** Sayfanın altına inildiğinde, ürünü daha önce satın almış bireylerin bıraktığı metinsel değerlendirmeler ve kullanıcıların platforma kendi yükledikleri "Fotoğraflı Yorumlar" bulunur.
*   **Benzer Ürünler (Öneri):** Yorumların altında yatay doğrultuda kaydırılabilen "Benzer Modeller / Alternatif Seçenekler" afiş stantları yer alır (Fotoğraflarıyla birlikte detaylı olarak eşleşen türevler oluşturulacaktır).

---

## 5. SEPET VE SATIN ALMA SÜRECİ
**5.1. Sepet Yönetimi:**
Bir ürün için "Sepete Ekle" butonuna basıldığında sistem dinamik Sepet arayüzünü açar. Eğer kullanıcı o oturumdan önce de farklı ürünleri sepete koymuş ve bekletmişse; geçmişteki tüm bu ürünler önbellekten veya veri tabanından getirtilerek sepette listelenmek zorundadır.
*   *Sepet Görünümü:* Sol panel sepetteki görselleştirilmiş ürün maddeleridir. Sağ kenar/yan pencere ise sepetteki tüm malzemelerin KDV dahil Total/Genel Toplam Bakiye (Fiyat) miktarını gösteren tutar tablosunu ve nihai büyük "**Siparişi Ver / Tamamla**" butonunu barındırır.

**5.2. Checkout (Sipariş Tamamlama ve Validasyon):**
Sağdaki Siparişi Ver butonuna tıklandığında müşteri Adres/Teslimat/Ödeme paneline düşer.
*   *Validasyon ve Hata Vurgulama:* Teslimat şekli (adres bölümü), ödeme yöntemi formları ve müşterinin kişisel kimlik verilerinden "herhangi biri veya tamamı boş ise"; sistem eksik alanları kırmızımsı bir çerçeveyle (Highlighting) parlatacak ve zorunlu veri girişini talep edecektir. 
*   *Zorunlu Kayıt:* Yukarıdaki doğrulama aşamasında sisteme üye olmayan (ya da hesaba girmemiş) her kullanıcı, ödeme veya adres tipini seçmeye kalktığı an "Giriş/Kayıt Cihazı" pop-up uyarısıyla durdurulacaktır.

**5.3. Interaktif Teslimat / Şehir Haritası Modülü:**
"Teslimat Yöntemi / Adres Seçimi" formuna tıklandığı taktirde dinamik bir etkileşimli Şehir Haritası patlayacaktır. Kullanıcının şehri / haritası açılacak ve harita penceresinin tam üstünde spesifik sokak, teslimat yeri, teslimat noktası veya ev bölgesi aratarak (Search Places and Addresses) nokta atışı teslimat lokasyonu tayini yapılacaktır.

**5.4. "Hemen Satın Al" Seçeneğinin Prensibi (One-Click Buy):**
Kullanıcı Ürün Detaylarında "Sepete Ekle" demek yerine "Hemen Satın Al" tuşunu tercih eder ise; arka planda dolu olan sepet tutarı tamamen sistem tarafından "görmezden gelinir / by-pass edilir".
Sepetin aksine açılan pencerede yalnızca ve yalnızca onay verilen "tekil/spesifik" ürün görünür; müşteriden tek bir ürüne ait olan o kısıtlı yekün ücret tahliyesi, o ürünün taşıma maliyeti ve sadece bu sürece özel ödeme/kargo onayı alınır. Geri kalan sepet arkaplanda dokunulmaz tutulur.

---

## 6. KULLANICI PANELİ (PROFİL / DASHBOARD) HİZMETLERİ
Ziyaretçiden farklı olarak, sisteme entegre olan ("Giriş Yapan") kişiler sitenin sağ üst bantındaki Kişi İkonuna tıkladıklarında, kendilerine tahsis edilmiş üst düzey Yönetim Arayüzüne (Kullanıcı Kabinesi / Profil Alanı) yönlendirilir. Site teması dışarıdan aynı kalsa da, arka panel özellikleri şunlardır:

**6.1. Operasyonel Paneller:**
*   **Siparişlerim:** Kullanıcının cüzdanından parası çıkmış olan ancak hala lojistik döngüsünde olan güncel alımlarının sergilendiği alandır. Satın alınan mülk, kartında görünür ve statüsü gerçek zamanlı olarak bildirilir: *"Gönderildi"*, *"Kargoda/Yolda"*, *"Tahsilat Noktasında / Ulaştı"*.
*   **Satın Almalarım (Geçmiş):** Zaman kısıtlaması olmaksızın kullanıcının sisteme üye olduğu andan itibaren gerçekleştiği her türlü tamamlanmış satın almanın kayıt/fatura defteridir. Kullanıcı beğendiği eski ürünü burada kolayca bularak anında "Yeniden Satın Al" tetiğini çekerek ürünü hızla sipariş edebilir.
*   **Bekleme Listesi (Wish-List):** Ürün sayfalarında, arama kısmında veya ana katalogda "Kalp İkonu" aracılığıyla beğenilen ve arşive atılan parçaların birikerek kullanıcının şahsına münhasır sanal dolabında rezerve gibi tutulduğu bölümdür.

**6.2. Temel Veri ve Para Ayarları:**
*   *Öz Kayıt (Kişisel Veri):* Kullanıcının form aracılığıyla bizzat yazdığı Vergi No (TC), İsim, Soyisim, Doğrulanmış GSM (Telefon numarası) ve doğrulanmış E-posta gibi iletişim öğelerinin konfigüre edildiği yerdir.
*   *Ödeme Sekmesi:* Daha sonradan hızla ödeme yapmak amacıyla kullanıcının kendi hür idaresiyle sisteme bırakmış olduğu şifrelenmiş kayıtlı ATM/Kredi Kartları listesi bu sekme altındadır. 
*   *Geri Ödemeler (İade Edilen Paralar):* Bugüne kadar yapılan tüm iade tutarlarının, sisteme ve kullanıcının kartına gerçekleşen nakit iadesi ve hareket akışının barındırıldığı hesap özetidir.
*   *Satın Alma Geçmiş Geçidi:* Kayıtlı spesifik bir kart ile bu site üzerinden tahsil edilmiş genel işlemlerin listesi.
*   *Ürün İadesi / Yöneltme:* Sorunlu ürünlerin geri kargolanması/cayma hakkı beyanlarının doldurulacağı ve satıcıya iletilmek üzere "İade Taleplerinin" bekletildiği operasyon platformudur.

**6.3. Platform ve Arayüz (Sistem) Ayarları:**
Paneller sekmesinde, platformun yazılımsal yapısını kullanıcı lehine değiştirebileceğimiz ince ayar menüleri mutlaka bulunacaktır:
*   **Bildirim Kontrolü:** SMS, Mail reklamları vs. için alınacak sistem habercilerini / uyarı mesajlarını "Açma ya da tamamen Kapatma" modülü.
*   **Dil ve Para Birimi:** Bölgesel/Yerel okuma dili tercihleri ve ödeme yapılacak sanal kur.
*   **Tema Ayarları (Ara Yüz Mode):** Uygulamanın ışık yapısı. Kullanıcı buradan Aydınlık Tema (Light), Karanlık Tema (Dark Mode) ve Sistem Varsayılanı seçimlerini yapar. *Önemli Not: Sistemin en temel "Varsayılan" temasının kökeni daima beyaz ve mavi renk cümbüşü (White/Blue) olacaktır.*
*   **Çıkış (Session Kill):** Sistem çerezlerini ve geçici oturumu şifreli şekilde sonlandıran "Çıkış Yap" eylemi.

---

## 7. KAPSAMLI YÖNETİM PANELİ (ADMIN CONTROL CENTER)
Platformun arka planda sorunsuz yürümesi satıcı/yönetici araçlarına bağlıdır. Sistemde sadece root admin yetkisine sahip bireylerin erişebileceği bir arayüz tasarlanacaktır.

**7.1. Ürün ve Stok Yönetimi (Katalog Kontrolü):**
*   **Tam Yetki (CRUD):** Yeni ürün girişi (Ekleme), mevcut ürünü Veritabanından Komple Silme ve ürün kimliğini Güncelleme.
*   **Stok & Fiyat Manipülasyonu:** Ürünün stok durumunu aktif/pasif (tükendi) yapma ve anlık indirim veya zam (Fiyat manipülasyonu) tanımlama mekanizması.

**7.2. Kullanıcı, Hesap ve Güvenlik Yönetimi:**
*   Sistemde kaydı bulunan tüm üyelerin (kullanıcıların) tablo listesini ve aktivitelerini görme.
*   Şüpheli hesapları tek tuşla pasife alma (Dondurma / Ban).
*   Yetki Atamaları: Sadece Admin'in, normal bir kullanıcıyı "Admin" (Yönetici) veya "User" statülerine atayabilmesi.
*   Müşterilerin şifre sıfırlama taleplerini görüntüleme ve yönetme.

**7.3. İçerik ve Moderasyon (Yorum Kontrolü):**
*   Müşterilerin ürünlere bıraktığı tüm yorumları ve Soru-Cevap modülündeki metinleri tek bir ekranda (Denetim Merkezi) kontrol etme.
*   Küfürlü, uygunsuz içerikleri veya manipülatif (Sahte) yorumları tespit edip "Tek Tıkla Silme / Kaldırma" fonksiyonu.

**7.4. Sipariş, Kargo ve Lojistik Masası:**
*   **Genel Bakış:** Müşterilerin tamamlamış olduğu tüm fiziki siparişlerin cüzdan, kime ve nereye gideceğine dair detaylı fatura/fiş dökümlerinin incelenmesi.
*   **Kargo Ataması:** Siparişlere manuel olarak Kargo Takip Numarası ekleme/güncelleme imkanı.
*   **Statü Manipülasyonu:** Sipariş paketinin lojistik aşamasını manuel tıkla ilerletme: *"Hazırlanıyor", "Kargoda", "İptal Edildi" veya "Tamamlandı"*.

**7.5. Dinamik İstatistikler (Genel Dashboard):**
*   Admin siteye girdiği an özet analiz paneline erişir.
*   *Platform Genel İstatistiği:* Toplam Satış Hacmi (Ciro), Toplam Canlı/Aktif Kullanıcı Sayısı ve Platformda En Çok Satılan Ürünlerin (Top Seller) grafiksel veya basit metinsel veri özeti.

---

## 8. ZENGİN MOCK DATA & GÖRSEL KALİTE GEREKSİNİMLERİ
Projenin müşteri/jüri sunumunda tatmin edici ve tam çalışır gözükmesi adına sunum öncesinde veritabanı boş bırakılmayacak, şu şekilde zenginleştirilecektir:

*   **Veri Seti (Katalog):** Her biri özenle seçilmiş, gerçekçi pazar değerlerine ve Wildberries/Trendyol algısına uygun en az 20 adet ürün sunum için önceden kurgulanacaktır.
*   **Medya Yükü (High-Res):** Ürün görselleri kesinlikle "placeholder (yer tutucu)" griliğinde olmayacaktır. Unsplash veya bizzat Wildberries üzerinden çekilmiş, yüksek çözünürlüklü gerçek URL'ler veya dosyalar kullanılacaktır.
*   **Detay Derinliği:** Bu 20 ürüne de istisnasız olarak manken/model bilgileri (Boy/Beden oranları), pamuk/kumaş/hammadde oranları (Tam teknik özellikler) girilecektir.
*   **Sosyal Kanıt Simülasyonu:** Siteyi yaşayan bir organizma gibi göstermek amacı ile spesifik popüler ürünlerin altına 50+ adet mantıklı, doğal dille yazılmış gerçekçi kullanıcı yorumları atılacaktır. Bu yorumların bir kısmı inandırıcılık teşkil etmesi adına zorunlu olarak "Fotoğraflı İnceleme" formatında olacaktır.

*(Yukarıdaki bu analiz ve planlama yönergesi uyarınca tek bir element dahi atlanmadan tasarlanacak platformun alt yapısının inşasına geçilecektir)*.
