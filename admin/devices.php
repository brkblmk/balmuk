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

// Görsel yükleme dizini
$upload_dir = '../assets/images/devices/';

// Görsel yükleme fonksiyonu
function uploadImage($file, $prefix = 'device') {
    global $upload_dir;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Sadece JPEG, PNG ve WebP formatları desteklenir.'];
    }

    if ($file['size'] > $max_size) {
        return ['error' => 'Dosya boyutu 5MB\'dan büyük olamaz.'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => 'assets/images/devices/' . $filename];
    } else {
        return ['error' => 'Dosya yüklenirken hata oluştu.'];
    }
}

// Cihaz ekleme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $slug = strtolower(str_replace(' ', '-', $name));
        $device_type = $_POST['device_type'] ?? 'other';
        $model = trim($_POST['model'] ?? '');
        $manufacturer = trim($_POST['manufacturer'] ?? 'i-motion');
        $short_description = trim($_POST['short_description'] ?? '');
        $long_description = trim($_POST['long_description'] ?? '');
        $features = json_encode(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? ''))));
        $specifications = json_encode(array_filter(array_map('trim', explode("\n", $_POST['specifications'] ?? ''))));
        $certifications = json_encode(array_filter(array_map('trim', explode("\n", $_POST['certifications'] ?? ''))));
        $usage_areas = json_encode(array_filter(array_map('trim', explode("\n", $_POST['usage_areas'] ?? ''))));
        $benefits = json_encode(array_filter(array_map('trim', explode("\n", $_POST['benefits'] ?? ''))));
        $exercise_programs = json_encode(array_filter(array_map('trim', explode("\n", $_POST['exercise_programs'] ?? ''))));
        $technical_documents = trim($_POST['technical_documents'] ?? '');
        $warranty_info = trim($_POST['warranty_info'] ?? '');
        $price_range = trim($_POST['price_range'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 1);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $seo_keywords = trim($_POST['seo_keywords'] ?? '');

        // Ana görsel yükleme
        $main_image = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
            $upload_result = uploadImage($_FILES['main_image'], 'main');
            if (isset($upload_result['success'])) {
                $main_image = $upload_result['filename'];
            } else {
                $error = $upload_result['error'];
            }
        }

        // Galeri görselleri yükleme
        $gallery_images = [];
        if (isset($_FILES['gallery_images'])) {
            foreach ($_FILES['gallery_images']['name'] as $key => $filename) {
                if ($_FILES['gallery_images']['error'][$key] === 0) {
                    $file = [
                        'name' => $_FILES['gallery_images']['name'][$key],
                        'type' => $_FILES['gallery_images']['type'][$key],
                        'tmp_name' => $_FILES['gallery_images']['tmp_name'][$key],
                        'error' => $_FILES['gallery_images']['error'][$key],
                        'size' => $_FILES['gallery_images']['size'][$key]
                    ];
                    $upload_result = uploadImage($file, 'gallery');
                    if (isset($upload_result['success'])) {
                        $gallery_images[] = $upload_result['filename'];
                    }
                }
            }
        }

        if (!$error) {
            try {
                if ($action === 'add') {
                    // Cihaz türü kontrolü - aynı türde ikinci cihaz eklenmesini engelle
                    if ($device_type === 'i-motion' && $i_motion_count >= 1) {
                        $error = 'Sadece 1 adet i-motion cihazı olabilir. Önce mevcut i-motion cihazını silin.';
                    } elseif ($device_type === 'i-model' && $i_model_count >= 1) {
                        $error = 'Sadece 1 adet i-model cihazı olabilir. Önce mevcut i-model cihazını silin.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO ems_devices (
                            name, slug, device_type, model, manufacturer, short_description, long_description,
                            features, specifications, certifications, usage_areas, benefits, exercise_programs, technical_documents,
                            warranty_info, price_range, main_image, gallery_images, capacity, is_active, is_featured,
                            sort_order, seo_title, seo_description, seo_keywords
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $name, $slug, $device_type, $model, $manufacturer, $short_description, $long_description,
                            $features, $specifications, $certifications, $usage_areas, $benefits, $exercise_programs, $technical_documents,
                            $warranty_info, $price_range, $main_image, json_encode($gallery_images), $capacity, $is_active, $is_featured,
                            $sort_order, $seo_title, $seo_description, $seo_keywords
                        ]);
                        $message = 'Cihaz başarıyla eklendi!';
                        logActivity('create', 'ems_devices', $pdo->lastInsertId());
                    }
                } else {
                    $id = $_POST['id'] ?? 0;

                    // Mevcut ana görseli kontrol et
                    $stmt = $pdo->prepare("SELECT main_image FROM ems_devices WHERE id = ?");
                    $stmt->execute([$id]);
                    $current_image = $stmt->fetchColumn();

                    if (!$main_image && $current_image) {
                        $main_image = $current_image;
                    }

                    $stmt = $pdo->prepare("UPDATE ems_devices SET
                        name = ?, slug = ?, device_type = ?, model = ?, manufacturer = ?, short_description = ?, long_description = ?,
                        features = ?, specifications = ?, certifications = ?, usage_areas = ?, benefits = ?, exercise_programs = ?, technical_documents = ?,
                        warranty_info = ?, price_range = ?, main_image = ?, gallery_images = ?, capacity = ?, is_active = ?, is_featured = ?,
                        sort_order = ?, seo_title = ?, seo_description = ?, seo_keywords = ? WHERE id = ?");
                    $stmt->execute([
                        $name, $slug, $device_type, $model, $manufacturer, $short_description, $long_description,
                        $features, $specifications, $certifications, $usage_areas, $benefits, $exercise_programs, $technical_documents,
                        $warranty_info, $price_range, $main_image, json_encode($gallery_images), $capacity, $is_active, $is_featured,
                        $sort_order, $seo_title, $seo_description, $seo_keywords, $id
                    ]);
                    $message = 'Cihaz başarıyla güncellendi!';
                    logActivity('update', 'ems_devices', $id);
                }
                header('Location: devices.php?message=' . urlencode($message));
                exit;
            } catch (PDOException $e) {
                $error = 'Hata: ' . $e->getMessage();
            }
        }
    }
}

// Cihaz silme
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM ems_devices WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Cihaz başarıyla silindi!';
        logActivity('delete', 'ems_devices', $id);
        header('Location: devices.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Cihaz düzenleme için veri çekme
$editData = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM ems_devices WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
    if ($editData) {
        // JSON verilerini text formatına çevir
        $json_fields = ['features', 'specifications', 'certifications', 'usage_areas', 'benefits', 'exercise_programs', 'gallery_images'];
        foreach ($json_fields as $field) {
            if ($editData[$field]) {
                $decoded = json_decode($editData[$field], true);
                if (is_array($decoded)) {
                    $editData[$field . '_text'] = implode("\n", $decoded);
                }
            }
        }
    }
}

// Cihazları listele
$devices = $pdo->query("SELECT * FROM ems_devices ORDER BY sort_order ASC, created_at DESC")->fetchAll();

// Mevcut cihaz sayılarını kontrol et
$i_motion_count = 0;
$i_model_count = 0;
foreach ($devices as $device) {
    if ($device['device_type'] === 'i-motion') {
        $i_motion_count++;
    } elseif ($device['device_type'] === 'i-model') {
        $i_model_count++;
    }
}

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
    <title>EMS Cihazları - Prime EMS Admin</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">

    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- Custom Styles -->
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border: 2px solid #dee2e6;
            border-radius: 5px;
        }
        .gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .gallery-preview img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        .form-section {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .form-section h5 {
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Cihazlar</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Cihaz
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
                <!-- Cihaz Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni EMS Cihazı Ekle' : 'EMS Cihazı Düzenle'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                            <?php endif; ?>

                            <!-- Temel Bilgiler -->
                            <div class="form-section">
                                <h5><i class="bi bi-info-circle me-2"></i>Temel Bilgiler</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Cihaz Adı *</label>
                                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($editData['name'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Cihaz Türü *</label>
                                        <select name="device_type" class="form-select" required>
                                            <option value="i-motion" <?php echo ($editData['device_type'] ?? '') === 'i-motion' ? 'selected' : ''; ?> <?php echo ($i_motion_count >= 1 && !$editData) ? 'disabled' : ''; ?>>
                                                i-motion <?php echo ($i_motion_count >= 1 && !$editData) ? '(Mevcut)' : ''; ?>
                                            </option>
                                            <option value="i-model" <?php echo ($editData['device_type'] ?? '') === 'i-model' ? 'selected' : ''; ?> <?php echo ($i_model_count >= 1 && !$editData) ? 'disabled' : ''; ?>>
                                                i-model <?php echo ($i_model_count >= 1 && !$editData) ? '(Mevcut)' : ''; ?>
                                            </option>
                                        </select>
                                        <?php if ($i_motion_count >= 1 && $i_model_count >= 1 && !$editData): ?>
                                            <small class="form-text text-warning">Sadece 1 i-motion ve 1 i-model cihazı olabilir. Yeni cihaz eklemek için mevcut cihazlardan birini silin.</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" class="form-control" value="<?php echo htmlspecialchars($editData['model'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Üretici Firma</label>
                                        <input type="text" name="manufacturer" class="form-control" value="<?php echo htmlspecialchars($editData['manufacturer'] ?? 'i-motion'); ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Kapasite</label>
                                        <input type="number" name="capacity" class="form-control" min="1" value="<?php echo $editData['capacity'] ?? 1; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Fiyat Aralığı</label>
                                        <input type="text" name="price_range" class="form-control" placeholder="₺10.000 - ₺25.000" value="<?php echo htmlspecialchars($editData['price_range'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Görseller -->
                            <div class="form-section">
                                <h5><i class="bi bi-images me-2"></i>Görseller</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ana Görsel</label>
                                        <input type="file" name="main_image" class="form-control" accept="image/*">
                                        <small class="form-text text-muted">JPEG, PNG, WebP formatları desteklenir. Max 5MB.</small>
                                        <?php if (!empty($editData['main_image'])): ?>
                                        <div class="mt-2">
                                            <img src="../<?php echo htmlspecialchars($editData['main_image']); ?>" alt="Mevcut görsel" class="image-preview">
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Galeri Görselleri</label>
                                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                        <small class="form-text text-muted">Birden fazla görsel seçebilirsiniz.</small>
                                        <?php if (!empty($editData['gallery_images'])): ?>
                                        <div class="gallery-preview">
                                            <?php foreach (json_decode($editData['gallery_images'], true) as $image): ?>
                                            <img src="../<?php echo htmlspecialchars($image); ?>" alt="Galeri görseli">
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- İçerik -->
                            <div class="form-section">
                                <h5><i class="bi bi-file-text me-2"></i>İçerik</h5>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Kısa Açıklama</label>
                                        <textarea name="short_description" class="form-control simple-editor" rows="2" maxlength="255"><?php echo htmlspecialchars($editData['short_description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">Detaylı Açıklama</label>
                                        <textarea name="long_description" id="long_description" class="form-control" rows="6"><?php echo htmlspecialchars($editData['long_description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Özellikler -->
                            <div class="form-section">
                                <h5><i class="bi bi-list-check me-2"></i>Özellikler</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                                        <textarea name="features" class="form-control simple-editor" rows="4" placeholder="Kablosuz teknoloji&#10;16 kas grubu&#10;Kişiye özel programlama"><?php echo htmlspecialchars($editData['features_text'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teknik Özellikler (Her satıra bir özellik)</label>
                                        <textarea name="specifications" class="form-control simple-editor" rows="4" placeholder="Frekans: 1-120 Hz&#10;Program sayısı: 50+&#10;Ağırlık: 15 kg"><?php echo htmlspecialchars($editData['specifications_text'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sertifikalar (Her satıra bir sertifika)</label>
                                        <textarea name="certifications" class="form-control simple-editor" rows="3" placeholder="CE Sertifikası&#10;ISO 13485&#10;TÜV Sertifikası"><?php echo htmlspecialchars($editData['certifications_text'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kullanım Alanları (Her satıra bir alan)</label>
                                        <textarea name="usage_areas" class="form-control simple-editor" rows="3" placeholder="Fitness salonları&#10;Spor kulüpleri&#10;Ev kullanımı"><?php echo htmlspecialchars($editData['usage_areas_text'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">Faydalar (Her satıra bir fayda)</label>
                                        <textarea name="benefits" class="form-control simple-editor" rows="3" placeholder="Kas kütlesi artışı&#10;Yağ yakımı&#10;Performans iyileştirmesi"><?php echo htmlspecialchars($editData['benefits_text'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">Egzersiz Programları (Her satıra bir program)</label>
                                        <textarea name="exercise_programs" class="form-control simple-editor" rows="4" placeholder="Yağ yakımı antrenmanları&#10;Kas geliştirme programları&#10;Rehabilitasyon seansları&#10;Performans artırma"><?php echo htmlspecialchars($editData['exercise_programs_text'] ?? ''); ?></textarea>
                                        <small class="form-text text-muted">Cihazın sunduğu egzersiz programlarını listeleyin.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Ek Bilgiler -->
                            <div class="form-section">
                                <h5><i class="bi bi-plus-circle me-2"></i>Ek Bilgiler</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teknik Dökümanlar</label>
                                        <textarea name="technical_documents" class="form-control simple-editor" rows="2" placeholder="Kullanım kılavuzu bağlantısı&#10;Teknik spesifikasyonlar"><?php echo htmlspecialchars($editData['technical_documents'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Garanti Bilgileri</label>
                                        <textarea name="warranty_info" class="form-control simple-editor" rows="2" placeholder="2 yıl üretici garantisi&#10;Ücretsiz teknik servis"><?php echo htmlspecialchars($editData['warranty_info'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO -->
                            <div class="form-section">
                                <h5><i class="bi bi-search me-2"></i>SEO Ayarları</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SEO Başlığı</label>
                                        <input type="text" name="seo_title" class="form-control" value="<?php echo htmlspecialchars($editData['seo_title'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SEO Anahtar Kelimeler</label>
                                        <input type="text" name="seo_keywords" class="form-control" placeholder="ems, elektrik stimülasyonu, fitness" value="<?php echo htmlspecialchars($editData['seo_keywords'] ?? ''); ?>">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">SEO Açıklaması</label>
                                        <textarea name="seo_description" class="form-control" rows="2" maxlength="160"><?php echo htmlspecialchars($editData['seo_description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Ayarlar -->
                            <div class="form-section">
                                <h5><i class="bi bi-gear me-2"></i>Ayarlar</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Sıralama</label>
                                        <input type="number" name="sort_order" class="form-control" value="<?php echo $editData['sort_order'] ?? 0; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo ($editData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">Aktif</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" <?php echo ($editData['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Kaydet
                                </button>
                                <a href="devices.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Cihaz Listesi -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">EMS Cihazları</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cihaz ara..." style="width: 200px;">
                            <select id="typeFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">Tüm Tipler</option>
                                <option value="i-motion">i-motion (<?php echo $i_motion_count; ?>/1)</option>
                                <option value="i-model">i-model (<?php echo $i_model_count; ?>/1)</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="devicesTable">
                                <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th width="80">Görsel</th>
                                        <th>Cihaz Adı</th>
                                        <th>Tür</th>
                                        <th>Model</th>
                                        <th>Kapasite</th>
                                        <th>Üretici</th>
                                        <th width="100">Durum</th>
                                        <th width="100">Öne Çıkan</th>
                                        <th width="100">Sıra</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($devices as $device): ?>
                                    <tr>
                                        <td><?php echo $device['id']; ?></td>
                                        <td>
                                            <?php if (!empty($device['main_image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($device['main_image']); ?>" alt="<?php echo htmlspecialchars($device['name']); ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($device['name']); ?></strong>
                                            <?php if (!empty($device['short_description'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($device['short_description'], 0, 50)) . (strlen($device['short_description']) > 50 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($device['device_type'] ?? 'other'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($device['model'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $device['capacity']; ?> Kişi</span>
                                        </td>
                                        <td><?php echo htmlspecialchars($device['manufacturer'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($device['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($device['is_featured']): ?>
                                            <span class="badge bg-warning"><i class="bi bi-star-fill"></i> Öne Çıkan</span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $device['sort_order']; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $device['id']; ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-info" title="Detaylar" onclick="showDeviceDetails(<?php echo $device['id']; ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="?action=delete&id=<?php echo $device['id']; ?>" class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu cihazı silmek istediğinizden emin misiniz?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($devices)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-device-hdd text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">Henüz cihaz eklenmemiş</h5>
                            <p class="text-muted">İlk EMS cihazınızı eklemek için yukarıdaki "Yeni Cihaz" butonuna tıklayın.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>

    <!-- TinyMCE Rich Text Editor -->
    <script>
        // Basit textarea'lar için TinyMCE
        tinymce.init({
            selector: 'textarea.simple-editor',
            height: 120,
            menubar: false,
            plugins: ['lists', 'link', 'charmap'],
            toolbar: 'bold italic | bullist numlist | link | removeformat',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });

        // Zengin editör için TinyMCE
        tinymce.init({
            selector: '#long_description',
            height: 300,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });

        // Arama ve filtreleme fonksiyonları
        document.getElementById('searchInput').addEventListener('keyup', filterDevices);
        document.getElementById('typeFilter').addEventListener('change', filterDevices);

        function filterDevices() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
            const table = document.getElementById('devicesTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let showRow = true;

                // Arama filtresi
                if (searchTerm) {
                    const deviceName = cells[2].textContent.toLowerCase();
                    const model = cells[4].textContent.toLowerCase();
                    const manufacturer = cells[6].textContent.toLowerCase();

                    if (!deviceName.includes(searchTerm) && !model.includes(searchTerm) && !manufacturer.includes(searchTerm)) {
                        showRow = false;
                    }
                }

                // Tip filtresi
                if (typeFilter && showRow) {
                    const deviceType = cells[3].textContent.toLowerCase().trim();
                    if (deviceType !== typeFilter) {
                        showRow = false;
                    }
                }

                rows[i].style.display = showRow ? '' : 'none';
            }
        }

        // Cihaz detaylarını gösterme fonksiyonu
        function showDeviceDetails(deviceId) {
            // Bu kısım AJAX ile cihaz detaylarını çekmek için kullanılabilir
            // Şimdilik basit bir alert gösterelim
            fetch(`api/get-device-details.php?id=${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showDeviceModal(data.device);
                    } else {
                        alert('Cihaz detayları yüklenirken hata oluştu.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Cihaz detayları yüklenirken hata oluştu.');
                });
        }

        function showDeviceModal(device) {
            const modalHtml = `
                <div class="modal fade" id="deviceModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${device.name}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        ${device.main_image ? `<img src="../${device.main_image}" class="img-fluid rounded mb-3" alt="${device.name}">` : '<div class="bg-light rounded p-5 text-center mb-3"><i class="bi bi-image text-muted" style="font-size: 3rem;"></i></div>'}

                                        <h6>Temel Bilgiler</h6>
                                        <table class="table table-sm">
                                            <tr><td><strong>Tür:</strong></td><td>${device.device_type}</td></tr>
                                            <tr><td><strong>Model:</strong></td><td>${device.model || '-'}</td></tr>
                                            <tr><td><strong>Üretici:</strong></td><td>${device.manufacturer}</td></tr>
                                            <tr><td><strong>Kapasite:</strong></td><td>${device.capacity} Kişi</td></tr>
                                            <tr><td><strong>Fiyat:</strong></td><td>${device.price_range || '-'}</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Özellikler</h6>
                                        <ul class="list-unstyled">
                                            ${device.features ? JSON.parse(device.features).map(f => `<li><i class="bi bi-check-circle text-success me-2"></i>${f}</li>`).join('') : '<li class="text-muted">Özellik bilgisi yok</li>'}
                                        </ul>

                                        <h6>Teknik Özellikler</h6>
                                        <ul class="list-unstyled">
                                            ${device.specifications ? JSON.parse(device.specifications).map(s => `<li><i class="bi bi-gear text-primary me-2"></i>${s}</li>`).join('') : '<li class="text-muted">Teknik özellik bilgisi yok</li>'}
                                        </ul>

                                        <h6>Egzersiz Programları</h6>
                                        <ul class="list-unstyled">
                                            ${device.exercise_programs ? JSON.parse(device.exercise_programs).map(p => `<li><i class="bi bi-activity text-info me-2"></i>${p}</li>`).join('') : '<li class="text-muted">Egzersiz programı bilgisi yok</li>'}
                                        </ul>
                                    </div>
                                </div>

                                ${device.long_description ? `
                                <div class="mt-3">
                                    <h6>Detaylı Açıklama</h6>
                                    <div>${device.long_description}</div>
                                </div>
                                ` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                <a href="?action=edit&id=${device.id}" class="btn btn-primary">Düzenle</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Mevcut modal varsa kaldır
            const existingModal = document.getElementById('deviceModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Yeni modal ekle
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Modal'ı göster
            const modal = new bootstrap.Modal(document.getElementById('deviceModal'));
            modal.show();
        }

        // Sayfa yüklendiğinde TinyMCE'yi başlat
        document.addEventListener('DOMContentLoaded', function() {
            // TinyMCE zaten başlatıldı
            console.log('EMS Devices Admin Panel loaded successfully');
        });
    </script>
</body>
</html>