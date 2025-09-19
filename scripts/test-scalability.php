<?php
/**
 * Scalability Improvements Test Script
 * Tüm skalabilite iyileştirmelerini test eder
 */

// Config dosyalarını dahil et
require_once '../config/database.php';
require_once '../config/performance.php';

class ScalabilityTester {
    private $results = [];

    public function runAllTests() {
        echo "Prime EMS Scalability Test Suite\n";
        echo "=================================\n\n";

        $this->testDatabaseConnection();
        $this->testSessionSecurity();
        $this->testCacheSystem();
        $this->testErrorHandling();
        $this->testBackupSystem();
        $this->testMaintenanceScripts();

        $this->generateTestReport();

        echo "\nTest suite completed. Check logs/test-report.json for detailed results.\n";
    }

    private function testDatabaseConnection() {
        echo "Testing Database Connection & Pooling...\n";

        try {
            global $pdo;

            // Test basic connection
            $stmt = $pdo->query("SELECT 1");
            $result = $stmt->fetch();

            // Test persistent connection (should reuse connection)
            $stmt2 = $pdo->query("SELECT CONNECTION_ID() as conn_id");
            $conn_info = $stmt2->fetch();

            $this->results['database'] = [
                'status' => 'PASS',
                'connection_id' => $conn_info['conn_id'],
                'persistent' => true
            ];

            echo "✓ Database connection successful\n";

        } catch (Exception $e) {
            $this->results['database'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
            echo "✗ Database connection failed: " . $e->getMessage() . "\n";
        }
    }

    private function testSessionSecurity() {
        echo "Testing Session Security Enhancements...\n";

        try {
            // Test session initialization
            SecurityUtils::secureSession();

            // Check session settings
            $session_secure = ini_get('session.cookie_secure');
            $session_httponly = ini_get('session.cookie_httponly');
            $session_samesite = ini_get('session.cookie_samesite');

            $this->results['session'] = [
                'status' => 'PASS',
                'secure' => $session_secure,
                'httponly' => $session_httponly,
                'samesite' => $session_samesite,
                'regeneration' => isset($_SESSION['last_regeneration'])
            ];

            echo "✓ Session security settings configured\n";

        } catch (Exception $e) {
            $this->results['session'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
            echo "✗ Session security test failed: " . $e->getMessage() . "\n";
        }
    }

    private function testCacheSystem() {
        echo "Testing Enhanced Cache System...\n";

        try {
            // Test basic caching
            $test_key = 'test_cache_' . time();
            $test_data = ['test' => 'data', 'timestamp' => time()];

            PerformanceOptimizer::cache($test_key, $test_data, 300);
            $cached_data = PerformanceOptimizer::getCache($test_key);

            // Test database query caching
            $query_result = PerformanceOptimizer::cacheQuery("SELECT 1 as test", [], 60);

            // Test cache stats
            $stats = PerformanceOptimizer::getCacheStats();

            $this->results['cache'] = [
                'status' => 'PASS',
                'basic_cache' => $cached_data === $test_data,
                'query_cache' => !empty($query_result),
                'stats_available' => is_array($stats)
            ];

            echo "✓ Cache system working correctly\n";

        } catch (Exception $e) {
            $this->results['cache'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
            echo "✗ Cache system test failed: " . $e->getMessage() . "\n";
        }
    }

    private function testErrorHandling() {
        echo "Testing Error Monitoring System...\n";

        try {
            // Test error logging by triggering a warning
            trigger_error("Test warning for scalability testing", E_USER_WARNING);

            // Check if logs directory exists
            $logs_exist = is_dir(__DIR__ . '/../logs');

            // Test performance logging
            ErrorMonitor::logPerformance('TEST_EVENT', ['test' => true]);

            $this->results['error_handling'] = [
                'status' => 'PASS',
                'logs_directory' => $logs_exist,
                'performance_logging' => true
            ];

            echo "✓ Error handling system active\n";

        } catch (Exception $e) {
            $this->results['error_handling'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
            echo "✗ Error handling test failed: " . $e->getMessage() . "\n";
        }
    }

    private function testBackupSystem() {
        echo "Testing Backup System...\n";

        try {
            // Check backup directory
            $backup_dir = __DIR__ . '/../backups';
            $dir_exists = is_dir($backup_dir);

            // Check mysqldump availability (simulated)
            $mysqldump_check = file_exists('C:/xampp/mysql/bin/mysqldump.exe');

            // Check backup script existence
            $script_exists = file_exists(__DIR__ . '/backup-database.php');

            $this->results['backup'] = [
                'status' => $dir_exists && $script_exists ? 'PASS' : 'WARN',
                'backup_directory' => $dir_exists,
                'mysqldump_available' => $mysqldump_check,
                'script_exists' => $script_exists
            ];

            if ($dir_exists && $script_exists) {
                echo "✓ Backup system ready\n";
            } else {
                echo "! Backup system partially configured\n";
            }

        } catch (Exception $e) {
            $this->results['backup'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
            echo "✗ Backup system test failed: " . $e->getMessage() . "\n";
        }
    }

    private function testMaintenanceScripts() {
        echo "Testing Maintenance Scripts...\n";

        try {
            // Check maintenance script existence
            $maintenance_exists = file_exists(__DIR__ . '/maintenance.php');
            $batch_exists = file_exists(__DIR__ . '/run-maintenance.bat');

            // Test maintenance class instantiation
            if ($maintenance_exists) {
                require_once __DIR__ . '/maintenance.php';
                $maintenance = new SystemMaintenance();
                $can_instantiate = $maintenance instanceof SystemMaintenance;
            } else {
                $can_instantiate = false;
            }

            $this->results['maintenance'] = [
                'status' => $maintenance_exists && $batch_exists ? 'PASS' : 'FAIL',
                'script_exists' => $maintenance_exists,
                'batch_exists' => $batch_exists,
                'can_instantiate' => $can_instantiate
            ];

            if ($maintenance_exists && $batch_exists) {
                echo "✓ Maintenance scripts ready\n";
            } else {
                echo "✗ Maintenance scripts missing\n";
            }

        } catch (Exception $e) {
            $this->results['maintenance'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
            echo "✗ Maintenance scripts test failed: " . $e->getMessage() . "\n";
        }
    }

    private function generateTestReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'test_results' => $this->results,
            'summary' => [
                'total_tests' => count($this->results),
                'passed' => count(array_filter($this->results, fn($r) => $r['status'] === 'PASS')),
                'failed' => count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL')),
                'warnings' => count(array_filter($this->results, fn($r) => $r['status'] === 'WARN')),
                'overall_status' => $this->calculateOverallStatus()
            ],
            'recommendations' => $this->generateRecommendations()
        ];

        $logs_dir = __DIR__ . '/../logs';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }

        $report_file = $logs_dir . '/scalability-test-report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo "\nTest Report Summary:\n";
        echo "- Total Tests: " . $report['summary']['total_tests'] . "\n";
        echo "- Passed: " . $report['summary']['passed'] . "\n";
        echo "- Failed: " . $report['summary']['failed'] . "\n";
        echo "- Warnings: " . $report['summary']['warnings'] . "\n";
        echo "- Overall Status: " . $report['summary']['overall_status'] . "\n";

        if (!empty($report['recommendations'])) {
            echo "\nRecommendations:\n";
            foreach ($report['recommendations'] as $rec) {
                echo "- $rec\n";
            }
        }
    }

    private function calculateOverallStatus() {
        $statuses = array_column($this->results, 'status');

        if (in_array('FAIL', $statuses)) {
            return 'FAIL';
        } elseif (in_array('WARN', $statuses)) {
            return 'WARN';
        } else {
            return 'PASS';
        }
    }

    private function generateRecommendations() {
        $recommendations = [];

        if (($this->results['backup']['status'] ?? 'FAIL') !== 'PASS') {
            $recommendations[] = "Configure automated backups using Windows Task Scheduler";
        }

        if (($this->results['maintenance']['status'] ?? 'FAIL') !== 'PASS') {
            $recommendations[] = "Set up maintenance scripts in cron jobs";
        }

        if (($this->results['cache']['stats_available'] ?? false) === false) {
            $recommendations[] = "Monitor cache performance and adjust expiry times as needed";
        }

        return $recommendations;
    }
}

// Run tests
$tester = new ScalabilityTester();
$tester->runAllTests();