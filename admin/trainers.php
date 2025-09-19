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
$trainerId = $_GET['id'] ?? null;

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formAction = $_POST['action'] ?? '';
        
        if ($formAction === 'create' || $formAction === 'update') {
            $name = $_POST['name'] ?? '';
            $title = $_POST['title'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $experience_years = intval($_POST['experience_years'] ?? 0);
            $social_instagram = $_POST['social_instagram'] ?? '';
            $social_linkedin = $_POST['social_linkedin'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $sort_order = intval($_POST['sort_order'] ?? 0);
            
            // Certifications array oluştur
            $certifications = [];
            if (isset($_POST['certifications']) && is_array($_POST['certifications'])) {
                $certifications = array_filter($_POST['certifications']);
            }
            $certifications_json = json_encode($certifications);
            
            // Specializations array oluştur
            $specializations = [];
            if (isset($_POST['specializations']) && is_array($_POST['specializations'])) {
                $specializations = array_filter($_POST['specializations']);
            }
            $specializations_json = json_encode($specializations);
            
            if ($formAction === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO trainers (name, title, bio, certifications, specializations, 
                                        experience_years, social_instagram, social_linkedin, 
                                        is_active, is_featured, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $title, $bio, $certifications_json, $specializations_json,
                    $experience_years, $social_instagram, $social_linkedin,
                    $is_active, $is_featured, $sort_order
                ]);
                
                logActivity('Trainer created', 'trainers', $pdo->lastInsertId());
                $message = 'Eğitmen başarıyla eklendi!';
                $messageType = 'success';
                $action = 'list';
                
            } else if ($formAction === 'update' && $trainerId) {
                $stmt = $pdo->prepare("
                    UPDATE trainers SET 
                        name = ?, title = ?, bio = ?, certifications = ?, specializations = ?,
                        experience_years = ?, social_instagram = ?, social_linkedin = ?,
                        is_active = ?, is_featured = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $title, $bio, $certifications_json, $specializations_json,
                    $experience_years, $social_instagram, $social_linkedin,
                    $is_active, $is_featured, $sort_order, $trainerId
                ]);
                
                logActivity('Trainer updated', 'trainers', $trainerId);
                $message = 'Eğitmen başarıyla güncellendi!';
                $messageType = 'success';
                $action = 'list';
            }
        }
        
        if ($formAction === 'delete' && $trainerId) {
            $stmt = $pdo->prepare("UPDATE trainers SET is_active = 0 WHERE id = ?");
            $stmt->execute([$trainerId]);
            
            logActivity('Trainer deleted', 'trainers', $trainerId);
            $message = 'Eğitmen başarıyla silindi!';
            $messageType = 'success';
            $action = 'list';
        }
        
    } catch (PDOException $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Eğitmen listesi
if ($action === 'list') {
    try {
        $stmt = $pdo->query("
            SELECT * FROM trainers 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, created_at DESC
        ");
        $trainers = $stmt->fetchAll();
    } catch (PDOException $e) {
        $trainers = [];
    }
}

// Düzenleme için eğitmen bilgilerini getir
if ($action === 'edit' && $trainerId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
        $stmt->execute([$trainerId]);
        $trainer = $stmt->fetch();
        
        if (!$trainer) {
            $action = 'list';
            $message = 'Eğitmen bulunamadı!';
            $messageType = 'danger';
        } else {
            // JSON arrays'i decode et
            $trainer['certifications_array'] = json_decode($trainer['certifications'] ?? '[]', true) ?: [];
            $trainer['specializations_array'] = json_decode($trainer['specializations'] ?? '[]', true) ?: [];
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
    <title>Eğitmenler - Prime EMS Admin</title>
    
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
                        <h1 class="h3 mb-1">Eğitmenler</h1>
                        <p class="text-muted">Eğitmen kadrosunu yönetin</p>
                    </div>
                    <div>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Yeni Eğitmen
                        </a>
                        <?php else: ?>
                        <a href="trainers.php" class="btn btn-outline-secondary">
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
                <!-- Trainers List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge-fill text-primary me-2"></i>
                            Eğitmen Listesi
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Eğitmen</th>
                                        <th>Unvan</th>
                                        <th>Deneyim</th>
                                        <th>Uzmanlık</th>
                                        <th>Durum</th>
                                        <th width="120">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trainers as $trainer): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($trainer['name']); ?></strong>
                                                    <?php if ($trainer['is_featured']): ?>
                                                    <span class="badge bg-warning ms-2">Öne Çıkan</span>
                                                    <?php endif; ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php if ($trainer['social_instagram']): ?>
                                                        <i class="bi bi-instagram me-1"></i>
                                                        <?php endif; ?>
                                                        <?php if ($trainer['social_linkedin']): ?>
                                                        <i class="bi bi-linkedin me-1"></i>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($trainer['title'] ?: '-'); ?></td>
                                        <td>
                                            <?php if ($trainer['experience_years'] > 0): ?>
                                            <span class="badge bg-success"><?php echo $trainer['experience_years']; ?> yıl</span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $specializations = json_decode($trainer['specializations'] ?? '[]', true);
                                            if (!empty($specializations)): 
                                            ?>
                                            <small>
                                                <?php echo implode(', ', array_slice($specializations, 0, 3)); ?>
                                                <?php if (count($specializations) > 3): ?>
                                                <span class="text-muted">+<?php echo count($specializations) - 3; ?> daha</span>
                                                <?php endif; ?>
                                            </small>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $trainer['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $trainer['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?action=edit&id=<?php echo $trainer['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-outline-danger" onclick="deleteTrainer(<?php echo $trainer['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($trainers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-person-badge display-4 d-block mb-2"></i>
                                                Henüz eğitmen eklenmemiş
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
                <!-- Trainer Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-<?php echo $action === 'create' ? 'plus' : 'pencil'; ?>-circle text-primary me-2"></i>
                                    <?php echo $action === 'create' ? 'Yeni Eğitmen Ekle' : 'Eğitmen Düzenle'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="<?php echo $action === 'create' ? 'create' : 'update'; ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label for="name" class="form-label">İsim Soyisim *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($trainer['name'] ?? ''); ?>" 
                                                   required maxlength="100">
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                                   value="<?php echo $trainer['sort_order'] ?? 0; ?>" min="0">
                                        </div>
                                        
                                        <div class="col-md-8">
                                            <label for="title" class="form-label">Unvan/Pozisyon</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($trainer['title'] ?? ''); ?>" 
                                                   maxlength="100" placeholder="Örn: EMS Uzmanı, Fizyoterapist">
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="experience_years" class="form-label">Deneyim (Yıl)</label>
                                            <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                                   value="<?php echo $trainer['experience_years'] ?? ''; ?>" 
                                                   min="0" max="50">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="bio" class="form-label">Biyografi</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="1000"><?php echo htmlspecialchars($trainer['bio'] ?? ''); ?></textarea>
                                            <div class="form-text">Eğitmen hakkında kısa açıklama (maksimum 1000 karakter)</div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Sertifikalar</label>
                                            <div id="certifications-container">
                                                <?php 
                                                $certifications = $trainer['certifications_array'] ?? [];
                                                if (empty($certifications)) $certifications = [''];
                                                foreach ($certifications as $cert): 
                                                ?>
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" name="certifications[]" 
                                                           value="<?php echo htmlspecialchars($cert); ?>" 
                                                           placeholder="Sertifika adı">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addCertification()">
                                                <i class="bi bi-plus me-1"></i>Sertifika Ekle
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Uzmanlık Alanları</label>
                                            <div id="specializations-container">
                                                <?php 
                                                $specializations = $trainer['specializations_array'] ?? [];
                                                if (empty($specializations)) $specializations = [''];
                                                foreach ($specializations as $spec): 
                                                ?>
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" name="specializations[]" 
                                                           value="<?php echo htmlspecialchars($spec); ?>" 
                                                           placeholder="Uzmanlık alanı">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addSpecialization()">
                                                <i class="bi bi-plus me-1"></i>Uzmanlık Ekle
                                            </button>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="social_instagram" class="form-label">Instagram</label>
                                            <div class="input-group">
                                                <span class="input-group-text">@</span>
                                                <input type="text" class="form-control" id="social_instagram" name="social_instagram" 
                                                       value="<?php echo htmlspecialchars($trainer['social_instagram'] ?? ''); ?>" 
                                                       placeholder="kullaniciadi">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="social_linkedin" class="form-label">LinkedIn</label>
                                            <input type="url" class="form-control" id="social_linkedin" name="social_linkedin" 
                                                   value="<?php echo htmlspecialchars($trainer['social_linkedin'] ?? ''); ?>" 
                                                   placeholder="https://linkedin.com/in/...">
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                                               <?php echo ($trainer['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="is_featured">
                                                            Öne Çıkan Eğitmen
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                               <?php echo ($trainer['is_active'] ?? 1) ? 'checked' : ''; ?>>
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
                                        <a href="trainers.php" class="btn btn-outline-secondary">
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
                                        Eğitmen fotoğrafı yükleyebilirsiniz
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Biyografi samimi ve profesyonel olmalı
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Sertifikaları önem sırasına göre sıralayın
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Sosyal medya hesaplarını ekleyebilirsiniz
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
                    <h5 class="modal-title">Eğitmeni Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bu eğitmeni silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
        function deleteTrainer(id) {
            const form = document.getElementById('deleteForm');
            form.action = '?id=' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function addCertification() {
            const container = document.getElementById('certifications-container');
            addFieldToContainer(container, 'certifications[]', 'Sertifika adı');
        }
        
        function addSpecialization() {
            const container = document.getElementById('specializations-container');
            addFieldToContainer(container, 'specializations[]', 'Uzmanlık alanı');
        }
        
        function addFieldToContainer(container, name, placeholder) {
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="${name}" placeholder="${placeholder}">
                <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        function removeField(btn) {
            const container = btn.closest('.input-group').parentNode;
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