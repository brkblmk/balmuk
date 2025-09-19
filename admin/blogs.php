<?php
require_once '../config/database.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtreleme
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Sorgu hazırla
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(bp.title LIKE ? OR bp.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "bp.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter !== '') {
    if ($status_filter === 'published') {
        $where_conditions[] = "bp.is_published = 1";
    } elseif ($status_filter === 'draft') {
        $where_conditions[] = "bp.is_published = 0";
    } elseif ($status_filter === 'featured') {
        $where_conditions[] = "bp.is_featured = 1";
    }
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Toplam kayıt sayısı
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts bp $where_clause");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Blog yazılarını çek
$sql = "SELECT bp.*, bc.name as category_name, bc.color as category_color,
        (SELECT COUNT(*) FROM blog_comments WHERE post_id = bp.id) as comment_count
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        $where_clause
        ORDER BY bp.created_at DESC
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Kategorileri çek
$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY sort_order")->fetchAll();

// İstatistikler
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn(),
    'published' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 1")->fetchColumn(),
    'draft' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 0")->fetchColumn(),
    'featured' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_featured = 1")->fetchColumn(),
    'views' => $pdo->query("SELECT SUM(view_count) FROM blog_posts")->fetchColumn() ?? 0,
    'comments' => $pdo->query("SELECT COUNT(*) FROM blog_comments")->fetchColumn()
];

// AJAX işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'delete':
            $id = $_POST['id'] ?? 0;
            try {
                // İlişkili kayıtları sil
                $pdo->prepare("DELETE FROM blog_post_tags WHERE post_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM blog_comments WHERE post_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
                
                logActivity('delete', 'blog_posts', $id);
                echo json_encode(['success' => true, 'message' => 'Blog yazısı silindi']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
            }
            exit;
            
        case 'toggle_publish':
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("UPDATE blog_posts SET is_published = NOT is_published, published_at = IF(is_published = 1, NOW(), NULL) WHERE id = ?");
                $stmt->execute([$id]);
                
                logActivity('update', 'blog_posts', $id);
                echo json_encode(['success' => true, 'message' => 'Durum güncellendi']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
            }
            exit;
            
        case 'toggle_featured':
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("UPDATE blog_posts SET is_featured = NOT is_featured WHERE id = ?");
                $stmt->execute([$id]);
                
                logActivity('update', 'blog_posts', $id);
                echo json_encode(['success' => true, 'message' => 'Öne çıkan durumu güncellendi']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
            }
            exit;
    }
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
    
    <style>
        .stats-card {
            border-radius: 15px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .card-body {
            padding: 1.5rem;
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .blog-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .category-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Content -->
            <div class="container-fluid p-4">
                <!-- Başlık ve Yeni Ekle -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-newspaper me-2"></i>Blog Yönetimi
                    </h1>
                    <a href="blog-write.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Blog Yazısı
                    </a>
                </div>
                
                <!-- İstatistikler -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="stats-card card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-white bg-opacity-25 me-3">
                                        <i class="bi bi-file-text"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Toplam</h6>
                                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="stats-card card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-white bg-opacity-25 me-3">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Yayında</h6>
                                        <h3 class="mb-0"><?php echo $stats['published']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="stats-card card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-white bg-opacity-25 me-3">
                                        <i class="bi bi-pencil-square"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Taslak</h6>
                                        <h3 class="mb-0"><?php echo $stats['draft']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="stats-card card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-white bg-opacity-25 me-3">
                                        <i class="bi bi-star"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Öne Çıkan</h6>
                                        <h3 class="mb-0"><?php echo $stats['featured']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="stats-card card bg-purple text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-white bg-opacity-25 me-3">
                                        <i class="bi bi-eye"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Görüntülenme</h6>
                                        <h3 class="mb-0"><?php echo number_format($stats['views']); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="stats-card card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-white bg-opacity-25 me-3">
                                        <i class="bi bi-chat-dots"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Yorumlar</h6>
                                        <h3 class="mb-0"><?php echo $stats['comments']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtreler -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Blog ara..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">Tüm Kategoriler</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Yayında</option>
                                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                    <option value="featured" <?php echo $status_filter === 'featured' ? 'selected' : ''; ?>>Öne Çıkan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Filtrele
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Blog Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="60">Görsel</th>
                                        <th>Başlık</th>
                                        <th>Kategori</th>
                                        <th>Durum</th>
                                        <th>İstatistikler</th>
                                        <th>Tarih</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($posts): ?>
                                        <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td>
                                                <?php if ($post['featured_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="blog-thumbnail" alt="">
                                                <?php else: ?>
                                                    <div class="blog-thumbnail bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                    <?php if ($post['is_featured']): ?>
                                                        <i class="bi bi-star-fill text-warning ms-1" title="Öne Çıkan"></i>
                                                    <?php endif; ?>
                                                    <?php if ($post['ai_generated']): ?>
                                                        <i class="bi bi-robot text-primary ms-1" title="AI ile oluşturuldu"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars(mb_substr($post['excerpt'] ?? '', 0, 100)); ?>...
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($post['category_name']): ?>
                                                    <span class="category-badge" style="background-color: <?php echo $post['category_color']; ?>20; color: <?php echo $post['category_color']; ?>">
                                                        <?php echo htmlspecialchars($post['category_name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($post['is_published']): ?>
                                                    <span class="badge bg-success status-badge">Yayında</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning status-badge">Taslak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="bi bi-eye me-1"></i><?php echo number_format($post['view_count']); ?> görüntülenme<br>
                                                    <i class="bi bi-chat me-1"></i><?php echo $post['comment_count']; ?> yorum
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d.m.Y', strtotime($post['created_at'])); ?><br>
                                                    <?php echo date('H:i', strtotime($post['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="blog-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button onclick="togglePublish(<?php echo $post['id']; ?>)" class="btn btn-sm btn-outline-<?php echo $post['is_published'] ? 'warning' : 'success'; ?>" title="<?php echo $post['is_published'] ? 'Taslağa Al' : 'Yayınla'; ?>">
                                                    <i class="bi bi-<?php echo $post['is_published'] ? 'pause' : 'play'; ?>"></i>
                                                </button>
                                                <button onclick="toggleFeatured(<?php echo $post['id']; ?>)" class="btn btn-sm btn-outline-<?php echo $post['is_featured'] ? 'warning' : 'info'; ?>" title="<?php echo $post['is_featured'] ? 'Öne Çıkandan Kaldır' : 'Öne Çıkar'; ?>">
                                                    <i class="bi bi-star<?php echo $post['is_featured'] ? '-fill' : ''; ?>"></i>
                                                </button>
                                                <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-sm btn-outline-danger" title="Sil">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-newspaper display-4 text-muted"></i>
                                                <p class="mt-2">Henüz blog yazısı bulunmuyor</p>
                                                <a href="blog-write.php" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle me-2"></i>İlk Blog Yazınızı Ekleyin
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Sayfalama -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Sayfalama" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Blog sil
        function deletePost(id) {
            if (confirm('Bu blog yazısını silmek istediğinizden emin misiniz?')) {
                fetch('blogs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification(data.message || 'Bir hata oluştu', 'error');
                    }
                });
            }
        }
        
        // Yayın durumu değiştir
        function togglePublish(id) {
            fetch('blogs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_publish&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Bir hata oluştu', 'error');
                }
            });
        }
        
        // Öne çıkan durumu değiştir
        function toggleFeatured(id) {
            fetch('blogs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_featured&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Bir hata oluştu', 'error');
                }
            });
        }
    </script>
</body>
</html>