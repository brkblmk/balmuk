<?php
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/performance.php';

// Get all content from database
try {
    // Get hero content
    $stmt = $pdo->prepare("SELECT * FROM hero_section WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 1");
    $stmt->execute();
    $hero = $stmt->fetch() ?: [];

    // Get about content
    $stmt = $pdo->prepare("SELECT * FROM about_section WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $about = $stmt->fetch() ?: [];

    // Get site settings for contact info
    $contact = [
        'phone' => getSetting('contact_phone', '+90 232 555 66 77'),
        'whatsapp' => getSetting('contact_whatsapp', '905XXXXXXXXX'),
        'email' => getSetting('contact_email', 'info@primeems.com'),
        'address' => getSetting('contact_address', 'Balçova, İzmir, Türkiye'),
        'working_hours_weekday' => getSetting('working_hours_weekday', '07:00 - 22:00'),
        'working_hours_weekend' => getSetting('working_hours_weekend', '09:00 - 20:00')
    ];

    // Campaigns
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 3");
    $stmt->execute();
    $campaigns = $stmt->fetchAll();

    // EMS Devices
    $stmt = $pdo->prepare("SELECT id, name, slug, device_type, model, manufacturer, short_description, long_description, main_image, gallery_images, features, specifications, exercise_programs, capacity, price_range, sort_order, is_featured FROM ems_devices WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $devices = $stmt->fetchAll();

    // Services (All)
    $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();

    // Featured Services
    $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC LIMIT 4");
    $stmt->execute();
    $featured_services = $stmt->fetchAll();

    // Statistics
    $stmt = $pdo->prepare("SELECT * FROM statistics WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $stats = $stmt->fetchAll();

    // Testimonials
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC LIMIT 6");
    $stmt->execute();
    $testimonials = $stmt->fetchAll();

    // FAQs
    $stmt = $pdo->prepare("SELECT question, answer, category FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");
    $stmt->execute();
    $faqs = $stmt->fetchAll();

    // Sayfa render işlemini logla
    error_log("Homepage rendered successfully - " . date('Y-m-d H:i:s'));

} catch (PDOException $e) {
    // Veritabanı hatasını logla
    error_log("Homepage database error: " . $e->getMessage() . " - " . date('Y-m-d H:i:s'));
    SecurityUtils::logSecurityEvent('HOME_PAGE_DB_ERROR', [
        'error' => $e->getMessage(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    // Continue with empty arrays if database error
    $hero = [];
    $about = [];
    $contact = [
        'phone' => '+90 232 555 66 77',
        'whatsapp' => '905XXXXXXXXX',
        'email' => 'info@primeems.com',
        'address' => 'Balçova, İzmir, Türkiye',
        'working_hours_weekday' => '07:00 - 22:00',
        'working_hours_weekend' => '09:00 - 20:00'
    ];
    $campaigns = [];
    $devices = [];
    $services = [];
    $featured_services = [];
    $stats = [];
    $testimonials = [];
    $faqs = [];
}

SecurityUtils::generateCSRFToken();

// Sabit veriler artık kullanılmıyor - tüm veriler veritabanından çekiliyor
// Admin panelden yapılan değişiklikler artık doğrudan anasayfada görünecek

$siteUrl = rtrim(getSetting('site_url', 'https://primeemsstudios.com'), '/');
$shareImage = getSetting('social_share_image', $siteUrl . '/assets/images/logo.png');

$pageMeta = [
    'title' => 'Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç | Premium EMS',
    'description' => "Prime EMS Studios, İzmir Balçova'da Almanya üretimi i-motion ve i-model EMS teknolojisi ile 20 dakikalık bilimsel antrenman programları sunar.",
    'keywords' => 'EMS İzmir, elektrik kas stimülasyonu, i-motion, i-model, Balçova EMS, 20 dakika antrenman, yağ yakımı, kas geliştirme, rehabilitasyon',
    'canonical' => $siteUrl . '/',
    'image' => $shareImage,
    'meta' => [
        'generator' => 'Prime EMS Studios Custom CMS',
        'language' => 'tr-TR',
        'revisit-after' => '1 days',
        'geo.region' => 'TR-35',
        'geo.placename' => 'İzmir, Balçova',
        'geo.position' => '38.3942;27.0322',
        'ICBM' => '38.3942, 27.0322'
    ],
    'styles' => ['assets/css/home.css'],
    'structured_data' => [
        [
            '@type' => 'OfferCatalog',
            '@id' => $siteUrl . '/#offer-catalog',
            'name' => 'EMS Hizmetleri',
            'itemListElement' => [
                ['@type' => 'Offer', 'name' => 'Prime Slim', 'description' => 'Yağ yakımı için EMS antrenmanı'],
                ['@type' => 'Offer', 'name' => 'Prime Sculpt', 'description' => 'Bölgesel sıkılaşma programı'],
                ['@type' => 'Offer', 'name' => 'Prime Power', 'description' => 'Güç ve performans artışı'],
                ['@type' => 'Offer', 'name' => 'Rehab & Pain', 'description' => 'Rehabilitasyon ve ağrı yönetimi']
            ]
        ]
    ]
];

include 'includes/site-head.php';
?>
<body>
    <!-- Skip Link for Screen Readers -->
    <a href="#main-content" class="skip-link">Ana içeriğe geç</a>

    <?php include 'includes/navbar.php'; ?>

    <main id="main-content" tabindex="-1">
        <!-- Hero Section -->
        <section id="home" class="hero-section" role="banner" aria-label="Ana kahraman bölümü">
        <?php
        $media_type = $hero['media_type'] ?? 'video';
        $media_path = $hero['media_path'] ?? '';

        if ($media_type === 'video' && !empty($media_path)): ?>
            <video class="hero-video" autoplay muted loop playsinline aria-hidden="true">
                <source src="<?php echo htmlspecialchars($media_path); ?>" type="video/mp4">
                <track kind="captions" srclang="tr" label="Türkçe altyazı">
            </video>
        <?php elseif ($media_type === 'image' && !empty($media_path)): ?>
            <img class="hero-image" src="<?php echo htmlspecialchars($media_path); ?>" alt="Prime EMS Studios - Profesyonel EMS Antrenman Salonu" loading="eager">
        <?php else: ?>
            <img class="hero-image" src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Prime EMS Studios - Profesyonel EMS Antrenman Salonu" loading="eager">
        <?php endif; ?>

        <div class="hero-overlay" aria-hidden="true"></div>

        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1 class="display-3 fw-bold mb-4">
                    <?php echo !empty($hero['title']) ? htmlspecialchars($hero['title']) : 'Prime EMS Studios'; ?><br>
                    <span style="color: var(--prime-gold);"><?php echo !empty($hero['subtitle']) ? htmlspecialchars($hero['subtitle']) : "İzmir'in Altın Standardı"; ?></span>
                </h1>
                <p class="lead mb-5">
                    <?php echo !empty($hero['description']) ? htmlspecialchars($hero['description']) : 'Bilimsel WB-EMS seansları: <strong>20 dakika</strong>, haftada 2 gün ile<br>daha güçlü, daha fit ve enerji dolu yaşam'; ?>
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#reservation" class="btn btn-prime btn-lg pulse-effect hero-cta-primary" role="button" aria-label="Ücretsiz keşif seansı için hemen rezervasyon yapın">
                        <i class="bi bi-calendar-check" aria-hidden="true"></i> Ücretsiz Keşif Seansı Alın
                    </a>
                    <a href="#campaigns" class="btn btn-prime-outline btn-lg hero-cta-secondary" role="button" aria-label="Mevcut kampanyaları görüntüleyin">
                        <i class="bi bi-tag-fill" aria-hidden="true"></i> Kampanyaları Gör
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 3 Quick USPs -->
    <section class="py-5 bg-light" aria-labelledby="usp-heading">
        <div class="container">
            <h2 id="usp-heading" class="visually-hidden">Neden Bizi Tercih Etmelisiniz</h2>
            <div class="row g-4" role="list">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100" role="listitem">
                    <div class="usp-card">
                        <i class="bi bi-clock-fill usp-icon" aria-hidden="true"></i>
                        <h4>20 Dakikada Maksimum Verim</h4>
                        <p class="text-muted">Geleneksel 90 dakikalık antrenman sonucunu sadece 20 dakikada alın</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200" role="listitem">
                    <div class="usp-card">
                        <i class="bi bi-gear-fill usp-icon" aria-hidden="true"></i>
                        <h4>i-motion & i-model Teknolojisi</h4>
                        <p class="text-muted"><strong>Almanya'nın öncü EMS cihazları</strong> - 24 elektrot sistemi, kablosuz bağlantı, profesyonel sonuçlar</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300" role="listitem">
                    <div class="usp-card">
                        <i class="bi bi-person-check-fill usp-icon" aria-hidden="true"></i>
                        <h4>Kişiye Özel Programlama</h4>
                        <p class="text-muted">Hedeflerinize özel, bilimsel olarak tasarlanmış programlar</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Campaigns Section -->
    <section id="campaigns" class="prime-section" aria-labelledby="campaigns-heading">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 id="campaigns-heading" class="display-5 fw-bold gold-accent">Açılış Kampanyaları</h2>
                <p class="lead text-muted">Prime EMS Studios'a özel fırsatları kaçırmayın</p>
            </div>

            <div class="row g-4" role="list" aria-label="Mevcut kampanyalar">
                <?php if (!empty($campaigns)): ?>
                    <?php foreach ($campaigns as $index => $campaign): ?>
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>" role="listitem">
                        <div class="campaign-card p-4">
                            <span class="campaign-badge" aria-label="Kampanya etiketi: <?php echo htmlspecialchars($campaign['badge'] ?? ''); ?>"><?php echo htmlspecialchars($campaign['badge'] ?? ''); ?></span>
                            <div class="text-center mb-3">
                                <i class="<?php echo htmlspecialchars($campaign['icon'] ?? 'bi-star-fill'); ?>" style="font-size: 3rem; color: var(--prime-gold);" aria-hidden="true"></i>
                            </div>
                            <h3 class="h4 mb-2"><?php echo htmlspecialchars($campaign['title'] ?? ''); ?></h3>
                            <p class="text-primary fw-bold mb-3"><?php echo htmlspecialchars($campaign['discount_text'] ?? ''); ?></p>
                            <p class="text-muted"><?php echo htmlspecialchars($campaign['description'] ?? ''); ?></p>
                            <button class="btn btn-prime w-100 mt-3" aria-label="<?php echo htmlspecialchars($campaign['title'] ?? ''); ?> kampanyası için hemen rezervasyon yapın">Hemen Rezerve Et</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Kampanya bulunmuyor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="prime-section prime-section-gray" aria-labelledby="services-heading">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 id="services-heading" class="display-5 fw-bold gold-accent">Hizmet Programlarımız</h2>
                <p class="lead text-muted">20 dakikada hedeflerinize ulaşın</p>
            </div>

            <div class="row g-4" role="list" aria-label="Mevcut hizmet programları">
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $index => $service): ?>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>" role="listitem">
                        <div class="service-card">
                            <i class="<?php echo htmlspecialchars($service['icon'] ?? 'bi-star'); ?> service-icon" aria-hidden="true"></i>
                            <h4><?php echo htmlspecialchars($service['name'] ?? ''); ?></h4>
                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($service['goal'] ?? ''); ?></p>
                            <p class="text-muted small"><?php echo htmlspecialchars($service['short_description'] ?? ''); ?></p>
                            <div class="mt-3">
                                <span class="badge bg-warning text-dark" aria-label="Seans süresi: <?php echo htmlspecialchars($service['duration'] ?? ''); ?>"><?php echo htmlspecialchars($service['duration'] ?? ''); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Hizmet bulunmuyor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Devices Section -->
    <section id="devices" class="prime-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold gold-accent">i-motion & i-model Cihazları</h2>
                <p class="lead text-muted"><strong>Almanya'nın lider EMS teknolojisi</strong> - Bilimsel olarak kanıtlanmış, profesyonel sonuçlar</p>
            </div>

            <div class="row g-4">
                <?php foreach ($devices as $index => $device): ?>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="device-card">
                        <!-- Device Image -->
                        <div class="device-image-container position-relative overflow-hidden">
                            <?php if (!empty($device['main_image'])): ?>
                                <img src="<?php echo htmlspecialchars($device['main_image']); ?>"
                                     alt="<?php echo htmlspecialchars($device['name']); ?> EMS Cihazı"
                                     class="device-image lazy-loading"
                                     loading="lazy"
                                     data-src="<?php echo htmlspecialchars($device['main_image']); ?>"
                                     onerror="this.src='assets/images/device-placeholder.jpg'; this.classList.add('fallback-image');">
                                <div class="device-image-overlay"></div>
                            <?php else: ?>
                                <div class="device-placeholder d-flex align-items-center justify-content-center">
                                    <i class="bi bi-device-hdd display-4 text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Device Type Badge -->
                            <div class="device-type-badge position-absolute top-0 start-0 m-3">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($device['device_type']); ?></span>
                            </div>

                            <!-- Capacity Badge -->
                            <div class="capacity-badge position-absolute top-0 end-0 m-3">
                                <span class="badge bg-info"><?php echo $device['capacity']; ?> Kişi</span>
                            </div>
                        </div>

                        <!-- Device Content -->
                        <div class="device-content p-4">
                            <h3 class="h4 mb-2"><?php echo htmlspecialchars($device['name']); ?></h3>
                            <p class="text-primary fw-bold mb-3"><?php echo htmlspecialchars($device['short_description']); ?></p>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($device['long_description']); ?></p>

                            <!-- EMS Exercise Sessions Highlight -->
                            <div class="ems-highlight mb-4">
                                <div class="badge bg-warning text-dark p-2 mb-2">
                                    <i class="bi bi-lightning-charge-fill me-1"></i>
                                    20 Dakikalık EMS Egzersiz Seansları
                                </div>
                                <small class="text-muted">Haftada 2 seans ile maksimum sonuç</small>
                            </div>

                            <!-- Quick Features -->
                            <?php if ($device['features']): ?>
                            <div class="device-quick-features mb-4">
                                <?php
                                $features = json_decode($device['features'], true);
                                foreach ($features as $feature): ?>
                                <span class="badge bg-light text-dark me-1 mb-1">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i><?php echo htmlspecialchars($feature); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Technical Highlights -->
                            <div class="device-highlights small text-muted mb-3">
                                <?php if ($device['model']): ?>
                                <span><i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($device['model']); ?></span>
                                <?php endif; ?>
                                <?php if ($device['manufacturer']): ?>
                                <span class="ms-2"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($device['manufacturer']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Certifications -->
                            <div class="device-certifications mb-4">
                                <span class="badge bg-warning text-dark me-1"><i class="bi bi-shield-check me-1"></i>CE</span>
                                <span class="badge bg-success me-1"><i class="bi bi-check-circle me-1"></i>FDA</span>
                                <span class="badge bg-info"><i class="bi bi-award me-1"></i>ISO 13485</span>
                            </div>

                            <!-- Action Button -->
                            <div class="device-actions">
                                <a href="#contact" class="btn btn-prime btn-sm w-100">
                                    <i class="bi bi-calendar-event me-2"></i>Ücretsiz EMS Keşif Seansı
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Certifications Section -->
            <div class="mt-5" data-aos="fade-up">
                <h3 class="text-center mb-4">Kalite Sertifikalarımız</h3>
                <div class="row g-3 justify-content-center">
                    <div class="col-md-2 col-4 text-center">
                        <img src="assets/img/certifications/ce-marking.svg" alt="CE Marking" class="certification-logo" style="width: 60px; height: 60px; filter: grayscale(100%) brightness(0.8);">
                        <p class="small mt-1 text-muted">CE Marking</p>
                    </div>
                    <div class="col-md-2 col-4 text-center">
                        <img src="assets/img/certifications/iso-9001-2015.svg" alt="ISO 9001:2015" class="certification-logo" style="width: 60px; height: 60px; filter: grayscale(100%) brightness(0.8);">
                        <p class="small mt-1 text-muted">ISO 9001:2015</p>
                    </div>
                    <div class="col-md-2 col-4 text-center">
                        <img src="assets/img/certifications/iso-13485.svg" alt="ISO 13485" class="certification-logo" style="width: 60px; height: 60px; filter: grayscale(100%) brightness(0.8);">
                        <p class="small mt-1 text-muted">ISO 13485</p>
                    </div>
                    <div class="col-md-2 col-4 text-center">
                        <div class="certification-logo" style="width: 60px; height: 60px; filter: grayscale(100%) brightness(1.2); opacity: 1; display: inline-block;">
                            <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: 100%;">
                                <rect width="120" height="120" fill="#1a5490" rx="8"/>
                                <text x="60" y="25" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="white" text-anchor="middle">FDA</text>
                                <text x="60" y="45" font-family="Arial, sans-serif" font-size="12" fill="white" text-anchor="middle">APPROVED</text>
                                <circle cx="60" cy="65" r="15" stroke="white" stroke-width="2" fill="none"/>
                                <path d="M52 65 L58 71 L70 59" stroke="white" stroke-width="3" fill="none"/>
                                <text x="60" y="90" font-family="Arial, sans-serif" font-size="9" fill="white" text-anchor="middle">U.S. Food & Drug</text>
                                <text x="60" y="105" font-family="Arial, sans-serif" font-size="9" fill="white" text-anchor="middle">Administration</text>
                            </svg>
                        </div>
                        <p class="small mt-1 text-muted">FDA Approved</p>
                    </div>
                    <div class="col-md-2 col-4 text-center">
                        <img src="assets/img/certifications/iec-60601.svg" alt="IEC 60601" class="certification-logo" style="width: 60px; height: 60px; filter: grayscale(100%) brightness(0.8);">
                        <p class="small mt-1 text-muted">IEC 60601</p>
                    </div>
                    <div class="col-md-2 col-4 text-center">
                        <img src="assets/img/certifications/sgs.svg" alt="SGS" class="certification-logo" style="width: 60px; height: 60px; filter: grayscale(100%) brightness(0.8);">
                        <p class="small mt-1 text-muted">SGS</p>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="mt-5" data-aos="fade-up">
                <h3 class="text-center mb-4">i-motion vs i-model Karşılaştırma</h3>
                <p class="text-center text-muted mb-4">Hangi cihaz sizin için daha uygun? Detaylı karşılaştırma tablomuzdan öğrenebilirsiniz.</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background: var(--prime-gold); color: var(--prime-dark);">
                            <tr>
                                <th>Program</th>
                                <th>i-motion</th>
                                <th>i-model</th>
                                <th>Önerilen Hedef</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Yağ Yakımı</strong></td>
                                <td><i class="bi bi-check-circle-fill text-success"></i> <small class="text-muted">Mükemmel</small></td>
                                <td><i class="bi bi-check-circle-fill text-info"></i> <small class="text-muted">Bölgesel</small></td>
                                <td>Kilo verme, metabolizma hızlandırma</td>
                            </tr>
                            <tr>
                                <td><strong>Kas Gelişim</strong></td>
                                <td><i class="bi bi-check-circle-fill text-success"></i> <small class="text-muted">Tam vücut</small></td>
                                <td><i class="bi bi-check-circle text-warning"></i> <small class="text-muted">Tonlama</small></td>
                                <td>Kas kitlesi artışı, güç geliştirme</td>
                            </tr>
                            <tr>
                                <td><strong>Performans</strong></td>
                                <td><i class="bi bi-check-circle-fill text-success"></i> <small class="text-muted">Yüksek</small></td>
                                <td><i class="bi bi-check-circle text-warning"></i> <small class="text-muted">Orta</small></td>
                                <td>Sporcu antrenmanı, atletik performans</td>
                            </tr>
                            <tr>
                                <td><strong>Selülit</strong></td>
                                <td><i class="bi bi-check-circle text-warning"></i> <small class="text-muted">Dolaylı</small></td>
                                <td><i class="bi bi-check-circle-fill text-success"></i> <small class="text-muted">Doğrudan</small></td>
                                <td>Cilt düzgünlüğü, selülit azaltma</td>
                            </tr>
                            <tr>
                                <td><strong>Rehabilitasyon</strong></td>
                                <td><i class="bi bi-check-circle-fill text-success"></i> <small class="text-muted">Tam destek</small></td>
                                <td><i class="bi bi-check-circle-fill text-info"></i> <small class="text-muted">Destek</small></td>
                                <td>Fizik tedavi, ağrı yönetimi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="prime-section prime-section-gray">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="display-5 fw-bold gold-accent mb-4">Neden Prime EMS Studios?</h2>
                    <p class="lead mb-4">
                        Prime EMS Studios, Parsfit mirasını İzmir'e taşıyarak premium ve sonuç odaklı EMS deneyimi sunar.
                    </p>
                    <p>
                        Kişiye özel programlama, medikal destek ve i-motion / i-model profesyonel cihazlarıyla güvenilir sonuçlar garanti ediyoruz. Uzman eğitmen kadromuz, her seansınızda size rehberlik eder.
                    </p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i> Sertifikalı uzman eğitmenler</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i> Hijyenik ve güvenli ortam</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i> Bilimsel takip ve raporlama</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i> %100 müşteri memnuniyeti</li>
                    </ul>

                    <!-- Social Media Sharing -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Paylaş</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary share-btn" data-platform="facebook" data-url="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" title="Facebook'ta Paylaş">
                                <i class="bi bi-facebook"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info share-btn" data-platform="twitter" data-url="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" title="Twitter'da Paylaş">
                                <i class="bi bi-twitter"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success share-btn" data-platform="whatsapp" data-url="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" title="WhatsApp'ta Paylaş">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary share-btn" data-platform="linkedin" data-url="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" title="LinkedIn'de Paylaş">
                                <i class="bi bi-linkedin"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                            <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80"
                                 alt="Prime EMS Studios - Profesyonel EMS Antrenman Salonu" class="img-fluid rounded-3 shadow" loading="lazy">
                        </div>
            </div>
        </div>
    </section>


    <!-- Blog Section -->
    <section id="blog" class="prime-section" style="background: #f8f9fa;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold">Blog & Haberler</h2>
                <p class="lead text-muted">EMS teknolojisi, fitness ve sağlıklı yaşam hakkında en güncel bilgiler</p>
            </div>
            
            <?php
            // Son blog yazılarını çek
            try {
                $blog_stmt = $pdo->prepare("
                    SELECT bp.*, bc.name as category_name, bc.color as category_color 
                    FROM blog_posts bp
                    LEFT JOIN blog_categories bc ON bp.category_id = bc.id
                    WHERE bp.is_published = 1
                    ORDER BY bp.published_at DESC
                    LIMIT 3
                ");
                $blog_stmt->execute();
                $blog_posts = $blog_stmt->fetchAll();
            } catch (PDOException $e) {
                $blog_posts = [];
            }
            ?>
            
            <div class="row g-4">
                <?php if ($blog_posts): ?>
                    <?php foreach ($blog_posts as $post): ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>"
                                         class="card-img-top"
                                         style="height: 250px; object-fit: cover;"
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center" 
                                         style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="bi bi-newspaper text-white" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($post['category_name']): ?>
                                <span class="position-absolute top-0 start-0 m-3 badge" 
                                      style="background-color: <?php echo $post['category_color']; ?>">
                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                </span>
                                <?php endif; ?>
                                
                                <span class="position-absolute top-0 end-0 m-3 badge bg-dark">
                                    <i class="bi bi-clock me-1"></i><?php echo $post['reading_time']; ?> dk
                                </span>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="blog-detail.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(mb_substr($post['excerpt'], 0, 120)); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?php echo date('d.m.Y', strtotime($post['published_at'])); ?>
                                    </small>
                                    <a href="blog-detail.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Devamını Oku
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Henüz blog yazısı bulunmuyor.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($blog_posts): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="blog.php" class="btn btn-prime btn-lg">
                    <i class="bi bi-newspaper me-2"></i>Tüm Yazıları Görüntüle
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($faqs)): ?>
    <section id="faq" class="prime-section faq-section futuristic-gradient">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="faq-intro-card">
                        <span class="badge rounded-pill bg-warning text-dark mb-3"><i class="bi bi-stars me-1"></i> Sık Sorulan Sorular</span>
                        <h2 class="display-5 fw-bold text-white mb-3">Prime EMS Teknolojisi Hakkında Merak Ettikleriniz</h2>
                        <p class="lead text-white-50">Bilimsel WB-EMS yaklaşımımız, kişiye özel programlarımız ve üyelik süreçlerimiz hakkında en çok sorulan soruları yanıtladık.</p>
                        <ul class="list-unstyled text-white-50 mt-4">
                            <li class="d-flex align-items-center mb-3"><i class="bi bi-cpu me-2 text-warning"></i>Yapay zekâ destekli antrenman planlaması</li>
                            <li class="d-flex align-items-center mb-3"><i class="bi bi-activity me-2 text-warning"></i>24 elektrotlu i-motion & i-model entegrasyonu</li>
                            <li class="d-flex align-items-center"><i class="bi bi-shield-check me-2 text-warning"></i>CE sertifikalı medikal güvenlik protokolleri</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="accordion accordion-flush neon-accordion" id="faqAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq-heading-<?php echo $index; ?>">
                                <button class="accordion-button <?php echo $index !== 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq-item-<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="faq-item-<?php echo $index; ?>">
                                    <div>
                                        <span class="faq-category badge bg-primary-subtle text-primary me-2"><?php echo htmlspecialchars($faq['category'] ?? 'Genel'); ?></span>
                                        <?php echo htmlspecialchars($faq['question']); ?>
                                    </div>
                                </button>
                            </h2>
                            <div id="faq-item-<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="faq-heading-<?php echo $index; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Contact Section -->
    <section id="contact" class="prime-section prime-section-dark">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold text-white">İletişim</h2>
                <p class="lead" style="color: var(--prime-gold);">Hemen iletişime geçin, ücretsiz keşif seansınızı planlayalım</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-center text-white">
                        <i class="bi bi-geo-alt-fill" style="font-size: 3rem; color: var(--prime-gold);"></i>
                        <h4 class="mt-3">Adres</h4>
                        <p><?php echo nl2br(htmlspecialchars($contact['address'])); ?></p>
                        <!-- Google Maps Entegrasyonu -->
                        <div class="mt-3">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3126.234567890123!2d27.15234567890123!3d38.45678901234567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14b9b9c9c9c9c9c9%3A0x9c9c9c9c9c9c9c9c!2sBal%C3%A7ova%2C+%C4%B0zmir!5e0!3m2!1str!2str!4v1698745678901!5m2!1str!2str"
                                    width="100%" height="200" style="border:0; border-radius: 10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-center text-white">
                        <i class="bi bi-telephone-fill" style="font-size: 3rem; color: var(--prime-gold);"></i>
                        <h4 class="mt-3">Telefon</h4>
                        <p><?php echo htmlspecialchars($contact['phone']); ?><br><?php echo htmlspecialchars($contact['whatsapp']); ?></p>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-center text-white">
                        <i class="bi bi-clock-fill" style="font-size: 3rem; color: var(--prime-gold);"></i>
                        <h4 class="mt-3">Çalışma Saatleri</h4>
                        <p>Pazartesi - Cumartesi: <?php echo htmlspecialchars($contact['working_hours_weekday']); ?><br>Pazar: <?php echo htmlspecialchars($contact['working_hours_weekend']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <!-- Quick Contact -->
                <div class="col-lg-6 mb-4" data-aos="fade-up">
                    <div class="text-center">
                        <h3 class="text-white mb-4">Hızlı İletişim</h3>
                        <div class="d-grid gap-3">
                            <a href="https://wa.me/<?php echo str_replace('+', '', $contact['whatsapp']); ?>" class="btn btn-success btn-lg">
                                <i class="bi bi-whatsapp me-2"></i> WhatsApp'tan Yaz
                            </a>
                            <a href="tel:<?php echo $contact['phone']; ?>" class="btn btn-prime btn-lg">
                                <i class="bi bi-telephone me-2"></i> Hemen Ara
                            </a>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-envelope me-2"></i> E-posta Gönder
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-form-wrapper">
                        <h3 class="text-white mb-4">Mesaj Gönder</h3>
                        <form id="contactForm" class="contact-form">
                            <?php
                            // Ensure session is started for CSRF token
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }
                            ?>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="website" style="display:none;"> <!-- Honeypot -->
                            
                            <div class="mb-3">
                                <label for="name" class="form-label text-white">Ad Soyad *</label>
                                <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label text-white">E-posta *</label>
                                    <input type="email" class="form-control" id="email" name="email" required maxlength="150">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label text-white">Telefon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="0532 XXX XX XX">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label text-white">Konu *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Konu Seçin</option>
                                    <option value="Ücretsiz Keşif Seansı">Ücretsiz Keşif Seansı</option>
                                    <option value="Randevu Talebi">Randevu Talebi</option>
                                    <option value="Fiyat Bilgisi">Fiyat Bilgisi</option>
                                    <option value="Kampanyalar">Kampanyalar</option>
                                    <option value="Genel Bilgi">Genel Bilgi</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label text-white">Mesajınız *</label>
                                <textarea class="form-control" id="message" name="message" rows="4" required minlength="10" maxlength="2000" placeholder="Mesajınızı buraya yazın..."></textarea>
                                <div class="form-text text-light"><span id="charCount">0</span>/2000 karakter</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy" required>
                                    <label class="form-check-label text-white" for="privacy">
                                        <a href="#" class="text-decoration-none" style="color: var(--prime-gold);">Kişisel Verilerin Korunması</a> kapsamında bilgilerimin işlenmesini kabul ediyorum. *
                                    </label>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-prime btn-lg" id="submitBtn">
                                    <span class="submit-text">
                                        <i class="bi bi-send me-2"></i>Mesajı Gönder
                                    </span>
                                    <span class="loading-text d-none">
                                        <i class="bi bi-arrow-repeat spin me-2"></i>Gönderiliyor...
                                    </span>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Success/Error Messages -->
                        <div id="formMessages" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Chatbot Container -->
    <div class="chatbot-container">
        <!-- Chatbot Bubble -->
        <div class="chatbot-bubble" id="chatbotBubble">
            <i class="bi bi-robot"></i>
            <span>Ücretsiz Deneme Dersi Randevusu Al!</span>
            <span class="bubble-close" onclick="hideBubble()">×</span>
        </div>
        
        <!-- WhatsApp Button -->
        <a href="https://wa.me/<?php echo str_replace('+', '', $contact['whatsapp']); ?>?text=Merhaba!%20Prime%20EMS%20Studios%20için%20randevu%20almak%20istiyorum." 
           class="whatsapp-button" target="_blank" title="WhatsApp ile İletişime Geç">
            <i class="bi bi-whatsapp"></i>
        </a>
        
        <!-- Chatbot Icon -->
        <div class="chatbot-icon" onclick="startChat()" title="AI Asistan ile Konuş">
            <i class="bi bi-robot"></i>
        </div>
    </div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Sticky CTA Bar -->
    <div class="sticky-cta" id="stickyCTA">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <strong>🎉 Açılış Kampanyası!</strong> İlk 30 müşterimize %45 indirim
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="#reservation" class="btn btn-prime btn-sm">Ücretsiz Keşif Seansı</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/scripts.php'; ?>

    <script>
        (function () {
            const doc = document;

            function shareOnSocialMedia(platform, url, title = 'Prime EMS Studios İzmir') {
                const encodedUrl = encodeURIComponent(url);
                const encodedTitle = encodeURIComponent(title);
                let shareUrl = '';

                switch (platform) {
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
                        break;
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`;
                        break;
                    case 'linkedin':
                        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
                        break;
                    case 'whatsapp':
                        shareUrl = `https://wa.me/?text=${encodedTitle}%20${encodedUrl}`;
                        break;
                }

                if (shareUrl) {
                    window.open(shareUrl, '_blank', 'width=600,height=400');
                }
            }

            function initLazyLoading() {
                const lazyImages = doc.querySelectorAll('.lazy-loading');

                if ('IntersectionObserver' in window) {
                    const imageObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                const src = img.dataset.src;

                                if (src) {
                                    img.src = src;
                                    img.classList.remove('lazy-loading');
                                    img.classList.add('lazy-loaded');

                                    img.addEventListener('load', () => {
                                        img.classList.remove('has-placeholder');
                                    });

                                    observer.unobserve(img);
                                }
                            }
                        });
                    }, {
                        rootMargin: '50px 0px',
                        threshold: 0.1
                    });

                    lazyImages.forEach(img => imageObserver.observe(img));
                } else {
                    lazyImages.forEach(img => {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-loading');
                        img.classList.add('lazy-loaded');
                    });
                }
            }

            function supportsWebP() {
                return new Promise(resolve => {
                    const webP = new Image();
                    webP.onload = webP.onerror = () => {
                        resolve(webP.height === 2);
                    };
                    webP.src = "data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA";
                });
            }

            function optimizeDeviceImages() {
                const deviceImages = doc.querySelectorAll('.device-image');

                deviceImages.forEach(img => {
                    img.classList.add('has-placeholder');

                    img.addEventListener('error', function () {
                        this.classList.add('fallback-image');
                        this.src = 'assets/images/device-placeholder.jpg';
                    });

                    img.addEventListener('load', function () {
                        this.classList.remove('lazy-loading', 'has-placeholder');
                        this.classList.add('lazy-loaded');
                    });
                });
            }

            document.addEventListener('keydown', event => {
                if (event.key === 's' && event.altKey) {
                    event.preventDefault();
                    const skipLink = doc.querySelector('.skip-link');
                    if (skipLink) {
                        skipLink.focus();
                    }
                }

                if (event.key === 'Escape') {
                    const activeElement = doc.activeElement;
                    if (activeElement && activeElement.blur) {
                        activeElement.blur();
                    }
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                initLazyLoading();
                optimizeDeviceImages();

                supportsWebP().then(supported => {
                    const message = supported ? 'WebP supported - using optimized images' : 'WebP not supported - using fallback images';
                    console.log(message);
                });

                doc.querySelectorAll('.share-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const platform = btn.getAttribute('data-platform');
                        const url = btn.getAttribute('data-url');
                        const title = 'Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç';
                        shareOnSocialMedia(platform, url, title);
                    });

                    btn.addEventListener('keydown', event => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            btn.click();
                        }
                    });
                });

                const contactForm = doc.getElementById('contactForm');
                const messageTextarea = doc.getElementById('message');
                const charCount = doc.getElementById('charCount');
                const submitBtn = doc.getElementById('submitBtn');
                const formMessages = doc.getElementById('formMessages');

                if (messageTextarea && charCount) {
                    messageTextarea.addEventListener('input', function () {
                        charCount.textContent = this.value.length;

                        if (this.value.length > 2000) {
                            this.classList.add('is-invalid');
                            this.setCustomValidity('Mesaj 2000 karakterden uzun olamaz.');
                        } else if (this.value.length < 10 && this.value.length > 0) {
                            this.classList.add('is-invalid');
                            this.setCustomValidity('Mesaj en az 10 karakter olmalıdır.');
                        } else {
                            this.classList.remove('is-invalid');
                            this.setCustomValidity('');
                        }
                    });
                }

                if (contactForm) {
                    contactForm.addEventListener('submit', event => {
                        event.preventDefault();

                        if (!contactForm.checkValidity()) {
                            contactForm.classList.add('was-validated');
                            return;
                        }

                        submitBtn.disabled = true;
                        submitBtn.querySelector('.submit-text').classList.add('d-none');
                        submitBtn.querySelector('.loading-text').classList.remove('d-none');
                        formMessages.innerHTML = '';

                        const formData = new FormData(contactForm);

                        fetch('contact-form.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    formMessages.innerHTML = `
                                        <div class="alert alert-success" role="alert">
                                            <i class="bi bi-check-circle me-2"></i>${data.message}
                                        </div>
                                    `;

                                    contactForm.reset();
                                    contactForm.classList.remove('was-validated');
                                    charCount.textContent = '0';
                                    formMessages.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                } else {
                                    let errorHtml = `
                                        <div class="alert alert-danger" role="alert">
                                            <i class="bi bi-exclamation-circle me-2"></i>${data.message}
                                        </div>
                                    `;

                                    if (data.errors) {
                                        Object.keys(data.errors).forEach(field => {
                                            const fieldElement = doc.getElementById(field);
                                            if (fieldElement) {
                                                fieldElement.classList.add('is-invalid');
                                                const feedback = fieldElement.parentNode.querySelector('.invalid-feedback');
                                                if (feedback) {
                                                    feedback.textContent = data.errors[field];
                                                }
                                            }
                                        });

                                        contactForm.classList.add('was-validated');
                                    }

                                    formMessages.innerHTML = errorHtml;
                                }
                            })
                            .catch(error => {
                                console.error('Form submission error:', error);
                                formMessages.innerHTML = `
                                    <div class="alert alert-danger" role="alert">
                                        <i class="bi bi-exclamation-circle me-2"></i>Bir hata oluştu. Lütfen daha sonra tekrar deneyin.
                                    </div>
                                `;
                            })
                            .finally(() => {
                                submitBtn.disabled = false;
                                submitBtn.querySelector('.submit-text').classList.remove('d-none');
                                submitBtn.querySelector('.loading-text').classList.add('d-none');
                            });
                    });

                    const inputs = contactForm.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        input.addEventListener('blur', function () {
                            if (this.checkValidity()) {
                                this.classList.remove('is-invalid');
                                this.classList.add('is-valid');
                            } else {
                                this.classList.remove('is-valid');
                                this.classList.add('is-invalid');
                            }
                        });

                        input.addEventListener('input', function () {
                            if (this.classList.contains('is-invalid') && this.checkValidity()) {
                                this.classList.remove('is-invalid');
                                this.classList.add('is-valid');
                            }
                        });
                    });
                }
            });
        }());
    </script>
</body>
</html>