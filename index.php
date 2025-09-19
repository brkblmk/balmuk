<?php
require_once 'config/database.php';
require_once 'config/security.php';

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
}

// Sabit veriler artık kullanılmıyor - tüm veriler veritabanından çekiliyor
// Admin panelden yapılan değişiklikler artık doğrudan anasayfada görünecek
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags -->
    <title>Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç | Premium EMS</title>
    <meta name="title" content="Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç | Premium EMS">
    <meta name="description" content="Prime EMS Studios İzmir'de EMS cihazları ve tıbbi ekipmanlarla 20 dakikalık sağlık teknolojileri eğitimi sunar. Premium EMS antrenmanları, yağ yakımı ve kas gelişimi için Balçova'da hizmet veriyoruz.">
    <meta name="keywords" content="EMS cihazları, tıbbi ekipmanlar, sağlık teknolojileri, elektrik kas stimülasyonu, i-motion, i-model, Balçova EMS, İzmir EMS, yağ yakımı, kas geliştirme, rehabilitasyon, Prime EMS Studios">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://primeemsstudios.com/">
    <meta property="og:title" content="Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç">
    <meta property="og:description" content="İzmir'de EMS cihazları ve tıbbi ekipmanlarla sağlık teknolojileri. 20 dakikada maksimum sonuç.">
    <meta property="og:image" content="https://primeemsstudios.com/assets/images/logo.png">
    <meta property="og:site_name" content="Prime EMS Studios">
    <meta property="og:locale" content="tr_TR">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://primeemsstudios.com/">
    <meta property="twitter:title" content="Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç">
    <meta property="twitter:description" content="İzmir'de EMS cihazları ve tıbbi ekipmanlarla sağlık teknolojileri. 20 dakikada maksimum sonuç.">
    <meta property="twitter:image" content="https://primeemsstudios.com/assets/images/logo.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="http://localhost:8000/">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <!-- Structured Data - Organization Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Prime EMS Studios",
        "description": "İzmir'de EMS cihazları ve tıbbi ekipmanlarla sağlık teknolojileri hizmetleri sunan premium EMS antrenman merkezi",
        "url": "https://primeemsstudios.com",
        "logo": "https://primeemsstudios.com/assets/images/logo.png",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Balçova",
            "addressRegion": "İzmir",
            "addressCountry": "TR"
        },
        "telephone": "+90 232 555 66 77",
        "email": "info@primeems.com",
        "sameAs": [
            "https://www.facebook.com/primeemsstudios",
            "https://www.instagram.com/primeemsstudios",
            "https://www.twitter.com/primeemsstudios"
        ],
        "foundingDate": "2024",
        "serviceArea": {
            "@type": "City",
            "name": "İzmir, Türkiye"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "name": "Prime EMS Studios İletişim",
            "description": "Prime EMS Studios ile iletişime geçin",
            "telephone": "+90 232 555 66 77",
            "email": "info@primeems.com",
            "contactType": "Customer Service",
            "areaServed": "TR",
            "availableLanguage": "Turkish",
            "hoursAvailable": [
                {
                    "@type": "OpeningHoursSpecification",
                    "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    "opens": "07:00",
                    "closes": "22:00"
                },
                {
                    "@type": "OpeningHoursSpecification",
                    "dayOfWeek": "Sunday",
                    "opens": "09:00",
                    "closes": "20:00"
                }
            ]
        },
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "EMS Hizmetleri",
            "itemListElement": [
                {
                    "@type": "Offer",
                    "name": "Prime Slim",
                    "description": "Yağ yakımı için EMS antrenmanı"
                },
                {
                    "@type": "Offer",
                    "name": "Prime Sculpt",
                    "description": "Bölgesel sıkılaşma"
                },
                {
                    "@type": "Offer",
                    "name": "Prime Power",
                    "description": "Güç ve performans artışı"
                },
                {
                    "@type": "Offer",
                    "name": "Rehab & Pain",
                    "description": "Rehabilitasyon ve ağrı yönetimi"
                }
            ]
        }
    }
    </script>

    <!-- WebSite Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Prime EMS Studios İzmir",
        "url": "https://primeemsstudios.com",
        "description": "İzmir'de EMS cihazları ve tıbbi ekipmanlarla sağlık teknolojileri. 20 dakikada maksimum sonuç.",
        "publisher": {
            "@type": "Organization",
            "name": "Prime EMS Studios"
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://primeemsstudios.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- ContactPoint Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ContactPoint",
        "name": "Prime EMS Studios İletişim",
        "description": "Prime EMS Studios ile iletişime geçin",
        "telephone": "+90 232 555 66 77",
        "email": "info@primeems.com",
        "contactType": "Customer Service",
        "areaServed": "TR",
        "availableLanguage": "Turkish",
        "hoursAvailable": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                "opens": "07:00",
                "closes": "22:00"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Sunday",
                "opens": "09:00",
                "closes": "20:00"
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
    
    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Prime EMS Theme -->
    <link rel="stylesheet" href="assets/css/theme.css">
    
    <style>
        /* Additional custom styles */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            padding: 0 2rem;
        }

        .hero-content h1,
        .hero-content p,
        .hero-content .d-flex {
            text-align: center;
            justify-content: center;
        }
        
        .hero-video, .hero-image {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            object-fit: cover;
            z-index: -2;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(43, 43, 43, 0.8) 0%,
                rgba(43, 43, 43, 0.6) 30%,
                rgba(43, 43, 43, 0.4) 60%,
                rgba(43, 43, 43, 0.7) 100%
            );
            z-index: -1;
        }

        .hero-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 215, 0, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 165, 0, 0.04) 0%, transparent 50%),
                linear-gradient(135deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.05) 100%);
            z-index: 1;
        }

        .hero-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><defs><pattern id="luxury-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="rgba(255,215,0,0.1)"/><circle cx="20" cy="20" r="0.5" fill="rgba(255,215,0,0.2)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23luxury-pattern)"/></svg>');
            opacity: 0.3;
            z-index: 1;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-family: var(--font-primary);
            font-weight: 700;
            font-size: 1.8rem;
            background: var(--prime-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--prime-dark) !important;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--prime-gold);
            transition: width 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }
        
        /* USP Cards */
        .usp-card {
            text-align: center;
            padding: 40px 30px;
            background: linear-gradient(145deg, white 0%, #fafafa 100%);
            border-radius: 20px;
            border: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .usp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--prime-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .usp-card:hover::before {
            transform: scaleX(1);
        }

        .usp-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 60px rgba(255, 215, 0, 0.15);
            border-color: rgba(255, 215, 0, 0.3);
        }

        .usp-icon {
            font-size: 4rem;
            background: var(--prime-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
            position: relative;
            transition: all 0.3s ease;
        }

        .usp-card:hover .usp-icon {
            transform: scale(1.1);
        }
        
        /* Campaign Cards */
        .campaign-card {
            background: linear-gradient(145deg, white 0%, #fefefe 100%);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .campaign-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--prime-gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .campaign-card:hover::before {
            transform: scaleX(1);
        }

        .campaign-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.03) 0%, rgba(255, 165, 0, 0.02) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .campaign-card:hover::after {
            opacity: 1;
        }

        .campaign-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: var(--prime-gradient);
            color: var(--prime-dark);
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            z-index: 2;
            transform: translateY(-10px);
            opacity: 0;
            transition: all 0.3s ease 0.1s;
        }

        .campaign-card:hover .campaign-badge {
            transform: translateY(0);
            opacity: 1;
        }

        .campaign-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 30px 70px rgba(255, 215, 0, 0.25);
            border-color: rgba(255, 215, 0, 0.3);
        }

        .campaign-card h3 {
            transition: color 0.3s ease;
        }

        .campaign-card:hover h3 {
            color: var(--prime-gold);
        }
        
        /* Service Cards */
        .service-card {
            background: linear-gradient(145deg, white 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.05) 0%, rgba(255, 165, 0, 0.02) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .service-card:hover {
            border-color: var(--prime-gold);
            transform: translateY(-8px) scale(1.02);
            background: white;
            box-shadow: 0 20px 50px rgba(255, 215, 0, 0.2);
        }

        .service-icon {
            font-size: 4rem;
            background: var(--prime-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
            position: relative;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .service-card:hover .service-icon {
            transform: scale(1.15) rotate(10deg);
        }

        .service-card h4 {
            position: relative;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .service-card:hover h4 {
            color: var(--prime-gold);
        }

        .service-card p {
            position: relative;
            z-index: 2;
        }
        
        /* Device Section */
        .device-card {
            background: linear-gradient(145deg, white 0%, #fafafa 100%);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            min-height: 600px;
            border: 1px solid rgba(255, 215, 0, 0.1);
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .device-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.03) 0%, rgba(255, 165, 0, 0.01) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .device-card:hover::before {
            opacity: 1;
        }

        .device-card:hover {
            transform: translateY(-10px) scale(1.01);
            box-shadow: 0 30px 60px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
        }

        /* Device Image Styles */
        .device-image-container {
            position: relative;
            height: 280px;
            overflow: hidden;
            border-radius: 15px 15px 0 0;
        }

        .device-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .device-card:hover .device-image {
            transform: scale(1.05);
        }

        .device-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.1) 0%, rgba(255, 215, 0, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .device-card:hover .device-image-overlay {
            opacity: 1;
        }

        .device-placeholder {
            height: 280px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px 15px 0 0;
            color: #6c757d;
        }

        .fallback-image {
            filter: grayscale(50%) brightness(0.9);
        }

        /* Device Badges */
        .device-type-badge .badge,
        .capacity-badge .badge {
            font-size: 0.75rem;
            padding: 6px 12px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Device Content */
        .device-content {
            padding: 30px 25px;
            height: calc(100% - 280px);
            display: flex;
            flex-direction: column;
        }

        .device-content h3 {
            color: var(--prime-dark);
            font-weight: 700;
            margin-bottom: 15px;
            transition: color 0.3s ease;
            font-size: 1.25rem;
        }

        .device-card:hover .device-content h3 {
            color: var(--prime-gold);
        }

        .device-content p {
            line-height: 1.6;
            flex-grow: 1;
        }

        /* EMS Highlight */
        .ems-highlight {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.05) 100%);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }

        .ems-highlight .badge {
            font-size: 0.8rem;
            padding: 8px 15px;
            font-weight: 700;
        }

        /* Device Quick Features */
        .device-quick-features {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .device-quick-features .badge {
            font-size: 0.7rem;
            padding: 5px 10px;
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Device Highlights */
        .device-highlights span {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }

        /* Device Certifications */
        .device-certifications .badge {
            font-size: 0.7rem;
            padding: 5px 10px;
            border-radius: 12px;
        }

        /* Device Actions */
        .device-actions {
            margin-top: auto;
            padding-top: 20px;
        }

        .device-actions .btn {
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .device-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
        }
        
        .device-features {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        
        .device-features li {
            padding: 10px 0;
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
        
        /* WhatsApp Float Button */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #25D366;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 999;
            transition: all 0.3s ease;
        }
        
        .whatsapp-float:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.5);
        }
        
        /* Sticky CTA Bar */
        .sticky-cta {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            z-index: 998;
            display: none;
        }
        
        .sticky-cta.show {
            display: block;
        }
        
        /* Accessibility improvements */
        .share-btn {
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .share-btn:focus {
            outline: 2px solid var(--prime-gold);
            outline-offset: 2px;
        }

        /* Skip link for screen readers */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--prime-dark);
            color: var(--prime-gold);
            padding: 8px;
            text-decoration: none;
            z-index: 9999;
            border-radius: 4px;
        }

        .skip-link:focus {
            top: 6px;
        }

        /* Enhanced focus styles */
        .btn:focus,
        .nav-link:focus,
        a:focus {
            outline: 2px solid var(--prime-gold);
            outline-offset: 2px;
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .btn-prime {
                background: black !important;
                color: yellow !important;
                border: 2px solid yellow !important;
            }

            .hero-overlay {
                background: rgba(0, 0, 0, 0.9) !important;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .pulse-effect,
            .fade-up,
            .hero-video {
                animation: none !important;
            }

            .hero-video {
                display: none;
            }

            .hero-image {
                display: block !important;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content .lead {
                font-size: 1.1rem;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }

            /* Touch-friendly buttons */
            .btn {
                min-height: 44px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            /* Mobile navigation improvements */
            .navbar-nav .nav-link {
                padding: 12px 16px;
                min-height: 44px;
                display: flex;
                align-items: center;
            }

            /* Better spacing for mobile */
            .hero-content {
                padding: 2rem 1rem;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .hero-content h1,
            .hero-content p,
            .hero-content .d-flex {
                text-align: center;
                justify-content: center;
                width: 100%;
            }

            /* Share buttons mobile */
            .share-btn {
                min-width: 48px;
                min-height: 48px;
                font-size: 18px;
            }

            /* Device cards mobile improvements */
            .device-card {
                margin-bottom: 20px;
                border-radius: 15px;
            }

            .device-image-container {
                height: 200px;
            }

            .device-content {
                padding: 20px 15px;
            }

            .device-content h3 {
                font-size: 1.1rem;
                margin-bottom: 10px;
            }

            .device-content p {
                font-size: 0.85rem;
                line-height: 1.4;
            }

            /* Quick features mobile */
            .device-quick-features .badge {
                font-size: 0.65rem;
                padding: 3px 6px;
            }

            /* Highlights mobile */
            .device-highlights span {
                font-size: 0.75rem;
                margin-right: 10px;
            }

            /* Certifications mobile */
            .device-certifications .badge {
                font-size: 0.65rem;
                padding: 3px 6px;
                margin-right: 3px;
            }

            /* Device badges mobile */
            .device-type-badge .badge,
            .capacity-badge .badge {
                font-size: 0.65rem;
                padding: 4px 8px;
            }

            /* Device actions mobile */
            .device-actions .btn {
                font-size: 0.85rem;
                padding: 8px 16px;
            }
        }

        /* Extra small devices */
        @media (max-width: 576px) {
            .device-card {
                padding: 25px 15px;
            }

            .device-card .mt-4 h5 {
                font-size: 1rem;
                margin-bottom: 12px;
            }

            /* Comparison table mobile */
            .table-responsive {
                font-size: 0.8rem;
            }

            .table-responsive .table th,
            .table-responsive .table td {
                padding: 8px 4px;
            }

            /* Certifications mobile */
            .certifications .col-md-2 {
                margin-bottom: 15px;
            }
        }

        /* Certification logos responsive */
        .certification-logo {
            transition: all 0.3s ease;
        }

        .certification-logo:hover {
            filter: grayscale(0%) brightness(1);
            transform: scale(1.1);
        }

        @media (max-width: 576px) {
            .certification-logo {
                width: 50px !important;
                height: 50px !important;
            }
        }
        
        /* Chatbot Bubble */
        .chatbot-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        
        .chatbot-bubble {
            background: var(--prime-gradient);
            color: var(--prime-dark);
            padding: 15px 20px;
            border-radius: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: pulse-bubble 2s infinite;
            font-weight: 600;
            position: relative;
            max-width: 280px;
        }
        
        @keyframes pulse-bubble {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .chatbot-bubble:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(255, 215, 0, 0.5);
        }
        
        .chatbot-bubble::before {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 30px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid var(--prime-gold);
        }
        
        .chatbot-icon {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .chatbot-icon:hover {
            transform: scale(1.15) rotate(10deg);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .whatsapp-button {
            width: 55px;
            height: 55px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .whatsapp-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
            color: white;
        }
        
        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 50px;
            height: 50px;
            background: var(--prime-dark);
            color: var(--prime-gold);
            border: 2px solid var(--prime-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 999;
        }
        
        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-to-top:hover {
            background: var(--prime-gold);
            color: var(--prime-dark);
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        /* Close button for bubble */
        .bubble-close {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: var(--prime-dark);
            color: var(--prime-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .chatbot-bubble:hover .bubble-close {
            opacity: 1;
        }

        /* Lazy Loading Styles */
        .lazy-loading {
            transition: opacity 0.3s ease;
            filter: blur(1px);
        }

        .lazy-loaded {
            filter: blur(0);
        }

        .has-placeholder {
            filter: blur(2px);
        }

        .lazy-error {
            opacity: 0.7;
            filter: grayscale(100%);
        }

        /* Image Loading Spinner */
        .image-loader {
            pointer-events: none;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            position: relative;
        }

        .spinner-ring {
            width: 100%;
            height: 100%;
            border: 3px solid var(--prime-light-gray);
            border-top: 3px solid var(--prime-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Component Loading Animation */
        .component-loader {
            text-align: center;
            padding: 2rem;
        }

        .loading-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: var(--prime-gold);
            border-radius: 50%;
            animation: loadingDots 1.4s infinite ease-in-out both;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        .dot:nth-child(3) { animation-delay: 0s; }

        @keyframes loadingDots {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Component fade-in animation */
        .component-loaded {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Lazy Loading Optimizations */
        @media (max-width: 768px) {
            .lazy-loading {
                /* Reduce blur effect on mobile for better performance */
                filter: blur(0.5px);
            }

            .has-placeholder {
                filter: blur(1px);
            }

            .image-loader {
                /* Smaller loader on mobile */
                transform: scale(0.8);
            }
        }

        /* Reduced motion support for lazy loading */
        @media (prefers-reduced-motion: reduce) {
            .lazy-loading,
            .lazy-loaded,
            .has-placeholder,
            .component-loaded,
            .image-loader,
            .component-loader {
                animation: none !important;
                transition: none !important;
                filter: none !important;
            }
        }

        /* Performance optimizations for slow connections */
        @media (max-width: 576px) and (max-height: 600px) {
            /* Extra small screens - prioritize above the fold content */
            .lazy-loading:not(.above-fold) {
                /* Delay loading for below-fold images on small screens */
                transition-delay: 0.2s;
            }
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .chatbot-container {
                bottom: 20px;
                right: 20px;
            }
            
            .chatbot-bubble {
                max-width: 240px;
                font-size: 14px;
            }
            
            .chatbot-icon {
                width: 55px;
                height: 55px;
                font-size: 25px;
            }
            
            .whatsapp-button {
                width: 48px;
                height: 48px;
                font-size: 24px;
            }
            
            .scroll-to-top {
                bottom: 20px;
                left: 20px;
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <!-- Skip Link for Screen Readers -->
    <a href="#main-content" class="skip-link">Ana içeriğe geç</a>

    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section id="home" class="hero-section" role="banner" aria-label="Ana kahraman bölümü">
        <main id="main-content">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Show sticky CTA and scroll button after scroll
        window.addEventListener('scroll', function() {
            const stickyCTA = document.getElementById('stickyCTA');
            const scrollBtn = document.getElementById('scrollToTop');
            
            if (window.scrollY > 500) {
                stickyCTA.classList.add('show');
            } else {
                stickyCTA.classList.remove('show');
            }
            
            // Show/hide scroll to top button
            if (window.scrollY > 300) {
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.remove('show');
            }
        });
        
        // Smooth scroll for anchor links
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
        
        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Chatbot functions
        function startChat() {
            // Chatbot sayfasını popup olarak aç
            const width = 500;
            const height = 700;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            window.open(
                'chatbot.php',
                'PrimeEMSChatbot',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=no`
            );
        }
        
        function hideBubble() {
            const bubble = document.getElementById('chatbotBubble');
            bubble.style.display = 'none';
            
            // Bubble'ı 30 saniye sonra tekrar göster
            setTimeout(() => {
                bubble.style.display = 'flex';
            }, 30000);
        }
        
        // Bubble'ı sayfa yüklendikten 5 saniye sonra göster
        window.addEventListener('load', function() {
            setTimeout(() => {
                const bubble = document.getElementById('chatbotBubble');
                if (bubble) {
                    bubble.style.display = 'flex';
                    
                    // 10 saniye sonra otomatik gizle
                    setTimeout(() => {
                        if (!sessionStorage.getItem('bubbleHidden')) {
                            bubble.style.animation = 'fadeOut 0.5s ease';
                            setTimeout(() => {
                                bubble.style.display = 'none';
                                sessionStorage.setItem('bubbleHidden', 'true');
                            }, 500);
                        }
                    }, 10000);
                }
            }, 5000);
        });
        
        // Chatbot bubble click event
        document.getElementById('chatbotBubble').addEventListener('click', function(e) {
            if (!e.target.classList.contains('bubble-close')) {
                startChat();
            }
        });
        
        // Social Media Sharing
        function shareOnSocialMedia(platform, url, title = 'Prime EMS Studios İzmir') {
            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(title);
            let shareUrl = '';

            switch(platform) {
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

        // Share buttons event listeners
        document.querySelectorAll('.share-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const platform = this.getAttribute('data-platform');
                const url = this.getAttribute('data-url');
                const title = 'Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç';
                shareOnSocialMedia(platform, url, title);
            });

            // Keyboard navigation support
            btn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        // Enhanced keyboard navigation
        document.addEventListener('keydown', function(e) {
            // Skip link activation
            if (e.key === 's' && e.altKey) {
                e.preventDefault();
                const skipLink = document.querySelector('.skip-link');
                if (skipLink) skipLink.focus();
            }

            // Focus management for modal-like elements
            if (e.key === 'Escape') {
                // Close any open modals or overlays if they exist
                const activeElement = document.activeElement;
                if (activeElement && activeElement.blur) {
                    activeElement.blur();
                }
            }
        });

        // Lazy Loading Implementation
        function initLazyLoading() {
            const lazyImages = document.querySelectorAll('.lazy-loading');

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

                                // Remove placeholder after loading
                                img.addEventListener('load', function() {
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
                // Fallback for browsers without IntersectionObserver
                lazyImages.forEach(img => {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-loading');
                    img.classList.add('lazy-loaded');
                });
            }
        }

        // WebP Support Detection and Fallback
        function supportsWebP() {
            return new Promise((resolve) => {
                const webP = new Image();
                webP.onload = webP.onerror = () => {
                    resolve(webP.height === 2);
                };
                webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
            });
        }

        // Image Optimization and Error Handling
        function optimizeDeviceImages() {
            const deviceImages = document.querySelectorAll('.device-image');

            deviceImages.forEach(img => {
                // Add loading class
                img.classList.add('has-placeholder');

                // Error handling
                img.addEventListener('error', function() {
                    this.classList.add('fallback-image');
                    this.src = 'assets/images/device-placeholder.jpg';
                });

                // Load event
                img.addEventListener('load', function() {
                    this.classList.remove('lazy-loading', 'has-placeholder');
                    this.classList.add('lazy-loaded');
                });
            });
        }

        // Initialize Image Features
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize lazy loading
            initLazyLoading();

            // Initialize image optimization
            optimizeDeviceImages();

            // WebP detection and optimization
            supportsWebP().then(supported => {
                if (supported) {
                    console.log('WebP supported - using optimized images');
                } else {
                    console.log('WebP not supported - using fallback images');
                }
            });

            const contactForm = document.getElementById('contactForm');
            const messageTextarea = document.getElementById('message');
            const charCount = document.getElementById('charCount');
            const submitBtn = document.getElementById('submitBtn');
            const formMessages = document.getElementById('formMessages');
            
            // Character count for message
            if (messageTextarea && charCount) {
                messageTextarea.addEventListener('input', function() {
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
            
            // Form submission
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (!contactForm.checkValidity()) {
                        contactForm.classList.add('was-validated');
                        return;
                    }
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.querySelector('.submit-text').classList.add('d-none');
                    submitBtn.querySelector('.loading-text').classList.remove('d-none');
                    
                    // Clear previous messages
                    formMessages.innerHTML = '';
                    
                    // Prepare form data
                    const formData = new FormData(contactForm);
                    
                    // Send form data
                    fetch('contact-form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            formMessages.innerHTML = `
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>${data.message}
                                </div>
                            `;
                            
                            // Reset form
                            contactForm.reset();
                            contactForm.classList.remove('was-validated');
                            charCount.textContent = '0';
                            
                            // Scroll to success message
                            formMessages.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                        } else {
                            // Show error message
                            let errorHtml = `
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-circle me-2"></i>${data.message}
                                </div>
                            `;
                            
                            // Show field-specific errors
                            if (data.errors) {
                                Object.keys(data.errors).forEach(field => {
                                    const fieldElement = document.getElementById(field);
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
                        // Reset loading state
                        submitBtn.disabled = false;
                        submitBtn.querySelector('.submit-text').classList.remove('d-none');
                        submitBtn.querySelector('.loading-text').classList.add('d-none');
                    });
                });
                
                // Real-time validation
                const inputs = contactForm.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        if (this.checkValidity()) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        }
                    });
                    
                    input.addEventListener('input', function() {
                        if (this.classList.contains('is-invalid') && this.checkValidity()) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        }
                    });
                });
            }
        });
    </script>
    
    <style>
        /* Animation for chatbot bubble */
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Hero CTA Buttons */
        .hero-cta-primary {
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(255, 215, 0, 0.4);
            background: var(--prime-gradient);
            border: none;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hero-cta-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .hero-cta-primary:hover::before {
            left: 100%;
        }

        .hero-cta-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 60px rgba(255, 215, 0, 0.6);
        }

        .hero-cta-primary:active {
            transform: translateY(-2px) scale(1.02);
        }

        .hero-cta-secondary {
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 215, 0, 0.9);
            position: relative;
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .hero-cta-secondary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 215, 0, 0.3));
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .hero-cta-secondary::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 215, 0, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .hero-cta-secondary:hover::before {
            opacity: 1;
        }

        .hero-cta-secondary:hover::after {
            width: 300px;
            height: 300px;
        }

        .hero-cta-secondary:hover {
            background: rgba(255, 215, 0, 0.2);
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.5);
            color: var(--prime-dark);
        }

        .hero-cta-secondary:active {
            transform: translateY(-2px) scale(1.01);
        }

        /* Contact Form Styles */
        .contact-form-wrapper {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .contact-form .form-control,
        .contact-form .form-select {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .contact-form .form-control:focus,
        .contact-form .form-select:focus {
            background: rgba(255, 255, 255, 1);
            border-color: var(--prime-gold);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .contact-form .form-control.is-valid,
        .contact-form .form-select.is-valid {
            border-color: #198754;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.53.53c.27.27.71.27.98 0l2.86-2.86c.27-.27.27-.71 0-.98L6.17 2.89c-.27-.27-.71-.27-.98 0L3.91 4.18 2.64 2.91c-.27-.27-.71-.27-.98 0L1.13 3.43c-.27.27-.27.71 0 .98l1.17 2.32Z'/%3e%3c/svg%3e");
        }
        
        .contact-form .form-control.is-invalid,
        .contact-form .form-select.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4m0-2.4L5.8 7'/%3e%3c/svg%3e");
        }
        
        .contact-form .form-check-input:checked {
            background-color: var(--prime-gold);
            border-color: var(--prime-gold);
        }
        
        .contact-form .form-check-input:focus {
            border-color: var(--prime-gold);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .contact-form .btn-prime {
            position: relative;
            overflow: hidden;
        }
        
        .contact-form .btn-prime:disabled {
            opacity: 0.8;
        }
        
        /* Spinning animation */
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Form validation feedback */
        .contact-form .invalid-feedback {
            display: block;
            color: #ff6b6b;
            font-weight: 500;
        }
        
        .contact-form .valid-feedback {
            display: block;
            color: #51cf66;
            font-weight: 500;
        }
        
        /* Alert messages */
        #formMessages .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .contact-form-wrapper {
                padding: 1.5rem;
                margin-top: 2rem;
            }
        }
    </style>
</body>
</html>