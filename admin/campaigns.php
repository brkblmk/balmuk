<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// İşlemler
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Kampanya ekleme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
        SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP(), 'action' => 'admin_campaigns']);
        $error = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
    if ($action === 'add' || $action === 'edit') {
        $title = SecurityUtils::sanitizeInput($_POST['title'] ?? '', 'string');
        $subtitle = SecurityUtils::sanitizeInput($_POST['subtitle'] ?? '', 'string');
        $description = SecurityUtils::sanitizeInput($_POST['description'] ?? '', 'string');
        $discount_text = SecurityUtils::sanitizeInput($_POST['discount_text'] ?? '', 'string');
        $badge_text = SecurityUtils::sanitizeInput($_POST['badge_text'] ?? '', 'string');
        $badge_color = SecurityUtils::sanitizeInput($_POST['badge_color'] ?? 'warning', 'string');
        $icon = SecurityUtils::sanitizeInput($_POST['icon'] ?? '', 'string');
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $button_text = SecurityUtils::sanitizeInput($_POST['button_text'] ?? 'Hemen Rezerve Et', 'string');
        $button_link = SecurityUtils::sanitizeInput($_POST['button_link'] ?? '', 'string');
        $image = SecurityUtils::sanitizeInput($_POST['image'] ?? '', 'string');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order = SecurityUtils::sanitizeInput($_POST['sort_order'] ?? 0, 'int');
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO campaigns (title, subtitle, description, discount_text, badge_text, badge_color, icon, start_date, end_date, image, button_text, button_link, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $description, $discount_text, $badge_text, $badge_color, $icon, $start_date, $end_date, $image, $button_text, $button_link, $is_featured, $is_active, $sort_order]);
                $message = 'Kampanya başarıyla eklendi!';
                logActivity('create', 'campaigns', $pdo->lastInsertId());
            } else {
                $id = $_POST['id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE campaigns SET title = ?, subtitle = ?, description = ?, discount_text = ?, badge_text = ?, badge_color = ?, icon = ?, start_date = ?, end_date = ?, image = ?, button_text = ?, button_link = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$title, $subtitle, $description, $discount_text, $badge_text, $badge_color, $icon, $start_date, $end_date, $image, $button_text, $button_link, $is_featured, $is_active, $sort_order, $id]);
                $message = 'Kampanya başarıyla güncellendi!';
                logActivity('update', 'campaigns', $id);
            }
            header('Location: campaigns.php?message=' . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $error = 'Hata: ' . $e->getMessage();
        }
        }
    }
}

// Kampanya silme
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Kampanya başarıyla silindi!';
        logActivity('delete', 'campaigns', $id);
        header('Location: campaigns.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Kampanya düzenleme için veri çekme
$editData = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Kampanyaları listele
$campaigns = $pdo->query("SELECT * FROM campaigns ORDER BY sort_order ASC, created_at DESC")->fetchAll();

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
    <title>Kampanyalar - Prime EMS Admin</title>
    
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
                    <h1 class="h3 mb-0">Kampanyalar</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Kampanya
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
                <!-- Kampanya Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni Kampanya Ekle' : 'Kampanya Düzenle'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                            <?php if ($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Başlık *</label>
                                    <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($editData['title'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alt Başlık</label>
                                    <input type="text" name="subtitle" class="form-control" value="<?php echo htmlspecialchars($editData['subtitle'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Açıklama</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editData['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">İndirim Metni</label>
                                    <input type="text" name="discount_text" class="form-control" placeholder="%45 İndirim" value="<?php echo htmlspecialchars($editData['discount_text'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Rozet Metni</label>
                                    <input type="text" name="badge_text" class="form-control" placeholder="FIRSAT" value="<?php echo htmlspecialchars($editData['badge_text'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Rozet Rengi</label>
                                    <select name="badge_color" class="form-select">
                                        <option value="primary" <?php echo ($editData['badge_color'] ?? '') === 'primary' ? 'selected' : ''; ?>>Mavi</option>
                                        <option value="success" <?php echo ($editData['badge_color'] ?? '') === 'success' ? 'selected' : ''; ?>>Yeşil</option>
                                        <option value="warning" <?php echo ($editData['badge_color'] ?? 'warning') === 'warning' ? 'selected' : ''; ?>>Sarı</option>
                                        <option value="danger" <?php echo ($editData['badge_color'] ?? '') === 'danger' ? 'selected' : ''; ?>>Kırmızı</option>
                                        <option value="info" <?php echo ($editData['badge_color'] ?? '') === 'info' ? 'selected' : ''; ?>>Turkuaz</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">İkon (Bootstrap Icons)</label>
                                    <input type="text" name="icon" class="form-control" placeholder="bi-star-fill" value="<?php echo htmlspecialchars($editData['icon'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Başlangıç Tarihi</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo $editData['start_date'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bitiş Tarihi</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo $editData['end_date'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Buton Metni</label>
                                    <input type="text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($editData['button_text'] ?? 'Hemen Rezerve Et'); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Buton Linki</label>
                                    <input type="text" name="button_link" class="form-control" placeholder="#reservation" value="<?php echo htmlspecialchars($editData['button_link'] ?? ''); ?>">
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Görsel URL</label>
                                    <input type="text" name="image" class="form-control" placeholder="https://example.com/image.jpg" value="<?php echo htmlspecialchars($editData['image'] ?? ''); ?>">
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
                                <a href="campaigns.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Kampanya Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th>Başlık</th>
                                        <th>İndirim</th>
                                        <th>Rozet</th>
                                        <th>Tarih Aralığı</th>
                                        <th width="100">Öne Çıkan</th>
                                        <th width="100">Durum</th>
                                        <th width="100">Sıra</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($campaigns as $campaign): ?>
                                    <tr>
                                        <td><?php echo $campaign['id']; ?></td>
                                        <td>
                                            <?php if ($campaign['icon']): ?>
                                            <i class="<?php echo $campaign['icon']; ?> me-2"></i>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($campaign['title']); ?></strong>
                                            <?php if ($campaign['subtitle']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($campaign['subtitle']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($campaign['discount_text'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($campaign['badge_text']): ?>
                                            <span class="badge bg-<?php echo $campaign['badge_color']; ?>">
                                                <?php echo htmlspecialchars($campaign['badge_text']); ?>
                                            </span>
                                            <?php else: ?>
                                            -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($campaign['start_date'] || $campaign['end_date']) {
                                                echo ($campaign['start_date'] ? date('d.m.Y', strtotime($campaign['start_date'])) : '∞');
                                                echo ' - ';
                                                echo ($campaign['end_date'] ? date('d.m.Y', strtotime($campaign['end_date'])) : '∞');
                                            } else {
                                                echo 'Süresiz';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($campaign['is_featured']): ?>
                                            <span class="badge bg-warning text-dark">Öne Çıkan</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($campaign['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $campaign['sort_order']; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu kampanyayı silmek istediğinizden emin misiniz?')">
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
    <script src="assets/js/turkish-support.js"></script>
</body>
</html>