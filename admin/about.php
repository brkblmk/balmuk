<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// About section verilerini çek
try {
    $stmt = $pdo->prepare("SELECT * FROM about_section WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $about = $stmt->fetch() ?: [];
} catch (PDOException $e) {
    $about = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hakkımızda Yönetimi - Prime EMS Admin</title>
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
                <h1 class="h3 mb-0">Hakkımızda Yönetimi</h1>
                <button class="btn btn-prime" id="saveBtn">
                    <i class="bi bi-check-circle"></i> Kaydet
                </button>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>İçerik Düzenleme</h5>
                        </div>
                        <div class="card-body">
                            <form id="aboutForm">
                                <div class="mb-3">
                                    <label class="form-label">Başlık</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                           value="<?php echo htmlspecialchars($about['title'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alt Başlık</label>
                                    <input type="text" class="form-control" id="subtitle" name="subtitle"
                                           value="<?php echo htmlspecialchars($about['subtitle'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">İçerik</label>
                                    <textarea class="form-control" id="content" name="content" rows="8" required><?php
                                        echo htmlspecialchars($about['content'] ?? '');
                                    ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Görev (Mission)</label>
                                    <textarea class="form-control" id="mission" name="mission" rows="4"><?php
                                        echo htmlspecialchars($about['mission'] ?? '');
                                    ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Vizyon (Vision)</label>
                                    <textarea class="form-control" id="vision" name="vision" rows="4"><?php
                                        echo htmlspecialchars($about['vision'] ?? '');
                                    ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Değerler (JSON Format)</label>
                                    <textarea class="form-control" id="values" name="values" rows="4"><?php
                                        echo htmlspecialchars($about['values'] ?? '');
                                    ?></textarea>
                                    <small class="form-text text-muted">JSON formatında: ["Değer 1", "Değer 2", ...]</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Özellikler (JSON Format)</label>
                                    <textarea class="form-control" id="features" name="features" rows="4"><?php
                                        echo htmlspecialchars($about['features'] ?? '');
                                    ?></textarea>
                                    <small class="form-text text-muted">JSON formatında: ["Özellik 1", "Özellik 2", ...]</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Resim URL</label>
                                    <input type="url" class="form-control" id="image" name="image"
                                           value="<?php echo htmlspecialchars($about['image'] ?? ''); ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Önizleme</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Başlık:</strong>
                                <p id="preview-title"><?php echo htmlspecialchars($about['title'] ?? 'Başlık buraya gelecek'); ?></p>
                            </div>

                            <div class="mb-3">
                                <strong>Alt Başlık:</strong>
                                <p id="preview-subtitle"><?php echo htmlspecialchars($about['subtitle'] ?? 'Alt başlık buraya gelecek'); ?></p>
                            </div>

                            <div class="mb-3">
                                <strong>İçerik (İlk 100 karakter):</strong>
                                <p id="preview-content"><?php echo htmlspecialchars(substr($about['content'] ?? 'İçerik buraya gelecek...', 0, 100)); ?>...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>

    <script>
        // Form kaydetme
        document.getElementById('saveBtn').addEventListener('click', async function() {
            const form = document.getElementById('aboutForm');
            const saveBtn = this;

            // Loading state
            saveBtn.disabled = true;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="bi bi-arrow-repeat bi-spin"></i> Kaydediliyor...';

            try {
                const formData = new FormData(form);

                const response = await fetch('api/save-about.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Success feedback
                    saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Kaydedildi!';
                    saveBtn.classList.remove('btn-prime');
                    saveBtn.classList.add('btn-success');

                    setTimeout(() => {
                        saveBtn.innerHTML = originalText;
                        saveBtn.classList.remove('btn-success');
                        saveBtn.classList.add('btn-prime');
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Bilinmeyen hata');
                }
            } catch (error) {
                console.error('Kaydetme hatası:', error);

                // Error feedback
                saveBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Hata!';
                saveBtn.classList.remove('btn-prime');
                saveBtn.classList.add('btn-danger');

                setTimeout(() => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.classList.remove('btn-danger');
                    saveBtn.classList.add('btn-prime');
                }, 2000);

                // User notification
                alert('Hata: ' + error.message);
            } finally {
                saveBtn.disabled = false;
            }
        });

        // Real-time preview
        document.querySelectorAll('#aboutForm input, #aboutForm textarea').forEach(input => {
            input.addEventListener('input', function() {
                const field = this.id;
                const value = this.value;
                const previewElement = document.getElementById(`preview-${field}`);

                if (previewElement) {
                    if (field === 'content') {
                        previewElement.textContent = value.substring(0, 100) + (value.length > 100 ? '...' : '');
                    } else {
                        previewElement.textContent = value || `${field} buraya gelecek`;
                    }
                }
            });
        });
    </script>
</body>
</html>