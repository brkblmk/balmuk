<?php
require_once '../config/database.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_hero') {
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            $description = $_POST['description'] ?? '';
            $button1_text = $_POST['button1_text'] ?? '';
            $button1_link = $_POST['button1_link'] ?? '';
            $button2_text = $_POST['button2_text'] ?? '';
            $button2_link = $_POST['button2_link'] ?? '';
            $overlay_opacity = floatval($_POST['overlay_opacity'] ?? 0.7);
            
            // Hero section güncelleme veya ekleme
            $stmt = $pdo->prepare("
                INSERT INTO hero_section (id, title, subtitle, description, button1_text, button1_link, button2_text, button2_link, overlay_opacity, is_active)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                subtitle = VALUES(subtitle),
                description = VALUES(description),
                button1_text = VALUES(button1_text),
                button1_link = VALUES(button1_link),
                button2_text = VALUES(button2_text),
                button2_link = VALUES(button2_link),
                overlay_opacity = VALUES(overlay_opacity),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([
                $title, $subtitle, $description, 
                $button1_text, $button1_link,
                $button2_text, $button2_link, $overlay_opacity
            ]);
            
            logActivity('Hero section updated', 'hero');
            $message = 'Hero section başarıyla güncellendi!';
            $messageType = 'success';
        }
        
    } catch (PDOException $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Mevcut hero verilerini getir
try {
    $stmt = $pdo->query("SELECT * FROM hero_section WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $hero = $stmt->fetch() ?: [];
} catch (PDOException $e) {
    $hero = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Section - Prime EMS Admin</title>
    
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
                        <h1 class="h3 mb-1">Hero Section</h1>
                        <p class="text-muted">Ana sayfa hero bölümünü yönetin</p>
                    </div>
                    <div>
                        <a href="../index.php#hero" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-eye me-1"></i>Önizle
                        </a>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Hero Form -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-image-fill text-primary me-2"></i>
                                    Hero Section Ayarları
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="update_hero">
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="title" class="form-label">Ana Başlık *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($hero['title'] ?? 'Prime EMS Studios'); ?>" 
                                                   required maxlength="255">
                                            <div class="form-text">Ana sayfa başlığı (maksimum 255 karakter)</div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="subtitle" class="form-label">Alt Başlık</label>
                                            <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                                   value="<?php echo htmlspecialchars($hero['subtitle'] ?? 'İzmir\'in Altın Standardı'); ?>" 
                                                   maxlength="255">
                                            <div class="form-text">İsteğe bağlı alt başlık</div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="description" class="form-label">Açıklama</label>
                                            <textarea class="form-control" id="description" name="description" rows="3" maxlength="500"><?php echo htmlspecialchars($hero['description'] ?? 'Bilimsel WB-EMS seansları: 20 dakika, haftada 2 gün ile daha güçlü, daha fit ve enerji dolu yaşam'); ?></textarea>
                                            <div class="form-text">Hero bölümünde gösterilecek açıklama metni</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="button1_text" class="form-label">1. Buton Metni</label>
                                            <input type="text" class="form-control" id="button1_text" name="button1_text" 
                                                   value="<?php echo htmlspecialchars($hero['button1_text'] ?? 'Ücretsiz Keşif Seansı Alın'); ?>" 
                                                   maxlength="100">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="button1_link" class="form-label">1. Buton Linki</label>
                                            <input type="text" class="form-control" id="button1_link" name="button1_link" 
                                                   value="<?php echo htmlspecialchars($hero['button1_link'] ?? '#reservation'); ?>" 
                                                   maxlength="255">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="button2_text" class="form-label">2. Buton Metni</label>
                                            <input type="text" class="form-control" id="button2_text" name="button2_text" 
                                                   value="<?php echo htmlspecialchars($hero['button2_text'] ?? 'Kampanyaları Gör'); ?>" 
                                                   maxlength="100">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="button2_link" class="form-label">2. Buton Linki</label>
                                            <input type="text" class="form-control" id="button2_link" name="button2_link" 
                                                   value="<?php echo htmlspecialchars($hero['button2_link'] ?? '#campaigns'); ?>" 
                                                   maxlength="255">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="overlay_opacity" class="form-label">Overlay Şeffaflığı</label>
                                            <div class="input-group">
                                                <input type="range" class="form-range" id="overlay_opacity" name="overlay_opacity" 
                                                       min="0" max="1" step="0.1" 
                                                       value="<?php echo $hero['overlay_opacity'] ?? 0.7; ?>">
                                                <span class="input-group-text" id="opacity-value">
                                                    <?php echo number_format(($hero['overlay_opacity'] ?? 0.7) * 100, 0); ?>%
                                                </span>
                                            </div>
                                            <div class="form-text">Arka plan görselinin üzerindeki karartma oranı</div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Sıfırla
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>Kaydet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview & Media -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-image text-primary me-2"></i>
                                    Medya Yönetimi
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Hero arka plan medyası şu anda <code>motion.mp4</code> dosyası kullanılmaktadır.
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" onclick="alert('Medya yönetimi yakında eklenecek!')">
                                        <i class="bi bi-upload me-1"></i>Yeni Medya Yükle
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="alert('Medya galerisi yakında eklenecek!')">
                                        <i class="bi bi-folder me-1"></i>Medya Galerisi
                                    </button>
                                </div>
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
                                        Başlık kısa ve etkili olmalı
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Buton metinleri eylem odaklı yazın
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Overlay değeri 0.5-0.8 arası ideal
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Değişiklikleri önizlemeyi unutmayın
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Opacity range slider
        const opacityRange = document.getElementById('overlay_opacity');
        const opacityValue = document.getElementById('opacity-value');
        
        opacityRange.addEventListener('input', function() {
            opacityValue.textContent = Math.round(this.value * 100) + '%';
        });
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>