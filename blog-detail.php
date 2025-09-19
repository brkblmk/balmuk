<?php
require_once 'config/database.php';

// Türkçe tarih formatı için locale ayarla
setlocale(LC_TIME, 'tr_TR');

// Slug parametresini al
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: blog.php');
    exit;
}

// Blog yazısını çek ve görüntülenme sayısını artır
try {
    // Blog yazısını çek
    $stmt = $pdo->prepare("
        SELECT bp.*, bc.name as category_name, bc.color as category_color, bc.slug as category_slug,
               a.full_name as author_name
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        LEFT JOIN admins a ON bp.author_id = a.id
        WHERE bp.slug = ? AND bp.is_published = 1
    ");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
    
    // Görüntülenme sayısını artır
    $pdo->prepare("UPDATE blog_posts SET view_count = view_count + 1 WHERE id = ?")->execute([$post['id']]);
    
    // Etiketleri çek (blog_posts tablosundan)
    $tags = [];
    if (!empty($post['tags'])) {
        $tag_strings = array_map('trim', explode(',', $post['tags']));
        foreach ($tag_strings as $tag_name) {
            if (!empty($tag_name)) {
                $tags[] = ['name' => $tag_name, 'slug' => strtolower(str_replace(' ', '-', $tag_name))];
            }
        }
    }
    
    // İlgili yazıları çek
    $related_stmt = $pdo->prepare("
        SELECT bp.*, bc.name as category_name, bc.color as category_color
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        WHERE bp.category_id = ? AND bp.id != ? AND bp.is_published = 1
        ORDER BY bp.published_at DESC
        LIMIT 3
    ");
    $related_stmt->execute([$post['category_id'], $post['id']]);
    $related_posts = $related_stmt->fetchAll();
    
    // Yorumları çek
    $comments_stmt = $pdo->prepare("
        SELECT * FROM blog_comments
        WHERE post_id = ? AND is_approved = 1 AND parent_id IS NULL
        ORDER BY created_at DESC
    ");
    $comments_stmt->execute([$post['id']]);
    $comments = $comments_stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: blog.php');
    exit;
}

// Yorum gönderme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $comment = $_POST['comment'] ?? '';
    
    if ($name && $comment) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO blog_comments (post_id, name, email, comment, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$post['id'], $name, $email, $comment, $_SERVER['REMOTE_ADDR']]);
            
            $success_message = "Yorumunuz onay bekliyor. Teşekkür ederiz!";
        } catch (PDOException $e) {
            $error_message = "Yorum gönderilemedi. Lütfen tekrar deneyin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['meta_title'] ?: $post['title']); ?> | Prime EMS Studios</title>
    <meta name="description" content="<?php echo htmlspecialchars($post['meta_description'] ?: mb_substr($post['excerpt'], 0, 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($post['meta_keywords']); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($post['featured_image'] ?: '/assets/images/og-default.jpg'); ?>">
    <meta property="og:url" content="https://primeemsstudios.com/blog-detail.php?slug=<?php echo $slug; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($post['featured_image'] ?: '/assets/images/og-default.jpg'); ?>">
    
    <!-- Schema.org Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?php echo htmlspecialchars($post['title']); ?>",
        "description": "<?php echo htmlspecialchars($post['excerpt']); ?>",
        "image": "<?php echo htmlspecialchars($post['featured_image'] ?: '/assets/images/og-default.jpg'); ?>",
        "datePublished": "<?php echo $post['published_at']; ?>",
        "dateModified": "<?php echo $post['updated_at']; ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo htmlspecialchars($post['author_name'] ?: 'Prime EMS Studios'); ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Prime EMS Studios",
            "logo": {
                "@type": "ImageObject",
                "url": "https://primeemsstudios.com/assets/images/logo.png"
            }
        },
        "articleBody": "<?php echo htmlspecialchars(strip_tags($post['content'])); ?>"
    }
    </script>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    
    <style>
        .blog-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 120px 0 60px;
            color: white;
        }
        
        .blog-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        
        .blog-content h2 {
            margin-top: 30px;
            margin-bottom: 20px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .blog-content h3 {
            margin-top: 25px;
            margin-bottom: 15px;
            font-weight: 500;
            color: #34495e;
        }
        
        .blog-content ul, .blog-content ol {
            margin: 20px 0;
            padding-left: 30px;
        }
        
        .blog-content li {
            margin-bottom: 10px;
        }
        
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .blog-content blockquote {
            border-left: 4px solid var(--primary-color);
            padding-left: 20px;
            margin: 20px 0;
            font-style: italic;
            color: #666;
        }
        
        .blog-content .cta-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: center;
        }
        
        .blog-content .cta-box h3 {
            color: white;
        }
        
        .author-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 40px 0;
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
            margin: 30px 0;
        }
        
        .share-buttons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .share-buttons a:hover {
            transform: scale(1.1);
        }
        
        .tag-link {
            display: inline-block;
            padding: 5px 15px;
            background: #e9ecef;
            border-radius: 20px;
            color: #495057;
            text-decoration: none;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .tag-link:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .related-post-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .related-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .comment-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Blog Header -->
    <section class="blog-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/" class="text-white-50">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="blog.php" class="text-white-50">Blog</a></li>
                    <?php if ($post['category_name'] && $post['category_slug']): ?>
                    <li class="breadcrumb-item"><a href="blog.php?category=<?php echo htmlspecialchars($post['category_slug']); ?>" class="text-white-50"><?php echo htmlspecialchars($post['category_name']); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($post['title']); ?></li>
                </ol>
            </nav>
            
            <h1 class="display-4 fw-bold mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
            
            <div class="d-flex align-items-center flex-wrap gap-3">
                <?php if ($post['category_name']): ?>
                <span class="badge" style="background-color: <?php echo $post['category_color']; ?>; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($post['category_name']); ?>
                </span>
                <?php endif; ?>
                
                <span>
                    <i class="bi bi-person me-1"></i>
                    <?php echo htmlspecialchars($post['author_name'] ?: 'Prime EMS Studios'); ?>
                </span>
                
                <span>
                    <i class="bi bi-calendar me-1"></i>
                    <?php echo date('d F Y', strtotime($post['published_at'])); ?>
                </span>
                
                <span>
                    <i class="bi bi-clock me-1"></i>
                    <?php echo $post['reading_time']; ?> dakika okuma
                </span>
                
                <span>
                    <i class="bi bi-eye me-1"></i>
                    <?php echo number_format($post['view_count']); ?> görüntülenme
                </span>
            </div>
        </div>
    </section>
    
    <!-- Blog Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <?php if ($post['featured_image']): ?>
                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                         class="img-fluid rounded mb-4" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="blog-content">
                        <?php echo $post['content']; ?>
                    </div>
                    
                    <!-- Tags -->
                    <?php if ($tags): ?>
                    <div class="mt-4">
                        <h5>Etiketler:</h5>
                        <?php foreach ($tags as $tag): ?>
                        <a href="blog.php?tag=<?php echo htmlspecialchars($tag['slug']); ?>" class="tag-link">
                            <?php echo htmlspecialchars($tag['name']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share Buttons -->
                    <div class="share-buttons mt-4">
                        <h5 class="me-3">Paylaş:</h5>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("https://primeemsstudios.com/blog-detail.php?slug=$slug"); ?>" 
                           target="_blank" style="background: #3b5998;">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("https://primeemsstudios.com/blog-detail.php?slug=$slug"); ?>&text=<?php echo urlencode($post['title']); ?>" 
                           target="_blank" style="background: #1da1f2;">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode("https://primeemsstudios.com/blog-detail.php?slug=$slug"); ?>" 
                           target="_blank" style="background: #0077b5;">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . " https://primeemsstudios.com/blog-detail.php?slug=$slug"); ?>" 
                           target="_blank" style="background: #25d366;">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                    
                    <!-- Author Box -->
                    <div class="author-box">
                        <h5>Yazar Hakkında</h5>
                        <div class="d-flex align-items-center mt-3">
                            <div class="me-3">
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person text-white" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($post['author_name'] ?: 'Prime EMS Studios'); ?></h6>
                                <p class="mb-0 text-muted">Prime EMS Studios'da içerik editörü ve fitness uzmanı. EMS teknolojisi ve sağlıklı yaşam konularında uzman yazılar hazırlıyor.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comments Section -->
                    <div class="mt-5">
                        <h4 class="mb-4">Yorumlar (<?php echo count($comments); ?>)</h4>
                        
                        <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <!-- Comment Form -->
                        <div class="comment-box mb-4">
                            <h5>Yorum Yap</h5>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="name" class="form-control" placeholder="Adınız *" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="email" name="email" class="form-control" placeholder="E-posta (isteğe bağlı)">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <textarea name="comment" class="form-control" rows="4" placeholder="Yorumunuz *" required></textarea>
                                </div>
                                <button type="submit" name="submit_comment" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Yorum Gönder
                                </button>
                            </form>
                        </div>
                        
                        <!-- Comments List -->
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-box">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><?php echo htmlspecialchars($comment['name']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                </small>
                            </div>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($comments)): ?>
                        <p class="text-muted">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- CTA Widget -->
                    <div class="card border-0 bg-primary text-white mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title">Ücretsiz Deneme Seansı</h5>
                            <p class="card-text">EMS teknolojisini deneyimlemek için hemen randevu alın!</p>
                            <a href="/reservation.php" class="btn btn-light">
                                <i class="bi bi-calendar-check me-2"></i>Randevu Al
                            </a>
                        </div>
                    </div>
                    
                    <!-- Related Posts -->
                    <?php if ($related_posts): ?>
                    <div class="card border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title">İlgili Yazılar</h5>
                            <?php foreach ($related_posts as $related): ?>
                            <div class="d-flex mb-3">
                                <?php if ($related['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px;" 
                                         alt="">
                                <?php else: ?>
                                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-newspaper text-white"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="ms-3">
                                    <h6 class="mb-1">
                                        <a href="blog-detail.php?slug=<?php echo htmlspecialchars($related['slug']); ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($related['title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i><?php echo $related['reading_time']; ?> dk okuma
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Bültenimize Katılın</h5>
                            <p class="card-text">En yeni blog yazılarından haberdar olun!</p>
                            <form>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="E-posta adresiniz">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Abone Ol</button>
                            </form>
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
</body>
</html>