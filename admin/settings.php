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
$messageType = '';

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_settings') {
            $settings = $_POST['settings'] ?? [];
            
            foreach ($settings as $key => $value) {
                updateSetting($key, $value);
            }
            
            logActivity('Site settings updated', 'settings');
            $message = 'Ayarlar başarıyla güncellendi!';
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Mevcut ayarları kategorilere göre getir
try {
    $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY category, setting_key");
    $all_settings = $stmt->fetchAll();
    
    // Kategorilere göre grupla
    $settings_by_category = [];
    foreach ($all_settings as $setting) {
        $settings_by_category[$setting['category']][] = $setting;
    }
    
} catch (PDOException $e) {
    $settings_by_category = [];
}

// Kategori adları
$category_names = [
    'general' => 'Genel Ayarlar',
    'contact' => 'İletişim Bilgileri',
    'design' => 'Tasarım Ayarları',
    'social' => 'Sosyal Medya',
    'integration' => 'Entegrasyonlar',
    'seo' => 'SEO Ayarları'
];

// Kategori ikonları
$category_icons = [
    'general' => 'bi-gear-fill',
    'contact' => 'bi-telephone-fill',
    'design' => 'bi-palette-fill',
    'social' => 'bi-share-fill',
    'integration' => 'bi-plug-fill',
    'seo' => 'bi-search'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Ayarları - Prime EMS Admin</title>
    
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
                        <h1 class="h3 mb-1">Site Ayarları</h1>
                        <p class="text-muted">Genel site ayarlarını yönetin</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary" onclick="exportSettings()">
                            <i class="bi bi-download me-1"></i>Ayarları Dışa Aktar
                        </button>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="row">
                        <!-- Settings Form -->
                        <div class="col-lg-9">
                            <?php foreach ($settings_by_category as $category => $settings): ?>
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0">
                                        <i class="<?php echo $category_icons[$category] ?? 'bi-gear'; ?> text-primary me-2"></i>
                                        <?php echo $category_names[$category] ?? ucfirst($category); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <?php foreach ($settings as $setting): ?>
                                        <div class="<?php echo in_array($setting['setting_type'], ['textarea', 'json']) ? 'col-12' : 'col-md-6'; ?>">
                                            <label for="<?php echo $setting['setting_key']; ?>" class="form-label">
                                                <?php echo $setting['description'] ?: ucfirst(str_replace('_', ' ', $setting['setting_key'])); ?>
                                            </label>
                                            
                                            <?php if ($setting['setting_type'] === 'textarea'): ?>
                                                <textarea class="form-control" 
                                                          id="<?php echo $setting['setting_key']; ?>" 
                                                          name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                          rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                            
                                            <?php elseif ($setting['setting_type'] === 'number'): ?>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="<?php echo $setting['setting_key']; ?>" 
                                                       name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                            
                                            <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="<?php echo $setting['setting_key']; ?>" 
                                                           name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                           value="1"
                                                           <?php echo $setting['setting_value'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="<?php echo $setting['setting_key']; ?>">
                                                        Etkinleştir
                                                    </label>
                                                </div>
                                            
                                            <?php elseif ($setting['setting_type'] === 'file'): ?>
                                                <div class="input-group">
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="<?php echo $setting['setting_key']; ?>" 
                                                           name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                                           placeholder="/path/to/file.ext">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="selectFile('<?php echo $setting['setting_key']; ?>')">
                                                        <i class="bi bi-folder"></i>
                                                    </button>
                                                </div>
                                            
                                            <?php else: ?>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="<?php echo $setting['setting_key']; ?>" 
                                                       name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                            <?php endif; ?>
                                            
                                            <?php if ($setting['setting_key'] === 'primary_color'): ?>
                                            <div class="form-text">
                                                <input type="color" class="form-control form-control-color" 
                                                       value="<?php echo $setting['setting_value']; ?>" 
                                                       onchange="document.getElementById('<?php echo $setting['setting_key']; ?>').value = this.value;">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Sıfırla
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Ayarları Kaydet
                                </button>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="col-lg-3">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-lightning-fill text-warning me-2"></i>
                                        Hızlı İşlemler
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="testEmailConfig()">
                                            <i class="bi bi-envelope-check me-1"></i>E-posta Test Et
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="clearCache()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Önbelleği Temizle
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="backupSettings()">
                                            <i class="bi bi-shield-check me-1"></i>Ayarları Yedekle
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-info-circle text-info me-2"></i>
                                        Sistem Bilgileri
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled small mb-0">
                                        <li class="mb-2">
                                            <strong>PHP Sürümü:</strong><br>
                                            <?php echo PHP_VERSION; ?>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Sunucu:</strong><br>
                                            <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor'; ?>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Veritabanı:</strong><br>
                                            <?php 
                                            try {
                                                $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                                                echo 'MySQL ' . $version;
                                            } catch (PDOException $e) {
                                                echo 'Bağlanamadı';
                                            }
                                            ?>
                                        </li>
                                        <li>
                                            <strong>Son Güncelleme:</strong><br>
                                            <?php 
                                            try {
                                                $stmt = $pdo->query("SELECT MAX(updated_at) FROM site_settings");
                                                $last_update = $stmt->fetchColumn();
                                                echo $last_update ? date('d.m.Y H:i', strtotime($last_update)) : 'Bilinmiyor';
                                            } catch (PDOException $e) {
                                                echo 'Bilinmiyor';
                                            }
                                            ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
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
                                            Değişikliklerden sonra önbelleti temizleyin
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            Sosyal medya URL'lerini tam yazın
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            E-posta ayarlarını test etmeyi unutmayın
                                        </li>
                                        <li>
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            Renk kodlarını # işareti ile başlatın
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectFile(settingKey) {
            // File selection functionality will be implemented with media manager
            alert('Medya yöneticisi yakında eklenecek!');
        }
        
        function testEmailConfig() {
            // Email configuration test
            fetch('ajax/test-email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('E-posta testi başarılı!');
                } else {
                    alert('E-posta testi başarısız: ' + data.message);
                }
            })
            .catch(error => {
                alert('E-posta testi sırasında hata oluştu');
            });
        }
        
        function clearCache() {
            // Cache clearing functionality
            if (confirm('Önbellek temizlensin mi?')) {
                fetch('ajax/clear-cache.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Önbellek başarıyla temizlendi!');
                        location.reload();
                    } else {
                        alert('Önbellek temizlenirken hata oluştu');
                    }
                })
                .catch(error => {
                    alert('Önbellek temizlenirken hata oluştu');
                });
            }
        }
        
        function backupSettings() {
            // Settings backup functionality
            window.location.href = 'ajax/backup-settings.php';
        }
        
        function exportSettings() {
            // Export settings as JSON
            window.location.href = 'ajax/export-settings.php';
        }
        
        // Auto-save functionality (saves every 30 seconds if there are changes)
        let formChanged = false;
        let autoSaveTimer;
        
        document.querySelectorAll('input, textarea, select').forEach(function(element) {
            element.addEventListener('change', function() {
                formChanged = true;
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSave, 30000); // 30 seconds
            });
        });
        
        function autoSave() {
            if (formChanged) {
                const formData = new FormData(document.querySelector('form'));
                
                fetch('ajax/auto-save-settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show subtle notification
                        showNotification('Ayarlar otomatik kaydedildi', 'success');
                        formChanged = false;
                    }
                })
                .catch(error => {
                    console.error('Auto-save failed:', error);
                });
            }
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
            notification.style.zIndex = '9999';
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
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
        
        // Prevent accidental page leave with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
                return 'Kaydedilmemiş değişiklikleriniz var. Sayfadan ayrılmak istediğinizden emin misiniz?';
            }
        });
    </script>
</body>
</html>