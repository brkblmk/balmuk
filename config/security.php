<?php
/**
 * Prime EMS Studios - Security Configuration
 * Comprehensive security measures and headers
 */

// Security Headers
function setPrimeEMSSecurityHeaders() {
    // Prevent CSRF attacks
    if (!headers_sent()) {
        // Development vs Production CSP
        $is_development = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ||
                         (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false);

        if ($is_development) {
            // Development: Allow source maps for debugging
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https: blob:; media-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none';";
        } else {
            // Production: Stricter CSP, no source maps for performance
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https: blob:; media-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none';";
        }

        header("Content-Security-Policy: " . $csp);
        
        // X-Frame-Options
        header('X-Frame-Options: DENY');
        
        // X-Content-Type-Options
        header('X-Content-Type-Options: nosniff');
        
        // X-XSS-Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        
        // HSTS (only for HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Remove server information
        header_remove('X-Powered-By');
        header_remove('Server');
    }
}

// Input Sanitization Functions
class SecurityUtils {
    
    /**
     * Sanitize user input
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            
            case 'phone':
                return preg_replace('/[^0-9+\-\(\)\s]/', '', trim($input));
            
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            
            case 'html':
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            
            case 'sql':
                // For database queries - use with PDO prepared statements
                return addslashes(trim($input));
            
            default: // string
                return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate input data
     */
    public static function validateInput($input, $type, $options = []) {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
            
            case 'phone':
                $cleaned = preg_replace('/[^0-9]/', '', $input);
                return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
            
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL) !== false;
            
            case 'int':
                $min = $options['min'] ?? null;
                $max = $options['max'] ?? null;
                $value = filter_var($input, FILTER_VALIDATE_INT);
                
                if ($value === false) return false;
                if ($min !== null && $value < $min) return false;
                if ($max !== null && $value > $max) return false;
                
                return true;
            
            case 'string':
                $min_length = $options['min_length'] ?? 0;
                $max_length = $options['max_length'] ?? 10000;
                $length = mb_strlen($input);
                
                return $length >= $min_length && $length <= $max_length;
            
            default:
                return !empty($input);
        }
    }
    
    /**
     * Generate CSRF Token
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF Token
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Rate Limiting
     */
    public static function checkRateLimit($action, $max_attempts = 5, $time_window = 300) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . $action . '_' . (($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $current_time = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_attempt' => $current_time];
        }
        
        $rate_data = $_SESSION[$key];
        
        // Reset if time window has passed
        if (($current_time - $rate_data['first_attempt']) > $time_window) {
            $_SESSION[$key] = ['count' => 1, 'first_attempt' => $current_time];
            return true;
        }
        
        // Check if limit exceeded
        if ($rate_data['count'] >= $max_attempts) {
            return false;
        }
        
        // Increment counter
        $_SESSION[$key]['count']++;
        return true;
    }
    
    /**
     * File Upload Security
     */
    public static function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) { // 5MB default
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Invalid file upload'];
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            return ['valid' => false, 'error' => 'File size exceeds limit'];
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!empty($allowed_types) && !in_array($file_type, $allowed_types)) {
            return ['valid' => false, 'error' => 'File type not allowed'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerous_extensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi', 'exe', 'bat'];
        
        if (in_array($extension, $dangerous_extensions)) {
            return ['valid' => false, 'error' => 'Dangerous file extension'];
        }
        
        // Generate secure filename
        $secure_name = bin2hex(random_bytes(16)) . '.' . $extension;
        
        return ['valid' => true, 'secure_name' => $secure_name, 'type' => $file_type];
    }
    
    /**
     * Password Security
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function validatePasswordStrength($password) {
        if (strlen($password) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain uppercase letter'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain lowercase letter'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain number'];
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain special character'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * IP Address Utilities
     */
    public static function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Enhanced Session Security with Hijacking Protection
     */
    public static function secureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session configuration
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 1800); // 30 minutes
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);

            // Set session save path to a secure directory
            $session_path = session_save_path();
            if (!$session_path || !is_writable($session_path)) {
                $custom_session_path = sys_get_temp_dir() . '/prime_ems_sessions';
                if (!is_dir($custom_session_path)) {
                    mkdir($custom_session_path, 0700, true);
                }
                session_save_path($custom_session_path);
            }

            session_start();

            // Enhanced session hijacking protection
            $current_ip = self::getClientIP();
            $current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Check for session hijacking
            if (isset($_SESSION['client_ip']) && $_SESSION['client_ip'] !== $current_ip) {
                // IP address changed - potential hijacking
                self::logSecurityEvent('SESSION_HIJACKING_DETECTED', [
                    'old_ip' => $_SESSION['client_ip'],
                    'new_ip' => $current_ip,
                    'session_id' => session_id()
                ]);
                session_destroy();
                session_start();
            }

            if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $current_user_agent) {
                // User agent changed - potential hijacking
                self::logSecurityEvent('SESSION_HIJACKING_DETECTED', [
                    'old_user_agent' => $_SESSION['user_agent'],
                    'new_user_agent' => $current_user_agent,
                    'session_id' => session_id()
                ]);
                session_destroy();
                session_start();
            }

            // Store client information for future checks
            $_SESSION['client_ip'] = $current_ip;
            $_SESSION['user_agent'] = $current_user_agent;

            // Regenerate session ID periodically (more frequent for better security)
            if (!isset($_SESSION['last_regeneration'])) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 180) { // 3 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }

            // Check session timeout
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
                self::logSecurityEvent('SESSION_EXPIRED', [
                    'last_activity' => date('Y-m-d H:i:s', $_SESSION['last_activity']),
                    'session_id' => session_id()
                ]);
                session_destroy();
                session_start();
            }
            $_SESSION['last_activity'] = time();
        }
    }
    
    /**
     * Database Query Logging (for debugging)
     */
    public static function logSecurityEvent($event, $details = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        error_log('SECURITY EVENT: ' . json_encode($log_entry));
    }
}

// Auto-apply security headers
setPrimeEMSSecurityHeaders();
SecurityUtils::secureSession();
?>