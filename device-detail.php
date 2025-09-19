<?php
require_once 'config/database.php';
require_once 'config/security.php';

// URL'den cihaz slug'ını al
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

try {
    // Cihaz bilgilerini çek
    $stmt = $pdo->prepare("SELECT * FROM ems_devices WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $device = $stmt->fetch();

    if (!$device) {
        header('Location: index.php');
        exit;
    }

    // Meta verileri hazırla
    $pageTitle = htmlspecialchars($device['seo_title'] ?: $device['name']);
    $pageDescription = htmlspecialchars($device['seo_description'] ?: $device['short_description']);
    $pageKeywords = htmlspecialchars($device['seo_keywords'] ?: 'EMS, elektrik stimülasyonu, fitness');

    // Site ayarlarını çek
    $contact = [
        'phone' => getSetting('contact_phone', '+90 232 555 66 77'),
        'whatsapp' => getSetting('contact_whatsapp', '905XXXXXXXXX'),
        'email' => getSetting('contact_email', 'info@primeems.com')
    ];

} catch (PDOException $e) {
    error_log("Device detail error: " . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags -->
    <title><?php echo $pageTitle; ?> - Prime EMS Studios</title>
    <meta name="title" content="<?php echo $pageTitle; ?>">
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="<?php echo $pageKeywords; ?>">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($device['main_image'] ? 'https://' . $_SERVER['HTTP_HOST'] . '/' . $device['main_image'] : 'https://primeemsstudios.com/assets/images/logo.png'); ?>">
    <meta property="og:site_name" content="Prime EMS Studios">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
    <meta property="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta property="twitter:description" content="<?php echo $pageDescription; ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($device['main_image'] ? 'https://' . $_SERVER['HTTP_HOST'] . '/' . $device['main_image'] : 'https://primeemsstudios.com/assets/images/logo.png'); ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            min-height: 60vh;
            display: flex;
            align-items: center;
        }

        .device-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.3" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .device-image {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
        }

        .device-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--prime-gradient);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--prime-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: white;
        }

        .spec-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .spec-table th {
            background: var(--prime-gold);
            color: var(--prime-dark);
            padding: 15px;
            font-weight: 700;
        }

        .spec-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .gallery-item {
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        .cta-section {
            background: linear-gradient(135deg, var(--prime-dark) 0%, #2c3e50 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .breadcrumbs {
            background: transparent;
            padding: 20px 0;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--prime-gold);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="breadcrumbs">
        <div class="container">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="#devices">Cihazlar</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($device['name']); ?></li>
            </ol>
        </div>
    </nav>

    <!-- Device Hero Section -->
    <section class="device-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="device-badge"><?php echo htmlspecialchars($device['device_type']); ?></div>
                    <h1 class="display-4 fw-bold mb-4"><?php echo htmlspecialchars($device['name']); ?></h1>
                    <p class="lead mb-4"><?php echo htmlspecialchars($device['short_description']); ?></p>

                    <div class="d-flex align-items-center mb-4">
                        <div class="me-4">
                            <h4 class="text-warning mb-1"><?php echo htmlspecialchars($device['price_range']); ?></h4>
                            <small class="text-light">Fiyat Aralığı</small>
                        </div>
                        <div class="me-4">
                            <h4 class="text-warning mb-1"><?php echo $device['capacity']; ?> Kişi</h4>
                            <small class="text-light">Kapasite</small>
                        </div>
                        <div>
                            <h4 class="text-warning mb-1"><?php echo htmlspecialchars($device['manufacturer']); ?></h4>
                            <small class="text-light">Üretici</small>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <a href="#reservation" class="btn btn-light btn-lg">
                            <i class="bi bi-calendar-check me-2"></i>Rezervasyon Yap
                        </a>
                        <a href="https://wa.me/<?php echo str_replace('+', '', $contact['whatsapp']); ?>?text=Merhaba! <?php echo htmlspecialchars($device['name']); ?> hakkında bilgi almak istiyorum." class="btn btn-success btn-lg" target="_blank">
                            <i class="bi bi-whatsapp me-2"></i>WhatsApp
                        </a>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left">
                    <?php if (!empty($device['main_image'])): ?>
                    <div class="device-image">
                        <img src="<?php echo htmlspecialchars($device['main_image']); ?>" alt="<?php echo htmlspecialchars($device['name']); ?>" class="img-fluid">
                    </div>
                    <?php else: ?>
                    <div class="device-image bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                        <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Device Details Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-up">
                    <!-- Detailed Description -->
                    <?php if (!empty($device['long_description'])): ?>
                    <div class="mb-5">
                        <h2 class="mb-4">Detaylı Bilgi</h2>
                        <div class="content">
                            <?php echo $device['long_description']; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Features -->
                    <?php if ($device['features']): ?>
                    <div class="mb-5">
                        <h2 class="mb-4">Özellikler</h2>
                        <div class="row g-3">
                            <?php
                            $features = json_decode($device['features'], true);
                            foreach ($features as $feature):
                            ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                    <span><?php echo htmlspecialchars($feature); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Benefits -->
                    <?php if ($device['benefits']): ?>
                    <div class="mb-5">
                        <h2 class="mb-4">Faydalar</h2>
                        <div class="bg-light p-4 rounded-3">
                            <div class="row g-3">
                                <?php
                                $benefits = json_decode($device['benefits'], true);
                                foreach ($benefits as $benefit):
                                ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-heart-fill text-danger me-3 fs-4"></i>
                                        <span><?php echo htmlspecialchars($benefit); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Usage Areas -->
                    <?php if ($device['usage_areas']): ?>
                    <div class="mb-5">
                        <h2 class="mb-4">Kullanım Alanları</h2>
                        <div class="row g-2">
                            <?php
                            $usage_areas = json_decode($device['usage_areas'], true);
                            foreach ($usage_areas as $area):
                            ?>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-primary bg-opacity-10 rounded-3">
                                    <i class="bi bi-geo-alt-fill text-primary fs-4 mb-2"></i>
                                    <div><?php echo htmlspecialchars($area); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Gallery -->
                    <?php if ($device['gallery_images']): ?>
                    <div class="mb-5">
                        <h2 class="mb-4">Galeri</h2>
                        <div class="row g-3">
                            <?php
                            $gallery_images = json_decode($device['gallery_images'], true);
                            foreach ($gallery_images as $image):
                            ?>
                            <div class="col-md-4">
                                <div class="gallery-item">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Galeri görseli" class="img-fluid rounded-3">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4" data-aos="fade-left">
                    <!-- Specifications -->
                    <?php if ($device['specifications']): ?>
                    <div class="spec-table mb-4">
                        <h3 class="text-center p-3 bg-warning text-dark mb-0">Teknik Özellikler</h3>
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <?php
                                $specifications = json_decode($device['specifications'], true);
                                foreach ($specifications as $spec):
                                ?>
                                <tr>
                                    <td colspan="2" class="text-center"><?php echo htmlspecialchars($spec); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Certifications -->
                    <?php if ($device['certifications']): ?>
                    <div class="bg-white p-4 rounded-3 shadow-sm mb-4">
                        <h4 class="mb-3">Sertifikalar</h4>
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $certifications = json_decode($device['certifications'], true);
                            foreach ($certifications as $cert):
                            ?>
                            <span class="badge bg-success fs-6 p-2"><?php echo htmlspecialchars($cert); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Warranty Info -->
                    <?php if (!empty($device['warranty_info'])): ?>
                    <div class="bg-info bg-opacity-10 p-4 rounded-3 shadow-sm mb-4">
                        <h4 class="mb-3 text-info"><i class="bi bi-shield-check me-2"></i>Garanti</h4>
                        <p class="mb-0"><?php echo htmlspecialchars($device['warranty_info']); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Quick Contact -->
                    <div class="bg-success bg-opacity-10 p-4 rounded-3 shadow-sm">
                        <h4 class="mb-3 text-success">Hemen Başlayın</h4>
                        <div class="d-grid gap-2">
                            <a href="tel:<?php echo $contact['phone']; ?>" class="btn btn-success">
                                <i class="bi bi-telephone me-2"></i> Ara
                            </a>
                            <a href="https://wa.me/<?php echo str_replace('+', '', $contact['whatsapp']); ?>" class="btn btn-success" target="_blank">
                                <i class="bi bi-whatsapp me-2"></i> WhatsApp
                            </a>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="btn btn-outline-success">
                                <i class="bi bi-envelope me-2"></i> E-posta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-4 fw-bold mb-4">Hazır mısınız?</h2>
                    <p class="lead mb-4">Prime EMS Studios ile <?php echo htmlspecialchars($device['name']); ?> cihazımızla tanışın ve 20 dakikalık dönüşümü deneyimleyin.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="#reservation" class="btn btn-warning btn-lg">
                            <i class="bi bi-calendar-check me-2"></i>Ücretsiz Keşif Seansı
                        </a>
                        <a href="https://wa.me/<?php echo str_replace('+', '', $contact['whatsapp']); ?>" class="btn btn-light btn-lg" target="_blank">
                            <i class="bi bi-whatsapp me-2"></i>Detaylı Bilgi Alın
                        </a>
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

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Gallery modal functionality
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').src;
                showGalleryModal(imgSrc);
            });
        });

        function showGalleryModal(imgSrc) {
            const modalHtml = `
                <div class="modal fade" id="galleryModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <img src="${imgSrc}" class="img-fluid w-100" alt="Galeri görseli">
                            </div>
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Kapat"></button>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            const existingModal = document.getElementById('galleryModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
            modal.show();
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>