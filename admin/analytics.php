<?php
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Analytics verilerini topla
function getAnalyticsData($pdo, $period = '7') {
    $data = [];
    
    try {
        // Website ziyaretçi istatistikleri (örnek veriler - gerçek Analytics API'den gelecek)
        $data['visitors'] = [
            'total' => 12500,
            'unique' => 8900,
            'bounce_rate' => 42.3,
            'avg_session_duration' => '2:34'
        ];
        
        // Randevu istatistikleri
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                status
            FROM appointments 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at), status
            ORDER BY date DESC
        ");
        $stmt->execute([$period]);
        $appointments_raw = $stmt->fetchAll();
        
        $data['appointments'] = [
            'total' => array_sum(array_column($appointments_raw, 'count')),
            'confirmed' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'daily_data' => []
        ];
        
        foreach ($appointments_raw as $row) {
            $data['appointments'][$row['status']] += $row['count'];
            if (!isset($data['appointments']['daily_data'][$row['date']])) {
                $data['appointments']['daily_data'][$row['date']] = 0;
            }
            $data['appointments']['daily_data'][$row['date']] += $row['count'];
        }
        
        // Contact form istatistikleri
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_messages,
                COUNT(CASE WHEN status = 'new' THEN 1 END) as new_messages,
                COUNT(CASE WHEN status = 'replied' THEN 1 END) as replied_messages
            FROM contact_messages 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ");
        $stmt->execute([$period]);
        $contact_stats = $stmt->fetch();
        $data['contact_forms'] = $contact_stats ?: ['total_messages' => 0, 'new_messages' => 0, 'replied_messages' => 0];
        
        // Popüler sayfalar (örnek veriler)
        $data['popular_pages'] = [
            ['page' => '/', 'views' => 3420, 'title' => 'Ana Sayfa'],
            ['page' => '/blog.php', 'views' => 1250, 'title' => 'Blog'],
            ['page' => '/chatbot.php', 'views' => 890, 'title' => 'Chatbot'],
            ['page' => '/blog-detail.php', 'views' => 650, 'title' => 'Blog Detay']
        ];
        
        // Cihaz istatistikleri (örnek veriler)
        $data['devices'] = [
            'mobile' => 62.5,
            'desktop' => 28.3,
            'tablet' => 9.2
        ];
        
        // Kaynak istatistikleri (örnek veriler)
        $data['sources'] = [
            'organic' => 45.2,
            'direct' => 32.1,
            'social' => 15.4,
            'referral' => 7.3
        ];
        
    } catch (PDOException $e) {
        error_log("Analytics data error: " . $e->getMessage());
    }
    
    return $data;
}

$period = $_GET['period'] ?? '7';
$analytics = getAnalyticsData($pdo, $period);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Prime EMS Admin</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Analytics Dashboard</h1>
                        <p class="text-muted">Website ve işletme analitikleri</p>
                    </div>
                    <div class="d-flex gap-2">
                        <!-- Period Selector -->
                        <select class="form-select" id="periodSelector" onchange="changePeriod(this.value)">
                            <option value="7" <?php echo $period == '7' ? 'selected' : ''; ?>>Son 7 Gün</option>
                            <option value="30" <?php echo $period == '30' ? 'selected' : ''; ?>>Son 30 Gün</option>
                            <option value="90" <?php echo $period == '90' ? 'selected' : ''; ?>>Son 3 Ay</option>
                        </select>
                        
                        <button class="btn btn-outline-primary" onclick="refreshData()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Yenile
                        </button>
                        
                        <button class="btn btn-primary" onclick="exportReport()">
                            <i class="bi bi-download me-1"></i>Rapor İndir
                        </button>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Toplam Ziyaretçi</p>
                                        <h3 class="mb-0"><?php echo number_format($analytics['visitors']['total']); ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> %12.5 artış
                                        </small>
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
                                        <p class="text-muted mb-1">Randevu Talepleri</p>
                                        <h3 class="mb-0"><?php echo $analytics['appointments']['total']; ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> %8.3 artış
                                        </small>
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
                                        <p class="text-muted mb-1">Contact Form</p>
                                        <h3 class="mb-0"><?php echo $analytics['contact_forms']['total_messages']; ?></h3>
                                        <small class="text-info">
                                            <i class="bi bi-envelope"></i> <?php echo $analytics['contact_forms']['new_messages']; ?> yeni
                                        </small>
                                    </div>
                                    <div class="icon-box bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-envelope-fill"></i>
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
                                        <p class="text-muted mb-1">Bounce Rate</p>
                                        <h3 class="mb-0"><?php echo $analytics['visitors']['bounce_rate']; ?>%</h3>
                                        <small class="text-warning">
                                            <i class="bi bi-dash"></i> Normal
                                        </small>
                                    </div>
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-graph-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <!-- Traffic Chart -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-graph-up text-primary me-2"></i>
                                    Ziyaretçi Trafiği
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="trafficChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Device Distribution -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-devices text-primary me-2"></i>
                                    Cihaz Dağılımı
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="deviceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Analytics -->
                <div class="row g-4">
                    <!-- Popular Pages -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-text text-primary me-2"></i>
                                    Popüler Sayfalar
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($analytics['popular_pages'] as $page): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1"><?php echo $page['title']; ?></h6>
                                        <small class="text-muted"><?php echo $page['page']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold"><?php echo number_format($page['views']); ?></span>
                                        <br><small class="text-muted">görüntülenme</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Traffic Sources -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-link-45deg text-primary me-2"></i>
                                    Trafik Kaynakları
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($analytics['sources'] as $source => $percentage): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-capitalize"><?php echo str_replace('_', ' ', $source); ?></span>
                                        <span class="fw-bold"><?php echo $percentage; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Real-time Stats -->
                <div class="row g-4 mt-2">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-broadcast text-primary me-2"></i>
                                        Gerçek Zamanlı İstatistikler
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="status-dot bg-success me-2"></div>
                                        <small class="text-muted">Canlı</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <h4 class="text-success mb-1" id="activeUsers">24</h4>
                                            <small class="text-muted">Aktif Kullanıcı</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <h4 class="text-primary mb-1" id="pageViews">156</h4>
                                            <small class="text-muted">Sayfa Görüntüleme (Son Saat)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <h4 class="text-warning mb-1" id="avgSession">3:45</h4>
                                            <small class="text-muted">Ortalama Oturum Süresi</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <h4 class="text-info mb-1" id="conversionRate">4.2%</h4>
                                            <small class="text-muted">Dönüşüm Oranı</small>
                                        </div>
                                    </div>
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
    <script>
        // Traffic Chart
        const trafficCtx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(trafficCtx, {
            type: 'line',
            data: {
                labels: ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'],
                datasets: [{
                    label: 'Ziyaretçiler',
                    data: [1200, 1900, 1500, 2100, 2400, 1800, 1600],
                    borderColor: '#FFD700',
                    backgroundColor: 'rgba(255, 215, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Sayfa Görüntülemeleri',
                    data: [2400, 3100, 2800, 3500, 4200, 3300, 2900],
                    borderColor: '#2B2B2B',
                    backgroundColor: 'rgba(43, 43, 43, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Device Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceChart = new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Mobil', 'Desktop', 'Tablet'],
                datasets: [{
                    data: [<?php echo $analytics['devices']['mobile']; ?>, 
                           <?php echo $analytics['devices']['desktop']; ?>, 
                           <?php echo $analytics['devices']['tablet']; ?>],
                    backgroundColor: ['#FFD700', '#2B2B2B', '#FF6B00'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Functions
        function changePeriod(period) {
            window.location.href = `?period=${period}`;
        }
        
        function refreshData() {
            location.reload();
        }
        
        function exportReport() {
            // Export functionality
            window.open('ajax/export-analytics.php?period=<?php echo $period; ?>', '_blank');
        }
        
        // Real-time data simulation
        function updateRealTimeStats() {
            const activeUsers = document.getElementById('activeUsers');
            const pageViews = document.getElementById('pageViews');
            
            // Simulate real-time updates
            setInterval(() => {
                const currentActive = parseInt(activeUsers.textContent);
                const variation = Math.floor(Math.random() * 6) - 3; // -3 to +3
                const newActive = Math.max(1, currentActive + variation);
                activeUsers.textContent = newActive;
                
                const currentViews = parseInt(pageViews.textContent);
                const viewsVariation = Math.floor(Math.random() * 10) - 5;
                const newViews = Math.max(0, currentViews + viewsVariation);
                pageViews.textContent = newViews;
            }, 5000); // Update every 5 seconds
        }
        
        // Initialize real-time updates
        updateRealTimeStats();
    </script>
    
    <style>
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .progress {
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
    </style>
</body>
</html>