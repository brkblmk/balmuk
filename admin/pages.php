<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Sayfa verilerini çek (şimdilik örnek veriler)
$pages = [
    [
        'id' => 1,
        'title' => 'Ana Sayfa',
        'slug' => 'index',
        'status' => 'published',
        'last_modified' => '2024-01-15 10:30:00',
        'author' => 'Admin'
    ],
    [
        'id' => 2,
        'title' => 'SSS',
        'slug' => 'sss',
        'status' => 'published',
        'last_modified' => '2024-01-14 15:45:00',
        'author' => 'Admin'
    ],
    [
        'id' => 3,
        'title' => 'Blog',
        'slug' => 'blog',
        'status' => 'published',
        'last_modified' => '2024-01-13 09:20:00',
        'author' => 'Admin'
    ]
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sayfa Yönetimi - Prime EMS Admin</title>
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
                <h1 class="h3 mb-0">Sayfa Yönetimi</h1>
                <button class="btn btn-prime" data-bs-toggle="modal" data-bs-target="#addPageModal">
                    <i class="bi bi-plus-circle"></i> Yeni Sayfa
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Sayfa Başlığı</th>
                                            <th>Slug</th>
                                            <th>Durum</th>
                                            <th>Son Düzenleme</th>
                                            <th>Yazar</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pages as $page): ?>
                                        <tr>
                                            <td><?php echo $page['id']; ?></td>
                                            <td><?php echo htmlspecialchars($page['title']); ?></td>
                                            <td><?php echo htmlspecialchars($page['slug']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $page['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo $page['status'] === 'published' ? 'Yayınlandı' : 'Taslak'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($page['last_modified'])); ?></td>
                                            <td><?php echo htmlspecialchars($page['author']); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil"></i> Düzenle
                                                    </button>
                                                    <button class="btn btn-outline-info">
                                                        <i class="bi bi-eye"></i> Önizle
                                                    </button>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Sil
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Page Modal -->
    <div class="modal fade" id="addPageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Sayfa Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="pageForm">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Sayfa Başlığı</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" class="form-control" name="slug" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" name="content" rows="10" id="pageContent"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Meta Başlık</label>
                                    <input type="text" class="form-control" name="meta_title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Meta Açıklama</label>
                                    <input type="text" class="form-control" name="meta_description">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Şablon</label>
                                    <select class="form-select" name="template">
                                        <option value="default">Varsayılan</option>
                                        <option value="full-width">Tam Genişlik</option>
                                        <option value="sidebar">Kenar Çubuğu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Durum</label>
                                    <select class="form-select" name="status">
                                        <option value="draft">Taslak</option>
                                        <option value="published">Yayınla</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-prime">Sayfayı Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>