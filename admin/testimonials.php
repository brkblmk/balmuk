<?php
require_once '../config/database.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// İşlemler
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Testimonial ekleme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $customer_name = $_POST['customer_name'] ?? '';
        $customer_title = $_POST['customer_title'] ?? '';
        $content = $_POST['content'] ?? '';
        $rating = $_POST['rating'] ?? 5;
        $service_used = $_POST['service_used'] ?? '';
        $result_achieved = $_POST['result_achieved'] ?? '';
        $video_url = $_POST['video_url'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order = $_POST['sort_order'] ?? 0;
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, customer_title, content, rating, service_used, result_achieved, video_url, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$customer_name, $customer_title, $content, $rating, $service_used, $result_achieved, $video_url, $is_featured, $is_active, $sort_order]);
                $message = 'Müşteri yorumu başarıyla eklendi!';
                logActivity('create', 'testimonials', $pdo->lastInsertId());
            } else {
                $id = $_POST['id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE testimonials SET customer_name = ?, customer_title = ?, content = ?, rating = ?, service_used = ?, result_achieved = ?, video_url = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$customer_name, $customer_title, $content, $rating, $service_used, $result_achieved, $video_url, $is_featured, $is_active, $sort_order, $id]);
                $message = 'Müşteri yorumu başarıyla güncellendi!';
                logActivity('update', 'testimonials', $id);
            }
            header('Location: testimonials.php?message=' . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $error = 'Hata: ' . $e->getMessage();
        }
    }
}

// Testimonial silme
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Müşteri yorumu başarıyla silindi!';
        logActivity('delete', 'testimonials', $id);
        header('Location: testimonials.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Testimonial düzenleme için veri çekme
$editData = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Testimonialları listele
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC")->fetchAll();

// Mesajları göster
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Yorumları - Prime EMS Admin</title>
    
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
            
            <!-- Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Müşteri Yorumları</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Yorum
                    </a>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Yorum Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni Yorum Ekle' : 'Yorum Düzenle'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Müşteri Adı *</label>
                                    <input type="text" name="customer_name" class="form-control" required value="<?php echo htmlspecialchars($editData['customer_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ünvan/Açıklama</label>
                                    <input type="text" name="customer_title" class="form-control" placeholder="Yazılım Geliştirici, 32" value="<?php echo htmlspecialchars($editData['customer_title'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Puan</label>
                                    <select name="rating" class="form-select">
                                        <option value="5" <?php echo ($editData['rating'] ?? 5) == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5)</option>
                                        <option value="4" <?php echo ($editData['rating'] ?? '') == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4)</option>
                                        <option value="3" <?php echo ($editData['rating'] ?? '') == 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3)</option>
                                        <option value="2" <?php echo ($editData['rating'] ?? '') == 2 ? 'selected' : ''; ?>>⭐⭐ (2)</option>
                                        <option value="1" <?php echo ($editData['rating'] ?? '') == 1 ? 'selected' : ''; ?>>⭐ (1)</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Yorum İçeriği *</label>
                                    <textarea name="content" class="form-control" rows="4" required><?php echo htmlspecialchars($editData['content'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kullanılan Hizmet</label>
                                    <input type="text" name="service_used" class="form-control" placeholder="Prime Slim" value="<?php echo htmlspecialchars($editData['service_used'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Elde Edilen Sonuç</label>
                                    <input type="text" name="result_achieved" class="form-control" placeholder="12 kg kayıp" value="<?php echo htmlspecialchars($editData['result_achieved'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Video URL</label>
                                    <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." value="<?php echo htmlspecialchars($editData['video_url'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sıralama</label>
                                    <input type="number" name="sort_order" class="form-control" value="<?php echo $editData['sort_order'] ?? 0; ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" <?php echo ($editData['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo ($editData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Kaydet
                                </button>
                                <a href="testimonials.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Yorum Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th>Müşteri</th>
                                        <th>Yorum</th>
                                        <th>Hizmet/Sonuç</th>
                                        <th>Puan</th>
                                        <th width="100">Öne Çıkan</th>
                                        <th width="100">Durum</th>
                                        <th width="100">Sıra</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                    <tr>
                                        <td><?php echo $testimonial['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($testimonial['customer_name']); ?></strong>
                                            <?php if ($testimonial['customer_title']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($testimonial['customer_title']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="max-width: 300px;">
                                                <?php echo htmlspecialchars(substr($testimonial['content'], 0, 100)); ?>
                                                <?php if (strlen($testimonial['content']) > 100): ?>...<?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($testimonial['service_used']): ?>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($testimonial['service_used']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($testimonial['result_achieved']): ?>
                                            <br><small><?php echo htmlspecialchars($testimonial['result_achieved']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $stars = str_repeat('⭐', $testimonial['rating']);
                                            echo $stars;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($testimonial['is_featured']): ?>
                                            <span class="badge bg-warning text-dark">Öne Çıkan</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($testimonial['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $testimonial['sort_order']; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu yorumu silmek istediğinizden emin misiniz?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>