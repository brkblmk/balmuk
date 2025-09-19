<?php
require_once '../config/database.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$action = $_GET['action'] ?? 'list';
$packageId = $_GET['id'] ?? null;

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formAction = $_POST['action'] ?? '';
        
        if ($formAction === 'create' || $formAction === 'update') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $session_count = intval($_POST['session_count'] ?? 0);
            $validity_days = intval($_POST['validity_days'] ?? 0);
            $price = floatval($_POST['price'] ?? 0);
            $discount_percentage = floatval($_POST['discount_percentage'] ?? 0);
            $is_trial = isset($_POST['is_trial']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $sort_order = intval($_POST['sort_order'] ?? 0);
            
            // Features array oluştur
            $features = [];
            if (isset($_POST['features']) && is_array($_POST['features'])) {
                $features = array_filter($_POST['features']);
            }
            $features_json = json_encode($features);
            
            if ($formAction === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO packages (name, description, session_count, validity_days, price, 
                                        discount_percentage, features, is_trial, is_featured, is_active, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $description, $session_count, $validity_days, $price,
                    $discount_percentage, $features_json, $is_trial, $is_featured, $is_active, $sort_order
                ]);
                
                logActivity('Package created', 'packages', $pdo->lastInsertId());
                $message = 'Paket başarıyla oluşturuldu!';
                $messageType = 'success';
                $action = 'list';
                
            } else if ($formAction === 'update' && $packageId) {
                $stmt = $pdo->prepare("
                    UPDATE packages SET 
                        name = ?, description = ?, session_count = ?, validity_days = ?, 
                        price = ?, discount_percentage = ?, features = ?, is_trial = ?, 
                        is_featured = ?, is_active = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $description, $session_count, $validity_days, $price,
                    $discount_percentage, $features_json, $is_trial, $is_featured, $is_active, $sort_order,
                    $packageId
                ]);
                
                logActivity('Package updated', 'packages', $packageId);
                $message = 'Paket başarıyla güncellendi!';
                $messageType = 'success';
                $action = 'list';
            }
        }
        
        if ($formAction === 'delete' && $packageId) {
            $stmt = $pdo->prepare("UPDATE packages SET is_active = 0 WHERE id = ?");
            $stmt->execute([$packageId]);
            
            logActivity('Package deleted', 'packages', $packageId);
            $message = 'Paket başarıyla silindi!';
            $messageType = 'success';
            $action = 'list';
        }
        
    } catch (PDOException $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Paket listesi
if ($action === 'list') {
    try {
        $stmt = $pdo->query("
            SELECT * FROM packages 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, created_at DESC
        ");
        $packages = $stmt->fetchAll();
    } catch (PDOException $e) {
        $packages = [];
    }
}

// Düzenleme için paket bilgilerini getir
if ($action === 'edit' && $packageId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$packageId]);
        $package = $stmt->fetch();
        
        if (!$package) {
            $action = 'list';
            $message = 'Paket bulunamadı!';
            $messageType = 'danger';
        } else {
            // JSON features'ı array'e çevir
            $package['features_array'] = json_decode($package['features'] ?? '[]', true) ?: [];
        }
    } catch (PDOException $e) {
        $action = 'list';
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paketler - Prime EMS Admin</title>
    
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
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Paketler</h1>
                        <p class="text-muted">Üyelik paketlerini yönetin</p>
                    </div>
                    <div>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Yeni Paket
                        </a>
                        <?php else: ?>
                        <a href="packages.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Geri
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'list'): ?>
                <!-- Packages List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam-fill text-primary me-2"></i>
                            Paket Listesi
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Paket Adı</th>
                                        <th>Seans</th>
                                        <th>Geçerlilik</th>
                                        <th>Fiyat</th>
                                        <th>İndirim</th>
                                        <th>Durum</th>
                                        <th width="120">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($packages as $pkg): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($pkg['name']); ?></strong>
                                                <?php if ($pkg['is_trial']): ?>
                                                <span class="badge bg-info ms-2">Deneme</span>
                                                <?php endif; ?>
                                                <?php if ($pkg['is_featured']): ?>
                                                <span class="badge bg-warning ms-1">Öne Çıkan</span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($pkg['description']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $pkg['session_count']; ?> Seans</span>
                                        </td>
                                        <td><?php echo $pkg['validity_days']; ?> gün</td>
                                        <td>
                                            <strong>₺<?php echo number_format($pkg['price'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($pkg['discount_percentage'] > 0): ?>
                                            <span class="text-success">%<?php echo $pkg['discount_percentage']; ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $pkg['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $pkg['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?action=edit&id=<?php echo $pkg['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-outline-danger" onclick="deletePackage(<?php echo $pkg['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($packages)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-box-seam display-4 d-block mb-2"></i>
                                                Henüz paket eklenmemiş
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($action === 'create' || $action === 'edit'): ?>
                <!-- Package Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-<?php echo $action === 'create' ? 'plus' : 'pencil'; ?>-circle text-primary me-2"></i>
                                    <?php echo $action === 'create' ? 'Yeni Paket Ekle' : 'Paket Düzenle'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="<?php echo $action === 'create' ? 'create' : 'update'; ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label for="name" class="form-label">Paket Adı *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($package['name'] ?? ''); ?>" 
                                                   required maxlength="100">
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                                   value="<?php echo $package['sort_order'] ?? 0; ?>" min="0">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="description" class="form-label">Açıklama</label>
                                            <textarea class="form-control" id="description" name="description" rows="2" maxlength="255"><?php echo htmlspecialchars($package['description'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="session_count" class="form-label">Seans Sayısı *</label>
                                            <input type="number" class="form-control" id="session_count" name="session_count" 
                                                   value="<?php echo $package['session_count'] ?? ''; ?>" 
                                                   required min="1" max="100">
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="validity_days" class="form-label">Geçerlilik (Gün) *</label>
                                            <input type="number" class="form-control" id="validity_days" name="validity_days" 
                                                   value="<?php echo $package['validity_days'] ?? ''; ?>" 
                                                   required min="1" max="365">
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="price" class="form-label">Fiyat (TL) *</label>
                                            <input type="number" class="form-control" id="price" name="price" 
                                                   value="<?php echo $package['price'] ?? ''; ?>" 
                                                   required min="0" step="0.01">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="discount_percentage" class="form-label">İndirim Oranı (%)</label>
                                            <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" 
                                                   value="<?php echo $package['discount_percentage'] ?? 0; ?>" 
                                                   min="0" max="100" step="0.01">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Paket Özellikleri</label>
                                            <div id="features-container">
                                                <?php 
                                                $features = $package['features_array'] ?? [];
                                                if (empty($features)) $features = [''];
                                                foreach ($features as $index => $feature): 
                                                ?>
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" name="features[]" 
                                                           value="<?php echo htmlspecialchars($feature); ?>" 
                                                           placeholder="Özellik ekleyin">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addFeature()">
                                                <i class="bi bi-plus me-1"></i>Özellik Ekle
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="is_trial" name="is_trial" 
                                                               <?php echo ($package['is_trial'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="is_trial">
                                                            Deneme Paketi
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                                               <?php echo ($package['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="is_featured">
                                                            Öne Çıkan Paket
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                               <?php echo ($package['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="is_active">
                                                            Aktif
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="packages.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg me-1"></i>İptal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>Kaydet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-lightbulb text-warning me-2"></i>
                                    İpuçları
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Paket adı kısa ve açıklayıcı olmalı
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Seans sayısı ile geçerlilik süresi uyumlu olmalı
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Özellikleri müşterinin anlayacağı şekilde yazın
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Fiyatlandırmada rekabeti göz önünde bulundurun
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paketi Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bu paketi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <form method="POST" class="d-inline" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deletePackage(id) {
            const form = document.getElementById('deleteForm');
            form.action = '?id=' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function addFeature() {
            const container = document.getElementById('features-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="features[]" placeholder="Özellik ekleyin">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function removeFeature(btn) {
            const container = document.getElementById('features-container');
            if (container.children.length > 1) {
                btn.closest('.input-group').remove();
            }
        }
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            });
        })();
    </script>
</body>
</html>