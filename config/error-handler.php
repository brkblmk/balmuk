<?php
/**
 * Prime EMS Error Handler and Monitoring System
 * Comprehensive error handling, logging, and monitoring
 */

class ErrorMonitor {
    private static $error_log_file;
    private static $performance_log_file;
    private static $initialized = false;

    /**
     * Initialize error monitoring system
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        // Create logs directory if it doesn't exist
        $logs_dir = __DIR__ . '/../logs';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }

        self::$error_log_file = $logs_dir . '/error.log';
        self::$performance_log_file = $logs_dir . '/performance.log';

        // Set custom error handler
        set_error_handler([__CLASS__, 'handleError']);

        // Set custom exception handler
        set_exception_handler([__CLASS__, 'handleException']);

        // Set custom shutdown function for fatal errors
        register_shutdown_function([__CLASS__, 'handleShutdown']);

        // Log script start
        self::logPerformance('SCRIPT_START', [
            'uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        self::$initialized = true;
    }

    /**
     * Custom error handler
     */
    public static function handleError($errno, $errstr, $errfile, $errline, $errcontext = null) {
        $error_types = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];

        $error_type = $error_types[$errno] ?? 'UNKNOWN_ERROR';

        $error_data = [
            'type' => $error_type,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'context' => self::sanitizeContext($errcontext),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            'server' => self::getServerInfo()
        ];

        self::logError($error_data);

        // Continue script execution for non-fatal errors
        if ($errno !== E_ERROR && $errno !== E_PARSE && $errno !== E_CORE_ERROR && $errno !== E_COMPILE_ERROR) {
            return true;
        }

        return false;
    }

    /**
     * Custom exception handler
     */
    public static function handleException($exception) {
        $error_data = [
            'type' => 'EXCEPTION',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTrace(),
            'server' => self::getServerInfo()
        ];

        self::logError($error_data);

        // Show user-friendly error page instead of default error
        if (!headers_sent()) {
            http_response_code(500);
            include __DIR__ . '/../templates/error-500.php';
            exit;
        }
    }

    /**
     * Handle shutdown for fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $error_data = [
                'type' => 'FATAL_ERROR',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'server' => self::getServerInfo()
            ];

            self::logError($error_data);
        }

        // Log script end performance
        self::logPerformance('SCRIPT_END', [
            'execution_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_usage' => memory_get_usage(true),
            'included_files' => count(get_included_files())
        ]);
    }

    /**
     * Log error to file
     */
    private static function logError($error_data) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'error' => $error_data
        ];

        $json_entry = json_encode($log_entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        if (file_put_contents(self::$error_log_file, $json_entry, FILE_APPEND | LOCK_EX) === false) {
            // Fallback to PHP error log if file write fails
            error_log('ErrorMonitor: Failed to write to error log file: ' . json_encode($error_data));
        }
    }

    /**
     * Log performance data
     */
    public static function logPerformance($event, $data = []) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'event' => $event,
            'data' => $data,
            'server' => self::getServerInfo()
        ];

        $json_entry = json_encode($log_entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        file_put_contents(self::$performance_log_file, $json_entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Sanitize error context to avoid logging sensitive data
     */
    private static function sanitizeContext($context) {
        if (!is_array($context)) {
            return $context;
        }

        $sensitive_keys = ['password', 'passwd', 'pwd', 'secret', 'token', 'key', 'api_key', 'apikey'];
        $sanitized = [];

        foreach ($context as $key => $value) {
            $lower_key = strtolower($key);
            if (in_array($lower_key, $sensitive_keys)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = is_array($value) ? self::sanitizeContext($value) : $value;
            }
        }

        return $sanitized;
    }

    /**
     * Get server information for logging
     */
    private static function getServerInfo() {
        return [
            'ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'hostname' => gethostname(),
            'php_version' => PHP_VERSION,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
    }

    /**
     * Get recent errors for monitoring dashboard
     */
    public static function getRecentErrors($limit = 50) {
        if (!file_exists(self::$error_log_file)) {
            return [];
        }

        $lines = array_slice(file(self::$error_log_file), -$limit);
        $errors = [];

        foreach ($lines as $line) {
            $entry = json_decode(trim($line), true);
            if ($entry) {
                $errors[] = $entry;
            }
        }

        return array_reverse($errors);
    }

    /**
     * Get performance metrics summary
     */
    public static function getPerformanceSummary($hours = 24) {
        if (!file_exists(self::$performance_log_file)) {
            return [];
        }

        $cutoff_time = time() - ($hours * 3600);
        $summary = [
            'total_requests' => 0,
            'avg_execution_time' => 0,
            'max_execution_time' => 0,
            'total_errors' => 0,
            'memory_peak_avg' => 0
        ];

        $execution_times = [];
        $memory_peaks = [];

        $handle = fopen(self::$performance_log_file, 'r');
        while (($line = fgets($handle)) !== false) {
            $entry = json_decode(trim($line), true);
            if ($entry && strtotime($entry['timestamp']) > $cutoff_time) {
                if ($entry['event'] === 'SCRIPT_START') {
                    $summary['total_requests']++;
                } elseif ($entry['event'] === 'SCRIPT_END') {
                    if (isset($entry['data']['execution_time'])) {
                        $execution_times[] = $entry['data']['execution_time'];
                    }
                    if (isset($entry['data']['memory_peak'])) {
                        $memory_peaks[] = $entry['data']['memory_peak'];
                    }
                }
            }
        }
        fclose($handle);

        if (!empty($execution_times)) {
            $summary['avg_execution_time'] = array_sum($execution_times) / count($execution_times);
            $summary['max_execution_time'] = max($execution_times);
        }

        if (!empty($memory_peaks)) {
            $summary['memory_peak_avg'] = array_sum($memory_peaks) / count($memory_peaks);
        }

        // Count errors
        if (file_exists(self::$error_log_file)) {
            $error_lines = file(self::$error_log_file);
            foreach ($error_lines as $line) {
                $entry = json_decode(trim($line), true);
                if ($entry && strtotime($entry['timestamp']) > $cutoff_time) {
                    $summary['total_errors']++;
                }
            }
        }

        return $summary;
    }
}

// Initialize error monitoring
ErrorMonitor::init();
?>