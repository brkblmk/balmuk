<?php
require_once __DIR__ . '/../config/database.php';

// Site ayarlarını çek
$site_name = getSetting('site_name', 'Prime EMS Studios');
$contact_phone = getSetting('contact_phone', '+90 232 555 66 77');
$contact_whatsapp = getSetting('contact_whatsapp', '905XXXXXXXXX');
$contact_email = getSetting('contact_email', 'info@primeems.com');
$contact_address = getSetting('contact_address', 'Balçova, İzmir, Türkiye');
$working_hours_weekday = getSetting('working_hours_weekday', '07:00 - 22:00');
$working_hours_weekend = getSetting('working_hours_weekend', '09:00 - 20:00');

// Sosyal medya linkleri
$social_facebook = getSetting('social_facebook', 'https://facebook.com/primeems');
$social_instagram = getSetting('social_instagram', 'https://instagram.com/primeems');
$social_twitter = getSetting('social_twitter', 'https://twitter.com/primeems');
$social_youtube = getSetting('social_youtube', 'https://youtube.com/primeems');
$social_linkedin = getSetting('social_linkedin', '');

// Hızlı linkler - Kısaltılmış
$quick_links = [
    ['title' => 'Ana Sayfa', 'url' => '/'],
    ['title' => 'Hizmetlerimiz', 'url' => '#services'],
    ['title' => 'Blog', 'url' => '/blog.php'],
    ['title' => 'İletişim', 'url' => '#contact']
];
?>

<footer class="footer">
    <!-- Ana Footer İçeriği -->
    <div class="footer-main">
        <div class="container">
            <div class="row g-4">
                <!-- Şirket Bilgileri -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <h4 class="footer-brand">
                                <i class="bi bi-lightning-charge-fill me-2"></i>
                                <?php echo htmlspecialchars($site_name); ?>
                            </h4>
                        </div>
                        <p class="footer-description">
                            İzmir'in premium EMS studios'u. Bilimsel WB-EMS seansları ile maksimum sonuç.
                        </p>
                        
                        <!-- Sosyal Medya -->
                        <div class="social-links">
                            <h6 class="social-title">Bizi Takip Edin</h6>
                            <div class="social-icons">
                                <?php if($social_facebook): ?>
                                <a href="<?php echo htmlspecialchars($social_facebook); ?>" target="_blank" class="social-link facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if($social_instagram): ?>
                                <a href="<?php echo htmlspecialchars($social_instagram); ?>" target="_blank" class="social-link instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if($social_twitter): ?>
                                <a href="<?php echo htmlspecialchars($social_twitter); ?>" target="_blank" class="social-link twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if($social_youtube): ?>
                                <a href="<?php echo htmlspecialchars($social_youtube); ?>" target="_blank" class="social-link youtube">
                                    <i class="bi bi-youtube"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if($social_linkedin): ?>
                                <a href="<?php echo htmlspecialchars($social_linkedin); ?>" target="_blank" class="social-link linkedin">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hızlı Linkler -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="widget-title">Hızlı Linkler</h5>
                        <ul class="footer-links">
                            <?php foreach($quick_links as $link): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    <?php echo htmlspecialchars($link['title']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Blog -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="widget-title">Blog</h5>
                        <a href="/blog.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-newspaper me-2"></i>Tüm Yazılar
                        </a>
                    </div>
                </div>
                
                <!-- İletişim Bilgileri -->
                <div class="col-lg-4 col-md-6">
                    <div id="contact" class="footer-widget">
                        <h5 class="widget-title">İletişim</h5>
                        <div class="contact-info">
                            <p><i class="bi bi-geo-alt-fill contact-icon"></i> <?php echo htmlspecialchars($contact_address); ?></p>
                            <p><i class="bi bi-telephone-fill contact-icon"></i> <a href="tel:<?php echo str_replace(' ', '', $contact_phone); ?>"><?php echo htmlspecialchars($contact_phone); ?></a></p>
                            <p><i class="bi bi-envelope-fill contact-icon"></i> <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>"><?php echo htmlspecialchars($contact_email); ?></a></p>
                            <p><i class="bi bi-clock-fill contact-icon"></i> Pzt-Cmt: <?php echo htmlspecialchars($working_hours_weekday); ?>, Pzr: <?php echo htmlspecialchars($working_hours_weekend); ?></p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="https://wa.me/<?php echo str_replace(['+', ' '], '', $contact_whatsapp); ?>?text=Prime%20EMS%20hakkında%20bilgi%20almak%20istiyorum" target="_blank" class="btn btn-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </a>
                            <a href="tel:<?php echo $contact_phone; ?>" class="btn btn-prime btn-sm">
                                <i class="bi bi-telephone me-1"></i> Ara
                            </a>
                            <a href="mailto:<?php echo $contact_email; ?>" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-envelope me-1"></i> E-posta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- Alt Footer -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. 
                        Tüm hakları saklıdır.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="footer-links-bottom">
                        <a href="#privacy">Gizlilik Politikası</a>
                        <a href="#terms">Kullanım Şartları</a>
                        <a href="#kvkk">KVKK</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" title="Yukarı Çık">
    <i class="bi bi-arrow-up"></i>
</button>

<style>
/* Footer Styles */
.footer {
    background: linear-gradient(135deg, var(--prime-dark, #2B2B2B) 0%, #1a1a1a 100%);
    color: #fff;
    padding: 40px 0 20px;
}

.footer-brand {
    color: #fff;
    font-weight: 700;
    margin-bottom: 15px;
}

.footer-description {
    color: #ccc;
    margin-bottom: 20px;
}

.widget-title {
    color: var(--prime-gold, #FFD700);
    margin-bottom: 15px;
    font-weight: 600;
}

/* Social */
.social-link {
    color: #fff;
    text-decoration: none;
    margin-right: 10px;
}

/* Footer Links */
.footer-links a {
    color: #ccc;
    text-decoration: none;
    display: block;
    margin-bottom: 8px;
}

/* Contact */
.contact-info p {
    color: #ccc;
    margin-bottom: 8px;
}

.contact-icon {
    color: var(--prime-gold, #FFD700);
    margin-right: 8px;
}

.contact-info a {
    color: #ccc;
    text-decoration: none;
}

/* Footer Bottom */
.footer-bottom {
    background: rgba(0,0,0,0.3);
    padding: 15px 0;
    text-align: center;
}

.copyright {
    color: #ccc;
    margin: 0;
}

.footer-links-bottom a {
    color: #ccc;
    text-decoration: none;
    margin: 0 10px;
}

/* Back to Top */
.back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: var(--prime-gold, #FFD700);
    color: #000;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-main {
        padding: 30px 0 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to Top
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    backToTop.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Smooth scroll for footer links
    document.querySelectorAll('.footer a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});
</script>