<?php
require_once '../config/database.php';
require_once '../config/security.php';
require_once 'includes/member-helpers.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

ensureMemberManagementTables($pdo);
SecurityUtils::generateCSRFToken();

$memberId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = $_GET['message'] ?? '';
$error = '';

if ($memberId <= 0) {
    header('Location: members.php');
    exit;
}

try {
    $memberStmt = $pdo->prepare('SELECT * FROM members WHERE id = ?');
    $memberStmt->execute([$memberId]);
    $member = $memberStmt->fetch();

    if (!$member) {
        header('Location: members.php?message=' . urlencode('Üye bulunamadı'));
        exit;
    }
} catch (PDOException $e) {
    $error = 'Üye bilgileri alınamadı: ' . $e->getMessage();
    $member = null;
}

if ($member && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!SecurityUtils::verifyCSRFToken($csrfToken)) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
        try {
            if ($formAction === 'add_metric') {
                $measurementDate = $_POST['measurement_date'] ?? date('Y-m-d');
                $weight = SecurityUtils::sanitizeInput($_POST['weight_kg'] ?? '', 'float');
                $bodyFat = SecurityUtils::sanitizeInput($_POST['body_fat_percentage'] ?? '', 'float');
                $muscle = SecurityUtils::sanitizeInput($_POST['muscle_mass_kg'] ?? '', 'float');
                $visceral = SecurityUtils::sanitizeInput($_POST['visceral_fat'] ?? '', 'float');
                $water = SecurityUtils::sanitizeInput($_POST['water_percentage'] ?? '', 'float');
                $waist = SecurityUtils::sanitizeInput($_POST['waist_cm'] ?? '', 'float');
                $hip = SecurityUtils::sanitizeInput($_POST['hip_cm'] ?? '', 'float');
                $notes = SecurityUtils::sanitizeInput($_POST['metric_notes'] ?? '', 'html');

                $stmt = $pdo->prepare('INSERT INTO member_metrics (member_id, measurement_date, weight_kg, body_fat_percentage, muscle_mass_kg, visceral_fat, water_percentage, waist_cm, hip_cm, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$memberId, $measurementDate, $weight ?: null, $bodyFat ?: null, $muscle ?: null, $visceral ?: null, $water ?: null, $waist ?: null, $hip ?: null, $notes ?: null]);

                logActivity('member_metric_added', 'members', $memberId);
                header('Location: member-profile.php?id=' . $memberId . '&message=' . urlencode('Vücut ölçümü kaydedildi.'));
                exit;
            }

            if ($formAction === 'add_session') {
                $sessionDate = $_POST['session_date'] ?? date('Y-m-d');
                $sessionType = SecurityUtils::sanitizeInput($_POST['session_type'] ?? '', 'string');
                $duration = (int)SecurityUtils::sanitizeInput($_POST['duration_minutes'] ?? 20, 'int');
                $intensity = SecurityUtils::sanitizeInput($_POST['intensity_level'] ?? '', 'string');
                $trainer = SecurityUtils::sanitizeInput($_POST['trainer_name'] ?? '', 'string');
                $notes = SecurityUtils::sanitizeInput($_POST['session_notes'] ?? '', 'html');

                $pdo->beginTransaction();

                $stmt = $pdo->prepare('INSERT INTO member_sessions (member_id, session_date, session_type, duration_minutes, intensity_level, trainer_name, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$memberId, $sessionDate, $sessionType ?: null, $duration ?: null, $intensity ?: null, $trainer ?: null, $notes ?: null]);

                $update = $pdo->prepare('UPDATE members SET remaining_sessions = CASE WHEN remaining_sessions > 0 THEN remaining_sessions - 1 ELSE 0 END WHERE id = ?');
                $update->execute([$memberId]);

                $pdo->commit();

                logActivity('member_session_logged', 'members', $memberId);
                header('Location: member-profile.php?id=' . $memberId . '&message=' . urlencode('Seans kaydı oluşturuldu.'));
                exit;
            }

            if ($formAction === 'add_payment') {
                $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
                $packageId = !empty($_POST['package_id']) ? (int)SecurityUtils::sanitizeInput($_POST['package_id'], 'int') : null;
                $amount = (float)SecurityUtils::sanitizeInput($_POST['amount'] ?? 0, 'float');
                $currency = strtoupper(SecurityUtils::sanitizeInput($_POST['currency'] ?? 'TRY', 'string'));
                $sessionsPurchased = (int)SecurityUtils::sanitizeInput($_POST['sessions_purchased'] ?? 0, 'int');
                $method = SecurityUtils::sanitizeInput($_POST['payment_method'] ?? '', 'string');
                $reference = SecurityUtils::sanitizeInput($_POST['reference_code'] ?? '', 'string');
                $notes = SecurityUtils::sanitizeInput($_POST['payment_notes'] ?? '', 'html');

                $pdo->beginTransaction();

                $stmt = $pdo->prepare('INSERT INTO member_payments (member_id, package_id, amount, currency, sessions_purchased, payment_date, payment_method, reference_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$memberId, $packageId ?: null, $amount, $currency ?: 'TRY', $sessionsPurchased, $paymentDate, $method ?: null, $reference ?: null, $notes ?: null]);

                if ($sessionsPurchased > 0) {
                    $update = $pdo->prepare('UPDATE members SET total_sessions = total_sessions + ?, remaining_sessions = remaining_sessions + ? WHERE id = ?');
                    $update->execute([$sessionsPurchased, $sessionsPurchased, $memberId]);
                }

                $pdo->commit();

                logActivity('member_payment_recorded', 'members', $memberId);
                header('Location: member-profile.php?id=' . $memberId . '&message=' . urlencode('Ödeme kaydı oluşturuldu.'));
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}

$aggregates = $member ? getMemberAggregates($pdo, $memberId) : ['sessions_purchased' => 0, 'sessions_completed' => 0, 'total_amount' => 0, 'last_payment' => null];
$ledgerRemaining = max(($aggregates['sessions_purchased'] ?? 0) - ($aggregates['sessions_completed'] ?? 0), 0);
$visualRemaining = max($ledgerRemaining, (int)($member['remaining_sessions'] ?? 0));

try {
    $paymentStmt = $pdo->prepare('SELECT mp.*, p.name AS package_name FROM member_payments mp LEFT JOIN packages p ON p.id = mp.package_id WHERE mp.member_id = ? ORDER BY mp.payment_date DESC, mp.created_at DESC');
    $paymentStmt->execute([$memberId]);
    $payments = $paymentStmt->fetchAll();
} catch (PDOException $e) {
    $payments = [];
}

try {
    $sessionStmt = $pdo->prepare('SELECT * FROM member_sessions WHERE member_id = ? ORDER BY session_date DESC, created_at DESC');
    $sessionStmt->execute([$memberId]);
    $sessions = $sessionStmt->fetchAll();
} catch (PDOException $e) {
    $sessions = [];
}

try {
    $metricStmt = $pdo->prepare('SELECT * FROM member_metrics WHERE member_id = ? ORDER BY measurement_date DESC, created_at DESC');
    $metricStmt->execute([$memberId]);
    $metrics = $metricStmt->fetchAll();
} catch (PDOException $e) {
    $metrics = [];
}

try {
    $packages = $pdo->query('SELECT id, name, session_count FROM packages WHERE is_active = 1 ORDER BY sort_order ASC, name ASC')->fetchAll();
} catch (PDOException $e) {
    $packages = [];
}

$metricChartLabels = [];
$metricChartWeights = [];
$metricChartBodyFat = [];

if (!empty($metrics)) {
    $orderedMetrics = array_reverse($metrics);
    foreach ($orderedMetrics as $metric) {
        $metricChartLabels[] = date('d.m', strtotime($metric['measurement_date']));
        $metricChartWeights[] = $metric['weight_kg'] !== null ? (float)$metric['weight_kg'] : null;
        $metricChartBodyFat[] = $metric['body_fat_percentage'] !== null ? (float)$metric['body_fat_percentage'] : null;
    }
}

$paymentTrendLabels = [];
$paymentTrendValues = [];

if (!empty($payments)) {
    $trendStmt = $pdo->prepare("SELECT DATE(payment_date) as payment_day, SUM(amount) as total FROM member_payments WHERE member_id = ? AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 180 DAY) GROUP BY payment_day ORDER BY payment_day ASC");
    $trendStmt->execute([$memberId]);
    $trendRows = $trendStmt->fetchAll();
    foreach ($trendRows as $row) {
        $paymentTrendLabels[] = date('d.m', strtotime($row['payment_day']));
        $paymentTrendValues[] = (float)$row['total'];
    }
}

$sessionTypeBreakdown = [];
if (!empty($sessions)) {
    $typeStmt = $pdo->prepare('SELECT session_type, COUNT(*) as total FROM member_sessions WHERE member_id = ? GROUP BY session_type HAVING session_type IS NOT NULL ORDER BY total DESC');
    $typeStmt->execute([$memberId]);
    $sessionTypeBreakdown = $typeStmt->fetchAll();
}

$latestMetric = $metrics[0] ?? null;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Profili - <?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>

            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Üye Profili</h1>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?> • <?php echo htmlspecialchars($member['member_code']); ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="members.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Üyelere Dön</a>
                        <a href="?print=1" class="btn btn-outline-info d-none d-md-inline-flex"><i class="bi bi-printer"></i> Yazdır</a>
                        <a href="members.php?action=edit&id=<?php echo $memberId; ?>" class="btn btn-primary"><i class="bi bi-pencil"></i> Profili Düzenle</a>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-primary">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Toplam Harcama</p>
                                <h3 class="mb-0">₺<?php echo number_format($aggregates['total_amount'] ?? 0, 2, ',', '.'); ?></h3>
                                <small class="text-white-50">Son ödeme: <?php echo $aggregates['last_payment'] ? date('d.m.Y', strtotime($aggregates['last_payment'])) : 'Henüz kayıt yok'; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-success">
                            <div class="card-body">
                                <p class="text-muted small mb-1 text-white-50">Satın Alınan Seans</p>
                                <h3 class="mb-0 text-white"><?php echo number_format($aggregates['sessions_purchased']); ?></h3>
                                <small class="text-white-50">Toplam paket sayısı: <?php echo count($payments); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-warning">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Tamamlanan Seans</p>
                                <h3 class="mb-0"><?php echo number_format($aggregates['sessions_completed']); ?></h3>
                                <small class="text-muted">Bu ay: <?php
                                    $monthlyCount = 0;
                                    foreach ($sessions as $session) {
                                        if (date('Y-m', strtotime($session['session_date'])) === date('Y-m')) {
                                            $monthlyCount++;
                                        }
                                    }
                                    echo $monthlyCount;
                                ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-info">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Kalan Seans</p>
                                <h3 class="mb-0"><?php echo number_format($visualRemaining); ?></h3>
                                <small class="text-muted">Üyelik bitişi: <?php echo $member['membership_end'] ? date('d.m.Y', strtotime($member['membership_end'])) : 'Belirtilmemiş'; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Vücut Kompozisyonu Trendleri</h5>
                                    <small class="text-muted">Kilogram ve yağ oranı değişimi</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#metricModal"><i class="bi bi-plus-circle"></i> Yeni Ölçüm</button>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($metricChartLabels)): ?>
                                <canvas id="metricChart" height="120"></canvas>
                                <?php else: ?>
                                <p class="text-center text-muted mb-0">Henüz ölçüm kaydı bulunmuyor.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Özet Bilgiler</h5>
                                <span class="badge <?php echo $member['is_active'] ? 'bg-success' : 'bg-danger'; ?>"><?php echo $member['is_active'] ? 'Aktif' : 'Pasif'; ?></span>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-5 text-muted">Telefon</dt>
                                    <dd class="col-7 mb-2"><?php echo htmlspecialchars($member['phone']); ?></dd>
                                    <dt class="col-5 text-muted">E-posta</dt>
                                    <dd class="col-7 mb-2"><?php echo htmlspecialchars($member['email'] ?? '-'); ?></dd>
                                    <dt class="col-5 text-muted">Üyelik Tipi</dt>
                                    <dd class="col-7 mb-2"><?php echo htmlspecialchars($member['membership_type'] ?? '-'); ?></dd>
                                    <dt class="col-5 text-muted">Acil Kişi</dt>
                                    <dd class="col-7 mb-2"><?php echo htmlspecialchars($member['emergency_contact'] ?? '-'); ?></dd>
                                    <dt class="col-5 text-muted">Adres</dt>
                                    <dd class="col-7 mb-0"><?php echo nl2br(htmlspecialchars($member['address'] ?? '-')); ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Ödeme Trendleri</h5>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="bi bi-plus-circle"></i> Ödeme Ekle</button>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($paymentTrendLabels)): ?>
                                <canvas id="paymentChart" height="120"></canvas>
                                <?php else: ?>
                                <p class="text-center text-muted mb-0">Henüz ödeme kaydı yok.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Seans Geçmişi</h5>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sessionModal"><i class="bi bi-plus-circle"></i> Seans Ekle</button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tarih</th>
                                                <th>Program</th>
                                                <th>Süre</th>
                                                <th>Yoğunluk</th>
                                                <th>Eğitmen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($sessions)): ?>
                                                <?php foreach (array_slice($sessions, 0, 12) as $session): ?>
                                                <tr>
                                                    <td><?php echo date('d.m.Y', strtotime($session['session_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($session['session_type'] ?? '-'); ?></td>
                                                    <td><?php echo $session['duration_minutes'] ? $session['duration_minutes'] . ' dk' : '-'; ?></td>
                                                    <td><?php echo htmlspecialchars($session['intensity_level'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($session['trainer_name'] ?? '-'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="5" class="text-center text-muted py-4">Henüz seans kaydı bulunmuyor.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Ödeme Geçmişi</h5>
                                <a href="payments.php?member=<?php echo $memberId; ?>" class="btn btn-sm btn-outline-secondary">Tümünü Gör</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tarih</th>
                                                <th>Paket</th>
                                                <th>Tutar</th>
                                                <th>Seans</th>
                                                <th>Yöntem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($payments)): ?>
                                                <?php foreach (array_slice($payments, 0, 10) as $payment): ?>
                                                <tr>
                                                    <td><?php echo date('d.m.Y', strtotime($payment['payment_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['package_name'] ?? '-'); ?></td>
                                                    <td>₺<?php echo number_format($payment['amount'], 2, ',', '.'); ?></td>
                                                    <td><span class="badge bg-primary-subtle text-primary"><?php echo $payment['sessions_purchased']; ?></span></td>
                                                    <td><?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="5" class="text-center text-muted py-4">Henüz ödeme kaydı bulunmuyor.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Vücut Ölçümleri</h5>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#metricModal"><i class="bi bi-clipboard-data"></i> Ölçüm Ekle</button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tarih</th>
                                                <th>Kilo</th>
                                                <th>Yağ %</th>
                                                <th>Kas Kütlesi</th>
                                                <th>Bel</th>
                                                <th>Kalça</th>
                                                <th>Not</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($metrics)): ?>
                                                <?php foreach (array_slice($metrics, 0, 12) as $metric): ?>
                                                <tr>
                                                    <td><?php echo date('d.m.Y', strtotime($metric['measurement_date'])); ?></td>
                                                    <td><?php echo $metric['weight_kg'] !== null ? number_format($metric['weight_kg'], 1) . ' kg' : '-'; ?></td>
                                                    <td><?php echo $metric['body_fat_percentage'] !== null ? number_format($metric['body_fat_percentage'], 1) . '%' : '-'; ?></td>
                                                    <td><?php echo $metric['muscle_mass_kg'] !== null ? number_format($metric['muscle_mass_kg'], 1) . ' kg' : '-'; ?></td>
                                                    <td><?php echo $metric['waist_cm'] !== null ? number_format($metric['waist_cm'], 1) . ' cm' : '-'; ?></td>
                                                    <td><?php echo $metric['hip_cm'] !== null ? number_format($metric['hip_cm'], 1) . ' cm' : '-'; ?></td>
                                                    <td><?php echo $metric['notes'] ? htmlspecialchars($metric['notes']) : '-'; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="7" class="text-center text-muted py-4">Henüz ölçüm kaydı bulunmuyor.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Notlar</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Hedefler:</strong></p>
                                <p class="text-muted"><?php echo $member['goals'] ? nl2br(htmlspecialchars($member['goals'])) : 'Henüz belirtilmemiş.'; ?></p>
                                <p class="mb-2"><strong>Sağlık Durumu:</strong></p>
                                <p class="text-muted"><?php echo $member['health_conditions'] ? nl2br(htmlspecialchars($member['health_conditions'])) : 'Henüz bilgi yok.'; ?></p>
                                <p class="mb-2"><strong>Admin Notları:</strong></p>
                                <p class="text-muted"><?php echo $member['notes'] ? nl2br(htmlspecialchars($member['notes'])) : 'Not eklenmemiş.'; ?></p>
                                <?php if ($latestMetric): ?>
                                <hr>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-activity"></i></div>
                                    <div>
                                        <p class="mb-0 fw-semibold">Son Ölçüm: <?php echo date('d.m.Y', strtotime($latestMetric['measurement_date'])); ?></p>
                                        <small class="text-muted">Kilo: <?php echo $latestMetric['weight_kg'] !== null ? number_format($latestMetric['weight_kg'], 1) . ' kg' : '-'; ?> • Yağ: <?php echo $latestMetric['body_fat_percentage'] !== null ? number_format($latestMetric['body_fat_percentage'], 1) . '%' : '-'; ?></small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($sessionTypeBreakdown)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Program Dağılımı</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($sessionTypeBreakdown as $type): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($type['session_type']); ?></span>
                                        <span class="badge bg-primary-subtle text-primary"><?php echo $type['total']; ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="metricModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" class="modal-content">
                <input type="hidden" name="form_action" value="add_metric">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Ölçüm Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tarih</label>
                            <input type="date" name="measurement_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kilo (kg)</label>
                            <input type="number" step="0.1" name="weight_kg" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Yağ Oranı (%)</label>
                            <input type="number" step="0.1" name="body_fat_percentage" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kas Kütlesi (kg)</label>
                            <input type="number" step="0.1" name="muscle_mass_kg" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Visseral Yağ</label>
                            <input type="number" step="0.1" name="visceral_fat" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vücut Suyu (%)</label>
                            <input type="number" step="0.1" name="water_percentage" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bel (cm)</label>
                            <input type="number" step="0.1" name="waist_cm" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kalça (cm)</label>
                            <input type="number" step="0.1" name="hip_cm" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Not</label>
                            <textarea name="metric_notes" rows="3" class="form-control" placeholder="Ölçüm hakkında kısa not"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="sessionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" class="modal-content">
                <input type="hidden" name="form_action" value="add_session">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Seans Kaydı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Seans Tarihi</label>
                            <input type="date" name="session_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Program Türü</label>
                            <input type="text" name="session_type" class="form-control" placeholder="Örn. Prime Power">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Süre (dk)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Yoğunluk</label>
                            <input type="text" name="intensity_level" class="form-control" placeholder="Örn. %75">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Eğitmen</label>
                            <input type="text" name="trainer_name" class="form-control" placeholder="Sorumlu eğitmen">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Not</label>
                            <textarea name="session_notes" rows="3" class="form-control" placeholder="Seans içeriği, gözlemler..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" class="modal-content">
                <input type="hidden" name="form_action" value="add_payment">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Ödeme Kaydı Oluştur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Ödeme Tarihi</label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tutar</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Para Birimi</label>
                            <input type="text" name="currency" class="form-control" value="TRY">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paket</label>
                            <select name="package_id" class="form-select">
                                <option value="">Seçiniz</option>
                                <?php foreach ($packages as $package): ?>
                                <option value="<?php echo $package['id']; ?>" data-sessions="<?php echo $package['session_count']; ?>"><?php echo htmlspecialchars($package['name']); ?> (<?php echo $package['session_count']; ?> seans)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Satılan Seans Sayısı</label>
                            <input type="number" name="sessions_purchased" class="form-control" placeholder="Örn. 8" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ödeme Yöntemi</label>
                            <input type="text" name="payment_method" class="form-control" placeholder="Nakit, Kredi Kartı...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referans / Fiş No</label>
                            <input type="text" name="reference_code" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Not</label>
                            <textarea name="payment_notes" rows="3" class="form-control" placeholder="Ödeme ile ilgili notlar"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const metricLabels = <?php echo json_encode($metricChartLabels); ?>;
        const metricWeights = <?php echo json_encode($metricChartWeights); ?>;
        const metricBodyFat = <?php echo json_encode($metricChartBodyFat); ?>;
        const paymentLabels = <?php echo json_encode($paymentTrendLabels); ?>;
        const paymentValues = <?php echo json_encode($paymentTrendValues); ?>;

        if (metricLabels.length && document.getElementById('metricChart')) {
            new Chart(document.getElementById('metricChart'), {
                type: 'line',
                data: {
                    labels: metricLabels,
                    datasets: [
                        {
                            label: 'Kilo (kg)',
                            data: metricWeights,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255,193,7,0.2)',
                            tension: 0.4,
                            spanGaps: true,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Yağ %',
                            data: metricBodyFat,
                            borderColor: '#0dcaf0',
                            backgroundColor: 'rgba(13,202,240,0.2)',
                            tension: 0.4,
                            spanGaps: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Kilo (kg)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Yağ %' }
                        }
                    }
                }
            });
        }

        if (paymentLabels.length && document.getElementById('paymentChart')) {
            new Chart(document.getElementById('paymentChart'), {
                type: 'bar',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        label: 'Günlük Ödeme (₺)',
                        data: paymentValues,
                        backgroundColor: 'rgba(13,110,253,0.6)',
                        borderRadius: 6,
                        maxBarThickness: 24
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: value => '₺' + value
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
