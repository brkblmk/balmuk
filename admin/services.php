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

// Hizmet ekleme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
        SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP(), 'action' => 'admin_services']);
        $error = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
    if ($action === 'add' || $action === 'edit') {
        $name = SecurityUtils::sanitizeInput($_POST['name'] ?? '', 'string');
        $slug = strtolower(str_replace(' ', '-', $name));
        $short_description = SecurityUtils::sanitizeInput($_POST['short_description'] ?? '', 'string');
        $long_description = SecurityUtils::sanitizeInput($_POST['long_description'] ?? '', 'string');
        $duration = SecurityUtils::sanitizeInput($_POST['duration'] ?? '', 'string');
        $goal = SecurityUtils::sanitizeInput($_POST['goal'] ?? '', 'string');
        $icon = SecurityUtils::sanitizeInput($_POST['icon'] ?? '', 'string');
        $price = SecurityUtils::sanitizeInput($_POST['price'] ?? 0, 'float');
        $session_count = SecurityUtils::sanitizeInput($_POST['session_count'] ?? 0, 'int');
        $features = json_encode(array_filter(explode("\n", SecurityUtils::sanitizeInput($_POST['features'] ?? '', 'string'))));
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order = SecurityUtils::sanitizeInput($_POST['sort_order'] ?? 0, 'int');
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO services (name, slug, short_description, long_description, duration, goal, icon, price, session_count, features, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $short_description, $long_description, $duration, $goal, $icon, $price, $session_count, $features, $is_featured, $is_active, $sort_order]);
                $message = 'Hizmet başarıyla eklendi!';
                logActivity('create', 'services', $pdo->lastInsertId());
            } else {
                $id = $_POST['id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE services SET name = ?, slug = ?, short_description = ?, long_description = ?, duration = ?, goal = ?, icon = ?, price = ?, session_count = ?, features = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $short_description, $long_description, $duration, $goal, $icon, $price, $session_count, $features, $is_featured, $is_active, $sort_order, $id]);
                $message = 'Hizmet başarıyla güncellendi!';
                logActivity('update', 'services', $id);
            }
            header('Location: services.php?message=' . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $error = 'Hata: ' . $e->getMessage();
        }
    }
}

// Hizmet silme
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Hizmet başarıyla silindi!';
        logActivity('delete', 'services', $id);
        header('Location: services.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Hizmet düzenleme için veri çekme
$editData = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
    if ($editData && $editData['features']) {
        $editData['features_text'] = implode("\n", json_decode($editData['features'], true));
    }
}

// Hizmetleri listele
$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, created_at DESC")->fetchAll();

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
    <title>Hizmetler - Prime EMS Admin</title>
    
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
                    <h1 class="h3 mb-0">Hizmetler</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Hizmet
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
                <!-- Hizmet Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni Hizmet Ekle' : 'Hizmet Düzenle'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                            <?php if ($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hizmet Adı *</label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($editData['name'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hedef</label>
                                    <input type="text" name="goal" class="form-control" placeholder="Yağ Yakımı" value="<?php echo htmlspecialchars($editData['goal'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Kısa Açıklama</label>
                                    <textarea name="short_description" class="form-control" rows="2"><?php echo htmlspecialchars($editData['short_description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Detaylı Açıklama</label>
                                    <textarea name="long_description" class="form-control" rows="4"><?php echo htmlspecialchars($editData['long_description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Süre</label>
                                    <input type="text" name="duration" class="form-control" placeholder="20 dk" value="<?php echo htmlspecialchars($editData['duration'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">İkon</label>
                                    <input type="text" name="icon" class="form-control" placeholder="bi-fire" value="<?php echo htmlspecialchars($editData['icon'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Fiyat (₺)</label>
                                    <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $editData['price'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Seans Sayısı</label>
                                    <input type="number" name="session_count" class="form-control" value="<?php echo $editData['session_count'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                                    <textarea name="features" class="form-control" rows="4" placeholder="Hızlı metabolizma&#10;Etkili yağ yakımı&#10;Kas koruma"><?php echo htmlspecialchars($editData['features_text'] ?? ''); ?></textarea>
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
                                <a href="services.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Hizmet Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th>Hizmet Adı</th>
                                        <th>Hedef</th>
                                        <th>Süre</th>
                                        <th>Fiyat</th>
                                        <th>Seans</th>
                                        <th width="100">Öne Çıkan</th>
                                        <th width="100">Durum</th>
                                        <th width="100">Sıra</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?php echo $service['id']; ?></td>
                                        <td>
                                            <?php if ($service['icon']): ?>
                                            <i class="<?php echo $service['icon']; ?> me-2"></i>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($service['goal'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($service['duration'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($service['price']): ?>
                                            ₺<?php echo number_format($service['price'], 2); ?>
                                            <?php else: ?>
                                            -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $service['session_count'] ?: '-'; ?></td>
                                        <td>
                                            <?php if ($service['is_featured']): ?>
                                            <span class="badge bg-warning text-dark">Öne Çıkan</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($service['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $service['sort_order']; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu hizmeti silmek istediğinizden emin misiniz?')">
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