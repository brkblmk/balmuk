<?php
/**
 * Prime EMS Maintenance Script
 * Cache temizleme, log rotasyonu, database optimizasyonu
 */

// Config dosyalarını dahil et
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/performance.php';

class SystemMaintenance {
    private $logs_dir;
    private $cache_dir;
    private $backups_dir;

    public function __construct() {
        $this->logs_dir = __DIR__ . '/../logs';
        $this->cache_dir = __DIR__ . '/../cache';
        $this->backups_dir = __DIR__ . '/../backups';
    }

    /**
     * Ana bakım işlemi
     */
    public function runMaintenance() {
        echo "Prime EMS Maintenance Script Started\n";
        echo "=====================================\n";

        $start_time = microtime(true);

        try {
            // Cache temizleme
            echo "1. Cleaning cache...\n";
            $cache_cleaned = $this->cleanExpiredCache();
            echo "   Cleaned $cache_cleaned expired cache files\n";

            // Log rotasyonu
            echo "2. Rotating logs...\n";
            $logs_rotated = $this->rotateLogs();
            echo "   Rotated $logs_rotated log files\n";

            // Database optimizasyonu
            echo "3. Optimizing database...\n";
            $db_optimized = $this->optimizeDatabase();
            echo "   Optimized $db_optimized tables\n";

            // Eski yedekleri temizleme
            echo "4. Cleaning old backups...\n";
            $backups_cleaned = $this->cleanOldBackups();
            echo "   Cleaned $backups_cleaned old backup files\n";

            // Disk alanı kontrolü
            echo "5. Checking disk space...\n";
            $this->checkDiskSpace();

            // Bakım loglaması
            $this->logMaintenance([
                'cache_cleaned' => $cache_cleaned,
                'logs_rotated' => $logs_rotated,
                'db_optimized' => $db_optimized,
                'backups_cleaned' => $backups_cleaned,
                'execution_time' => microtime(true) - $start_time
            ]);

            echo "\nMaintenance completed successfully!\n";

        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $this->logMaintenance(['error' => $e->getMessage()]);
            exit(1);
        }
    }

    /**
     * Süresi dolmuş cache dosyalarını temizle
     */
    private function cleanExpiredCache() {
        if (!is_dir($this->cache_dir)) {
            return 0;
        }

        $files = glob($this->cache_dir . '/*.cache');
        $cleaned = 0;

        foreach ($files as $file) {
            if (!file_exists($file)) continue;

            $cache_data = unserialize(file_get_contents($file));

            if (isset($cache_data['created']) && isset($cache_data['expiry'])) {
                if (time() - $cache_data['created'] > $cache_data['expiry']) {
                    unlink($file);
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    /**
     * Log dosyalarını döndür (rotate)
     */
    private function rotateLogs() {
        if (!is_dir($this->logs_dir)) {
            return 0;
        }

        $log_files = ['error.log', 'performance.log', 'backup.log'];
        $rotated = 0;

        foreach ($log_files as $log_file) {
            $file_path = $this->logs_dir . '/' . $log_file;

            if (file_exists($file_path)) {
                $file_size = filesize($file_path);

                // 10MB'dan büyük logları döndür
                if ($file_size > 10485760) { // 10MB
                    $backup_path = $file_path . '.' . date('Y-m-d_H-i-s') . '.bak';
                    rename($file_path, $backup_path);

                    // Eski rotasyon dosyalarını temizle (7 günden eski)
                    $backup_files = glob($this->logs_dir . '/' . $log_file . '.*.bak');
                    foreach ($backup_files as $backup) {
                        if (filemtime($backup) < time() - (7 * 24 * 60 * 60)) {
                            unlink($backup);
                        }
                    }

                    $rotated++;
                }
            }
        }

        return $rotated;
    }

    /**
     * Database tablolarını optimize et
     */
    private function optimizeDatabase() {
        global $pdo;

        try {
            // Tüm tabloları al
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $stmt->closeCursor(); // Cursor'ı kapat

            $optimized = 0;

            // Optimize table için prepared statement kullan
            $optimizeStmt = $pdo->prepare("OPTIMIZE TABLE ?");
            $optimizeStmt->bindParam(1, $table);

            foreach ($tables as $table) {
                // Tabloyu optimize et
                $optimizeStmt->execute();
                $optimized++;
            }

            $optimizeStmt = null; // Statement'ı kapat

            return $optimized;

        } catch (PDOException $e) {
            throw new Exception("Database optimization failed: " . $e->getMessage());
        }
    }

    /**
     * Eski yedek dosyalarını temizle
     */
    private function cleanOldBackups() {
        if (!is_dir($this->backups_dir)) {
            return 0;
        }

        $backup_files = glob($this->backups_dir . '/backup_*.sql');
        $cleaned = 0;

        // 30 günden eski yedekleri sil
        $cutoff_time = time() - (30 * 24 * 60 * 60);

        foreach ($backup_files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Disk alanı kontrolü
     */
    private function checkDiskSpace() {
        $free_space = disk_free_space('/');
        $total_space = disk_total_space('/');
        $used_percentage = (($total_space - $free_space) / $total_space) * 100;

        echo sprintf("   Disk usage: %.2f%% (%.2f GB free)\n",
            $used_percentage,
            $free_space / (1024 * 1024 * 1024)
        );

        if ($used_percentage > 90) {
            echo "   WARNING: Disk usage is above 90%!\n";
        }
    }

    /**
     * Bakım işlemini logla
     */
    private function logMaintenance($data) {
        $log_file = $this->logs_dir . '/maintenance.log';

        if (!is_dir($this->logs_dir)) {
            mkdir($this->logs_dir, 0755, true);
        }

        $log_entry = date('Y-m-d H:i:s') . ' - ' . json_encode($data) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }

    /**
     * Sistem durumu raporu
     */
    public function generateSystemReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'cache_stats' => PerformanceOptimizer::getCacheStats(),
            'performance_summary' => ErrorMonitor::getPerformanceSummary(24),
            'database_size' => $this->getDatabaseSize(),
            'disk_usage' => $this->getDiskUsage()
        ];

        $report_file = $this->logs_dir . '/system-report_' . date('Y-m-d') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));

        echo "System report generated: $report_file\n";
        return $report;
    }

    /**
     * Database boyutu
     */
    private function getDatabaseSize() {
        global $pdo;

        try {
            $stmt = $pdo->query("
                SELECT
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ");
            $result = $stmt->fetch();
            return $result['size_mb'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Disk kullanımı
     */
    private function getDiskUsage() {
        $free_space = disk_free_space('/');
        $total_space = disk_total_space('/');

        return [
            'total_gb' => round($total_space / (1024 * 1024 * 1024), 2),
            'free_gb' => round($free_space / (1024 * 1024 * 1024), 2),
            'used_percentage' => round((($total_space - $free_space) / $total_space) * 100, 2)
        ];
    }
}

// Script çalıştırma kontrolü
if ($argc > 1) {
    $action = $argv[1];
    $maintenance = new SystemMaintenance();

    switch ($action) {
        case 'run':
            $maintenance->runMaintenance();
            break;
        case 'report':
            $maintenance->generateSystemReport();
            break;
        case 'cache':
            $cleaned = $maintenance->cleanExpiredCache();
            echo "Cleaned $cleaned expired cache files\n";
            break;
        case 'logs':
            $rotated = $maintenance->rotateLogs();
            echo "Rotated $rotated log files\n";
            break;
        default:
            echo "Usage: php maintenance.php [run|report|cache|logs]\n";
            exit(1);
    }
} else {
    // Varsayılan olarak tam bakım çalıştır
    $maintenance = new SystemMaintenance();
    $maintenance->runMaintenance();
}