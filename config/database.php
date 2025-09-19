<?php
// Prime EMS Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'prime_ems_new');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Include error monitoring system
require_once __DIR__ . '/error-handler.php';
require_once __DIR__ . '/security.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true, // Connection pooling için persistent bağlantılar
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true // Buffered query için
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Helper Functions
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

function updateSetting($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

function getActiveRecords($table, $limit = null, $orderBy = 'sort_order ASC, created_at DESC') {
    global $pdo;
    $sql = "SELECT * FROM $table WHERE is_active = 1 ORDER BY $orderBy";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function logActivity($action, $module = null, $recordId = null, $adminId = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, module, record_id, ip_address, user_agent) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $adminId ?? ($_SESSION['admin_id'] ?? null),
            $action,
            $module,
            $recordId,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        // Log hatası sessizce geçilir
    }
}

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>