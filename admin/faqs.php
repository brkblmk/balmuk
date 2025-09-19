<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin session kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// CRUD İşlemleri
$action = $_GET['action'] ?? 'list';
$faq_id = $_GET['id'] ?? null;

// CSRF Token oluştur
$csrf_token = SecurityUtils::generateCSRFToken();

// FAQ Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF kontrolü
    if (!SecurityUtils::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Güvenlik hatası!';
    } else {
        $action = $_POST['action'];

        if ($action === 'add') {
            // FAQ Ekleme
            $question = SecurityUtils::sanitizeInput($_POST['question'], 'html');
            $answer = SecurityUtils::sanitizeInput($_POST['answer'], 'html');
            $category = SecurityUtils::sanitizeInput($_POST['category'], 'string');
            $is_published = isset($_POST['is_published']) ? 1 : 0;

            if (empty($question) || empty($answer)) {
                $error = 'Soru ve cevap alanları zorunludur!';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, is_published) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$question, $answer, $category, $is_published]);
                    $success = 'FAQ başarıyla eklendi!';
                } catch (PDOException $e) {
                    $error = 'FAQ eklenirken hata oluştu: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'edit' && $faq_id) {
            // FAQ Düzenleme
            $question = SecurityUtils::sanitizeInput($_POST['question'], 'html');
            $answer = SecurityUtils::sanitizeInput($_POST['answer'], 'html');
            $category = SecurityUtils::sanitizeInput($_POST['category'], 'string');
            $is_published = isset($_POST['is_published']) ? 1 : 0;

            if (empty($question) || empty($answer)) {
                $error = 'Soru ve cevap alanları zorunludur!';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, is_published = ? WHERE id = ?");
                    $stmt->execute([$question, $answer, $category, $is_published, $faq_id]);
                    $success = 'FAQ başarıyla güncellendi!';
                } catch (PDOException $e) {
                    $error = 'FAQ güncellenirken hata oluştu: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete' && $faq_id) {
            // FAQ Silme
            try {
                $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
                $stmt->execute([$faq_id]);
                $success = 'FAQ başarıyla silindi!';
                header('Location: faqs.php');
                exit;
            } catch (PDOException $e) {
                $error = 'FAQ silinirken hata oluştu: ' . $e->getMessage();
            }
        }
    }
}

// FAQ Getir (Düzenleme için)
if ($action === 'edit' && $faq_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
        $stmt->execute([$faq_id]);
        $faq = $stmt->fetch();
        if (!$faq) {
            $error = 'FAQ bulunamadı!';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = 'FAQ yüklenirken hata oluştu: ' . $e->getMessage();
        $action = 'list';
    }
}

// FAQ Listesi
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY category ASC, created_at DESC");
    $faqs = $stmt->fetchAll();

    // Kategorilere göre grupla
    $faqs_by_category = [];
    foreach ($faqs as $faq) {
        $faqs_by_category[$faq['category']][] = $faq;
    }
} catch (PDOException $e) {
    $error = 'FAQ listesi yüklenirken hata oluştu: ' . $e->getMessage();
    $faqs_by_category = [];
}

// Sayfa başlığı
$page_title = match($action) {
    'add' => 'Yeni FAQ Ekle',
    'edit' => 'FAQ Düzenle',
    default => 'SSS Yönetimi'
};
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Prime EMS Admin</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">

    <style>
        .faq-item {
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .category-header {
            background: var(--prime-gradient);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                                    </ol>
                                </nav>
                            </div>
                            <?php if ($action === 'list'): ?>
                            <a href="faqs.php?action=add" class="btn btn-prime">
                                <i class="bi bi-plus-circle me-2"></i>Yeni FAQ Ekle
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                <!-- FAQ List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">SSS Listesi</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($faqs_by_category)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 text-muted">Henüz FAQ eklenmemiş</h5>
                                    <p class="text-muted">İlk SSS'yi eklemek için yukarıdaki butona tıklayın.</p>
                                    <a href="faqs.php?action=add" class="btn btn-prime">İlk FAQ'yi Ekle</a>
                                </div>
                                <?php else: ?>
                                <?php foreach ($faqs_by_category as $category => $category_faqs): ?>
                                <div class="mb-4">
                                    <h6 class="category-header">
                                        <i class="bi bi-folder me-2"></i><?php echo htmlspecialchars($category); ?>
                                        <span class="badge bg-white text-dark ms-2"><?php echo count($category_faqs); ?> soru</span>
                                    </h6>

                                    <div class="row">
                                        <?php foreach ($category_faqs as $faq): ?>
                                        <div class="col-lg-6 mb-3">
                                            <div class="card faq-item h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="badge <?php echo $faq['is_published'] ? 'bg-success' : 'bg-secondary'; ?> status-badge">
                                                            <?php echo $faq['is_published'] ? 'Yayınlandı' : 'Taslak'; ?>
                                                        </span>
                                                        <small class="text-muted">
                                                            <?php echo date('d.m.Y', strtotime($faq['created_at'])); ?>
                                                        </small>
                                                    </div>

                                                    <h6 class="card-title mb-2" title="<?php echo htmlspecialchars($faq['question']); ?>">
                                                        <?php echo htmlspecialchars(substr($faq['question'], 0, 80)) . (strlen($faq['question']) > 80 ? '...' : ''); ?>
                                                    </h6>

                                                    <p class="card-text text-muted small mb-3" title="<?php echo htmlspecialchars(strip_tags($faq['answer'])); ?>">
                                                        <?php echo htmlspecialchars(substr(strip_tags($faq['answer']), 0, 100)) . (strlen(strip_tags($faq['answer'])) > 100 ? '...' : ''); ?>
                                                    </p>

                                                    <div class="d-flex gap-2">
                                                        <a href="faqs.php?action=edit&id=<?php echo $faq['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i> Düzenle
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteFAQ(<?php echo $faq['id']; ?>, '<?php echo htmlspecialchars(addslashes($faq['question'])); ?>')">
                                                            <i class="bi bi-trash"></i> Sil
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni FAQ Ekle' : 'FAQ Düzenle'; ?></h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="faqs.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                                    <?php if ($action === 'edit'): ?>
                                    <input type="hidden" name="id" value="<?php echo $faq_id; ?>">
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label for="question" class="form-label">Soru *</label>
                                        <input type="text" class="form-control" id="question" name="question"
                                               value="<?php echo $action === 'edit' ? htmlspecialchars($faq['question']) : ''; ?>" required maxlength="500">
                                        <div class="form-text">Maksimum 500 karakter</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="answer" class="form-label">Cevap *</label>
                                        <textarea class="form-control" id="answer" name="answer" rows="6" required><?php echo $action === 'edit' ? htmlspecialchars($faq['answer']) : ''; ?></textarea>
                                        <div class="form-text">HTML etiketleri kullanabilirsiniz</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Kategori</label>
                                                <select class="form-select" id="category" name="category">
                                                    <option value="Genel Bilgiler" <?php echo ($action === 'edit' && $faq['category'] === 'Genel Bilgiler') ? 'selected' : ''; ?>>Genel Bilgiler</option>
                                                    <option value="Güvenlik ve Uygulama" <?php echo ($action === 'edit' && $faq['category'] === 'Güvenlik ve Uygulama') ? 'selected' : ''; ?>>Güvenlik ve Uygulama</option>
                                                    <option value="Teknik Detaylar" <?php echo ($action === 'edit' && $faq['category'] === 'Teknik Detaylar') ? 'selected' : ''; ?>>Teknik Detaylar</option>
                                                    <option value="Kullanım Koşulları" <?php echo ($action === 'edit' && $faq['category'] === 'Kullanım Koşulları') ? 'selected' : ''; ?>>Kullanım Koşulları</option>
                                                    <option value="Sonuçlar ve Etkililik" <?php echo ($action === 'edit' && $faq['category'] === 'Sonuçlar ve Etkililik') ? 'selected' : ''; ?>>Sonuçlar ve Etkililik</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                                                           <?php echo ($action === 'edit' && $faq['is_published']) ? 'checked' : ($action === 'add' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="is_published">
                                                        Yayınla
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-prime">
                                            <i class="bi bi-check-circle me-2"></i><?php echo $action === 'add' ? 'FAQ Ekle' : 'FAQ Güncelle'; ?>
                                        </button>
                                        <a href="faqs.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-2"></i>İptal
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Preview -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Önizleme</h6>
                            </div>
                            <div class="card-body">
                                <div id="preview-question" class="mb-2 fw-bold"></div>
                                <div id="preview-answer" class="text-muted small"></div>
                            </div>
                        </div>

                        <!-- Tips -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">İpuçları</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small mb-0">
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Soru net ve anlaşılır olmalı</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Cevap detaylı ama öz olmalı</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>HTML etiketleri kullanabilirsiniz</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Yayınlamadan önce kontrol edin</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Delete confirmation
        function deleteFAQ(id, question) {
            if (confirm(`"${question}" sorusunu silmek istediğinizden emin misiniz?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'faqs.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?php echo $csrf_token; ?>';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Live preview
        document.getElementById('question')?.addEventListener('input', function() {
            document.getElementById('preview-question').textContent = this.value || 'Soru önizlemesi...';
        });

        document.getElementById('answer')?.addEventListener('input', function() {
            document.getElementById('preview-answer').textContent = this.value || 'Cevap önizlemesi...';
        });

        // Initialize preview
        window.addEventListener('DOMContentLoaded', function() {
            const questionInput = document.getElementById('question');
            const answerInput = document.getElementById('answer');

            if (questionInput) {
                document.getElementById('preview-question').textContent = questionInput.value || 'Soru önizlemesi...';
            }
            if (answerInput) {
                document.getElementById('preview-answer').textContent = answerInput.value || 'Cevap önizlemesi...';
            }
        });
    </script>
</body>
</html>