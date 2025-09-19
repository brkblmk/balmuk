<?php
require_once '../config/database.php';
require_once '../config/security.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
        SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP(), 'action' => 'admin_blog_categories']);
        $message = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
        $message_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add_category':
                $name = SecurityUtils::sanitizeInput($_POST['name'] ?? '', 'string');
                $slug = SecurityUtils::sanitizeInput($_POST['slug'] ?? '', 'string');
                $description = SecurityUtils::sanitizeInput($_POST['description'] ?? '', 'string');
                $color = $_POST['color'] ?? '#007bff';
                $icon = $_POST['icon'] ?? 'bi-circle-fill';
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                if (empty($name)) {
                    $message = 'Kategori adı zorunludur.';
                    $message_type = 'warning';
                } else {
                    // Slug oluştur (boş ise)
                    if (empty($slug)) {
                        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    }

                    // Slug benzersizliğini kontrol et
                    $stmt = $pdo->prepare("SELECT id FROM blog_categories WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if ($stmt->fetch()) {
                        $slug = $slug . '-' . time();
                    }

                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO blog_categories (name, slug, description, color, icon, sort_order, is_active, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$name, $slug, $description, $color, $icon, $sort_order, $is_active]);

                        SecurityUtils::logSecurityEvent('BLOG_CATEGORY_CREATED', [
                            'category_id' => $pdo->lastInsertId(),
                            'category_name' => $name,
                            'created_by' => $_SESSION['admin_id']
                        ]);

                        $message = 'Blog kategorisi başarıyla eklendi.';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Kategori eklenirken hata: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
                break;

            case 'update_category':
                $id = (int)$_POST['id'];
                $name = SecurityUtils::sanitizeInput($_POST['name'] ?? '', 'string');
                $slug = SecurityUtils::sanitizeInput($_POST['slug'] ?? '', 'string');
                $description = SecurityUtils::sanitizeInput($_POST['description'] ?? '', 'string');
                $color = $_POST['color'] ?? '#007bff';
                $icon = $_POST['icon'] ?? 'bi-circle-fill';
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                if (empty($name)) {
                    $message = 'Kategori adı zorunludur.';
                    $message_type = 'warning';
                } else {
                    // Slug oluştur (boş ise)
                    if (empty($slug)) {
                        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    }

                    // Slug benzersizliğini kontrol et (kendi hariç)
                    $stmt = $pdo->prepare("SELECT id FROM blog_categories WHERE slug = ? AND id != ?");
                    $stmt->execute([$slug, $id]);
                    if ($stmt->fetch()) {
                        $slug = $slug . '-' . time();
                    }

                    try {
                        $stmt = $pdo->prepare("
                            UPDATE blog_categories
                            SET name = ?, slug = ?, description = ?, color = ?, icon = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$name, $slug, $description, $color, $icon, $sort_order, $is_active, $id]);

                        SecurityUtils::logSecurityEvent('BLOG_CATEGORY_UPDATED', [
                            'category_id' => $id,
                            'category_name' => $name,
                            'updated_by' => $_SESSION['admin_id']
                        ]);

                        $message = 'Blog kategorisi başarıyla güncellendi.';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Kategori güncellenirken hata: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
                break;

            case 'delete_category':
                $id = (int)$_POST['id'];

                // Kategoriye ait blog yazıları var mı kontrol et
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE category_id = ?");
                $stmt->execute([$id]);
                $post_count = $stmt->fetchColumn();

                if ($post_count > 0) {
                    $message = 'Bu kategoriye ait ' . $post_count . ' blog yazısı bulunduğu için silinemez.';
                    $message_type = 'warning';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM blog_categories WHERE id = ?");
                        $stmt->execute([$id]);

                        SecurityUtils::logSecurityEvent('BLOG_CATEGORY_DELETED', [
                            'category_id' => $id,
                            'deleted_by' => $_SESSION['admin_id']
                        ]);

                        $message = 'Blog kategorisi başarıyla silindi.';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Kategori silinirken hata: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
                break;

            case 'update_order':
                $orders = $_POST['sort_order'] ?? [];

                try {
                    $pdo->beginTransaction();

                    foreach ($orders as $id => $order) {
                        $stmt = $pdo->prepare("UPDATE blog_categories SET sort_order = ? WHERE id = ?");
                        $stmt->execute([$order, $id]);
                    }

                    $pdo->commit();
                    $message = 'Kategori sıralaması güncellendi.';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $message = 'Sıralama güncellenirken hata: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Kategorileri çek
try {
    $categories = $pdo->query("
        SELECT bc.*,
               (SELECT COUNT(*) FROM blog_posts WHERE category_id = bc.id) as post_count
        FROM blog_categories bc
        ORDER BY bc.sort_order, bc.name
    ")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// İstatistikler
try {
    $stats = [
        'total_categories' => $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn(),
        'active_categories' => $pdo->query("SELECT COUNT(*) FROM blog_categories WHERE is_active = 1")->fetchColumn(),
        'inactive_categories' => $pdo->query("SELECT COUNT(*) FROM blog_categories WHERE is_active = 0")->fetchColumn(),
        'total_posts' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 1")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats = ['total_categories' => 0, 'active_categories' => 0, 'inactive_categories' => 0, 'total_posts' => 0];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Kategorileri - Prime EMS Admin</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">

    <style>
        .category-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s;
            overflow: hidden;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            border: none;
        }

        .form-floating > label {
            padding: 1rem 0.75rem;
        }

        .color-picker {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            cursor: pointer;
            display: inline-block;
        }

        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
        }

        .icon-option {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            cursor: pointer;
            transition: all 0.3s;
        }

        .icon-option:hover,
        .icon-option.selected {
            border-color: var(--primary-color);
            background-color: var(--primary-color);
            color: white;
        }

        .sortable-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            margin: 5px 0;
            border-radius: 10px;
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

            <!-- Page Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-tags me-2"></i>Blog Kategorileri
                    </h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Kategori
                    </button>
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
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-white-50 mb-1">Toplam Kategori</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_categories']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-tags"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-white-50 mb-1">Aktif Kategoriler</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['active_categories']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-white-50 mb-1">Pasif Kategoriler</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['inactive_categories']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-pause-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-white-50 mb-1">Toplam Yazı</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_posts']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Kategoriler</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($categories): ?>
                            <div class="row g-3" id="categories-list">
                                <?php foreach ($categories as $category): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="category-card card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="category-icon me-3" style="background-color: <?php echo $category['color']; ?>20; color: <?php echo $category['color']; ?>">
                                                    <i class="bi <?php echo $category['icon']; ?>"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                    <small class="text-muted"><?php echo $category['post_count']; ?> yazı</small>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editCategory(<?php echo $category['id']; ?>)">
                                                            <i class="bi bi-pencil me-2"></i>Düzenle
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                                            <i class="bi bi-trash me-2"></i>Sil
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <?php if ($category['description']): ?>
                                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge <?php echo $category['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo $category['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted">Sıra: <?php echo $category['sort_order']; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-tags display-1 text-muted"></i>
                                <p class="mt-3 text-muted">Henüz kategori bulunmuyor.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="bi bi-plus-circle me-2"></i>İlk Kategoriyi Ekle
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_category">

                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Kategori Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Kategori Adı *</label>
                                    <input type="text" class="form-control" id="category_name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="category_slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control" id="category_slug" name="slug">
                                    <div class="form-text">Otomatik oluşturulur, boş bırakabilirsiniz.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="category_description" class="form-label">Açıklama</label>
                                    <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_color" class="form-label">Renk</label>
                                            <input type="color" class="form-control form-control-color" id="category_color" name="color" value="#007bff">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control" id="category_sort_order" name="sort_order" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="category_active" name="is_active" checked>
                                        <label class="form-check-label" for="category_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">İkon Seçin</label>
                                <div class="icon-grid">
                                    <?php
                                    $icons = [
                                        'bi-circle-fill', 'bi-square-fill', 'bi-triangle-fill', 'bi-star-fill',
                                        'bi-heart-fill', 'bi-lightning-fill', 'bi-gear-fill', 'bi-tools',
                                        'bi-person-fill', 'bi-house-fill', 'bi-book-fill', 'bi-music-note',
                                        'bi-camera-fill', 'bi-bell-fill', 'bi-chat-fill', 'bi-graph-up',
                                        'bi-trophy-fill', 'bi-award-fill', 'bi-shield-fill', 'bi-flag-fill'
                                    ];

                                    foreach ($icons as $icon) {
                                        echo "<div class='icon-option' data-icon='$icon' onclick='selectIcon(this)'>
                                                <i class='bi $icon'></i>
                                              </div>";
                                    }
                                    ?>
                                </div>
                                <input type="hidden" id="selected_icon" name="icon" value="bi-circle-fill">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="id" id="edit_category_id">

                    <div class="modal-header">
                        <h5 class="modal-title">Kategori Düzenle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="edit_category_name" class="form-label">Kategori Adı *</label>
                                    <input type="text" class="form-control" id="edit_category_name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_category_slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control" id="edit_category_slug" name="slug">
                                    <div class="form-text">Otomatik oluşturulur, boş bırakabilirsiniz.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_category_description" class="form-label">Açıklama</label>
                                    <textarea class="form-control" id="edit_category_description" name="description" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_category_color" class="form-label">Renk</label>
                                            <input type="color" class="form-control form-control-color" id="edit_category_color" name="color">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_category_sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control" id="edit_category_sort_order" name="sort_order">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_category_active" name="is_active">
                                        <label class="form-check-label" for="edit_category_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">İkon Seçin</label>
                                <div class="icon-grid">
                                    <?php
                                    foreach ($icons as $icon) {
                                        echo "<div class='icon-option' data-icon='$icon' onclick='selectIcon(this, true)'>
                                                <i class='bi $icon'></i>
                                              </div>";
                                    }
                                    ?>
                                </div>
                                <input type="hidden" id="edit_selected_icon" name="icon">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>

    <script>
        // Slug oluşturma
        function generateSlug(text) {
            return text.toLowerCase()
                .replace(/ğ/g, 'g')
                .replace(/ü/g, 'u')
                .replace(/ş/g, 's')
                .replace(/ı/g, 'i')
                .replace(/ö/g, 'o')
                .replace(/ç/g, 'c')
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }

        document.getElementById('category_name').addEventListener('input', function() {
            document.getElementById('category_slug').value = generateSlug(this.value);
        });

        document.getElementById('edit_category_name').addEventListener('input', function() {
            document.getElementById('edit_category_slug').value = generateSlug(this.value);
        });

        // İkon seçme
        function selectIcon(element, isEdit = false) {
            const icon = element.getAttribute('data-icon');
            const container = isEdit ? document.getElementById('editCategoryModal') : document.getElementById('addCategoryModal');

            // Önceki seçimi kaldır
            container.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));

            // Yeni seçimi ekle
            element.classList.add('selected');

            // Hidden input'u güncelle
            const inputId = isEdit ? 'edit_selected_icon' : 'selected_icon';
            document.getElementById(inputId).value = icon;
        }

        // Kategori düzenleme
        function editCategory(id) {
            fetch('blog-categories.php?action=get_category&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const category = data.category;
                        document.getElementById('edit_category_id').value = category.id;
                        document.getElementById('edit_category_name').value = category.name;
                        document.getElementById('edit_category_slug').value = category.slug;
                        document.getElementById('edit_category_description').value = category.description || '';
                        document.getElementById('edit_category_color').value = category.color;
                        document.getElementById('edit_category_sort_order').value = category.sort_order;
                        document.getElementById('edit_category_active').checked = category.is_active == 1;
                        document.getElementById('edit_selected_icon').value = category.icon;

                        // İkon seçimi
                        document.querySelectorAll('#editCategoryModal .icon-option').forEach(el => {
                            el.classList.remove('selected');
                            if (el.getAttribute('data-icon') === category.icon) {
                                el.classList.add('selected');
                            }
                        });

                        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
                    }
                });
        }

        // Kategori silme
        function deleteCategory(id, name) {
            if (confirm('"' + name + '" kategorisini silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Sayfa yüklendiğinde varsayılan ikonları seç
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.icon-option[data-icon="bi-circle-fill"]').classList.add('selected');
        });
    </script>
</body>
</html>