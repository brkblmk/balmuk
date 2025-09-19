<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Galeri resimlerini çek (şimdilik örnek veriler)
$gallery_images = [
    [
        'id' => 1,
        'title' => 'EMS Antrenman Salonu',
        'alt' => 'Modern EMS antrenman salonu',
        'url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop',
        'category' => 'Salon'
    ],
    [
        'id' => 2,
        'title' => 'i-motion Cihazı',
        'alt' => 'Profesyonel EMS cihazı',
        'url' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=400&h=400&fit=crop',
        'category' => 'Cihaz'
    ],
    [
        'id' => 3,
        'title' => 'EMS Antrenman Alanları',
        'alt' => 'Modern antrenman alanları',
        'url' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=400&h=400&fit=crop',
        'category' => 'Salon'
    ]
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Yönetimi - Prime EMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Galeri Yönetimi</h1>
                <button class="btn btn-prime" data-bs-toggle="modal" data-bs-target="#addImageModal">
                    <i class="bi bi-plus-circle"></i> Yeni Resim Ekle
                </button>
            </div>

            <div class="row">
                <?php foreach ($gallery_images as $image): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($image['url']); ?>"
                             class="card-img-top" alt="<?php echo htmlspecialchars($image['alt']); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h6>
                            <p class="text-muted small"><?php echo htmlspecialchars($image['category']); ?></p>
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Düzenle
                                </button>
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Sil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div class="modal fade" id="addImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Resim Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Resim Başlığı</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" required>
                                <option value="">Seçin</option>
                                <option value="salon">Salon</option>
                                <option value="cihaz">Cihaz</option>
                                <option value="antrenman">Antrenman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resim URL</label>
                            <input type="url" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alt Metin</label>
                            <input type="text" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-prime">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>