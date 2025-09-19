# Prime EMS Studios

İzmir Balçova'da premium EMS (Elektrik Kas Stimülasyonu) antrenmanları sunan fitness merkezi web sitesi.

## 🚀 Özellikler

### 🎨 Kullanıcı Özellikleri
- **Modern Tasarım**: Responsive ve kullanıcı dostu arayüz
- **EMS Teknolojisi**: i-motion ve i-model profesyonel cihazlar
- **Blog Sistemi**: SEO optimize edilmiş blog yazıları ve kategoriler
- **Randevu Sistemi**: Online randevu alma ve yönetim
- **Chatbot**: AI destekli müşteri asistanı
- **Çok Dilli Destek**: Türkçe odaklı, çoklu dil altyapısı hazır

### 🔍 SEO ve Performans
- **SEO Optimize**: Meta tagler, structured data, canonical URL'ler
- **Performance**: WebP optimizasyonu, lazy loading, database index'leri
- **Accessibility**: WCAG 2.1 uyumlu, ARIA labels, skip links
- **Güvenlik**: CSRF koruma, XSS prevention, secure headers

### 🛠️ Teknik Özellikler
- **E-posta Entegrasyonu**: SMTP ile otomatik e-posta gönderme
- **Veritabanı Yönetimi**: MySQL ile kapsamlı veri yönetimi, index optimizasyonu
- **Admin Paneli**: Kapsamlı yönetim arayüzü
- **API Sistem**: RESTful API endpoint'leri
- **Media Yönetimi**: Görsel optimizasyonu ve medya kütüphanesi
- **Analytics**: Detaylı istatistik ve raporlama
- **Blog Sistemi**: Tam entegre blog yönetimi, SEO optimize edilmiş
- **Güvenlik Sistemi**: CSRF, XSS, SQL injection koruması, rate limiting
- **Performance**: Cache sistemi, minification, OPcache optimizasyonu
- **Code Quality**: ESLint/Stylelint flat config sistemi

## 📋 Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- Composer (PHP bağımlılıkları için)
- Node.js (linting araçları için)

## 🛠️ Kurulum

### 🚀 Hızlı Kurulum (XAMPP ile)

1. **XAMPP'i indirin ve kurun:**
   - [XAMPP Download](https://www.apachefriends.org/download.html) sayfasından Windows versiyonunu indirin
   - XAMPP'i `C:\xampp` dizinine kurun

2. **Projeyi XAMPP htdocs dizinine kopyalayın:**
   ```bash
   # Proje dosyalarını C:\xampp\htdocs\ dizinine kopyalayın
   # Veya git kullanarak klonlayın:
   cd C:\xampp\htdocs
   git clone <repository-url> prime-ems-studios
   cd prime-ems-studios
   ```

3. **Veritabanını oluşturun:**
   - XAMPP Control Panel'den MySQL'i başlatın
   - phpMyAdmin'e gidin: http://localhost/phpmyadmin
   - `prime_ems_new` isminde yeni veritabanı oluşturun
   - SQL dosyalarını sırayla çalıştırın:
     - `sql/create-tables.sql`
     - `sql/sample-data.sql`
     - `sql/index-optimizations.sql`

4. **PHP Server'ı başlatın:**
   ```bash
   # Proje dizininde
   php -S localhost:8000
   ```

5. **Siteye erişin:**
   - Ana site: http://localhost:8000
   - Admin paneli: http://localhost:8000/admin (admin/admin123)

### 🔧 Gelişmiş Kurulum

1. **Composer bağımlılıklarını yükleyin:**
   ```bash
   composer install
   ```

2. **Node.js bağımlılıklarını yükleyin (linting için):**
   ```bash
   npm install
   ```

3. **Konfigürasyon dosyalarını düzenleyin:**
   - `config/database.php` - Veritabanı bağlantısı
   - `config/email.php` - SMTP ayarları

4. **Web sunucusunu yapılandırın** (Apache/Nginx) ve siteye erişin.

## 📁 Proje Yapısı

```
prime-ems-studios/
├── admin/                 # Yönetim paneli
├── api/                   # API endpoint'leri
├── assets/                # Statik dosyalar (CSS, JS, images)
├── config/                # Konfigürasyon dosyaları
├── includes/              # Yeniden kullanılabilir PHP dosyaları
├── sql/                   # Veritabanı dosyaları
├── templates/             # E-posta şablonları
├── index.php             # Ana sayfa
├── sitemap.xml           # Site haritası
├── robots.txt            # Arama motoru yönergeleri
└── README.md             # Bu dosya
```

## 🔧 Yapılandırma

### E-posta Ayarları
`config/email.php` dosyasında SMTP bilgilerinizi güncelleyin:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### Veritabanı Ayarları
`config/database.php` dosyasında bağlantı bilgilerinizi kontrol edin.

Tam şema ve başlangıç verileri `sql/schema.sql` dosyasında yer alır. phpMyAdmin üzerinden içe aktarmak için önce veritabanını oluşturup ardından bu dosyayı çalıştırmanız yeterlidir; dosya gerekli tabloları, örnek yönetici hesabını ve temel site ayarlarını otomatik olarak hazırlar.

## 🎨 Geliştirme

### Linting ve Kod Kalitesi

**JavaScript için ESLint:**
```bash
npx eslint assets/js/
```

**CSS için Stylelint:**
```bash
npx stylelint assets/css/
```

### Kod Standartları

- PHP: PSR-12 standartlarını takip eder
- JavaScript: ESLint kurallarına uygun
- CSS: BEM metodolojisi ve Stylelint kuralları

## 📊 Veritabanı Tabloları

### 🗄️ Ana Tablolar
- `admins` - Yönetici kullanıcıları (Argon2ID hash, session security)
- `site_settings` - Site ayarları ve konfigürasyon
- `services` - Hizmetler/Programlar (is_featured, is_active, sort_order)
- `ems_devices` - EMS cihazları (i-motion, i-model verileri)
- `trainers` - Eğitmenler ve sertifikaları
- `members` - Üye kayıtları ve üyelik bilgileri

### 📅 Operasyon Tabloları
- `appointments` - Randevu sistemi (status, customer info)
- `campaigns` - Kampanya yönetimi (discount_text, badge_color)
- `contact_messages` - İletişim formu mesajları
- `activity_logs` - Sistem aktivite logları

### 📝 İçerik Tabloları
- `blog_posts` - Blog yazıları (SEO meta, AI generated, view_count)
- `blog_categories` - Blog kategorileri (color, icon)
- `blog_tags` - Etiket sistemi
- `blog_post_tags` - Blog-etiket ilişkileri
- `blog_comments` - Blog yorumları
- `testimonials` - Müşteri yorumları (rating, featured)
- `faqs` - Sık sorulan sorular (is_active eklendi)
- `pages` - Özel sayfa sistemi

### 🤖 AI ve Otomasyon
- `chatbot_config` - Chatbot ayarları
- `chatbot_logs` - Chatbot konuşma logları
- `blog_ai_suggestions` - AI blog önerileri

### 📊 Analitik ve İstatistik
- `statistics` - Site istatistikleri
- `gallery` - Galeri resimleri
- `media` - Medya kütüphanesi

### 🔒 Güvenlik Tabloları
Tüm tablolar prepared statements ile korunmuş, input sanitization uygulanmış.
CSRF token'ları ve rate limiting aktif.

##  Dağıtım

1. Tüm dosyaları production sunucusuna yükleyin
2. Veritabanını oluşturun ve verileri aktarın
3. Konfigürasyon dosyalarını güncelleyin
4. Dosya izinlerini ayarlayın:
   ```bash
   chmod 755 config/
   chmod 644 assets/uploads/
   ```

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 İletişim

- **Web Sitesi:** https://primeemsstudios.com
- **E-posta:** info@primeems.com
- **Telefon:** +90 232 555 66 77
- **Adres:** Balçova Marina AVM, Kat:2, No:205, Balçova/İzmir

## 🏆 Teknik Özellikler

### 🎨 Tasarım ve UX
- ✅ Fully responsive tasarım (mobile-first)
- ✅ Modern CSS Grid ve Flexbox kullanımı
- ✅ Smooth animations ve transitions
- ✅ Accessibility (WCAG 2.1 AA compliant)
- ✅ Dark mode desteği (CSS variables)

### 🔍 SEO ve Performans
- ✅ Complete SEO optimization (meta tags, structured data)
- ✅ WebP image optimization (%40 boyut azaltma)
- ✅ Lazy loading implementasyonu
- ✅ Database performance index'leri
- ✅ Gzip compression aktif
- ✅ Browser caching headers

### 🔒 Güvenlik Sistemi
- ✅ CSRF token koruması (tüm formlar)
- ✅ XSS prevention (input sanitization)
- ✅ SQL injection koruması (prepared statements)
- ✅ Secure password hashing (Argon2ID)
- ✅ Rate limiting ve brute force koruması
- ✅ Session hijacking prevention
- ✅ Security headers (CSP, HSTS, X-Frame-Options)
- ✅ File upload security validation
- ✅ Honeypot spam koruması
- ✅ Tüm input'lar SecurityUtils ile filtrelenmiş

### 🗄️ Veritabanı ve API
- ✅ PDO prepared statements
- ✅ Database connection pooling
- ✅ RESTful API endpoints
- ✅ Input validation ve sanitization
- ✅ Error logging ve monitoring

### 📧 İletişim ve Otomasyon
- ✅ SMTP e-posta entegrasyonu
- ✅ E-posta şablonları (HTML)
- ✅ Chatbot sistemi (AI destekli)
- ✅ Sosyal medya paylaşım API'leri
- ✅ WhatsApp entegrasyonu

### 👥 Yönetim Sistemi
- ✅ Kapsamlı admin paneli
- ✅ Kullanıcı rol yönetimi
- ✅ Blog content management
- ✅ Analytics ve raporlama
- ✅ Backup ve maintenance araçları

### 🚀 Dağıtım Hazırlığı
- ✅ Production-ready konfigürasyon
- ✅ Environment-specific settings
- ✅ Error handling ve logging
- ✅ Performance monitoring
- ✅ CDN ready structure