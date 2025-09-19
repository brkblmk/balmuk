<?php
session_start();
require_once '../config/database.php';

// Admin kontrolü (basit)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$page_title = "İletişim Mesajları";

// Mesaj durumunu güncelle
if (isset($_POST['update_status'])) {
    $message_id = (int)$_POST['message_id'];
    $new_status = $_POST['status'];
    
    $update_stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $message_id]);
    
    logActivity("Message status updated", "messages", $message_id);
    $success_message = "Mesaj durumu güncellendi.";
}

// Mesaj silme
if (isset($_POST['delete_message'])) {
    $message_id = (int)$_POST['message_id'];
    
    $delete_stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $delete_stmt->execute([$message_id]);
    
    logActivity("Message deleted", "messages", $message_id);
    $success_message = "Mesaj silindi.";
}

// Filtreleme
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Mesajları çek
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $pdo->prepare("
    SELECT *, DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') as formatted_date 
    FROM contact_messages 
    $where_clause 
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->execute($params);
$messages = $stmt->fetchAll();

// İstatistikler
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
    FROM contact_messages
");
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Prime EMS Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-main">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="admin-title"><?php echo $page_title; ?></h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">İletişim Mesajları</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    
                    <!-- Success Message -->
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="card-title mb-0"><?php echo $stats['total']; ?></h3>
                                            <p class="card-text mb-0">Toplam Mesaj</p>
                                        </div>
                                        <i class="bi bi-envelope-fill fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="card-title mb-0"><?php echo $stats['new_count']; ?></h3>
                                            <p class="card-text mb-0">Yeni Mesajlar</p>
                                        </div>
                                        <i class="bi bi-envelope-plus-fill fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="card-title mb-0"><?php echo $stats['read_count']; ?></h3>
                                            <p class="card-text mb-0">Okunmuş</p>
                                        </div>
                                        <i class="bi bi-envelope-open-fill fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="card-title mb-0"><?php echo $stats['replied_count']; ?></h3>
                                            <p class="card-text mb-0">Yanıtlanmış</p>
                                        </div>
                                        <i class="bi bi-reply-fill fs-1 opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Durum Filtresi</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">Tüm Durumlar</option>
                                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>Yeni</option>
                                        <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Okunmuş</option>
                                        <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Yanıtlanmış</option>
                                        <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Arşivlenmiş</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="search" class="form-label">Arama</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Ad, e-posta, konu veya mesaj içeriğinde ara...">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-search"></i> Filtrele
                                    </button>
                                    <a href="messages.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Messages List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul me-2"></i>Mesaj Listesi
                                <small class="text-muted">(<?php echo count($messages); ?> mesaj gösteriliyor)</small>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($messages): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tarih</th>
                                            <th>Gönderen</th>
                                            <th>Konu</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($messages as $message): ?>
                                        <tr class="<?php echo $message['status'] === 'new' ? 'table-warning' : ''; ?>">
                                            <td class="text-nowrap">
                                                <small><?php echo $message['formatted_date']; ?></small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($message['email']); ?></small>
                                                    <?php if ($message['phone']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($message['phone']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($message['subject']); ?></strong>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(mb_substr($message['message'], 0, 100)); ?>
                                                    <?php if (mb_strlen($message['message']) > 100): ?>...<?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($message['status']) {
                                                        'new' => 'warning',
                                                        'read' => 'info',
                                                        'replied' => 'success',
                                                        'archived' => 'secondary'
                                                    };
                                                ?>">
                                                    <?php 
                                                    echo match($message['status']) {
                                                        'new' => 'Yeni',
                                                        'read' => 'Okunmuş',
                                                        'replied' => 'Yanıtlanmış',
                                                        'archived' => 'Arşivlenmiş'
                                                    };
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" 
                                                            data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary" data-bs-toggle="modal"
                                                            data-bs-target="#statusModal<?php echo $message['id']; ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal<?php echo $message['id']; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Message Detail Modal -->
                                                <div class="modal fade" id="messageModal<?php echo $message['id']; ?>">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Mesaj Detayı</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <strong>Gönderen:</strong><br>
                                                                        <?php echo htmlspecialchars($message['name']); ?>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <strong>Tarih:</strong><br>
                                                                        <?php echo $message['formatted_date']; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <strong>E-posta:</strong><br>
                                                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                                            <?php echo htmlspecialchars($message['email']); ?>
                                                                        </a>
                                                                    </div>
                                                                    <?php if ($message['phone']): ?>
                                                                    <div class="col-md-6">
                                                                        <strong>Telefon:</strong><br>
                                                                        <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                                                            <?php echo htmlspecialchars($message['phone']); ?>
                                                                        </a>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <strong>Konu:</strong><br>
                                                                    <?php echo htmlspecialchars($message['subject']); ?>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <strong>Mesaj:</strong><br>
                                                                    <div class="border p-3 bg-light rounded">
                                                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                                    </div>
                                                                </div>
                                                                <?php if ($message['admin_notes']): ?>
                                                                <div class="mb-3">
                                                                    <strong>Admin Notları:</strong><br>
                                                                    <div class="border p-3 bg-warning bg-opacity-10 rounded">
                                                                        <?php echo nl2br(htmlspecialchars($message['admin_notes'])); ?>
                                                                    </div>
                                                                </div>
                                                                <?php endif; ?>
                                                                <small class="text-muted">
                                                                    IP: <?php echo htmlspecialchars($message['ip_address']); ?>
                                                                </small>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-success">
                                                                    <i class="bi bi-reply"></i> E-posta ile Yanıtla
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Status Update Modal -->
                                                <div class="modal fade" id="statusModal<?php echo $message['id']; ?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Durum Güncelle</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="status<?php echo $message['id']; ?>" class="form-label">Yeni Durum</label>
                                                                        <select name="status" id="status<?php echo $message['id']; ?>" class="form-select">
                                                                            <option value="new" <?php echo $message['status'] === 'new' ? 'selected' : ''; ?>>Yeni</option>
                                                                            <option value="read" <?php echo $message['status'] === 'read' ? 'selected' : ''; ?>>Okunmuş</option>
                                                                            <option value="replied" <?php echo $message['status'] === 'replied' ? 'selected' : ''; ?>>Yanıtlanmış</option>
                                                                            <option value="archived" <?php echo $message['status'] === 'archived' ? 'selected' : ''; ?>>Arşivlenmiş</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit" name="update_status" class="btn btn-primary">Güncelle</button>
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $message['id']; ?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Mesajı Sil</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                                    <p>Bu mesajı silmek istediğinizden emin misiniz?</p>
                                                                    <div class="alert alert-warning">
                                                                        <strong>Uyarı:</strong> Bu işlem geri alınamaz.
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit" name="delete_message" class="btn btn-danger">Sil</button>
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-envelope text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">Henüz mesaj bulunmuyor</h5>
                                <p class="text-muted">İletişim formu üzerinden gelen mesajlar burada görünecek.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>