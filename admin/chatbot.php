<?php
require_once '../config/database.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_config') {
            $configs = $_POST['config'] ?? [];
            
            foreach ($configs as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO chatbot_config (config_key, config_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)
                ");
                $stmt->execute([$key, $value]);
            }
            
            logActivity('Chatbot configuration updated', 'chatbot');
            $message = 'Chatbot ayarları başarıyla güncellendi!';
            $messageType = 'success';
        }
        
        if ($action === 'test_webhook') {
            $webhook_url = $_POST['webhook_url'] ?? '';
            
            // Test webhook
            $test_data = json_encode([
                'test' => true,
                'message' => 'Prime EMS Chatbot Test',
                'timestamp' => time()
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code >= 200 && $http_code < 300) {
                $message = 'Webhook testi başarılı! HTTP Code: ' . $http_code;
                $messageType = 'success';
            } else {
                $message = 'Webhook testi başarısız! HTTP Code: ' . $http_code;
                $messageType = 'danger';
            }
        }
        
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Chatbot konfigürasyonunu getir
try {
    $stmt = $pdo->query("SELECT config_key, config_value FROM chatbot_config");
    $config_rows = $stmt->fetchAll();
    
    $chatbot_config = [];
    foreach ($config_rows as $row) {
        $chatbot_config[$row['config_key']] = $row['config_value'];
    }
} catch (PDOException $e) {
    $chatbot_config = [];
}

// Chatbot loglarını getir
try {
    $stmt = $pdo->query("
        SELECT * FROM chatbot_logs 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $recent_logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_logs = [];
}

// Chatbot randevularını getir
try {
    $stmt = $pdo->query("
        SELECT * FROM chatbot_appointments 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recent_appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_appointments = [];
}

// İstatistikler
try {
    $total_conversations = $pdo->query("SELECT COUNT(DISTINCT phone) FROM chatbot_logs")->fetchColumn() ?: 0;
    $total_messages = $pdo->query("SELECT COUNT(*) FROM chatbot_logs")->fetchColumn() ?: 0;
    $total_bot_appointments = $pdo->query("SELECT COUNT(*) FROM chatbot_appointments")->fetchColumn() ?: 0;
    $active_conversations = $pdo->query("SELECT COUNT(DISTINCT phone) FROM chatbot_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_conversations = $total_messages = $total_bot_appointments = $active_conversations = 0;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Yönetimi - Prime EMS Admin</title>
    
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
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Chatbot Yönetimi</h1>
                        <p class="text-muted">AI chatbot ayarları ve performans takibi</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="testChatbot()">
                            <i class="bi bi-play-circle me-1"></i>Chatbot Test Et
                        </button>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
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
                                        <p class="text-muted mb-1">Toplam Konuşma</p>
                                        <h3 class="mb-0"><?php echo number_format($total_conversations); ?></h3>
                                    </div>
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-chat-dots-fill"></i>
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
                                        <p class="text-muted mb-1">Toplam Mesaj</p>
                                        <h3 class="mb-0"><?php echo number_format($total_messages); ?></h3>
                                    </div>
                                    <div class="icon-box bg-success bg-opacity-10 text-success">
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
                                        <p class="text-muted mb-1">Bot Randevuları</p>
                                        <h3 class="mb-0"><?php echo number_format($total_bot_appointments); ?></h3>
                                    </div>
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-calendar-event"></i>
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
                                        <p class="text-muted mb-1">Aktif (24 Saat)</p>
                                        <h3 class="mb-0"><?php echo number_format($active_conversations); ?></h3>
                                    </div>
                                    <div class="icon-box bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-activity"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <!-- Configuration Panel -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-gear-fill text-primary me-2"></i>
                                    Chatbot Konfigürasyonu
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="update_config">
                                    
                                    <div class="row g-3">
                                        <!-- Basic Settings -->
                                        <div class="col-12">
                                            <h6 class="text-muted mb-3">Temel Ayarlar</h6>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="whatsapp_number" class="form-label">WhatsApp Numarası</label>
                                            <input type="tel" class="form-control" id="whatsapp_number" name="config[whatsapp_number]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['whatsapp_number'] ?? ''); ?>" 
                                                   placeholder="905XXXXXXXXX">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="enabled" class="form-label">Durum</label>
                                            <select class="form-select" id="enabled" name="config[enabled]">
                                                <option value="1" <?php echo ($chatbot_config['enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="0" <?php echo ($chatbot_config['enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>Pasif</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="bubble_text" class="form-label">Chatbot Bubble Metni</label>
                                            <input type="text" class="form-control" id="bubble_text" name="config[bubble_text]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['bubble_text'] ?? ''); ?>" 
                                                   placeholder="Ücretsiz Deneme Dersi Randevusu Al!">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="bubble_delay" class="form-label">Bubble Gecikmesi (ms)</label>
                                            <input type="number" class="form-control" id="bubble_delay" name="config[bubble_delay]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['bubble_delay'] ?? '5000'); ?>" 
                                                   min="0" max="60000">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="bubble_duration" class="form-label">Bubble Süresi (ms)</label>
                                            <input type="number" class="form-control" id="bubble_duration" name="config[bubble_duration]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['bubble_duration'] ?? '10000'); ?>" 
                                                   min="1000" max="300000">
                                        </div>
                                        
                                        <!-- Integration Settings -->
                                        <div class="col-12 mt-4">
                                            <h6 class="text-muted mb-3">Entegrasyon Ayarları</h6>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="webhook_url" class="form-label">Webhook URL</label>
                                            <div class="input-group">
                                                <input type="url" class="form-control" id="webhook_url" name="config[webhook_url]" 
                                                       value="<?php echo htmlspecialchars($chatbot_config['webhook_url'] ?? ''); ?>" 
                                                       placeholder="https://your-n8n-instance.com/webhook/prime-ems">
                                                <button class="btn btn-outline-secondary" type="button" onclick="testWebhook()">
                                                    <i class="bi bi-play"></i> Test
                                                </button>
                                            </div>
                                            <div class="form-text">N8N veya benzer otomasyon platformu webhook URL'si</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="meta_token" class="form-label">Meta Verification Token</label>
                                            <input type="text" class="form-control" id="meta_token" name="config[meta_token]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['meta_token'] ?? ''); ?>" 
                                                   placeholder="Meta WhatsApp Business API Token">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="openai_key" class="form-label">OpenAI API Key</label>
                                            <input type="password" class="form-control" id="openai_key" name="config[openai_key]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['openai_key'] ?? ''); ?>" 
                                                   placeholder="sk-...">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="supabase_url" class="form-label">Supabase URL</label>
                                            <input type="url" class="form-control" id="supabase_url" name="config[supabase_url]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['supabase_url'] ?? ''); ?>" 
                                                   placeholder="https://xxx.supabase.co">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="supabase_key" class="form-label">Supabase Anon Key</label>
                                            <input type="password" class="form-control" id="supabase_key" name="config[supabase_key]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['supabase_key'] ?? ''); ?>" 
                                                   placeholder="eyJ...">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="google_calendar_id" class="form-label">Google Calendar ID</label>
                                            <input type="text" class="form-control" id="google_calendar_id" name="config[google_calendar_id]" 
                                                   value="<?php echo htmlspecialchars($chatbot_config['google_calendar_id'] ?? 'primary'); ?>" 
                                                   placeholder="primary">
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Sıfırla
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>Ayarları Kaydet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Panel -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-activity text-success me-2"></i>
                                    Chatbot Durumu
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="status-dot bg-<?php echo ($chatbot_config['enabled'] ?? '1') == '1' ? 'success' : 'danger'; ?> me-2"></div>
                                    <span><?php echo ($chatbot_config['enabled'] ?? '1') == '1' ? 'Aktif' : 'Pasif'; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Son Aktivite</small>
                                    <div><?php echo $recent_logs ? date('d.m.Y H:i', strtotime($recent_logs[0]['created_at'])) : 'Henüz aktivite yok'; ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Webhook Durumu</small>
                                    <div>
                                        <?php if (empty($chatbot_config['webhook_url'])): ?>
                                        <span class="text-warning">Ayarlanmamış</span>
                                        <?php else: ?>
                                        <span class="text-success">Aktif</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-lightbulb text-warning me-2"></i>
                                    İpuçları
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        WhatsApp Business API kullanın
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Webhook URL'sini test edin
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        OpenAI API limitlerini takip edin
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Chatbot loglarını düzenli kontrol edin
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Logs & Recent Activity -->
                <div class="row g-4 mt-2">
                    <!-- Recent Logs -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list-ul text-primary me-2"></i>
                                        Son Chatbot Logları
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshLogs()">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tarih</th>
                                                <th>Telefon</th>
                                                <th>Mesaj</th>
                                                <th>Yanıt</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_logs)): ?>
                                            <?php foreach ($recent_logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <small><?php echo date('d.m H:i', strtotime($log['created_at'])); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['phone']); ?></td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($log['message']); ?>">
                                                        <?php echo htmlspecialchars(mb_substr($log['message'], 0, 50)); ?>...
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($log['response']); ?>">
                                                        <?php echo htmlspecialchars(mb_substr($log['response'], 0, 50)); ?>...
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $log['status'] == 'success' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($log['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    Henüz chatbot logu bulunmuyor
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bot Appointments -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-calendar-event text-primary me-2"></i>
                                    Bot Randevuları
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_appointments)): ?>
                                <?php foreach (array_slice($recent_appointments, 0, 5) as $appointment): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($appointment['name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php 
                                            echo match($appointment['status']) {
                                                'confirmed' => 'success',
                                                'pending' => 'warning',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                        <br><small class="text-muted">
                                            <?php echo date('d.m H:i', strtotime($appointment['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                                    <p>Henüz chatbot randevusu yok</p>
                                </div>
                                <?php endif; ?>
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
        function testWebhook() {
            const webhookUrl = document.getElementById('webhook_url').value;
            
            if (!webhookUrl) {
                alert('Lütfen webhook URL\'sini girin');
                return;
            }
            
            // Create a form to test webhook
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.name = 'action';
            actionInput.value = 'test_webhook';
            form.appendChild(actionInput);
            
            const urlInput = document.createElement('input');
            urlInput.name = 'webhook_url';
            urlInput.value = webhookUrl;
            form.appendChild(urlInput);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function testChatbot() {
            // Open chatbot in new window
            const width = 500;
            const height = 700;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            window.open(
                '../chatbot.php',
                'ChatbotTest',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no`
            );
        }
        
        function refreshLogs() {
            location.reload();
        }
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            });
        })();
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
    </style>
</body>
</html>