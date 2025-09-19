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

// Dashboard istatistikleri
try {
    // Toplam üye sayısı
    $total_members = $pdo->query("SELECT COUNT(*) FROM members WHERE is_active = 1")->fetchColumn() ?? 0;

    // Bugünkü randevular
    $today_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status != 'cancelled'")->fetchColumn() ?? 0;

    // Aktif kampanyalar
    $active_campaigns = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE())")->fetchColumn() ?? 0;

    // Aylık gelir (randevular için packages tablosundan fiyat alınması)
    $total_revenue_query = "
        SELECT COALESCE(SUM(s.price), 0) as total
        FROM appointments a
        LEFT JOIN services s ON a.service_id = s.id
        WHERE a.status = 'completed'
        AND MONTH(a.appointment_date) = MONTH(CURDATE())
        AND YEAR(a.appointment_date) = YEAR(CURDATE())
    ";
    $total_revenue = $pdo->query($total_revenue_query)->fetchColumn() ?? 0;

    $stats = [
        'total_members' => $total_members,
        'today_appointments' => $today_appointments,
        'active_campaigns' => $active_campaigns,
        'total_revenue' => $total_revenue
    ];

    // Dashboard yüklendiğini logla
    logActivity('dashboard_view', 'admin_panel', null, $_SESSION['admin_id'] ?? null);

} catch (PDOException $e) {
    // Hata logla
    error_log("Dashboard statistics error: " . $e->getMessage());
    SecurityUtils::logSecurityEvent('DASHBOARD_STATISTICS_ERROR', [
        'error' => $e->getMessage(),
        'admin_id' => $_SESSION['admin_id'] ?? null
    ]);

    // Fallback values if tables don't exist or have issues
    $stats = [
        'total_members' => 0,
        'today_appointments' => 0,
        'active_campaigns' => 0,
        'total_revenue' => 0
    ];
}

// Son randevular
try {
    $recent_appointments = $pdo->query("
        SELECT a.*, s.name as service_name 
        FROM appointments a 
        LEFT JOIN services s ON a.service_id = s.id 
        WHERE a.appointment_date >= CURDATE() 
        ORDER BY a.appointment_date, a.appointment_time 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $recent_appointments = [];
}

// Son üyeler
try {
    $recent_members = $pdo->query("
        SELECT * FROM members 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $recent_members = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prime EMS Admin Panel</title>
    
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
            
            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <h1 class="h3 mb-4">Dashboard</h1>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Toplam Üye</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_members']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-people-fill"></i>
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
                                        <p class="text-muted mb-1">Bugünkü Randevular</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['today_appointments']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-calendar-check"></i>
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
                                        <p class="text-muted mb-1">Aktif Kampanyalar</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['active_campaigns']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-megaphone-fill"></i>
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
                                        <p class="text-muted mb-1">Aylık Gelir</p>
                                        <h3 class="mb-0">₺<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                                    </div>
                                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics Cards -->
                <h5 class="mb-3">Performans Metrikleri</h5>
                <div class="row g-3 mb-4">
                    <?php
                    $performance_metrics = PerformanceOptimizer::getPerformanceMetrics();
                    ?>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Sayfa Yükleme</p>
                                        <h3 class="mb-0"><?php echo number_format($performance_metrics['execution_time'] * 1000, 2); ?>ms</h3>
                                    </div>
                                    <div class="icon-box bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-speedometer2"></i>
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
                                        <p class="text-muted mb-1">Bellek Kullanımı</p>
                                        <h3 class="mb-0"><?php echo PerformanceOptimizer::formatBytes($performance_metrics['memory_usage']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-memory"></i>
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
                                        <p class="text-muted mb-1">Dosya Sayısı</p>
                                        <h3 class="mb-0"><?php echo number_format($performance_metrics['included_files']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-file-earmark-code"></i>
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
                                        <p class="text-muted mb-1">Cache Boyutu</p>
                                        <h3 class="mb-0"><?php echo PerformanceOptimizer::formatBytes($performance_metrics['cache_dir_size']); ?></h3>
                                    </div>
                                    <div class="icon-box bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-hdd-stack"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3">
                    <!-- Recent Appointments -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Yaklaşan Randevular</h5>
                                    <a href="appointments.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Müşteri</th>
                                                <th>Hizmet</th>
                                                <th>Tarih</th>
                                                <th>Saat</th>
                                                <th>Durum</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['service_name'] ?? 'Belirtilmemiş'); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'pending' => 'warning',
                                                        'confirmed' => 'success',
                                                        'completed' => 'info',
                                                        'cancelled' => 'danger'
                                                    ][$appointment['status']] ?? 'secondary';
                                                    
                                                    $statusText = [
                                                        'pending' => 'Bekliyor',
                                                        'confirmed' => 'Onaylandı',
                                                        'completed' => 'Tamamlandı',
                                                        'cancelled' => 'İptal'
                                                    ][$appointment['status']] ?? 'Bilinmiyor';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Members -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Yeni Üyeler</h5>
                                    <a href="members.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_members as $member): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($member['phone']); ?></small>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('d.m', strtotime($member['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>