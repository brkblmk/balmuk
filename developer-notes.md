# Prime EMS Studios - Developer Notes

## 📋 Kodlama Standartları

### PHP
- PSR-12 standartlarını takip eder
- Tüm veritabanı sorguları prepared statements kullanır
- Input sanitization SecurityUtils::sanitizeInput() ile yapılır
- CSRF token'ları zorunludur
- Error logging aktif, tüm hatalar loglanır

### JavaScript
- ESLint kurallarına uygun (flat config)
- DOM manipulation için vanilla JS tercih edilir
- Event delegation pattern kullanılır
- Lazy loading implementasyonu

### CSS
- Stylelint kurallarına uygun (flat config)
- BEM metodolojisi kullanılır
- CSS variables (--prime-gold, --prime-dark) kullanılır
- Mobile-first responsive design

## 🔧 Yapılandırma

### Environment Variables
```php
// Production vs Development
$is_development = strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;

// CSP headers development/production farkı
if ($is_development) {
    // Development: 'unsafe-eval', 'unsafe-inline' izinli
} else {
    // Production: Sıkı CSP
}
```

### Database Connection
- PDO prepared statements
- Connection pooling aktif
- UTF8MB4 charset
- Auto-commit aktif

## 🔒 Güvenlik Önlemleri

### Session Security
- Session hijacking protection aktif
- IP/User-Agent kontrolü
- Session timeout: 30 dakika
- Secure cookie settings

### Input Validation
```php
// Tüm input'lar şu şekilde filtrelenir:
$name = SecurityUtils::sanitizeInput($_POST['name'], 'string');
$email = SecurityUtils::validateInput($email, 'email');
```

### Rate Limiting
- Contact form: 3 istek/dakika
- Honeypot spam koruması aktif
- CSRF token validation

## 🚀 Performance Optimizasyonları

### Cache Sistemi
```php
// Database query caching
$result = PerformanceOptimizer::cacheQuery($query, $params, 1800);

// HTML output minification
ob_start(function($buffer) {
    return PerformanceOptimizer::minifyHTML($buffer);
});
```

### Image Optimization
- WebP format desteği
- Lazy loading implementation
- Responsive images (srcset)

### Database Indexes
- blog_posts: (is_published, published_at)
- appointments: (appointment_date, status)
- members: (phone, email, is_active)

## 📁 Dosya Yapısı

```
prime-ems-studios/
├── config/           # Konfigürasyon dosyaları
│   ├── database.php  # DB bağlantısı + helper functions
│   ├── security.php  # Güvenlik utils (CSRF, session, validation)
│   ├── performance.php # Cache, minify, optimization
│   └── email.php     # SMTP ayarları
├── admin/           # Yönetim paneli
├── api/            # RESTful API endpoints
├── assets/         # Static dosyalar
│   ├── css/        # Stil dosyaları
│   ├── js/         # JavaScript dosyaları
│   └── images/     # Görseller
├── includes/       # Reusable PHP components
├── sql/           # Database scripts
└── templates/     # Email templates
```

## 🐛 Troubleshooting

### Common Issues

#### Blog Görselleri Gösterilmiyor
- `assets/images/blog/` klasörünün varlığını kontrol edin
- Featured image path'lerinin doğru olduğunu doğrulayın
- Cache'i temizleyin: `PerformanceOptimizer::clearCache()`

#### Database Connection Error
- `config/database.php`'teki bağlantı bilgilerini kontrol edin
- MySQL servisinin çalıştığından emin olun
- PDO extension'ın aktif olduğunu doğrulayın

#### CSRF Token Errors
- Form'ların CSRF token içerdiğinden emin olun
- Session'ın aktif olduğunu kontrol edin
- SecurityUtils::generateCSRFToken() çağrıldığından emin olun

#### Performance Issues
- OPcache'in aktif olduğunu kontrol edin
- Database index'lerinin mevcut olduğunu doğrulayın
- Cache klasörünün yazılabilir olduğundan emin olun

### Debug Tools
```php
// Performance metrics
$metrics = PerformanceOptimizer::getPerformanceMetrics();

// Cache statistics
$stats = PerformanceOptimizer::getCacheStats();

// Security events log
SecurityUtils::logSecurityEvent('DEBUG_EVENT', ['details' => $data]);
```

## 🔄 Bakım Görevleri

### Günlük
- Cache temizleme
- Log dosyalarını kontrol etme
- Database connection health check

### Haftalık
- Old backup dosyalarını silme
- Performance metrics review
- Security logs analysis

### Aylık
- Database optimization
- Index rebuild
- System updates

## 📊 Monitoring

### Performance Metrics
- Page load times
- Database query execution times
- Cache hit/miss ratios
- Memory usage

### Security Monitoring
- Failed login attempts
- CSRF token violations
- Rate limiting triggers
- Suspicious activity logs

## 🚀 Deployment Checklist

- [ ] Environment variables set
- [ ] Database configured
- [ ] File permissions set (755 config/, 644 uploads/)
- [ ] SSL certificate installed
- [ ] CDN configured (if used)
- [ ] Backup systems active
- [ ] Monitoring tools configured

## 📞 Support

### Development Team
- **Lead Developer**: [İsim]
- **UI/UX Designer**: [İsim]
- **DevOps**: [İsim]

### Third-party Services
- **SMTP Provider**: Gmail/Google Workspace
- **Analytics**: Google Analytics
- **CDN**: [If used]

---

*Bu dokümantasyon düzenli olarak güncellenmelidir.*