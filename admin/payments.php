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

$message = $_GET['message'] ?? '';
$error = '';

$filterMember = isset($_GET['member']) ? (int)$_GET['member'] : 0;
$filterRange = $_GET['range'] ?? '30';
$filterMethod = $_GET['method'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!SecurityUtils::verifyCSRFToken($csrfToken)) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
        try {
            $memberId = (int)SecurityUtils::sanitizeInput($_POST['member_id'] ?? 0, 'int');
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
            $packageId = !empty($_POST['package_id']) ? (int)SecurityUtils::sanitizeInput($_POST['package_id'], 'int') : null;
            $amount = (float)SecurityUtils::sanitizeInput($_POST['amount'] ?? 0, 'float');
            $currency = strtoupper(SecurityUtils::sanitizeInput($_POST['currency'] ?? 'TRY', 'string'));
            $sessionsPurchased = (int)SecurityUtils::sanitizeInput($_POST['sessions_purchased'] ?? 0, 'int');
            $method = SecurityUtils::sanitizeInput($_POST['payment_method'] ?? '', 'string');
            $reference = SecurityUtils::sanitizeInput($_POST['reference_code'] ?? '', 'string');
            $notes = SecurityUtils::sanitizeInput($_POST['payment_notes'] ?? '', 'html');

            if ($memberId <= 0) {
                throw new InvalidArgumentException('Lütfen geçerli bir üye seçin.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO member_payments (member_id, package_id, amount, currency, sessions_purchased, payment_date, payment_method, reference_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$memberId, $packageId ?: null, $amount, $currency ?: 'TRY', $sessionsPurchased, $paymentDate, $method ?: null, $reference ?: null, $notes ?: null]);

            if ($sessionsPurchased > 0) {
                $update = $pdo->prepare('UPDATE members SET total_sessions = total_sessions + ?, remaining_sessions = remaining_sessions + ? WHERE id = ?');
                $update->execute([$sessionsPurchased, $sessionsPurchased, $memberId]);
            }

            $pdo->commit();

            logActivity('member_payment_recorded', 'payments', $pdo->lastInsertId());
            header('Location: payments.php?message=' . urlencode('Ödeme kaydı başarıyla eklendi.'));
            exit;
        } catch (InvalidArgumentException $invalid) {
            $error = $invalid->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Ödeme kaydedilirken hata oluştu: ' . $e->getMessage();
        }
    }
}

try {
    $members = $pdo->query('SELECT id, first_name, last_name FROM members WHERE is_active = 1 ORDER BY first_name ASC')->fetchAll();
} catch (PDOException $e) {
    $members = [];
}

try {
    $packages = $pdo->query('SELECT id, name, session_count FROM packages WHERE is_active = 1 ORDER BY sort_order ASC, name ASC')->fetchAll();
} catch (PDOException $e) {
    $packages = [];
}

$where = [];
$params = [];

if ($filterMember > 0) {
    $where[] = 'mp.member_id = ?';
    $params[] = $filterMember;
}

if ($filterMethod) {
    $where[] = 'mp.payment_method = ?';
    $params[] = $filterMethod;
}

switch ($filterRange) {
    case '7':
        $where[] = 'mp.payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        break;
    case '90':
        $where[] = 'mp.payment_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
        break;
    case 'year':
        $where[] = 'YEAR(mp.payment_date) = YEAR(CURDATE())';
        break;
    case 'all':
        // no additional filter
        break;
    case '30':
    default:
        $where[] = 'mp.payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
        $filterRange = '30';
        break;
}

$paymentsSql = 'SELECT mp.*, m.first_name, m.last_name, p.name AS package_name FROM member_payments mp LEFT JOIN members m ON m.id = mp.member_id LEFT JOIN packages p ON p.id = mp.package_id';

if ($where) {
    $paymentsSql .= ' WHERE ' . implode(' AND ', $where);
}

$paymentsSql .= ' ORDER BY mp.payment_date DESC, mp.created_at DESC LIMIT 200';

try {
    $paymentStmt = $pdo->prepare($paymentsSql);
    $paymentStmt->execute($params);
    $payments = $paymentStmt->fetchAll();
} catch (PDOException $e) {
    $payments = [];
}

$analytics = getPaymentAnalytics($pdo);

try {
    $topMembersStmt = $pdo->query('SELECT m.id, m.first_name, m.last_name, SUM(mp.amount) as total_amount, SUM(mp.sessions_purchased) as total_sessions FROM member_payments mp INNER JOIN members m ON m.id = mp.member_id GROUP BY m.id, m.first_name, m.last_name ORDER BY total_amount DESC LIMIT 5');
    $topMembers = $topMembersStmt->fetchAll();
} catch (PDOException $e) {
    $topMembers = [];
}

try {
    $recentMethodsStmt = $pdo->query('SELECT payment_method, COUNT(*) as count FROM member_payments WHERE payment_method IS NOT NULL AND payment_method != "" GROUP BY payment_method ORDER BY count DESC');
    $paymentMethods = $recentMethodsStmt->fetchAll();
} catch (PDOException $e) {
    $paymentMethods = [];
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödemeler - Prime EMS Admin</title>
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
                        <h1 class="h3 mb-1">Üye Ödemeleri</h1>
                        <p class="text-muted mb-0">Satılan paketler, seanslar ve gelir trendlerini yönetin</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="bi bi-plus-circle me-1"></i>Yeni Ödeme</button>
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

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-primary">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Bu Yıl Toplam</p>
                                <h3 class="mb-0">₺<?php echo number_format($analytics['year_total'], 2, ',', '.'); ?></h3>
                                <small class="text-white-50">1 Ocak - <?php echo date('d.m.Y'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-success">
                            <div class="card-body">
                                <p class="text-muted small mb-1 text-white-50">Son 30 Gün</p>
                                <h3 class="mb-0 text-white">₺<?php echo number_format($analytics['last_30_total'], 2, ',', '.'); ?></h3>
                                <small class="text-white-50">Günlük ortalama: ₺<?php echo number_format(($analytics['last_30_total'] / 30), 2, ',', '.'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-warning">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Geçen Ay</p>
                                <h3 class="mb-0">₺<?php echo number_format($analytics['last_month_total'], 2, ',', '.'); ?></h3>
                                <small class="text-muted"><?php echo date('F', strtotime('-1 month')); ?> dönemi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm gradient-card gradient-card-info">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Aktif Üye Geliri</p>
                                <h3 class="mb-0">₺<?php echo number_format(array_sum(array_column($topMembers, 'total_amount')), 2, ',', '.'); ?></h3>
                                <small class="text-muted">İlk 5 üye toplamı</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex flex-wrap gap-3 align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0">Ödeme Trendleri</h5>
                            <small class="text-muted">Aylık toplam gelir ve son 30 gün performansı</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <form class="d-flex flex-wrap gap-2" method="GET" action="">
                                <input type="hidden" name="range" value="<?php echo htmlspecialchars($filterRange); ?>">
                                <select name="member" class="form-select">
                                    <option value="0">Tüm Üyeler</option>
                                    <?php foreach ($members as $memberOption): ?>
                                    <option value="<?php echo $memberOption['id']; ?>" <?php echo $filterMember == $memberOption['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($memberOption['first_name'] . ' ' . $memberOption['last_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="method" class="form-select">
                                    <option value="">Tüm Yöntemler</option>
                                    <?php foreach ($paymentMethods as $methodOption): ?>
                                    <option value="<?php echo htmlspecialchars($methodOption['payment_method']); ?>" <?php echo $filterMethod === $methodOption['payment_method'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($methodOption['payment_method']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="range" class="form-select">
                                    <option value="7" <?php echo $filterRange === '7' ? 'selected' : ''; ?>>7 Gün</option>
                                    <option value="30" <?php echo $filterRange === '30' ? 'selected' : ''; ?>>30 Gün</option>
                                    <option value="90" <?php echo $filterRange === '90' ? 'selected' : ''; ?>>90 Gün</option>
                                    <option value="year" <?php echo $filterRange === 'year' ? 'selected' : ''; ?>>Bu Yıl</option>
                                    <option value="all" <?php echo $filterRange === 'all' ? 'selected' : ''; ?>>Tümü</option>
                                </select>
                                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel"></i> Filtrele</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-xl-7">
                                <h6 class="text-muted">Aylık Toplam Gelir (₺)</h6>
                                <canvas id="monthlyChart" height="140"></canvas>
                            </div>
                            <div class="col-xl-5">
                                <h6 class="text-muted">Son 30 Günlük Günlük Gelir</h6>
                                <canvas id="dailyChart" height="140"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Ödeme Kayıtları</h5>
                                <span class="badge bg-primary-subtle text-primary"><?php echo count($payments); ?> kayıt</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tarih</th>
                                                <th>Üye</th>
                                                <th>Paket</th>
                                                <th>Tutar</th>
                                                <th>Seans</th>
                                                <th>Yöntem</th>
                                                <th>Referans</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($payments)): ?>
                                                <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo date('d.m.Y', strtotime($payment['payment_date'])); ?></td>
                                                    <td>
                                                        <a href="member-profile.php?id=<?php echo $payment['member_id']; ?>" class="fw-semibold text-decoration-none">
                                                            <?php echo htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''))); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($payment['package_name'] ?? '-'); ?></td>
                                                    <td>₺<?php echo number_format($payment['amount'], 2, ',', '.'); ?></td>
                                                    <td><span class="badge bg-success-subtle text-success"><?php echo $payment['sessions_purchased']; ?></span></td>
                                                    <td><?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['reference_code'] ?? '-'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="7" class="text-center text-muted py-4">Seçili kriterlere göre ödeme kaydı bulunamadı.</td></tr>
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
                                <h5 class="mb-0">En Çok Gelir Getiren Üyeler</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($topMembers)): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($topMembers as $rank => $top): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-dark-subtle text-dark me-2">#<?php echo $rank + 1; ?></span>
                                            <a href="member-profile.php?id=<?php echo $top['id']; ?>" class="fw-semibold text-decoration-none">
                                                <?php echo htmlspecialchars($top['first_name'] . ' ' . $top['last_name']); ?>
                                            </a>
                                            <div class="text-muted small">Toplam seans: <?php echo (int)$top['total_sessions']; ?></div>
                                        </div>
                                        <span class="fw-semibold">₺<?php echo number_format($top['total_amount'], 2, ',', '.'); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p class="text-muted mb-0">Henüz ödeme kaydı olmadığı için liste boş.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Ödeme Yöntemi Dağılımı</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($analytics['method_breakdown'])): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($analytics['method_breakdown'] as $methodRow): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($methodRow['payment_method']); ?></span>
                                        <span class="fw-semibold">₺<?php echo number_format($methodRow['total'], 2, ',', '.'); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p class="text-muted mb-0">Ödeme yöntemleri henüz kaydedilmedi.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" class="modal-content">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Ödeme Kaydı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Üye</label>
                            <select name="member_id" class="form-select" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($members as $memberOption): ?>
                                <option value="<?php echo $memberOption['id']; ?>"><?php echo htmlspecialchars($memberOption['first_name'] . ' ' . $memberOption['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tarih</label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tutar (₺)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paket</label>
                            <select name="package_id" class="form-select">
                                <option value="">Seçiniz</option>
                                <?php foreach ($packages as $package): ?>
                                <option value="<?php echo $package['id']; ?>"><?php echo htmlspecialchars($package['name']); ?> (<?php echo $package['session_count']; ?> seans)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Satılan Seans</label>
                            <input type="number" name="sessions_purchased" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Para Birimi</label>
                            <input type="text" name="currency" class="form-control" value="TRY">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ödeme Yöntemi</label>
                            <input type="text" name="payment_method" class="form-control" placeholder="Nakit, Kredi Kartı...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Referans</label>
                            <input type="text" name="reference_code" class="form-control" placeholder="Fiş / dekont no">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Not</label>
                            <input type="text" name="payment_notes" class="form-control" placeholder="Kısa not">
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
        const monthlyLabels = <?php echo json_encode($analytics['monthly_labels']); ?>;
        const monthlyTotals = <?php echo json_encode($analytics['monthly_totals']); ?>;
        const dailyLabels = <?php echo json_encode($analytics['daily_labels']); ?>;
        const dailyTotals = <?php echo json_encode($analytics['daily_totals']); ?>;

        if (monthlyLabels.length && document.getElementById('monthlyChart')) {
            new Chart(document.getElementById('monthlyChart'), {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Aylık Gelir',
                        data: monthlyTotals,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.2)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
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

        if (dailyLabels.length && document.getElementById('dailyChart')) {
            new Chart(document.getElementById('dailyChart'), {
                type: 'bar',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Günlük Gelir',
                        data: dailyTotals,
                        backgroundColor: 'rgba(255,193,7,0.8)',
                        borderRadius: 6,
                        maxBarThickness: 28
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
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
