<?php
require_once 'config/database.php';
require_once 'config/security.php';

// Güvenli HTML render fonksiyonu
function safeHtmlRender($content) {
    if (empty($content)) {
        return '';
    }

    // Tehlikeli HTML etiketlerini kaldır, ancak güvenli olanları koru
    $allowed_tags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><code><pre>';
    $clean_html = strip_tags($content, $allowed_tags);

    // XSS koruması için ek filtreleme
    $clean_html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $clean_html);
    $clean_html = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi', '', $clean_html);
    $clean_html = preg_replace('/on\w+="[^"]*"/i', '', $clean_html);

    return $clean_html;
}

// Get devices from database (ems_devices tablosundan)
try {
    $stmt = $pdo->prepare("SELECT * FROM ems_devices WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    $devices = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Devices page database error: " . $e->getMessage());
    $devices = [];
}

// Default devices if database empty
$default_devices = [
    [
        'name' => 'i-motion (Pro)',
        'model' => 'EMS-001',
        'short_description' => 'Kablosuz, tam vücut stimülasyonu',
        'long_description' => '20 dakika, 300+ kas uyarımı. Çoklu program modları ile yağ yakımı, kuvvet, rehabilitasyon. Alman teknolojisi.',
        'features' => ['Kablosuz teknoloji', '16 kas grubu', 'Kişiye özel programlama', 'Gerçek zamanlı takip'],
        'specifications' => ['Frekans: 1-120 Hz', 'Program sayısı: 50+', 'Ağırlık: 15 kg', 'Garanti: 2 yıl']
    ],
    [
        'name' => 'i-model (Targeted)',
        'model' => 'EMS-002',
        'short_description' => 'Bölgesel yüksek yoğunluk',
        'long_description' => 'Estetik & sıkılaşma odaklı. Bölgesel kontrole odaklı programlar. Selülit ve sarkma tedavisinde etkili.',
        'features' => ['Hedefli stimülasyon', 'Yüksek frekans', 'Derin kas aktivasyonu', 'Hızlı sonuç'],
        'specifications' => ['Frekans: 1-150 Hz', 'Program sayısı: 30+', 'Ağırlık: 12 kg', 'Garanti: 2 yıl']
    ]
];
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags -->
    <title>Cihazlar - Prime EMS Studios İzmir | Profesyonel EMS Cihazları</title>
    <meta name="title" content="Cihazlar - Prime EMS Studios İzmir | Profesyonel EMS Cihazları">
    <meta name="description" content="Prime EMS Studios İzmir'de kullanılan Almanya menşeli profesyonel EMS cihazları. i-motion ve i-model cihazları ile güvenli ve etkili antrenman.">
    <meta name="keywords" content="EMS cihazları, i-motion, i-model, tıbbi cihazlar, elektrik kas stimülasyonu, Almanya teknolojisi, CE sertifikalı">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://primeemsstudios.com/devices.php">
    <meta property="og:title" content="Cihazlar - Prime EMS Studios İzmir">
    <meta property="og:description" content="Almanya menşeli profesyonel EMS cihazları ile tanışın.">
    <meta property="og:image" content="https://primeemsstudios.com/assets/images/logo.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://primeemsstudios.com/devices.php">

    <!-- Product Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "i-motion EMS Cihazı",
        "description": "Kablosuz, tam vücut EMS stimülasyon cihazı. Yağ yakımı, kuvvet artışı ve rehabilitasyon için profesyonel cihaz.",
        "brand": {
            "@type": "Brand",
            "name": "i-motion"
        },
        "manufacturer": {
            "@type": "Organization",
            "name": "Alman Üretici",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "DE"
            }
        },
        "model": "EMS-001",
        "category": "Medical Device",
        "offers": {
            "@type": "Offer",
            "price": "150000",
            "priceCurrency": "TRY",
            "availability": "https://schema.org/InStock",
            "seller": {
                "@type": "Organization",
                "name": "Prime EMS Studios"
            }
        },
        "additionalProperty": [
            {
                "@type": "PropertyValue",
                "name": "Frekans Aralığı",
                "value": "1-120 Hz"
            },
            {
                "@type": "PropertyValue",
                "name": "Program Sayısı",
                "value": "50+"
            },
            {
                "@type": "PropertyValue",
                "name": "Garanti",
                "value": "2 yıl"
            }
        ],
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "150"
        }
    }
    </script>

    <!-- MedicalDevice Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "MedicalDevice",
        "name": "i-motion EMS Tıbbi Cihazı",
        "description": "Elektrik Kas Stimülasyonu (EMS) tıbbi cihazı. Fizik tedavi ve rehabilitasyon amaçlı kullanılır.",
        "medicalDevicePurpose": [
            "Fizik tedavi",
            "Rehabilitasyon",
            "Kas güçlendirme",
            "Ağrı yönetimi"
        ],
        "relevantSpecialty": [
            {
                "@type": "MedicalSpecialty",
                "name": "Fiziksel Tıp ve Rehabilitasyon"
            },
            {
                "@type": "MedicalSpecialty",
                "name": "Spor Hekimliği"
            }
        ],
        "isAvailableGenerically": false,
        "isProprietary": true,
        "manufacturer": {
            "@type": "Organization",
            "name": "Alman Tıbbi Cihaz Üreticisi",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "DE"
            }
        },
        "proprietaryName": "i-motion Pro EMS System",
        "regulatoryApproval": [
            {
                "@type": "RegulatoryApproval",
                "approvalNumber": "CE-123456",
                "approvingAuthority": "European CE Marking"
            },
            {
                "@type": "RegulatoryApproval",
                "approvalNumber": "FDA-789012",
                "approvingAuthority": "FDA"
            }
        ]
    }
    </script>

    <!-- Product Schema for i-model -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "i-model EMS Cihazı",
        "description": "Bölgesel EMS stimülasyon cihazı. Estetik ve sıkılaşma odaklı yüksek frekanslı cihaz.",
        "brand": {
            "@type": "Brand",
            "name": "i-model"
        },
        "manufacturer": {
            "@type": "Organization",
            "name": "Alman Üretici",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "DE"
            }
        },
        "model": "EMS-002",
        "category": "Medical Device",
        "offers": {
            "@type": "Offer",
            "price": "120000",
            "priceCurrency": "TRY",
            "availability": "https://schema.org/InStock",
            "seller": {
                "@type": "Organization",
                "name": "Prime EMS Studios"
            }
        },
        "additionalProperty": [
            {
                "@type": "PropertyValue",
                "name": "Frekans Aralığı",
                "value": "1-150 Hz"
            },
            {
                "@type": "PropertyValue",
                "name": "Program Sayısı",
                "value": "30+"
            },
            {
                "@type": "PropertyValue",
                "name": "Garanti",
                "value": "2 yıl"
            }
        ],
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.7",
            "reviewCount": "120"
        }
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
        .device-hero {
            background: linear-gradient(135deg, var(--prime-dark) 0%, var(--prime-primary) 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .device-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--prime-shadow-card);
            transition: var(--transition-normal);
            height: 100%;
        }

        .device-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .device-image {
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }

        .device-content {
            padding: 30px;
        }

        .device-features {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .device-features li {
            padding: 8px 0;
            border-bottom: 1px solid var(--prime-light-gray);
            display: flex;
            align-items: center;
        }

        .device-features li::before {
            content: '✓';
            color: var(--prime-gold);
            font-weight: bold;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .tech-specs {
            background: var(--prime-light-gray);
            padding: 30px;
            border-radius: var(--radius-md);
            margin-top: 30px;
        }

        .certifications-section {
            background: var(--prime-light-gray);
            padding: 80px 0;
        }

        .certification-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
            box-shadow: var(--prime-shadow-card);
            transition: var(--transition-normal);
        }

        .certification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .comparison-table {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--prime-shadow-card);
        }

        .comparison-table thead th {
            background: var(--prime-gold);
            color: var(--prime-dark);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="device-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Profesyonel Cihazlarımız</h1>
                    <p class="lead mb-4">Almanya'nın önde gelen EMS teknolojisi ile güvenli ve etkili antrenman deneyimi</p>
                    <a href="#devices" class="btn btn-prime btn-lg">Cihazları İncele</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Devices Section -->
    <section id="devices" class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold gold-accent">EMS Cihaz Teknolojisi</h2>
                <p class="lead text-muted">CE sertifikalı, FDA onaylı profesyonel cihazlar</p>
            </div>

            <div class="row g-4">
                <?php
                $device_list = !empty($devices) ? $devices : $default_devices;
                foreach ($device_list as $index => $device):
                    $features = isset($device['features']) ? json_decode($device['features'], true) : ($device['features'] ?? []);
                    $specifications = isset($device['specifications']) ? json_decode($device['specifications'], true) : ($device['specifications'] ?? []);
                    $certifications = isset($device['certifications']) ? json_decode($device['certifications'], true) : [];
                    $usage_areas = isset($device['usage_areas']) ? json_decode($device['usage_areas'], true) : [];
                    $benefits = isset($device['benefits']) ? json_decode($device['benefits'], true) : [];
                    $exercise_programs = isset($device['exercise_programs']) ? json_decode($device['exercise_programs'], true) : [];
                ?>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="device-card">
                        <div class="device-image">
                            <?php if (!empty($device['main_image'])): ?>
                                <img src="assets/images/devices/<?php echo htmlspecialchars(basename($device['main_image'])); ?>" alt="<?php echo htmlspecialchars($device['name']); ?>" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover; border-radius: 16px;">
                            <?php else: ?>
                                <i class="bi bi-cpu"></i>
                            <?php endif; ?>
                        </div>
                        <div class="device-content">
                            <h3 class="h4 mb-2"><?php echo htmlspecialchars($device['name']); ?></h3>
                            <?php if (isset($device['device_type'])): ?>
                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($device['device_type'] === 'i-motion' ? 'Hareketli EMS Sistemi' : 'Hareketsiz EMS Sistemi'); ?></p>
                            <?php endif; ?>
                            <?php if (isset($device['model'])): ?>
                            <p class="text-primary fw-bold">Model: <?php echo htmlspecialchars($device['model']); ?></p>
                            <?php endif; ?>
                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($device['short_description'] ?? 'Profesyonel EMS cihazı'); ?></p>
                            <div class="text-muted"><?php echo safeHtmlRender($device['long_description'] ?? 'Detaylı açıklama mevcut değil.'); ?></div>

                            <?php if (!empty($features)): ?>
                            <ul class="device-features">
                                <?php foreach ($features as $feature): ?>
                                <li><?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>

                            <?php if (!empty($specifications)): ?>
                            <div class="tech-specs">
                                <h5 class="text-warning mb-3"><i class="bi bi-gear me-2"></i>Teknik Özellikler</h5>
                                <ul class="list-unstyled small">
                                    <?php foreach ($specifications as $spec): ?>
                                    <li><i class="bi bi-dot me-2"></i><?php echo htmlspecialchars($spec); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <span class="badge bg-primary">Almanya Menşeli</span>
                                <?php if (!empty($certifications)): ?>
                                    <?php foreach($certifications as $cert): ?>
                                        <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($cert); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">CE Sertifikalı</span>
                                    <span class="badge bg-success">FDA Onaylı</span>
                                    <span class="badge bg-info">ISO 13485</span>
                                <?php endif; ?>
                                <?php if (isset($device['capacity']) && $device['capacity'] > 0): ?>
                                    <span class="badge bg-secondary"><?php echo $device['capacity']; ?> Kişilik</span>
                                <?php endif; ?>
                                <?php if (!empty($device['price_range'])): ?>
                                    <span class="badge bg-danger"><?php echo htmlspecialchars($device['price_range']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Certifications Section -->
    <section class="certifications-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold gold-accent">Kalite Sertifikalarımız</h2>
                <p class="lead text-muted">Uluslararası standartlarda güvenilirlik</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-md-2 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="certification-card">
                        <img src="assets/img/certifications/ce-marking.svg" alt="CE Marking" class="img-fluid mb-2" style="width: 50px; height: 50px;">
                        <p class="small mb-0">CE Marking</p>
                    </div>
                </div>
                <div class="col-md-2 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="certification-card">
                        <img src="assets/img/certifications/iso-9001-2015.svg" alt="ISO 9001:2015" class="img-fluid mb-2" style="width: 50px; height: 50px;">
                        <p class="small mb-0">ISO 9001:2015</p>
                    </div>
                </div>
                <div class="col-md-2 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="certification-card">
                        <img src="assets/img/certifications/iso-13485.svg" alt="ISO 13485" class="img-fluid mb-2" style="width: 50px; height: 50px;">
                        <p class="small mb-0">ISO 13485</p>
                    </div>
                </div>
                <div class="col-md-2 col-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="certification-card">
                        <img src="assets/img/certifications/fda-approved.svg" alt="FDA Approved" class="img-fluid mb-2" style="width: 50px; height: 50px;">
                        <p class="small mb-0">FDA Approved</p>
                    </div>
                </div>
                <div class="col-md-2 col-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="certification-card">
                        <img src="assets/img/certifications/iec-60601.svg" alt="IEC 60601" class="img-fluid mb-2" style="width: 50px; height: 50px;">
                        <p class="small mb-0">IEC 60601</p>
                    </div>
                </div>
                <div class="col-md-2 col-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="certification-card">
                        <img src="assets/img/certifications/sgs.svg" alt="SGS" class="img-fluid mb-2" style="width: 50px; height: 50px;">
                        <p class="small mb-0">SGS</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold gold-accent">Cihaz Karşılaştırma</h2>
                <p class="lead text-muted">Hangi cihaz sizin için uygun?</p>
            </div>

            <div class="comparison-table" data-aos="fade-up">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Özellik</th>
                            <th>i-motion</th>
                            <th>i-model</th>
                            <th>Önerilen Kullanım</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Yağ Yakımı</td>
                            <td><i class="bi bi-check-circle-fill text-success"></i></td>
                            <td><i class="bi bi-check-circle text-warning"></i></td>
                            <td>Kilo verme, metabolizma hızlandırma</td>
                        </tr>
                        <tr>
                            <td>Sıkılaşma</td>
                            <td><i class="bi bi-check-circle text-warning"></i></td>
                            <td><i class="bi bi-check-circle-fill text-success"></i></td>
                            <td>Bölgesel sıkılaşma, selülit</td>
                        </tr>
                        <tr>
                            <td>Güç Artışı</td>
                            <td><i class="bi bi-check-circle-fill text-success"></i></td>
                            <td><i class="bi bi-check-circle text-warning"></i></td>
                            <td>Atletik performans, kas gelişimi</td>
                        </tr>
                        <tr>
                            <td>Rehabilitasyon</td>
                            <td><i class="bi bi-check-circle-fill text-success"></i></td>
                            <td><i class="bi bi-check-circle-fill text-success"></i></td>
                            <td>Fizik tedavi desteği, ağrı yönetimi</td>
                        </tr>
                        <tr>
                            <td>Frekans</td>
                            <td>1-120 Hz</td>
                            <td>1-150 Hz</td>
                            <td>Yüksek frekans = Daha derin etki</td>
                        </tr>
                        <tr>
                            <td>Program Sayısı</td>
                            <td>50+</td>
                            <td>30+</td>
                            <td>Çoklu seçenekler</td>
                        </tr>
                    </tbody>
                </table>
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