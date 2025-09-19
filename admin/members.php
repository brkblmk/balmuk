<?php
require_once '../config/database.php';
require_once '../config/security.php';
require_once 'includes/member-helpers.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Üyelik tablolarını garanti altına al
ensureMemberManagementTables($pdo);

// İşlemler
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Üye ekleme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        // SecurityUtils ile input sanitizasyonu
        $member_code = SecurityUtils::sanitizeInput($_POST['member_code'] ?? 'PM' . time(), 'string');
        $first_name = SecurityUtils::sanitizeInput($_POST['first_name'] ?? '', 'string');
        $last_name = SecurityUtils::sanitizeInput($_POST['last_name'] ?? '', 'string');
        $phone = SecurityUtils::sanitizeInput($_POST['phone'] ?? '', 'phone');
        $email = SecurityUtils::sanitizeInput($_POST['email'] ?? '', 'email');
        $birth_date = $_POST['birth_date'] ?? null;
        $gender = SecurityUtils::sanitizeInput($_POST['gender'] ?? null, 'string');
        $address = SecurityUtils::sanitizeInput($_POST['address'] ?? '', 'html');
        $emergency_contact = SecurityUtils::sanitizeInput($_POST['emergency_contact'] ?? '', 'string');
        $emergency_phone = SecurityUtils::sanitizeInput($_POST['emergency_phone'] ?? '', 'phone');
        $health_conditions = SecurityUtils::sanitizeInput($_POST['health_conditions'] ?? '', 'html');
        $goals = SecurityUtils::sanitizeInput($_POST['goals'] ?? '', 'html');
        $membership_type = SecurityUtils::sanitizeInput($_POST['membership_type'] ?? '', 'string');
        $membership_start = $_POST['membership_start'] ?? date('Y-m-d');
        $membership_end = $_POST['membership_end'] ?? null;
        $total_sessions = SecurityUtils::sanitizeInput($_POST['total_sessions'] ?? 0, 'int');
        $remaining_sessions = SecurityUtils::sanitizeInput($_POST['remaining_sessions'] ?? 0, 'int');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $notes = SecurityUtils::sanitizeInput($_POST['notes'] ?? '', 'html');
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO members (member_code, first_name, last_name, phone, email, birth_date, gender, address, emergency_contact, emergency_phone, health_conditions, goals, membership_type, membership_start, membership_end, total_sessions, remaining_sessions, is_active, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$member_code, $first_name, $last_name, $phone, $email, $birth_date, $gender, $address, $emergency_contact, $emergency_phone, $health_conditions, $goals, $membership_type, $membership_start, $membership_end, $total_sessions, $remaining_sessions, $is_active, $notes]);
                $message = 'Üye başarıyla eklendi!';
                logActivity('create', 'members', $pdo->lastInsertId());
            } else {
                $id = $_POST['id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE members SET first_name = ?, last_name = ?, phone = ?, email = ?, birth_date = ?, gender = ?, address = ?, emergency_contact = ?, emergency_phone = ?, health_conditions = ?, goals = ?, membership_type = ?, membership_start = ?, membership_end = ?, total_sessions = ?, remaining_sessions = ?, is_active = ?, notes = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $phone, $email, $birth_date, $gender, $address, $emergency_contact, $emergency_phone, $health_conditions, $goals, $membership_type, $membership_start, $membership_end, $total_sessions, $remaining_sessions, $is_active, $notes, $id]);
                $message = 'Üye başarıyla güncellendi!';
                logActivity('update', 'members', $id);
            }
            header('Location: members.php?message=' . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $error = 'Hata: ' . $e->getMessage();
        }
    }
}

// Üye silme
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Üye başarıyla silindi!';
        logActivity('delete', 'members', $id);
        header('Location: members.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        $error = 'Silme hatası: ' . $e->getMessage();
    }
}

// Üye düzenleme için veri çekme
$editData = null;
$editAggregates = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
    if ($editData) {
        $editAggregates = getMemberAggregates($pdo, (int)$editData['id']);
    }
}

// Üyeleri listele - performans için LIMIT ve gerekli kolonlar
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; // Sayfa başına 50 üye
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT m.id, m.member_code, m.first_name, m.last_name, m.phone, m.email,
           m.membership_type, m.membership_end, m.remaining_sessions, m.is_active, m.created_at,
           COALESCE(p.sessions_purchased, 0) AS sessions_purchased,
           COALESCE(p.total_amount, 0) AS total_spent,
            COALESCE(s.sessions_completed, 0) AS sessions_completed
    FROM members m
    LEFT JOIN (
        SELECT member_id, SUM(sessions_purchased) AS sessions_purchased, SUM(amount) AS total_amount
        FROM member_payments
        GROUP BY member_id
    ) p ON p.member_id = m.id
    LEFT JOIN (
        SELECT member_id, COUNT(*) AS sessions_completed
        FROM member_sessions
        GROUP BY member_id
    ) s ON s.member_id = m.id
    ORDER BY m.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$members = $stmt->fetchAll();

// Toplam üye sayısı
$totalMembers = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$totalPages = ceil($totalMembers / $limit);

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
    <title>Üyeler - Prime EMS Admin</title>
    
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
                    <h1 class="h3 mb-0">Üyeler</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Yeni Üye
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
                <!-- Üye Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Yeni Üye Ekle' : 'Üye Düzenle'; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($editAggregates): ?>
                        <div class="alert alert-info bg-opacity-25 border-0 shadow-sm">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <p class="text-muted small mb-1">Satın Alınan Seans</p>
                                    <h5 class="mb-0"><?php echo number_format($editAggregates['sessions_purchased']); ?></h5>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted small mb-1">Tamamlanan Seans</p>
                                    <h5 class="mb-0"><?php echo number_format($editAggregates['sessions_completed']); ?></h5>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted small mb-1">Toplam Harcama</p>
                                    <h5 class="mb-0">₺<?php echo number_format($editAggregates['total_amount'], 2, ',', '.'); ?></h5>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted small mb-1">Son Ödeme</p>
                                    <h5 class="mb-0"><?php echo $editAggregates['last_payment'] ? date('d.m.Y', strtotime($editAggregates['last_payment'])) : '-'; ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <form method="POST">
                            <?php if ($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Üye Kodu</label>
                                    <input type="text" name="member_code" class="form-control" value="<?php echo htmlspecialchars($editData['member_code'] ?? 'PM' . time()); ?>" <?php echo $editData ? 'readonly' : ''; ?>>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Ad *</label>
                                    <input type="text" name="first_name" class="form-control" required value="<?php echo htmlspecialchars($editData['first_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Soyad *</label>
                                    <input type="text" name="last_name" class="form-control" required value="<?php echo htmlspecialchars($editData['last_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Telefon *</label>
                                    <input type="tel" name="phone" class="form-control" required value="<?php echo htmlspecialchars($editData['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($editData['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Doğum Tarihi</label>
                                    <input type="date" name="birth_date" class="form-control" value="<?php echo $editData['birth_date'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cinsiyet</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Seçiniz</option>
                                        <option value="male" <?php echo ($editData['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Erkek</option>
                                        <option value="female" <?php echo ($editData['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Kadın</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Adres</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($editData['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Acil Durum Kişisi</label>
                                    <input type="text" name="emergency_contact" class="form-control" value="<?php echo htmlspecialchars($editData['emergency_contact'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Acil Durum Telefonu</label>
                                    <input type="tel" name="emergency_phone" class="form-control" value="<?php echo htmlspecialchars($editData['emergency_phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Üyelik Tipi</label>
                                    <input type="text" name="membership_type" class="form-control" placeholder="Standart" value="<?php echo htmlspecialchars($editData['membership_type'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Üyelik Başlangıç</label>
                                    <input type="date" name="membership_start" class="form-control" value="<?php echo $editData['membership_start'] ?? date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Üyelik Bitiş</label>
                                    <input type="date" name="membership_end" class="form-control" value="<?php echo $editData['membership_end'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Toplam Seans</label>
                                    <input type="number" name="total_sessions" class="form-control" value="<?php echo $editData['total_sessions'] ?? 0; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kalan Seans</label>
                                    <input type="number" name="remaining_sessions" class="form-control" value="<?php echo $editData['remaining_sessions'] ?? 0; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sağlık Durumu</label>
                                    <textarea name="health_conditions" class="form-control" rows="2"><?php echo htmlspecialchars($editData['health_conditions'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hedefler</label>
                                    <textarea name="goals" class="form-control" rows="2"><?php echo htmlspecialchars($editData['goals'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Notlar</label>
                                    <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($editData['notes'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo ($editData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Aktif Üye</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Kaydet
                                </button>
                                <a href="members.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Üye Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Üye Kodu</th>
                                        <th>Ad Soyad</th>
                                        <th>Telefon</th>
                                        <th>E-posta</th>
                                        <th>Üyelik Tipi</th>
                                        <th>Satın Alınan Seans</th>
                                        <th>Tamamlanan</th>
                                        <th>Kalan</th>
                                        <th>Toplam Harcama</th>
                                        <th>Üyelik Bitiş</th>
                                        <th>Durum</th>
                                        <th width="200">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['member_code']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($member['membership_type'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($member['sessions_purchased'] > 0): ?>
                                            <span class="badge bg-gradient bg-primary text-light"><?php echo number_format($member['sessions_purchased']); ?></span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($member['sessions_completed'] > 0): ?>
                                            <span class="badge bg-success"><?php echo number_format($member['sessions_completed']); ?></span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $ledgerRemaining = max(($member['sessions_purchased'] ?? 0) - ($member['sessions_completed'] ?? 0), 0);
                                            $remainingVisual = max($ledgerRemaining, (int)($member['remaining_sessions'] ?? 0));
                                            ?>
                                            <span class="badge <?php echo $remainingVisual > 0 ? 'bg-info text-dark' : 'bg-danger'; ?>"><?php echo number_format($remainingVisual); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($member['total_spent'] > 0): ?>
                                            <strong>₺<?php echo number_format($member['total_spent'], 2, ',', '.'); ?></strong>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($member['membership_end']) {
                                                $endDate = strtotime($member['membership_end']);
                                                $today = time();
                                                if ($endDate < $today) {
                                                    echo '<span class="text-danger">' . date('d.m.Y', $endDate) . '</span>';
                                                } else {
                                                    echo date('d.m.Y', $endDate);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($member['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="member-profile.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-info" title="Üye profilini görüntüle">
                                                    <i class="bi bi-person-vcard"></i>
                                                </a>
                                                <a href="?action=edit&id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-primary" title="Üyeyi düzenle">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu üyeyi silmek istediğinizden emin misiniz?')" title="Üyeyi sil">
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