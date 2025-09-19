<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h3>
            <i class="bi bi-lightning-charge-fill"></i> 
            Prime EMS
        </h3>
        <small>Admin Panel</small>
    </div>
    
    <nav>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-section-title">İçerik Yönetimi</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hero.php' ? 'active' : ''; ?>" href="hero.php">
                    <i class="bi bi-image-fill"></i>
                    <span>Hero Section</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>" href="services.php">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span>Hizmetler</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'campaigns.php' ? 'active' : ''; ?>" href="campaigns.php">
                    <i class="bi bi-megaphone-fill"></i>
                    <span>Kampanyalar</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : ''; ?>" href="devices.php">
                    <i class="bi bi-cpu-fill"></i>
                    <span>Cihazlar</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>" href="testimonials.php">
                    <i class="bi bi-chat-quote-fill"></i>
                    <span>Müşteri Yorumları</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>" href="gallery.php">
                    <i class="bi bi-images"></i>
                    <span>Galeri</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Hakkımızda</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' || basename($_SERVER['PHP_SELF']) == 'blogs.php' || basename($_SERVER['PHP_SELF']) == 'blog-categories.php' ? 'active' : ''; ?>" href="blog.php">
                    <i class="bi bi-newspaper"></i>
                    <span>Blog Yönetimi</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'blog-categories.php' ? 'active' : ''; ?>" href="blog-categories.php">
                    <i class="bi bi-tags"></i>
                    <span>Blog Kategorileri</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'faqs.php' ? 'active' : ''; ?>" href="faqs.php">
                    <i class="bi bi-question-circle-fill"></i>
                    <span>SSS Yönetimi</span>
                </a>
            </li>

            <li class="nav-section-title">Müşteri Yönetimi</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>" href="members.php">
                    <i class="bi bi-people-fill"></i>
                    <span>Üyeler</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>" href="appointments.php">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>Randevular</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'active' : ''; ?>" href="packages.php">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Paketler</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                    <i class="bi bi-cash-stack"></i>
                    <span>Ödeme Kayıtları</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'trainers.php' ? 'active' : ''; ?>" href="trainers.php">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Eğitmenler</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" href="messages.php">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>İletişim Mesajları</span>
                </a>
            </li>
            
            <li class="nav-section-title">Site Ayarları</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear-fill"></i>
                    <span>Genel Ayarlar</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>" href="menu.php">
                    <i class="bi bi-list-nested"></i>
                    <span>Menü Yönetimi</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pages.php' ? 'active' : ''; ?>" href="pages.php">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>Sayfalar</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>" href="media.php">
                    <i class="bi bi-folder-fill"></i>
                    <span>Medya</span>
                </a>
            </li>
            
            <li class="nav-section-title">Entegrasyonlar</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'chatbot.php' ? 'active' : ''; ?>" href="chatbot.php">
                    <i class="bi bi-robot"></i>
                    <span>Chatbot</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Analytics</span>
                </a>
            </li>
            
            <li class="nav-section-title">Sistem</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-person-fill-gear"></i>
                    <span>Kullanıcılar</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'performance-monitor.php' ? 'active' : ''; ?>" href="../performance-monitor.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Performans Monitörü</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>" href="logs.php">
                    <i class="bi bi-file-text-fill"></i>
                    <span>Aktivite Logları</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Çıkış Yap</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
