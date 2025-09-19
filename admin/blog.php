<?php
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/performance.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Security check for admin session
SecurityUtils::secureSession();

// Check if session is expired (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    SecurityUtils::logSecurityEvent('ADMIN_SESSION_EXPIRED', ['admin_id' => $_SESSION['admin_id'] ?? null]);
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}
$_SESSION['last_activity'] = time();

$message = '';
$message_type = '';

// AI Blog Generation Function
function generateBlogWithAI($topic, $category_id, $admin_id) {
    global $pdo;
    
    // Simulated AI response - in real implementation, you would call an AI API
    $ai_prompts = [
        'EMS Technology' => [
            'title' => 'EMS Teknolojisinin Geleceği: Yenilikçi Antrenman Yöntemleri',
            'content' => '<p>Elektrik kas stimülasyonu (EMS) teknolojisi, fitness dünyasında devrim yaratan bir yeniliktir. Bu teknoloji, geleneksel egzersiz yöntemlerini destekleyerek daha etkili ve kısa süreli antrenmanlar yapma imkanı sunar.</p>

<h3>EMS Teknolojisinin Avantajları</h3>
<p>EMS antrenmanları, kas liflerine doğrudan elektriksel uyarımlar göndererek kasların daha yoğun çalışmasını sağlar. Bu sayede:</p>
<ul>
<li>Daha kısa sürede maksimum kas aktivasyonu</li>
<li>Geleneksel antrenmana göre %30 daha fazla kas lifi kullanımı</li>
<li>Yaralanma riski minimal</li>
<li>Zaman tasarrufu</li>
</ul>

<h3>Bilimsel Araştırmalar</h3>
<p>Yapılan bilimsel çalışmalar, EMS teknolojisinin kas gelişimi, yağ yakımı ve dayanıklılık artışında etkili olduğunu göstermektedir.</p>',
            'excerpt' => 'EMS teknolojisinin fitness dünyasında yarattığı devrim ve gelecekteki potansiyeli hakkında kapsamlı bir inceleme.',
            'tags' => ['EMS', 'teknoloji', 'fitness', 'antrenman'],
            'reading_time' => 8
        ],
        'Fitness' => [
            'title' => 'Etkili Fitness Rutinleri: Modern Yaşamda Spor',
            'content' => '<p>Modern yaşamın hızlı temposunda spor yapmak bazen zor olabilir. Ancak doğru rutinler ve yaklaşımlarla sağlıklı bir yaşam sürdürmek mümkündür.</p>

<h3>Zaman Yönetimi</h3>
<p>Fitness rutinleri oluştururken en önemli faktörlerden biri zamandır. Kısa ama etkili antrenmanlar tercih edilmelidir:</p>
<ul>
<li>HIIT (Yüksek Yoğunluklu Interval Antrenman)</li>
<li>Fonksiyonel antrenmanlar</li>
<li>EMS destekli egzersizler</li>
</ul>

<h3>Beslenme ve Antrenman Uyumu</h3>
<p>Doğru beslenme, antrenmanların verimliliğini artıran önemli bir faktördür.</p>',
            'excerpt' => 'Modern yaşamda etkili fitness rutinleri oluşturma ve sürdürme stratejileri.',
            'tags' => ['fitness', 'antrenman', 'beslenme', 'sağlık'],
            'reading_time' => 6
        ],
        'Nutrition' => [
            'title' => 'Sporcu Beslenmesi: Performansı Artıran Besinler',
            'content' => '<p>Sporcu beslenmesi, performansı doğrudan etkileyen kritik bir konudur. Doğru beslenme stratejileri, antrenman öncesi, sırası ve sonrasında farklı yaklaşımlar gerektirir.</p>

<h3>Antrenman Öncesi Beslenme</h3>
<p>Antrenman öncesi beslenme, enerji depolarını doldurma ve kas performansını optimize etme odaklı olmalıdır:</p>
<ul>
<li>Karmaşık karbonhidratlar</li>
<li>Az yağlı protein kaynakları</li>
<li>Bol su tüketimi</li>
</ul>

<h3>Antrenman Sonrası Beslenme</h3>
<p>Antrenman sonrası beslenme, kas onarımı ve glycogen depolarının yenilenmesi için kritiktir.</p>',
            'excerpt' => 'Sporcu beslenmesi prensipleri ve performansı artıran beslenme stratejileri.',
            'tags' => ['beslenme', 'sporcu', 'performans', 'protein'],
            'reading_time' => 7
        ]
    ];
    
    // Get category name for AI context
    $category_stmt = $pdo->prepare("SELECT name FROM blog_categories WHERE id = ?");
    $category_stmt->execute([$category_id]);
    $category_name = $category_stmt->fetchColumn();
    
    // Select appropriate AI content based on topic/category
    $ai_content = null;
    foreach ($ai_prompts as $key => $content) {
        if (stripos($topic, $key) !== false || stripos($category_name, $key) !== false) {
            $ai_content = $content;
            break;
        }
    }
    
    // Default content if no match
    if (!$ai_content) {
        $ai_content = [
            'title' => "AI Destekli İçerik: " . ucfirst($topic),
            'content' => "<p>Bu içerik AI teknolojisi ile oluşturulmuştur. {$topic} konusunda kapsamlı bilgiler içermektedir.</p><h3>Giriş</h3><p>Bu makale, {$topic} konusunda derinlemesine bilgi sunmayı amaçlamaktadır.</p>",
            'excerpt' => "{$topic} konusunda AI destekli kapsamlı içerik.",
            'tags' => [strtolower($topic), 'ai', 'teknoloji'],
            'reading_time' => 5
        ];
    }
    
    // Create slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $ai_content['title'])));
    
    // Insert blog post
    try {
        $stmt = $pdo->prepare("
            INSERT INTO blog_posts 
            (title, slug, content, excerpt, category_id, author_id, featured_image, tags, reading_time, is_published, is_ai_generated, published_at, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW())
        ");
        
        $stmt->execute([
            $ai_content['title'],
            $slug,
            $ai_content['content'],
            $ai_content['excerpt'],
            $category_id,
            $admin_id,
            '/assets/images/blog/default-ai.jpg', // Default AI generated image
            json_encode($ai_content['tags']),
            $ai_content['reading_time']
        ]);
        
        return [
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'title' => $ai_content['title']
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
        SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP(), 'action' => 'admin_blog']);
        $message = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
        $message_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'generate_ai_blog':
                $topic = SecurityUtils::sanitizeInput($_POST['topic'] ?? '', 'string');
                $category_id = (int)($_POST['category_id'] ?? 0);
                
                if (empty($topic)) {
                    $message = 'Konu belirtilmelidir.';
                    $message_type = 'warning';
                } elseif (!$category_id) {
                    $message = 'Kategori seçilmelidir.';
                    $message_type = 'warning';
                } else {
                    $result = generateBlogWithAI($topic, $category_id, $_SESSION['admin_id']);
                    
                    if ($result['success']) {
                        SecurityUtils::logSecurityEvent('AI_BLOG_GENERATED', [
                            'blog_id' => $result['id'],
                            'title' => $result['title'],
                            'topic' => $topic,
                            'generated_by' => $_SESSION['admin_id']
                        ]);
                        $message = 'AI blog yazısı başarıyla oluşturuldu: ' . $result['title'];
                        $message_type = 'success';
                    } else {
                        $message = 'AI blog oluşturulurken hata: ' . $result['error'];
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                $new_status = (int)$_POST['status'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE blog_posts SET is_published = ? WHERE id = ?");
                    
                    if ($stmt->execute([$new_status, $id])) {
                        SecurityUtils::logSecurityEvent('BLOG_STATUS_CHANGED', [
                            'blog_id' => $id,
                            'new_status' => $new_status,
                            'changed_by' => $_SESSION['admin_id']
                        ]);
                        $message = 'Blog durumu güncellendi.';
                        $message_type = 'success';
                    }
                } catch (PDOException $e) {
                    $message = 'Durum güncellenirken hata oluştu.';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                    
                    if ($stmt->execute([$id])) {
                        SecurityUtils::logSecurityEvent('BLOG_DELETED', [
                            'blog_id' => $id,
                            'deleted_by' => $_SESSION['admin_id']
                        ]);
                        $message = 'Blog yazısı silindi.';
                        $message_type = 'success';
                    }
                } catch (PDOException $e) {
                    $message = 'Blog silinirken hata oluştu.';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Search and filters
$search = SecurityUtils::sanitizeInput($_GET['search'] ?? '', 'string');
$category_filter = (int)($_GET['category'] ?? 0);
$status_filter = $_GET['status'] ?? '';

// Build query
$where_conditions = ['1=1'];
$search_params = [];

if ($search) {
    $where_conditions[] = "(bp.title LIKE ? OR bp.content LIKE ? OR bp.excerpt LIKE ?)";
    $search_params = array_merge($search_params, ["%$search%", "%$search%", "%$search%"]);
}

if ($category_filter) {
    $where_conditions[] = "bp.category_id = ?";
    $search_params[] = $category_filter;
}

if ($status_filter !== '') {
    $where_conditions[] = "bp.is_published = ?";
    $search_params[] = (int)$status_filter;
}

$where_query = " WHERE " . implode(' AND ', $where_conditions);

// Get total count
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts bp" . $where_query);
    $count_stmt->execute($search_params);
    $total_posts = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    $total_posts = 0;
}

$total_pages = ceil($total_posts / $limit);

// Get blog posts
try {
    $posts_query = "
        SELECT bp.*, bc.name as category_name, a.username as author_name
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        LEFT JOIN admins a ON bp.author_id = a.id
        " . $where_query . "
        ORDER BY bp.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($posts_query);
    $params = array_merge($search_params, [$limit, $offset]);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}

// Get categories for filters and AI generation
try {
    $categories = $pdo->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get statistics
try {
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn(),
        'published' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 1")->fetchColumn(),
        'draft' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 0")->fetchColumn(),
        'ai_generated' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_ai_generated = 1")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats = ['total' => 0, 'published' => 0, 'draft' => 0, 'ai_generated' => 0];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yönetimi - Prime EMS Admin</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Page Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Blog Yönetimi</h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                            <i class="bi bi-robot"></i> AI Blog Oluştur
                        </button>
                        <button class="btn btn-primary">
                            <i class="bi bi-plus"></i> Yeni Blog
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Toplam Blog</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Yayında</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['published']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Taslak</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['draft']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">AI Üretimi</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['ai_generated']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Blog başlığı veya içerik ara..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">Tüm Kategoriler</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter === (int)$category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Yayında</option>
                                    <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Taslak</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary me-2">Ara</button>
                                <a href="blog.php" class="btn btn-outline-secondary">Temizle</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Blog Posts Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Başlık</th>
                                        <th>Kategori</th>
                                        <th>Yazar</th>
                                        <th>Durum</th>
                                        <th>AI</th>
                                        <th>Görüntülenme</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo $post['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> <?php echo $post['reading_time']; ?> dk okuma
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($post['category_name']): ?>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['author_name'] ?? 'Bilinmeyen'); ?></td>
                                        <td>
                                            <?php if ($post['is_published']): ?>
                                                <span class="badge bg-success">Yayında</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Taslak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($post['is_ai_generated']): ?>
                                                <i class="bi bi-robot text-info" title="AI ile oluşturuldu"></i>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="bi bi-eye"></i> <?php echo number_format($post['view_count']); ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d.m.Y', strtotime($post['created_at'])); ?><br>
                                                <?php echo date('H:i', strtotime($post['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        onclick="viewPost('<?php echo htmlspecialchars($post['slug']); ?>')">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-<?php echo $post['is_published'] ? 'warning' : 'success'; ?>"
                                                        onclick="toggleStatus(<?php echo $post['id']; ?>, <?php echo $post['is_published'] ? '0' : '1'; ?>)">
                                                    <i class="bi bi-<?php echo $post['is_published'] ? 'pause' : 'play'; ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="deletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['title']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($posts)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-journal-text display-1 text-muted"></i>
                            <p class="mt-3 text-muted">Blog yazısı bulunamadı.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php
                        $query_params = [
                            'search' => $search,
                            'category' => $category_filter,
                            'status' => $status_filter
                        ];
                        $query_string = http_build_query(array_filter($query_params));
                        ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $query_string ? '&' . $query_string : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- AI Generate Blog Modal -->
    <div class="modal fade" id="aiGenerateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="generate_ai_blog">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-robot me-2"></i>AI ile Blog Oluştur
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="topic" class="form-label">Blog Konusu</label>
                            <input type="text" class="form-control" id="topic" name="topic" 
                                   placeholder="Örn: EMS teknolojisi, fitness rutinleri, sporcu beslenmesi..." required>
                            <div class="form-text">AI, bu konuya göre kapsamlı bir blog yazısı oluşturacak.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>AI Özellikler:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Konu-uyumlu başlık oluşturma</li>
                                <li>SEO dostu içerik</li>
                                <li>Otomatik etiketleme</li>
                                <li>Okuma süre hesaplaması</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-robot me-2"></i>AI ile Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toggle Status Form (Hidden) -->
    <form id="toggleStatusForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="id" id="toggle_id">
        <input type="hidden" name="status" id="toggle_status">
    </form>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/mobile-nav.js"></script>
    
    <script>
        function viewPost(slug) {
            window.open('../blog-post.php?slug=' + slug, '_blank');
        }
        
        function toggleStatus(id, newStatus) {
            if (confirm('Blog yazısının durumunu değiştirmek istediğinizden emin misiniz?')) {
                document.getElementById('toggle_id').value = id;
                document.getElementById('toggle_status').value = newStatus;
                document.getElementById('toggleStatusForm').submit();
            }
        }
        
        function deletePost(id, title) {
            if (confirm('Bu blog yazısını silmek istediğinizden emin misiniz?\n\n"' + title + '"')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>