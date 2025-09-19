<?php
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/performance.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Security check for admin session
SecurityUtils::secureSession();

// Check if session is expired (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    SecurityUtils::logSecurityEvent('ADMIN_SESSION_EXPIRED', ['admin_id' => $_SESSION['admin_id'] ?? null]);
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}
$_SESSION['last_activity'] = time();

$message = '';
$message_type = '';

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
        SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP(), 'action' => 'admin_logs']);
        $message = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
        $message_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'clear_old':
                $days = (int)($_POST['days'] ?? 30);
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
                    $stmt->execute([$days]);
                    
                    $deleted_count = $stmt->rowCount();
                    SecurityUtils::logSecurityEvent('ADMIN_LOGS_CLEARED', [
                        'days' => $days,
                        'deleted_count' => $deleted_count,
                        'cleared_by' => $_SESSION['admin_id']
                    ]);
                    
                    $message = "{$deleted_count} adet eski log kaydı silindi.";
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Loglar silinirken hata oluştu: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;
                
            case 'export':
                $format = $_POST['format'] ?? 'csv';
                $date_from = $_POST['date_from'] ?? '';
                $date_to = $_POST['date_to'] ?? '';
                
                try {
                    $query = "SELECT * FROM activity_logs WHERE 1=1";
                    $params = [];
                    
                    if ($date_from) {
                        $query .= " AND created_at >= ?";
                        $params[] = $date_from . ' 00:00:00';
                    }
                    
                    if ($date_to) {
                        $query .= " AND created_at <= ?";
                        $params[] = $date_to . ' 23:59:59';
                    }
                    
                    $query .= " ORDER BY created_at DESC";
                    
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($params);
                    $logs = $stmt->fetchAll();
                    
                    if ($format === 'csv') {
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');
                        
                        $output = fopen('php://output', 'w');
                        
                        // CSV headers
                        fputcsv($output, ['ID', 'User ID', 'Action', 'Table Name', 'Record ID', 'IP Address', 'User Agent', 'Details', 'Created At']);
                        
                        // CSV data
                        foreach ($logs as $log) {
                            fputcsv($output, [
                                $log['id'],
                                $log['user_id'] ?? '',
                                $log['action'],
                                $log['table_name'] ?? '',
                                $log['record_id'] ?? '',
                                $log['ip_address'],
                                $log['user_agent'] ?? '',
                                $log['details'] ?? '',
                                $log['created_at']
                            ]);
                        }
                        
                        fclose($output);
                        exit;
                    }
                } catch (PDOException $e) {
                    $message = 'Export sırasında hata oluştu: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$search = SecurityUtils::sanitizeInput($_GET['search'] ?? '', 'string');
$action_filter = SecurityUtils::sanitizeInput($_GET['action'] ?? '', 'string');
$user_filter = (int)($_GET['user_id'] ?? 0);
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$search_params = [];

if ($search) {
    $where_conditions[] = "(action LIKE ? OR ip_address LIKE ? OR user_agent LIKE ? OR details LIKE ?)";
    $search_params = array_merge($search_params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

if ($action_filter) {
    $where_conditions[] = "action = ?";
    $search_params[] = $action_filter;
}

if ($user_filter) {
    $where_conditions[] = "user_id = ?";
    $search_params[] = $user_filter;
}

if ($date_from) {
    $where_conditions[] = "created_at >= ?";
    $search_params[] = $date_from . ' 00:00:00';
}

if ($date_to) {
    $where_conditions[] = "created_at <= ?";
    $search_params[] = $date_to . ' 23:59:59';
}

$where_query = '';
if (!empty($where_conditions)) {
    $where_query = " WHERE " . implode(' AND ', $where_conditions);
}

// Get total count
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs" . $where_query);
    $count_stmt->execute($search_params);
    $total_logs = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    $total_logs = 0;
}

$total_pages = ceil($total_logs / $limit);

// Get logs with admin info
try {
    $logs_query = "
        SELECT al.*, a.username, a.email
        FROM activity_logs al
        LEFT JOIN admins a ON al.user_id = a.id
        " . $where_query . "
        ORDER BY al.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($logs_query);
    $params = array_merge($search_params, [$limit, $offset]);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $logs = [];
}

// Get unique actions for filter
try {
    $actions_stmt = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
    $available_actions = $actions_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $available_actions = [];
}

// Get users for filter
try {
    $users_stmt = $pdo->query("SELECT id, username FROM admins WHERE is_active = 1 ORDER BY username");
    $available_users = $users_stmt->fetchAll();
} catch (PDOException $e) {
    $available_users = [];
}

// Get statistics
try {
    $stats_today = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $stats_week = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    $stats_month = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $stats_total = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
} catch (PDOException $e) {
    $stats_today = $stats_week = $stats_month = $stats_total = 0;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Logları - Prime EMS Admin</title>
    
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Aktivite Logları</h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearModal">
                            <i class="bi bi-trash"></i> Temizle
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Bugün</p>
                                        <h3 class="mb-0"><?php echo number_format($stats_today); ?></h3>
                                    </div>
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Bu Hafta</p>
                                        <h3 class="mb-0"><?php echo number_format($stats_week); ?></h3>
                                    </div>
                                    <div class="icon-box bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-calendar-week"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Bu Ay</p>
                                        <h3 class="mb-0"><?php echo number_format($stats_month); ?></h3>
                                    </div>
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-calendar-month"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Toplam</p>
                                        <h3 class="mb-0"><?php echo number_format($stats_total); ?></h3>
                                    </div>
                                    <div class="icon-box bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-list-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Arama..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="action" class="form-select">
                                    <option value="">Tüm Aksiyonlar</option>
                                    <?php foreach ($available_actions as $action): ?>
                                    <option value="<?php echo htmlspecialchars($action); ?>" 
                                            <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($action); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-select">
                                    <option value="">Tüm Kullanıcılar</option>
                                    <?php foreach ($available_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" 
                                            <?php echo $user_filter === (int)$user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" 
                                       value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" 
                                       value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        <div class="mt-2">
                            <a href="logs.php" class="btn btn-outline-secondary btn-sm">Filtreleri Temizle</a>
                        </div>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı</th>
                                        <th>Aksiyon</th>
                                        <th>IP Adresi</th>
                                        <th>Detaylar</th>
                                        <th>Tarih</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td>
                                            <?php if ($log['username']): ?>
                                                <strong><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($log['email']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Sistem</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $action_badges = [
                                                'LOGIN' => 'success',
                                                'LOGOUT' => 'info',
                                                'CREATE' => 'primary',
                                                'UPDATE' => 'warning',
                                                'DELETE' => 'danger',
                                                'ADMIN_' => 'secondary'
                                            ];
                                            
                                            $badge_class = 'secondary';
                                            foreach ($action_badges as $prefix => $class) {
                                                if (strpos($log['action'], $prefix) === 0) {
                                                    $badge_class = $class;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                        </td>
                                        <td>
                                            <?php 
                                            $details = $log['details'] ? json_decode($log['details'], true) : [];
                                            if (is_array($details) && !empty($details)):
                                            ?>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="showDetails(<?php echo htmlspecialchars(json_encode($details)); ?>)">
                                                <i class="bi bi-info-circle"></i> Detaylar
                                            </button>
                                            <?php elseif ($log['details']): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($log['details']); ?></small>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="showUserAgent('<?php echo htmlspecialchars($log['user_agent']); ?>')">
                                                <i class="bi bi-browser-chrome"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($logs)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-list-check display-1 text-muted"></i>
                            <p class="mt-3 text-muted">Log kaydı bulunamadı.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php
                        $query_params = [
                            'search' => $search,
                            'action' => $action_filter,
                            'user_id' => $user_filter,
                            'date_from' => $date_from,
                            'date_to' => $date_to
                        ];
                        $query_string = http_build_query(array_filter($query_params));
                        ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $query_string ? '&' . $query_string : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clear Logs Modal -->
    <div class="modal fade" id="clearModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="clear_old">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Eski Logları Temizle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="days" class="form-label">Kaç gün öncesini temizle?</label>
                            <select class="form-select" id="days" name="days" required>
                                <option value="7">7 gün öncesi</option>
                                <option value="30" selected>30 gün öncesi</option>
                                <option value="60">60 gün öncesi</option>
                                <option value="90">90 gün öncesi</option>
                                <option value="365">1 yıl öncesi</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Bu işlem geri alınamaz! Seçilen tarihten eski tüm log kayıtları silinecek.
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Temizle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="export">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Logları Export Et</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="export_format" class="form-label">Format</label>
                            <select class="form-select" id="export_format" name="format" required>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="export_date_from" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" id="export_date_from" name="date_from">
                        </div>
                        
                        <div class="mb-3">
                            <label for="export_date_to" class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" id="export_date_to" name="date_to">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="detailsContent" class="bg-light p-3 rounded"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Agent Modal -->
    <div class="modal fade" id="userAgentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <code id="userAgentContent" class="d-block p-3 bg-light rounded"></code>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/mobile-nav.js"></script>
    
    <script>
        function showDetails(details) {
            document.getElementById('detailsContent').textContent = JSON.stringify(details, null, 2);
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }
        
        function showUserAgent(userAgent) {
            document.getElementById('userAgentContent').textContent = userAgent;
            const modal = new bootstrap.Modal(document.getElementById('userAgentModal'));
            modal.show();
        }
    </script>
</body>
</html>