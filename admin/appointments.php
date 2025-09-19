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

// Randevu ekleme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        // SecurityUtils ile input sanitizasyonu
        $customer_name = SecurityUtils::sanitizeInput($_POST['customer_name'] ?? '', 'string');
        $customer_phone = SecurityUtils::sanitizeInput($_POST['customer_phone'] ?? '', 'phone');
        $customer_email = SecurityUtils::sanitizeInput($_POST['customer_email'] ?? '', 'email');
        $service_id = $_POST['service_id'] ?? null;
        $device_id = $_POST['device_id'] ?? null;
        $trainer_id = $_POST['trainer_id'] ?? null;
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $duration_minutes = SecurityUtils::sanitizeInput($_POST['duration_minutes'] ?? 20, 'int');
        $status = SecurityUtils::sanitizeInput($_POST['status'] ?? 'pending', 'string');
        $notes = SecurityUtils::sanitizeInput($_POST['notes'] ?? '', 'html');
        $source = SecurityUtils::sanitizeInput($_POST['source'] ?? 'website', 'string');
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO appointments (customer_name, customer_phone, customer_email, service_id, device_id, trainer_id, appointment_date, appointment_time, duration_minutes, status, notes, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$customer_name, $customer_phone, $customer_email, $service_id, $device_id, $trainer_id, $appointment_date, $appointment_time, $duration_minutes, $status, $notes, $source]);
                $message = 'Randevu başarıyla eklendi!';
                logActivity('create', 'appointments', $pdo->lastInsertId());
            } else {
                $id = $_POST['id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE appointments SET customer_name = ?, customer_phone = ?, customer_email = ?, service_id = ?, device_id = ?, trainer_id = ?, appointment_date = ?, appointment_time = ?, duration_minutes = ?, status = ?, notes = ? WHERE id = ?");
                $stmt->execute([$customer_name, $customer_phone, $customer_email, $service_id, $device_id, $trainer_id, $appointment_date, $appointment_time, $duration_minutes, $status, $notes, $id]);
                $message = 'Randevu başarıyla güncellendi!';
                logActivity('update', 'appointments', $id);
            }
            header('Location: appointments.php?message=' . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $error = 'Hata: ' . $e->getMessage();
        }
    }
}

// Randevu silme
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Randevu başarıyla silindi!';
        logActivity('delete', 'appointments', $id);
        header('Location: appointments.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Randevu düzenleme için veri çekme
$editData = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Get services, devices and trainers for dropdowns
$services = $pdo->query("SELECT id, name FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();
$devices = $pdo->query("SELECT id, name FROM devices WHERE is_active = 1 ORDER BY name")->fetchAll();
$trainers = $pdo->query("SELECT id, name FROM trainers WHERE is_active = 1 ORDER BY name")->fetchAll();

// Randevuları listele
$filter_date = $_GET['date'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT a.*, s.name as service_name, d.name as device_name, t.name as trainer_name 
          FROM appointments a
          LEFT JOIN services s ON a.service_id = s.id
          LEFT JOIN devices d ON a.device_id = d.id
          LEFT JOIN trainers t ON a.trainer_id = t.id
          WHERE 1=1";

$params = [];
if ($filter_date) {
    $query .= " AND a.appointment_date = ?";
    $params[] = $filter_date;
}
if ($filter_status) {
    $query .= " AND a.status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

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
    <title>Randevular - Prime EMS Admin</title>
    
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
            
            <!-- Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Randevular</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Randevu
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
                <!-- Randevu Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni Randevu Ekle' : 'Randevu Düzenle'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Müşteri Adı *</label>
                                    <input type="text" name="customer_name" class="form-control" required value="<?php echo htmlspecialchars($editData['customer_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Telefon *</label>
                                    <input type="tel" name="customer_phone" class="form-control" required value="<?php echo htmlspecialchars($editData['customer_phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($editData['customer_email'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hizmet</label>
                                    <select name="service_id" class="form-select">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id']; ?>" <?php echo ($editData['service_id'] ?? '') == $service['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cihaz</label>
                                    <select name="device_id" class="form-select">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($devices as $device): ?>
                                        <option value="<?php echo $device['id']; ?>" <?php echo ($editData['device_id'] ?? '') == $device['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($device['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Eğitmen</label>
                                    <select name="trainer_id" class="form-select">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo $trainer['id']; ?>" <?php echo ($editData['trainer_id'] ?? '') == $trainer['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trainer['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tarih *</label>
                                    <input type="date" name="appointment_date" class="form-control" required value="<?php echo $editData['appointment_date'] ?? date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Saat *</label>
                                    <input type="time" name="appointment_time" class="form-control" required value="<?php echo $editData['appointment_time'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Süre (dk)</label>
                                    <input type="number" name="duration_minutes" class="form-control" value="<?php echo $editData['duration_minutes'] ?? 20; ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Durum</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php echo ($editData['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Bekliyor</option>
                                        <option value="confirmed" <?php echo ($editData['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Onaylandı</option>
                                        <option value="completed" <?php echo ($editData['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Tamamlandı</option>
                                        <option value="cancelled" <?php echo ($editData['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>İptal</option>
                                        <option value="no-show" <?php echo ($editData['status'] ?? '') === 'no-show' ? 'selected' : ''; ?>>Gelmedi</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kaynak</label>
                                    <select name="source" class="form-select">
                                        <option value="website" <?php echo ($editData['source'] ?? 'website') === 'website' ? 'selected' : ''; ?>>Website</option>
                                        <option value="phone" <?php echo ($editData['source'] ?? '') === 'phone' ? 'selected' : ''; ?>>Telefon</option>
                                        <option value="whatsapp" <?php echo ($editData['source'] ?? '') === 'whatsapp' ? 'selected' : ''; ?>>WhatsApp</option>
                                        <option value="instagram" <?php echo ($editData['source'] ?? '') === 'instagram' ? 'selected' : ''; ?>>Instagram</option>
                                        <option value="referral" <?php echo ($editData['source'] ?? '') === 'referral' ? 'selected' : ''; ?>>Tavsiye</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Notlar</label>
                                    <textarea name="notes" class="form-control" rows="1"><?php echo htmlspecialchars($editData['notes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Kaydet
                                </button>
                                <a href="appointments.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Filtreler -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tarih</label>
                                <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Durum</label>
                                <select name="status" class="form-select">
                                    <option value="">Tümü</option>
                                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Bekliyor</option>
                                    <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Onaylandı</option>
                                    <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Tamamlandı</option>
                                    <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>İptal</option>
                                    <option value="no-show" <?php echo $filter_status === 'no-show' ? 'selected' : ''; ?>>Gelmedi</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search me-2"></i>Filtrele
                                    </button>
                                    <a href="appointments.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Temizle
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Randevu Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih/Saat</th>
                                        <th>Müşteri</th>
                                        <th>Telefon</th>
                                        <th>Hizmet</th>
                                        <th>Cihaz</th>
                                        <th>Eğitmen</th>
                                        <th>Durum</th>
                                        <th>Kaynak</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?></strong><br>
                                            <small><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['customer_phone']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['device_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['trainer_name'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                                'no-show' => 'secondary'
                                            ][$appointment['status']] ?? 'secondary';
                                            
                                            $statusText = [
                                                'pending' => 'Bekliyor',
                                                'confirmed' => 'Onaylandı',
                                                'completed' => 'Tamamlandı',
                                                'cancelled' => 'İptal',
                                                'no-show' => 'Gelmedi'
                                            ][$appointment['status']] ?? 'Bilinmiyor';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo ucfirst($appointment['source'] ?? 'website'); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>