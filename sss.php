<?php
require_once 'config/database.php';
require_once 'config/security.php';

// Get FAQs from database
try {
    $stmt = $pdo->prepare("
        SELECT * FROM faqs
        WHERE is_published = 1
        ORDER BY category ASC, created_at DESC
    ");
    $stmt->execute();
    $faqs = $stmt->fetchAll();

    // Group FAQs by category
    $faqs_by_category = [];
    foreach ($faqs as $faq) {
        $faqs_by_category[$faq['category']][] = $faq;
    }

} catch (PDOException $e) {
    error_log("SSS page database error: " . $e->getMessage());
    $faqs_by_category = [];
}
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags for FAQ Page -->
    <title>Sık Sorulan Sorular - Prime EMS Studios İzmir</title>
    <meta name="title" content="Sık Sorulan Sorular - Prime EMS Studios İzmir">
    <meta name="description" content="Prime EMS Studios İzmir hakkında sık sorulan sorular. EMS cihazları, tıbbi ekipmanlar, hizmet süreçleri ve fiyat bilgileri.">
    <meta name="keywords" content="SSS, sık sorulan sorular, EMS, Prime EMS Studios, İzmir, tıbbi cihazlar">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost:8000/sss.php">
    <meta property="og:title" content="Sık Sorulan Sorular - Prime EMS Studios İzmir">
    <meta property="og:description" content="Prime EMS Studios İzmir hakkında sık sorulan sorular ve cevapları.">
    <meta property="og:image" content="http://localhost:8000/assets/images/logo.png">
    <meta property="og:site_name" content="Prime EMS Studios">
    <meta property="og:locale" content="tr_TR">

    <!-- Canonical URL -->
    <link rel="canonical" href="http://localhost:8000/sss.php">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="http://localhost:8000/sss.php">
    <meta property="twitter:title" content="Sık Sorulan Sorular - Prime EMS Studios İzmir">
    <meta property="twitter:description" content="Prime EMS Studios İzmir hakkında sık sorulan sorular ve cevapları.">
    <meta property="twitter:image" content="http://localhost:8000/assets/images/logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Prime EMS Theme -->
    <link rel="stylesheet" href="assets/css/theme.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Navbar Styles from Homepage */
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

        /* Modern FAQ Section */
        .faq-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            position: relative;
        }

        .faq-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(47, 47, 47, 0.05) 0%, transparent 50%);
            z-index: 0;
        }

        .faq-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }

        .faq-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .faq-card:hover::before {
            transform: scaleX(1);
        }

        .faq-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            border-color: rgba(255, 215, 0, 0.3);
        }

        .faq-question {
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            color: #2F2F2F;
            padding: 24px 28px;
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .faq-question::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .faq-question:hover::before,
        .faq-active .faq-question::before {
            opacity: 1;
        }

        .faq-question:focus {
            outline: 2px solid #FFD700;
            outline-offset: 2px;
        }

        .faq-icon {
            font-size: 1.375rem;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #2F2F2F;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(47, 47, 47, 0.1);
            border-radius: 50%;
        }

        .faq-active .faq-icon,
        .faq-icon.rotate {
            transform: rotate(180deg);
            background: rgba(47, 47, 47, 0.2);
        }

        .faq-answer {
            padding: 0 28px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        .faq-active .faq-answer {
            padding: 28px;
        }

        .faq-answer p {
            margin-bottom: 16px;
            line-height: 1.6;
            color: #495057;
            font-size: 1rem;
        }

        .faq-answer p:last-child {
            margin-bottom: 0;
        }

        .faq-category {
            background: linear-gradient(135deg, #2F2F2F 0%, #1A1A1A 100%);
            color: #FFD700;
            padding: 80px 0 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .faq-category::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255, 215, 0, 0.1), transparent);
            animation: rotate 20s linear infinite;
            z-index: 0;
        }

        .faq-category > * {
            position: relative;
            z-index: 1;
        }

        .faq-category h1 {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-badge {
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            color: #2F2F2F;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-block;
            margin-top: 24px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            transition: all 0.3s ease;
        }

        .category-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
        }

        .back-to-home {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            color: #2F2F2F;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.3);
            z-index: 999;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            border: 2px solid rgba(47, 47, 47, 0.1);
        }

        .back-to-home:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 12px 30px rgba(255, 215, 0, 0.5);
        }

        /* Contact CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 20px;
            padding: 40px;
            margin-top: 40px;
            border: 1px solid rgba(255, 215, 0, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255, 215, 0, 0.05), transparent);
            animation: rotate 25s linear infinite;
            z-index: 0;
        }

        .cta-section > * {
            position: relative;
            z-index: 1;
        }

        .cta-section h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #2F2F2F;
        }

        .cta-section p {
            color: #6c757d;
            margin-bottom: 24px;
            font-size: 1.1rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            color: #2F2F2F;
            padding: 14px 28px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            border: 2px solid rgba(47, 47, 47, 0.1);
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.5);
            background: linear-gradient(135deg, #E6C200 0%, #FFD700 100%);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .faq-section {
                padding: 60px 0;
            }

            .faq-category {
                padding: 60px 0 40px;
            }

            .faq-category h1 {
                font-size: 2.25rem;
            }

            .faq-question {
                font-size: 1.05rem;
                padding: 20px 24px;
            }

            .faq-active .faq-answer {
                padding: 24px;
            }

            .back-to-home {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .cta-section {
                padding: 30px 20px;
                margin: 30px 15px 0;
            }

            .cta-section h4 {
                font-size: 1.25rem;
            }

            .cta-btn {
                padding: 12px 24px;
                font-size: 0.95rem;
            }
        }

        /* Accessibility */
        .faq-question[aria-expanded="true"] .faq-icon::before {
            content: '−';
        }

        .faq-question[aria-expanded="false"] .faq-icon::before {
            content: '+';
        }

        /* Loading animation */
        .loading-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            padding: 50px;
        }

        .loading-dots div {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFD700 0%, #E6C200 100%);
            animation: loadingDots 1.4s infinite ease-in-out both;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }

        .loading-dots div:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots div:nth-child(2) { animation-delay: -0.16s; }
        .loading-dots div:nth-child(3) { animation-delay: 0s; }

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

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- FAQ Category Header -->
    <section class="faq-category">
        <div class="container">
            <h1>Sık Sorulan Sorular</h1>
            <p class="lead mb-0">Prime EMS Studios hakkında merak ettiğiniz tüm soruların cevapları</p>
        </div>
    </section>

    <!-- FAQ Content -->
    <section class="faq-section">
        <div class="container">
            <?php if (empty($faqs_by_category)): ?>
                <div class="text-center">
                    <div class="loading-dots">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <p class="text-muted">Henüz SSS içeriği bulunmuyor. Lütfen daha sonra tekrar kontrol edin.</p>
                </div>
            <?php else: ?>
                <?php foreach ($faqs_by_category as $category => $category_faqs): ?>
                    <div class="row justify-content-center mb-5">
                        <div class="col-lg-8">
                            <div class="category-badge"><?php echo htmlspecialchars($category); ?></div>

                            <div class="accordion mt-4" id="faqAccordion-<?php echo md5($category); ?>">
                                <?php foreach ($category_faqs as $index => $faq): ?>
                                    <div class="faq-card">
                                        <button class="faq-question"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#faq-<?php echo $faq['id']; ?>"
                                                aria-expanded="false"
                                                aria-controls="faq-<?php echo $faq['id']; ?>">
                                            <span><?php echo htmlspecialchars($faq['question']); ?></span>
                                            <i class="bi bi-plus-circle-fill faq-icon"></i>
                                        </button>
                                        <div id="faq-<?php echo $faq['id']; ?>"
                                             class="collapse faq-answer"
                                             data-bs-parent="#faqAccordion-<?php echo md5($category); ?>">
                                            <div><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Contact CTA -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="cta-section">
                        <h4 class="mb-3">Başka Sorunuz Mu Var?</h4>
                        <p class="mb-4">
                            Yukarıdaki SSS'lerde aradığınızı bulamadıysanız, uzman ekibimizle iletişime geçebilirsiniz.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="#contact" class="cta-btn">
                                <i class="bi bi-envelope"></i>İletişime Geçin
                            </a>
                            <a href="https://wa.me/905XXXXXXXXX?text=Merhaba!%20SSS%20sayfanızdan%20ulaşıyorum." target="_blank" class="cta-btn">
                                <i class="bi bi-whatsapp"></i>WhatsApp ile Sor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to Home Button -->
    <a href="index.php" class="back-to-home" title="Ana Sayfaya Dön" aria-label="Ana sayfaya dön">
        <i class="bi bi-house"></i>
    </a>

    <!-- Scripts -->
    <?php include 'includes/scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // FAQ Accordion Functionality with Bootstrap Events
        document.addEventListener('show.bs.collapse', function (e) {
            const button = e.target.previousElementSibling;
            if (button && button.classList.contains('faq-question')) {
                const card = button.closest('.faq-card');
                const icon = button.querySelector('.faq-icon');

                if (card) card.classList.add('faq-active');
                if (icon) icon.classList.add('rotate');
            }
        });

        document.addEventListener('hide.bs.collapse', function (e) {
            const button = e.target.previousElementSibling;
            if (button && button.classList.contains('faq-question')) {
                const card = button.closest('.faq-card');
                const icon = button.querySelector('.faq-icon');

                if (card) card.classList.remove('faq-active');
                if (icon) icon.classList.remove('rotate');
            }
        });

        // Smooth scroll for anchor links
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a[href^="#"]');
            if (anchor) {
                e.preventDefault();
                const targetId = anchor.getAttribute('href');
                const target = document.querySelector(targetId);
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 100, // Offset for navbar
                        behavior: 'smooth'
                    });
                }
            }
        });

        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            // Skip link for screen readers (if exists)
            if (e.key === 's' && e.altKey) {
                e.preventDefault();
                const skipLink = document.querySelector('.skip-link');
                if (skipLink) skipLink.focus();
            }

            // Enter/Space for FAQ buttons
            if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('faq-question')) {
                e.preventDefault();
                e.target.click();
            }
        });

        // Initialize FAQ state on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial ARIA states
            document.querySelectorAll('.faq-question').forEach(button => {
                button.setAttribute('aria-expanded', 'false');
            });

            // Add keyboard accessibility
            document.querySelectorAll('.faq-question').forEach(button => {
                button.setAttribute('tabindex', '0');
            });
        });
    </script>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>