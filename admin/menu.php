<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Mevcut menü öğelerini çek (şimdilik örnek veriler)
$menu_items = [
    [
        'id' => 1,
        'title' => 'Ana Sayfa',
        'url' => 'index.php',
        'icon' => 'bi-house',
        'order' => 1,
        'is_active' => 1
    ],
    [
        'id' => 2,
        'title' => 'Hakkımızda',
        'url' => 'index.php#about',
        'icon' => 'bi-info-circle',
        'order' => 2,
        'is_active' => 1
    ],
    [
        'id' => 3,
        'title' => 'Hizmetler',
        'url' => 'index.php#services',
        'icon' => 'bi-grid-3x3',
        'order' => 3,
        'is_active' => 1
    ],
    [
        'id' => 4,
        'title' => 'Cihazlar',
        'url' => 'index.php#devices',
        'icon' => 'bi-cpu',
        'order' => 4,
        'is_active' => 1
    ],
    [
        'id' => 5,
        'title' => 'Kampanyalar',
        'url' => 'index.php#campaigns',
        'icon' => 'bi-megaphone',
        'order' => 5,
        'is_active' => 1
    ],
    [
        'id' => 6,
        'title' => 'SSS',
        'url' => 'sss.php',
        'icon' => 'bi-question-circle',
        'order' => 6,
        'is_active' => 1
    ],
    [
        'id' => 7,
        'title' => 'İletişim',
        'url' => 'index.php#contact',
        'icon' => 'bi-envelope',
        'order' => 7,
        'is_active' => 1
    ]
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menü Yönetimi - Prime EMS Admin</title>
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
                <h1 class="h3 mb-0">Menü Yönetimi</h1>
                <button class="btn btn-prime" data-bs-toggle="modal" data-bs-target="#addMenuModal">
                    <i class="bi bi-plus-circle"></i> Yeni Menü Öğesi
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Menü Öğeleri</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sıra</th>
                                    <th>Başlık</th>
                                    <th>URL</th>
                                    <th>İkon</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menu_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['order']; ?></td>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo htmlspecialchars($item['url']); ?></td>
                                    <td><i class="bi <?php echo $item['icon']; ?>"></i></td>
                                    <td>
                                        <span class="badge bg-<?php echo $item['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $item['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary">
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary">
                                                <i class="bi bi-arrow-down"></i>
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

    <!-- Add Menu Item Modal -->
    <div class="modal fade" id="addMenuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Menü Öğesi Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="menuForm">
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="url" placeholder="index.php#section" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İkon</label>
                            <select class="form-select" name="icon" required>
                                <option value="bi-house">Ana Sayfa</option>
                                <option value="bi-info-circle">Bilgi</option>
                                <option value="bi-grid-3x3">Grid</option>
                                <option value="bi-cpu">CPU</option>
                                <option value="bi-megaphone">Megafon</option>
                                <option value="bi-question-circle">Soru</option>
                                <option value="bi-envelope">E-posta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" class="form-control" name="order" min="1" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Aktif</label>
                            </div>
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