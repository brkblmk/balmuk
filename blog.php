<?php
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/performance.php';

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Kategori filtresi
$category_slug = $_GET['category'] ?? '';

// Blog yazılarını çek
try {
    $where = "WHERE bp.is_published = 1";
    $params = [];
    
    if ($category_slug) {
        $where .= " AND bc.slug = ?";
        $params[] = $category_slug;
    }
    
    // Toplam sayı
    $count_sql = "SELECT COUNT(*) FROM blog_posts bp 
                  LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                  $where";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_posts = $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);
    
    // Blog yazıları - prepared statement ile LIMIT kullanımı
    $sql = "SELECT bp.*, bc.name as category_name, bc.color as category_color, bc.slug as category_slug
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            $where
            ORDER BY bp.published_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($sql);
    $query_params = array_merge($params, [$per_page, $offset]);
    $stmt->execute($query_params);
    $posts = $stmt->fetchAll();
    
    // Kategorileri çek
    $categories = $pdo->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
    
    // Popüler yazılar
    $popular_posts = $pdo->query("SELECT id, title, slug, featured_image, view_count FROM blog_posts WHERE is_published = 1 ORDER BY view_count DESC LIMIT 5")->fetchAll();
    
} catch (PDOException $e) {
    // Veritabanı hatasını logla
    error_log("Blog page database error: " . $e->getMessage() . " - " . date('Y-m-d H:i:s'));
    SecurityUtils::logSecurityEvent('BLOG_DB_ERROR', [
        'error' => $e->getMessage(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    // Tablo yoksa boş array döndür
    $posts = [];
    $categories = [];
    $popular_posts = [];
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags -->
    <title>Blog - EMS Teknolojisi ve Sağlık Haberleri | Prime EMS Studios İzmir</title>
    <meta name="title" content="Blog - EMS Teknolojisi ve Sağlık Haberleri | Prime EMS Studios İzmir">
    <meta name="description" content="Prime EMS Studios blogunda EMS cihazları, tıbbi ekipmanlar ve sağlık teknolojileri hakkında uzman yazılar. Fitness, beslenme ve wellness konularında güncel bilgiler. Bilimsel araştırmalar ve pratik ipuçları.">
    <meta name="keywords" content="EMS cihazları, tıbbi ekipmanlar, sağlık teknolojileri, elektrik kas stimülasyonu, fitness blog, beslenme, wellness, Prime EMS Studios, İzmir blog, EMS araştırmaları, fitness ipuçları, sağlıklı yaşam">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="language" content="tr-TR">
    <meta name="geo.region" content="TR-35">
    <meta name="geo.placename" content="İzmir, Türkiye">
    <meta name="geo.position" content="38.4192;27.1287">
    <meta name="ICBM" content="38.4192, 27.1287">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://primeemsstudios.com/blog.php">
    <meta property="og:title" content="Blog - EMS Teknolojisi ve Sağlık Haberleri | Prime EMS Studios İzmir">
    <meta property="og:description" content="EMS cihazları, tıbbi ekipmanlar ve sağlık teknolojileri hakkında uzman yazılar. Fitness ve wellness konularında güncel bilgiler.">
    <meta property="og:image" content="https://primeemsstudios.com/assets/images/logo.png">
    <meta property="og:site_name" content="Prime EMS Studios">
    <meta property="og:locale" content="tr_TR">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://primeemsstudios.com/blog.php">
    <meta property="twitter:title" content="Blog - EMS Teknolojisi ve Sağlık Haberleri | Prime EMS Studios İzmir">
    <meta property="twitter:description" content="EMS cihazları, tıbbi ekipmanlar ve sağlık teknolojileri hakkında uzman yazılar. Fitness ve wellness konularında güncel bilgiler.">
    <meta property="twitter:image" content="https://primeemsstudios.com/assets/images/logo.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://primeemsstudios.com/blog.php">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <!-- Structured Data - Article Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "Prime EMS Studios Blog - EMS Teknolojisi ve Sağlık Haberleri",
        "description": "EMS cihazları, tıbbi ekipmanlar ve sağlık teknolojileri hakkında uzman yazılar. Fitness, beslenme ve wellness konularında güncel bilgiler.",
        "author": "Prime EMS Studios",
        "publisher": {
            "@type": "Organization",
            "name": "Prime EMS Studios",
            "logo": "https://primeemsstudios.com/assets/images/logo.png"
        },
        "datePublished": "2024-01-01",
        "dateModified": "2024-12-31",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "https://primeemsstudios.com/blog.php"
        },
        "image": "https://primeemsstudios.com/assets/images/logo.png",
        "articleSection": "EMS Teknolojisi, Sağlık, Fitness"
    }
    </script>

    <!-- BlogPosting Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "EMS Teknolojisi ve Sağlık Haberleri",
        "description": "Prime EMS Studios blogunda EMS cihazları, tıbbi ekipmanlar ve sağlık teknolojileri hakkında uzman yazılar.",
        "image": "https://primeemsstudios.com/assets/images/logo.png",
        "author": {
            "@type": "Organization",
            "name": "Prime EMS Studios"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Prime EMS Studios",
            "logo": {
                "@type": "ImageObject",
                "url": "https://primeemsstudios.com/assets/images/logo.png"
            }
        },
        "datePublished": "2024-01-01",
        "dateModified": "2024-12-31",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "https://primeemsstudios.com/blog.php"
        },
        "articleSection": "EMS Teknolojisi, Sağlık, Fitness",
        "keywords": ["EMS", "sağlık teknolojileri", "fitness", "beslenme", "wellness"]
    }
    </script>

    <!-- BreadcrumbList Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Ana Sayfa",
                "item": "https://primeemsstudios.com/"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Blog",
                "item": "https://primeemsstudios.com/blog.php"
            }
        ]
    }
    </script>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/blog-fix.css">
    
    <style>
        /* Hero Section Styles */
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

        /* Blog Section */
        .prime-section {
            padding: 100px 0;
        }

        .prime-section-gray {
            background: #f8f9fa;
        }

        /* Blog Card Styles */
        .blog-card {
            background: linear-gradient(145deg, white 0%, #fafafa 100%);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            height: 100%;
            border: 1px solid rgba(255, 215, 0, 0.1);
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .blog-card::before {
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

        .blog-card:hover::before {
            opacity: 1;
        }

        .blog-card:hover {
            transform: translateY(-10px) scale(1.01);
            box-shadow: 0 30px 60px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
        }

        .blog-card .card-img-top {
            height: 250px;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .blog-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .category-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .reading-time {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .blog-content {
            padding: 25px 20px;
        }

        .blog-title {
            color: var(--prime-dark);
            font-weight: 700;
            margin-bottom: 15px;
            transition: color 0.3s ease;
            font-size: 1.25rem;
        }

        .blog-card:hover .blog-title {
            color: var(--prime-gold);
        }

        .blog-excerpt {
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .blog-meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }

        .blog-meta small {
            display: inline-block;
            margin-right: 15px;
        }

        .blog-actions {
            margin-top: 20px;
        }

        .blog-read-more {
            color: var(--prime-gold);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .blog-read-more:hover {
            color: var(--prime-dark);
            transform: translateX(5px);
        }

        /* Sidebar Widget Styles */
        .sidebar-widget {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .sidebar-widget h5 {
            margin-bottom: 20px;
            font-weight: 600;
            color: var(--prime-dark);
        }

        .category-list {
            list-style: none;
            padding: 0;
        }

        .category-list li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .category-list li:last-child {
            border-bottom: none;
        }

        .category-list a {
            color: #333;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            padding: 5px 0;
        }

        .category-list a:hover {
            color: var(--prime-gold);
            transform: translateX(5px);
        }

        .popular-post {
            display: flex;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .popular-post:hover {
            background: rgba(255, 215, 0, 0.05);
            transform: translateX(5px);
        }

        .popular-post img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }

        .popular-post-content h6 {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: var(--prime-dark);
        }

        .popular-post-content small {
            color: #999;
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .hero-content {
                padding: 2rem 1rem;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .hero-content h1 {
                font-size: 2.2rem;
                line-height: 1.2;
            }

            .hero-content p {
                font-size: 1.1rem;
                line-height: 1.4;
            }

            .hero-content .d-flex {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
            }

            .hero-cta-primary,
            .hero-cta-secondary {
                width: 100%;
                min-height: 48px;
                font-size: 1rem;
                padding: 12px 24px;
            }

            .blog-card {
                margin-bottom: 20px;
                border-radius: 15px;
            }

            .blog-card .card-img-top {
                height: 200px;
            }

            .sidebar-widget {
                padding: 20px;
                margin-bottom: 20px;
            }

            .popular-post {
                padding: 12px;
            }

            .popular-post img {
                width: 70px;
                height: 70px;
            }

            .popular-post-content h6 {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .hero-content {
                padding: 1.5rem 1rem;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .blog-card .card-img-top {
                height: 180px;
            }

            .blog-content {
                padding: 20px 15px;
            }

            .blog-title {
                font-size: 1.1rem;
            }

            .blog-meta small {
                font-size: 0.8rem;
            }

            .sidebar-widget {
                padding: 15px;
                margin-bottom: 15px;
            }

            .category-list li {
                padding: 10px 0;
            }

            .category-list a {
                font-size: 0.9rem;
            }

            .popular-post img {
                width: 60px;
                height: 60px;
            }

            .popular-post-content h6 {
                font-size: 0.8rem;
            }
        }

        /* Print Styles */
        @media print {
            .hero-section,
            .sidebar-widget,
            .navbar,
            .footer,
            .scroll-to-top,
            .chatbot-container {
                display: none !important;
            }

            .blog-card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .blog-content {
                padding: 15px;
            }
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .blog-card {
                border: 2px solid;
            }

            .hero-section {
                background: linear-gradient(135deg, #000 0%, #333 100%);
            }

            .sidebar-widget {
                border: 2px solid;
                background: #fff;
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .blog-card,
            .sidebar-widget,
            .popular-post,
            .category-list a {
                transition: none !important;
            }

            .hero-section {
                background-attachment: scroll;
            }
        }

        /* Futuristic enhancements */
        .hero-section {
            position: relative;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 64, 175, 0.85));
            color: #f8fafc;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.2), transparent 55%);
        }
        .hero-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('assets/images/hero-bg.svg') center/cover no-repeat;
            opacity: 0.15;
        }
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
        .article-card {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.92), rgba(226, 232, 240, 0.8));
            border-radius: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: blur(6px);
        }
        .article-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
        }
        .article-card .card-title a {
            color: #0f172a;
        }
        .article-card .card-title a:hover {
            color: #2563eb;
        }
        .blog-hero-meta {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
            color: rgba(226, 232, 240, 0.85);
        }
        .blog-hero-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        @media (max-width: 767.98px) {
            .hero-section {
                text-align: center;
            }
        }

    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section" role="banner" aria-label="Blog ana kahraman bölümü">
        <div class="hero-overlay" aria-hidden="true"></div>

        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1 class="display-3 fw-bold mb-4">
                    Prime EMS Blog<br>
                    <span style="color: var(--prime-gold);">Uzman İçerikler</span>
                </h1>
                <p class="lead mb-4">
                    EMS teknolojisi, fitness, beslenme ve wellness konularında uzman yazılar<br>
                    <strong>20 dakikalık bilimsel antrenman yöntemleri</strong>
                </p>
                  <div class="blog-hero-meta">
                      <span><i class="bi bi-lightning-charge-fill"></i> <?php echo number_format($total_posts); ?>+ içerik</span>
                      <span><i class="bi bi-people-fill"></i> Uzman eğitmen görüşleri</span>
                      <span><i class="bi bi-graph-up"></i> Güncel bilimsel araştırmalar</span>
                  </div>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#blog-posts" class="btn btn-prime btn-lg pulse-effect hero-cta-primary" role="button" aria-label="Blog yazılarını görüntüleyin">
                        <i class="bi bi-newspaper" aria-hidden="true"></i> Yazıları Oku
                    </a>
                    <a href="#categories" class="btn btn-prime-outline btn-lg hero-cta-secondary" role="button" aria-label="Blog kategorilerini keşfedin">
                        <i class="bi bi-tags-fill" aria-hidden="true"></i> Kategoriler
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Blog Content -->
    <section id="blog-posts" class="prime-section" aria-labelledby="blog-heading">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 id="blog-heading" class="display-5 fw-bold gold-accent">Blog Yazıları</h2>
                <p class="lead text-muted">EMS teknolojisi ve sağlıklı yaşam hakkında uzman içerikler</p>
            </div>

            <div class="row">
                <!-- Blog Posts -->
                <div class="col-lg-8">
                    <?php if ($category_slug && $posts): ?>
                    <div class="alert alert-info mb-4" data-aos="fade-up">
                        <i class="bi bi-filter me-2"></i>
                        Kategori: <strong><?php echo htmlspecialchars($posts[0]['category_name'] ?? ''); ?></strong>
                        <a href="blog.php" class="float-end text-decoration-none">
                            <i class="bi bi-x-circle me-1"></i>Tüm Yazılar
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <?php if ($posts): ?>
                            <?php foreach ($posts as $index => $post): ?>
                            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index % 4 + 1) * 100; ?>">
                                <div class="blog-card h-100 article-card">
                                    <div class="position-relative">
                                        <?php if ($post['featured_image']): ?>
                                              <?php echo PerformanceOptimizer::optimizeImage($post['featured_image'], htmlspecialchars($post['title']), 'card-img-top', 'lazy'); ?>
                                          <?php else: ?>
                                              <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center"
                                                   style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 250px;">
                                                  <i class="bi bi-newspaper text-white" style="font-size: 3rem;"></i>
                                              </div>
                                          <?php endif; ?>

                                        <?php if ($post['category_name']): ?>
                                        <a href="blog.php?category=<?php echo htmlspecialchars($post['category_slug']); ?>"
                                            class="category-badge"
                                            style="background-color: <?php echo $post['category_color']; ?>20; color: <?php echo $post['category_color']; ?>">
                                            <?php echo htmlspecialchars($post['category_name']); ?>
                                        </a>
                                        <?php endif; ?>

                                        <span class="reading-time">
                                            <i class="bi bi-clock me-1"></i><?php echo $post['reading_time']; ?> dk
                                        </span>
                                    </div>

                                    <div class="blog-content">
                                          <div class="blog-meta">
                                              <small class="blog-date">
                                                  <i class="bi bi-calendar me-1"></i>
                                                  <?php echo date('d.m.Y', strtotime($post['published_at'])); ?>
                                              </small>
                                              <small class="blog-read-time">
                                                  <i class="bi bi-eye me-1"></i><?php echo number_format($post['view_count']); ?> görüntülenme
                                              </small>
                                          </div>

                                          <h5 class="blog-title">
                                              <a href="blog-detail.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none">
                                                  <?php echo htmlspecialchars($post['title']); ?>
                                              </a>
                                          </h5>

                                          <p class="blog-excerpt">
                                              <?php echo htmlspecialchars(mb_substr($post['excerpt'], 0, 150)); ?>...
                                          </p>

                                          <div class="blog-actions">
                                              <a href="blog-detail.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="blog-read-more">
                                                  <i class="bi bi-arrow-right me-1"></i>Devamını Oku
                                              </a>
                                          </div>
                                      </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center" data-aos="fade-up">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Henüz blog yazısı bulunmuyor.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Sayfalama" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $category_slug ? '&category='.$category_slug : ''; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_slug ? '&category='.$category_slug : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $category_slug ? '&category='.$category_slug : ''; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4" data-aos="fade-left">
                    <!-- Search Widget -->
                    <div class="sidebar-widget" data-aos="fade-up" data-aos-delay="200">
                        <h5><i class="bi bi-search me-2" style="color: var(--prime-gold);"></i>Blog Ara</h5>
                        <form action="blog-search.php" method="GET">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Ara..." style="border-radius: 25px 0 0 25px; border: 2px solid rgba(255, 215, 0, 0.2);">
                                <button class="btn btn-prime" type="submit" style="border-radius: 0 25px 25px 0;">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    <div id="categories" class="sidebar-widget" data-aos="fade-up" data-aos-delay="400">
                        <h5><i class="bi bi-tags me-2" style="color: var(--prime-gold);"></i>Kategoriler</h5>
                        <ul class="category-list">
                            <li>
                                <a href="blog.php">
                                    <span><i class="bi bi-grid me-2"></i>Tüm Yazılar</span>
                                    <span class="badge bg-warning text-dark"><?php echo $total_posts ?? 0; ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="blog.php?category=<?php echo htmlspecialchars($cat['slug']); ?>">
                                    <span>
                                        <i class="bi <?php echo $cat['icon']; ?> me-2" style="color: <?php echo $cat['color']; ?>"></i>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </span>
                                    <i class="bi bi-chevron-right opacity-50"></i>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Popular Posts Widget -->
                    <div class="sidebar-widget" data-aos="fade-up" data-aos-delay="600">
                        <h5><i class="bi bi-star me-2" style="color: var(--prime-gold);"></i>Popüler Yazılar</h5>
                        <?php foreach ($popular_posts as $popular): ?>
                        <div class="popular-post">
                            <?php if ($popular['featured_image']): ?>
                                <?php echo PerformanceOptimizer::optimizeImage($popular['featured_image'], htmlspecialchars($popular['title']), '', 'lazy'); ?>
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-newspaper text-white"></i>
                                </div>
                            <?php endif; ?>

                            <div class="popular-post-content">
                                <h6>
                                    <a href="blog-detail.php?slug=<?php echo htmlspecialchars($popular['slug']); ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($popular['title']); ?>
                                    </a>
                                </h6>
                                <small>
                                    <i class="bi bi-eye me-1"></i><?php echo number_format($popular['view_count']); ?> görüntülenme
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($popular_posts)): ?>
                        <p class="text-muted"><i class="bi bi-info-circle me-1"></i>Henüz popüler yazı bulunmuyor.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Newsletter Widget -->
                    <div class="sidebar-widget" data-aos="fade-up" data-aos-delay="800" style="background: var(--prime-gradient); color: white;">
                        <h5 class="text-white"><i class="bi bi-envelope me-2"></i>Bültenimize Katılın</h5>
                        <p class="text-white-50">En yeni blog yazılarından ve EMS teknolojilerinden haberdar olun!</p>
                        <form>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="E-posta adresiniz" style="background: rgba(255,255,255,0.9); border: none;">
                            </div>
                            <button type="submit" class="btn btn-light w-100" style="font-weight: 600;">
                                <i class="bi bi-send me-2"></i>Abone Ol
                            </button>
                        </form>
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
            once: true,
            offset: 100
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

        // Newsletter form handling
        document.querySelector('.sidebar-widget form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;

            if (email) {
                // Show success message
                const btn = this.querySelector('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Abone Oldunuz!';
                btn.classList.add('btn-success');
                btn.disabled = true;

                // Reset after 3 seconds
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.disabled = false;
                    this.reset();
                }, 3000);
            }
        });

        // Lazy loading for blog images
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
                                observer.unobserve(img);
                            }
                        }
                    });
                }, {
                    rootMargin: '50px 0px',
                    threshold: 0.1
                });

                lazyImages.forEach(img => imageObserver.observe(img));
            }
        }

        // Initialize features when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initLazyLoading();
        });
    </script>
</body>
</html>