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

// Hızlı linkler
try {
    $quick_links_stmt = $pdo->query("
        SELECT title, url FROM menu_items 
        WHERE menu_location = 'footer' AND is_active = 1 
        ORDER BY sort_order ASC
        LIMIT 6
    ");
    $quick_links = $quick_links_stmt->fetchAll();
} catch (PDOException $e) {
    // Varsayılan linkler
    $quick_links = [
        ['title' => 'Ana Sayfa', 'url' => '/'],
        ['title' => 'Hizmetlerimiz', 'url' => '#services'],
        ['title' => 'Kampanyalar', 'url' => '#campaigns'],
        ['title' => 'Blog', 'url' => '/blog.php'],
        ['title' => 'İletişim', 'url' => '#contact'],
        ['title' => 'SSS', 'url' => '#faq']
    ];
}

// Son blog yazıları
try {
    $blog_stmt = $pdo->query("
        SELECT title, slug FROM blog_posts 
        WHERE is_published = 1 
        ORDER BY published_at DESC 
        LIMIT 3
    ");
    $recent_posts = $blog_stmt->fetchAll();
} catch (PDOException $e) {
    $recent_posts = [];
}
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
                            İzmir'in premium EMS studios'u. Bilimsel WB-EMS seansları ile 20 dakikada maksimum sonuç. 
                            Sağlıklı yaşamınızı dönüştürün.
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
                
                <!-- Son Blog Yazıları -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="widget-title">Son Blog Yazıları</h5>
                        <?php if($recent_posts): ?>
                        <div class="recent-posts">
                            <?php foreach($recent_posts as $post): ?>
                            <div class="recent-post-item">
                                <h6 class="post-title">
                                    <a href="/blog-detail.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h6>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Henüz blog yazısı bulunmuyor.</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="/blog.php" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-newspaper me-2"></i>Tüm Yazılar
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- İletişim Bilgileri -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="widget-title">İletişim</h5>
                        <div class="contact-info">
                            <!-- Adres -->
                            <div class="contact-item">
                                <i class="bi bi-geo-alt-fill contact-icon"></i>
                                <div>
                                    <span class="contact-label">Adres</span>
                                    <p><?php echo nl2br(htmlspecialchars($contact_address)); ?></p>
                                </div>
                            </div>
                            
                            <!-- Telefon -->
                            <div class="contact-item">
                                <i class="bi bi-telephone-fill contact-icon"></i>
                                <div>
                                    <span class="contact-label">Telefon</span>
                                    <p>
                                        <a href="tel:<?php echo str_replace(' ', '', $contact_phone); ?>">
                                            <?php echo htmlspecialchars($contact_phone); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- E-posta -->
                            <div class="contact-item">
                                <i class="bi bi-envelope-fill contact-icon"></i>
                                <div>
                                    <span class="contact-label">E-posta</span>
                                    <p>
                                        <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>">
                                            <?php echo htmlspecialchars($contact_email); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Çalışma Saatleri -->
                            <div class="contact-item">
                                <i class="bi bi-clock-fill contact-icon"></i>
                                <div>
                                    <span class="contact-label">Çalışma Saatleri</span>
                                    <p>
                                        Pzt-Cmt: <?php echo htmlspecialchars($working_hours_weekday); ?><br>
                                        Pazar: <?php echo htmlspecialchars($working_hours_weekend); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- WhatsApp CTA -->
                        <div class="mt-3">
                            <a href="https://wa.me/<?php echo str_replace(['+', ' '], '', $contact_whatsapp); ?>?text=Prime%20EMS%20hakkında%20bilgi%20almak%20istiyorum" 
                               target="_blank" class="btn btn-success btn-sm w-100">
                                <i class="bi bi-whatsapp me-2"></i>WhatsApp'tan Mesaj At
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Newsletter Alanı -->
    <div class="newsletter-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h4 class="newsletter-title">
                        <i class="bi bi-envelope-heart me-2"></i>
                        Haberlerden Haberdar Olun
                    </h4>
                    <p class="newsletter-text">
                        En yeni EMS haberleri, fitness ipuçları ve özel kampanyalar için bültenimize katılın.
                    </p>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="E-posta adresinizi girin" required>
                            <button class="btn btn-prime" type="submit">
                                <i class="bi bi-send me-2"></i>Abone Ol
                            </button>
                        </div>
                    </form>
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
    color: #ffffff;
    position: relative;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--prime-gradient, linear-gradient(135deg, #FFD700 0%, #FFC700 100%));
}

.footer-main {
    padding: 60px 0 40px;
    position: relative;
    z-index: 2;
}

.footer-widget {
    height: 100%;
}

.footer-brand {
    color: #ffffff;
    font-family: var(--font-primary, 'Poppins', sans-serif);
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.footer-brand i {
    color: var(--prime-gold, #FFD700);
}

.footer-description {
    color: #cccccc;
    line-height: 1.7;
    margin-bottom: 30px;
}

.widget-title {
    color: var(--prime-gold, #FFD700);
    font-weight: 600;
    margin-bottom: 25px;
    position: relative;
}

.widget-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 2px;
    background: var(--prime-gold, #FFD700);
}

/* Social Links */
.social-title {
    color: #ffffff;
    margin-bottom: 15px;
    font-size: 0.9rem;
    font-weight: 500;
}

.social-icons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: #ffffff;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.social-link:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
}

.social-link.facebook:hover { background: #3b5998; }
.social-link.instagram:hover { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
.social-link.twitter:hover { background: #1da1f2; }
.social-link.youtube:hover { background: #ff0000; }
.social-link.linkedin:hover { background: #0077b5; }

/* Footer Links */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #cccccc;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.footer-links a:hover {
    color: var(--prime-gold, #FFD700);
    transform: translateX(5px);
}

.footer-links a i {
    transition: all 0.3s ease;
    font-size: 0.8rem;
}

.footer-links a:hover i {
    color: var(--prime-gold, #FFD700);
}

/* Recent Posts */
.recent-posts {
    margin-bottom: 20px;
}

.recent-post-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.recent-post-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.post-title a {
    color: #cccccc;
    text-decoration: none;
    font-size: 0.9rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s ease;
}

.post-title a:hover {
    color: var(--prime-gold, #FFD700);
}

/* Contact Info */
.contact-info {
    margin-bottom: 20px;
}

.contact-item {
    display: flex;
    margin-bottom: 20px;
    align-items: flex-start;
}

.contact-icon {
    color: var(--prime-gold, #FFD700);
    font-size: 1.1rem;
    margin-right: 12px;
    margin-top: 4px;
    flex-shrink: 0;
}

.contact-label {
    color: var(--prime-gold, #FFD700);
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 5px;
}

.contact-item p {
    color: #cccccc;
    margin: 0;
    line-height: 1.5;
}

.contact-item a {
    color: #cccccc;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-item a:hover {
    color: var(--prime-gold, #FFD700);
}

/* Newsletter Section */
.newsletter-section {
    background: rgba(255, 215, 0, 0.1);
    padding: 40px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.newsletter-title {
    color: var(--prime-gold, #FFD700);
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.newsletter-text {
    color: #cccccc;
    margin-bottom: 20px;
}

.newsletter-form .form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border-radius: 8px 0 0 8px;
}

.newsletter-form .form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: var(--prime-gold, #FFD700);
    color: #ffffff;
    box-shadow: none;
}

.newsletter-form .form-control::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.newsletter-form .btn {
    border-radius: 0 8px 8px 0;
    padding: 12px 24px;
}

/* Footer Bottom */
.footer-bottom {
    background: rgba(0, 0, 0, 0.3);
    padding: 20px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.copyright {
    color: #cccccc;
    margin: 0;
    font-size: 0.9rem;
}

.footer-links-bottom {
    display: flex;
    gap: 20px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.footer-links-bottom a {
    color: #cccccc;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-links-bottom a:hover {
    color: var(--prime-gold, #FFD700);
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--prime-gradient, linear-gradient(135deg, #FFD700 0%, #FFC700 100%));
    color: var(--prime-dark, #2B2B2B);
    border: none;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
    visibility: hidden;
    z-index: 999;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .footer-main {
        padding: 50px 0 30px;
    }
    
    .newsletter-form {
        margin-top: 20px;
    }
    
    .footer-links-bottom {
        justify-content: flex-start;
        margin-top: 15px;
    }
    
    .back-to-top {
        bottom: 100px; /* Above mobile nav */
        width: 45px;
        height: 45px;
        font-size: 16px;
    }
}

@media (max-width: 575.98px) {
    .social-icons {
        justify-content: center;
    }
    
    .newsletter-form .input-group {
        flex-direction: column;
    }
    
    .newsletter-form .form-control {
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    .newsletter-form .btn {
        border-radius: 8px;
        width: 100%;
    }
    
    .footer-links-bottom {
        flex-direction: column;
        gap: 10px;
    }
}

/* Dark Mode Support */
[data-theme="dark"] .footer {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
}

[data-theme="dark"] .newsletter-section {
    background: rgba(255, 215, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to Top Button
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Newsletter Form
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            if (email) {
                // Newsletter subscription logic here
                alert('Bültenimize abone oldunuz! Teşekkür ederiz.');
                this.reset();
            }
        });
    }
    
    // Smooth scrolling for footer links
    document.querySelectorAll('.footer a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offsetTop = target.offsetTop - 80; // Navbar height offset
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});
</script>