<?php
require_once 'config/database.php';
require_once 'config/security.php';

// Get services from database
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();

    // Get site settings for contact info
    $contact = [
        'phone' => getSetting('contact_phone', '+90 232 555 66 77'),
        'email' => getSetting('contact_email', 'info@primeems.com')
    ];
} catch (PDOException $e) {
    error_log("Services page database error: " . $e->getMessage());
    $services = [];
    $contact = [
        'phone' => '+90 232 555 66 77',
        'email' => 'info@primeems.com'
    ];
}
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags -->
    <title>Hizmetler - Prime EMS Studios İzmir | EMS Antrenman Programları</title>
    <meta name="title" content="Hizmetler - Prime EMS Studios İzmir | EMS Antrenman Programları">
    <meta name="description" content="Prime EMS Studios İzmir'de profesyonel EMS antrenman programları. Yağ yakımı, sıkılaşma, güç artışı ve rehabilitasyon hizmetleri. Kişiye özel programlar.">
    <meta name="keywords" content="EMS hizmetleri, yağ yakımı, sıkılaşma, güç artışı, rehabilitasyon, Prime EMS, İzmir EMS antrenman">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://primeemsstudios.com/services.php">
    <meta property="og:title" content="Hizmetler - Prime EMS Studios İzmir">
    <meta property="og:description" content="Profesyonel EMS antrenman programları ile hedeflerinize ulaşın.">
    <meta property="og:image" content="https://primeemsstudios.com/assets/images/logo.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://primeemsstudios.com/services.php">

    <!-- Service Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Prime EMS Antrenman Hizmetleri",
        "description": "Profesyonel EMS cihazları ile 20 dakikalık antrenman programları",
        "provider": {
            "@type": "Organization",
            "name": "Prime EMS Studios",
            "url": "https://primeemsstudios.com"
        },
        "areaServed": {
            "@type": "City",
            "name": "İzmir, Türkiye"
        },
        "serviceType": "EMS Antrenman",
        "offers": [
            {
                "@type": "Offer",
                "name": "Prime Slim",
                "description": "Yağ yakımı için EMS antrenmanı",
                "priceSpecification": {
                    "@type": "PriceSpecification",
                    "price": "200",
                    "priceCurrency": "TRY"
                },
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Prime Sculpt",
                "description": "Bölgesel sıkılaşma",
                "priceSpecification": {
                    "@type": "PriceSpecification",
                    "price": "250",
                    "priceCurrency": "TRY"
                },
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Prime Power",
                "description": "Güç ve performans artışı",
                "priceSpecification": {
                    "@type": "PriceSpecification",
                    "price": "300",
                    "priceCurrency": "TRY"
                },
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Rehab & Pain",
                "description": "Rehabilitasyon ve ağrı yönetimi",
                "priceSpecification": {
                    "@type": "PriceSpecification",
                    "price": "180",
                    "priceCurrency": "TRY"
                },
                "availability": "https://schema.org/InStock"
            }
        ]
    }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Prime EMS Theme -->
    <link rel="stylesheet" href="assets/css/theme.css">

    <style>
        .service-hero {
            background: linear-gradient(135deg, var(--prime-dark) 0%, var(--prime-primary) 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .service-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--prime-shadow-card);
            transition: var(--transition-normal);
            height: 100%;
            border: 2px solid transparent;
        }

        .service-card:hover {
            border-color: var(--prime-gold);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.2);
        }

        .service-icon {
            font-size: 4rem;
            color: var(--prime-gold);
            margin-bottom: 20px;
        }

        .pricing-section {
            background: var(--prime-light-gray);
            padding: 80px 0;
        }

        .pricing-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            text-align: center;
            box-shadow: var(--prime-shadow-card);
            transition: var(--transition-normal);
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .pricing-card .price {
            font-size: 2.5rem;
            color: var(--prime-gold);
            font-weight: 700;
        }

        .pricing-card .period {
            font-size: 1rem;
            color: var(--prime-dark);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="service-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Hizmet Programlarımız</h1>
                    <p class="lead mb-4">20 dakikada maksimum sonuç, bilimsel EMS teknolojisi ile hedeflerinize ulaşın</p>
                    <a href="#contact" class="btn btn-prime btn-lg">Ücretsiz Keşif Seansı Alın</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold gold-accent">EMS Antrenman Programları</h2>
                <p class="lead text-muted">Hedeflerinize özel tasarlanmış profesyonel programlar</p>
            </div>

            <div class="row g-4">
                <?php if ($services): ?>
                    <?php foreach ($services as $index => $service): ?>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="service-card">
                            <div class="text-center">
                                <i class="bi bi-lightning-charge-fill service-icon"></i>
                                <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                                <p class="text-primary fw-bold"><?php echo htmlspecialchars($service['goal']); ?></p>
                                <p class="text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                                <div class="mt-3">
                                    <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($service['duration'] ?? '20 dk'); ?></span>
                                    <?php if ($service['price']): ?>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($service['price']); ?> ₺</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default services if database empty -->
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-card">
                            <div class="text-center">
                                <i class="bi bi-fire service-icon"></i>
                                <h4>Prime Slim</h4>
                                <p class="text-primary fw-bold">Yağ Yakımı</p>
                                <p class="text-muted">Hızlı metabolizma ve etkili yağ yakımı için özel program</p>
                                <div class="mt-3">
                                    <span class="badge bg-warning text-dark">20 dk</span>
                                    <span class="badge bg-success">200 ₺</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-card">
                            <div class="text-center">
                                <i class="bi bi-lightning-charge-fill service-icon"></i>
                                <h4>Prime Sculpt</h4>
                                <p class="text-primary fw-bold">Bölgesel Sıkılaşma</p>
                                <p class="text-muted">İstediğiniz bölgelerde sıkılaşma ve şekillendirme</p>
                                <div class="mt-3">
                                    <span class="badge bg-warning text-dark">20 dk</span>
                                    <span class="badge bg-success">250 ₺</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="service-card">
                            <div class="text-center">
                                <i class="bi bi-rocket-takeoff-fill service-icon"></i>
                                <h4>Prime Power</h4>
                                <p class="text-primary fw-bold">Güç & Performans</p>
                                <p class="text-muted">Atletik performans ve kas gücü artışı</p>
                                <div class="mt-3">
                                    <span class="badge bg-warning text-dark">20 dk</span>
                                    <span class="badge bg-success">300 ₺</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <div class="service-card">
                            <div class="text-center">
                                <i class="bi bi-heart-pulse-fill service-icon"></i>
                                <h4>Rehab & Pain</h4>
                                <p class="text-primary fw-bold">Rehabilitasyon</p>
                                <p class="text-muted">Fizyoterapi desteği ve ağrı yönetimi</p>
                                <div class="mt-3">
                                    <span class="badge bg-warning text-dark">20 dk</span>
                                    <span class="badge bg-success">180 ₺</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold gold-accent">Paket Seçenekleri</h2>
                <p class="lead text-muted">İhtiyacınıza uygun paket seçin</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="pricing-card">
                        <h4>Başlangıç</h4>
                        <div class="price">200<span class="period">₺/seans</span></div>
                        <p>Tek seans deneyimi</p>
                        <ul class="list-unstyled">
                            <li>✓ 20 dakikalık antrenman</li>
                            <li>✓ Kişisel danışmanlık</li>
                            <li>✓ Vücut analizi</li>
                        </ul>
                        <a href="#contact" class="btn btn-prime">Rezervasyon Yap</a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="pricing-card">
                        <h4>Standart</h4>
                        <div class="price">1.500<span class="period">₺/ay</span></div>
                        <p>Haftada 2 seans</p>
                        <ul class="list-unstyled">
                            <li>✓ 8 seans/ay</li>
                            <li>✓ İlerleme takibi</li>
                            <li>✓ Beslenme önerileri</li>
                        </ul>
                        <a href="#contact" class="btn btn-prime">Rezervasyon Yap</a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="pricing-card">
                        <h4>VIP</h4>
                        <div class="price">2.500<span class="period">₺/ay</span></div>
                        <p>Haftada 3 seans</p>
                        <ul class="list-unstyled">
                            <li>✓ 12 seans/ay</li>
                            <li>✓ Özel eğitmen</li>
                            <li>✓ Detaylı raporlama</li>
                        </ul>
                        <a href="#contact" class="btn btn-prime">Rezervasyon Yap</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section id="contact" class="prime-section prime-section-dark">
        <div class="container">
            <div class="text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold text-white mb-4">Ücretsiz Keşif Seansı</h2>
                <p class="lead mb-4" style="color: var(--prime-gold);">EMS teknolojisini deneyimleyin, sonuçları görün</p>
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                            <a href="tel:<?php echo $contact['phone']; ?>" class="btn btn-prime btn-lg">
                                <i class="bi bi-telephone me-2"></i><?php echo $contact['phone']; ?>
                            </a>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-envelope me-2"></i>E-posta Gönder
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>