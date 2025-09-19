<?php
/**
 * Prime EMS Studios - Performance Optimization Configuration
 * Comprehensive performance tuning and optimization
 */

class PerformanceOptimizer {
    
    private static $cache_dir = null;
    private static $compression_enabled = true;
    
    /**
     * Initialize performance optimizations
     */
    public static function init() {
        // Set cache directory
        self::$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/cache';
        
        // Create cache directory if it doesn't exist
        if (!is_dir(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }
        
        // Enable output compression
        if (self::$compression_enabled && !ob_get_level() && extension_loaded('zlib')) {
            ini_set('zlib.output_compression', 1);
            ini_set('zlib.output_compression_level', 6);
        }
        
        // Set optimal PHP settings
        self::optimizePHPSettings();
        
        // Enable browser caching headers
        self::setBrowserCaching();
    }
    
    /**
     * Optimize PHP settings for performance
     */
    private static function optimizePHPSettings() {
        // Memory and execution optimizations
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 30);
        ini_set('max_input_vars', 3000);
        
        // OPcache optimizations (if available)
        if (extension_loaded('opcache')) {
            ini_set('opcache.enable', 1);
            ini_set('opcache.memory_consumption', 128);
            ini_set('opcache.max_accelerated_files', 4000);
            ini_set('opcache.revalidate_freq', 60);
            ini_set('opcache.fast_shutdown', 1);
        }
        
        // Disable unnecessary functions for performance
        ini_set('expose_php', 0);
        ini_set('log_errors_max_len', 1024);
    }
    
    /**
     * Set browser caching headers
     */
    private static function setBrowserCaching() {
        if (!headers_sent()) {
            $current_file = basename($_SERVER['REQUEST_URI']);
            $file_extension = strtolower(pathinfo($current_file, PATHINFO_EXTENSION));
            
            // Different cache times for different file types
            $cache_times = [
                'css' => 2592000,  // 30 days
                'js' => 2592000,   // 30 days
                'jpg' => 2592000,  // 30 days
                'jpeg' => 2592000, // 30 days
                'png' => 2592000,  // 30 days
                'gif' => 2592000,  // 30 days
                'webp' => 2592000, // 30 days
                'svg' => 2592000,  // 30 days
                'ico' => 604800,   // 7 days
                'woff' => 2592000, // 30 days
                'woff2' => 2592000,// 30 days
                'ttf' => 2592000,  // 30 days
                'eot' => 2592000,  // 30 days
                'pdf' => 86400,    // 1 day
                'php' => 0,        // No cache for PHP files
                'html' => 3600,    // 1 hour for HTML
            ];
            
            $cache_time = $cache_times[$file_extension] ?? 3600; // Default 1 hour
            
            if ($cache_time > 0) {
                header('Cache-Control: public, max-age=' . $cache_time);
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');
                
                // ETag for better caching
                $etag = md5($current_file . filemtime(__FILE__));
                header('ETag: "' . $etag . '"');
                
                // Check if client has cached version
                if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
                    header('HTTP/1.1 304 Not Modified');
                    exit;
                }
            } else {
                // No cache for dynamic content
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
            }
        }
    }
    
    /**
     * Minify HTML output
     */
    public static function minifyHTML($html) {
        // Remove HTML comments (but preserve IE conditionals)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        
        // Remove extra whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Remove whitespace around HTML tags
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Remove leading and trailing whitespace
        return trim($html);
    }
    
    /**
     * Minify CSS content
     */
    public static function minifyCSS($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        
        // Remove extra spaces around selectors and properties
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*}/', '}', $css);
        $css = preg_replace('/\s*;\s*/', ';', $css);
        $css = preg_replace('/\s*:\s*/', ':', $css);
        $css = preg_replace('/\s*,\s*/', ',', $css);
        
        return trim($css);
    }
    
    /**
     * Minify JavaScript content
     */
    public static function minifyJS($js) {
        // Remove single line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        
        // Remove extra whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove whitespace around operators and punctuation
        $js = preg_replace('/\s*([{}();,])\s*/', '$1', $js);
        
        return trim($js);
    }
    
    /**
     * Enhanced cache system for dynamic content with database query support
     */
    public static function cache($key, $content, $expiry = 3600, $tags = []) {
        $cache_file = self::$cache_dir . '/' . md5($key) . '.cache';
        $cache_data = [
            'content' => $content,
            'created' => time(),
            'expiry' => $expiry,
            'tags' => $tags,
            'hits' => 1
        ];

        // Update access time for cache management
        touch($cache_file);

        return file_put_contents($cache_file, serialize($cache_data));
    }

    /**
     * Retrieve cached content with hit tracking
     */
    public static function getCache($key) {
        $cache_file = self::$cache_dir . '/' . md5($key) . '.cache';

        if (!file_exists($cache_file)) {
            return false;
        }

        $cache_data = unserialize(file_get_contents($cache_file));

        // Check if cache has expired
        if (time() - $cache_data['created'] > $cache_data['expiry']) {
            unlink($cache_file);
            return false;
        }

        // Update hit count and access time
        $cache_data['hits'] = ($cache_data['hits'] ?? 0) + 1;
        file_put_contents($cache_file, serialize($cache_data));
        touch($cache_file);

        return $cache_data['content'];
    }

    /**
     * Database query result caching
     */
    public static function cacheQuery($query, $params = [], $expiry = 1800) {
        global $pdo;

        $cache_key = 'db_' . md5($query . serialize($params));

        // Check cache first
        $cached_result = self::getCache($cache_key);
        if ($cached_result !== false) {
            ErrorMonitor::logPerformance('CACHE_HIT', ['query' => substr($query, 0, 100), 'key' => $cache_key]);
            return $cached_result;
        }

        // Execute query
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetchAll();

            // Cache the result
            self::cache($cache_key, $result, $expiry, ['database', 'query']);

            ErrorMonitor::logPerformance('CACHE_MISS', ['query' => substr($query, 0, 100), 'key' => $cache_key]);

            return $result;
        } catch (PDOException $e) {
            ErrorMonitor::logPerformance('QUERY_ERROR', ['query' => substr($query, 0, 100), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Clear cache by tags
     */
    public static function clearCacheByTags($tags) {
        $files = glob(self::$cache_dir . '/*.cache');
        $cleared = 0;

        foreach ($files as $file) {
            if (!file_exists($file)) continue;

            $cache_data = unserialize(file_get_contents($file));

            if (isset($cache_data['tags']) && is_array($cache_data['tags'])) {
                $intersection = array_intersect($tags, $cache_data['tags']);
                if (!empty($intersection)) {
                    unlink($file);
                    $cleared++;
                }
            }
        }

        return $cleared;
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats() {
        $files = glob(self::$cache_dir . '/*.cache');
        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'oldest_file' => null,
            'newest_file' => null,
            'expired_files' => 0,
            'hit_ratios' => []
        ];

        foreach ($files as $file) {
            $size = filesize($file);
            $stats['total_size'] += $size;

            $mtime = filemtime($file);
            if ($stats['oldest_file'] === null || $mtime < $stats['oldest_file']) {
                $stats['oldest_file'] = $mtime;
            }
            if ($stats['newest_file'] === null || $mtime > $stats['newest_file']) {
                $stats['newest_file'] = $mtime;
            }

            $cache_data = unserialize(file_get_contents($file));
            if (isset($cache_data['created']) && isset($cache_data['expiry'])) {
                if (time() - $cache_data['created'] > $cache_data['expiry']) {
                    $stats['expired_files']++;
                }
            }

            if (isset($cache_data['hits'])) {
                $stats['hit_ratios'][] = $cache_data['hits'];
            }
        }

        $stats['avg_hit_ratio'] = !empty($stats['hit_ratios']) ? array_sum($stats['hit_ratios']) / count($stats['hit_ratios']) : 0;

        return $stats;
    }
    
    /**
     * Clear cache
     */
    public static function clearCache($pattern = '*') {
        $files = glob(self::$cache_dir . '/' . $pattern . '.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Image optimization and lazy loading attributes
     */
    public static function optimizeImage($src, $alt = '', $class = '', $loading = 'lazy') {
        $width = '';
        $height = '';
        
        // Try to get image dimensions for better loading
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $src)) {
            $image_info = getimagesize($_SERVER['DOCUMENT_ROOT'] . $src);
            if ($image_info) {
                $width = ' width="' . $image_info[0] . '"';
                $height = ' height="' . $image_info[1] . '"';
            }
        }
        
        return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '"' . 
               ($class ? ' class="' . htmlspecialchars($class) . '"' : '') .
               ' loading="' . $loading . '"' . $width . $height . '>';
    }
    
    /**
     * Combine and minify CSS files
     */
    public static function combineCSSFiles($files, $output_file = null) {
        $combined_css = '';
        $last_modified = 0;
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $combined_css .= file_get_contents($file) . "\n";
                $last_modified = max($last_modified, filemtime($file));
            }
        }
        
        $minified_css = self::minifyCSS($combined_css);
        
        if ($output_file) {
            file_put_contents($output_file, $minified_css);
        }
        
        return $minified_css;
    }
    
    /**
     * Combine and minify JS files
     */
    public static function combineJSFiles($files, $output_file = null) {
        $combined_js = '';
        $last_modified = 0;
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $combined_js .= file_get_contents($file) . ";\n";
                $last_modified = max($last_modified, filemtime($file));
            }
        }
        
        $minified_js = self::minifyJS($combined_js);
        
        if ($output_file) {
            file_put_contents($output_file, $minified_js);
        }
        
        return $minified_js;
    }
    
    /**
     * Database query optimization
     */
    public static function optimizeQuery($query) {
        // Basic query optimization suggestions
        $optimizations = [];
        
        // Check for SELECT *
        if (preg_match('/SELECT\s+\*/i', $query)) {
            $optimizations[] = 'Avoid SELECT * - specify columns explicitly';
        }
        
        // Check for missing WHERE clause in UPDATE/DELETE
        if (preg_match('/(UPDATE|DELETE)\s+(?!.*WHERE)/i', $query)) {
            $optimizations[] = 'Missing WHERE clause in UPDATE/DELETE query';
        }
        
        // Check for ORDER BY without LIMIT
        if (preg_match('/ORDER\s+BY(?!.*LIMIT)/i', $query)) {
            $optimizations[] = 'Consider adding LIMIT to ORDER BY queries';
        }
        
        return $optimizations;
    }
    
    /**
     * Resource preloading helpers
     */
    public static function preloadResource($href, $as, $type = null) {
        if (!headers_sent()) {
            $preload_header = '<' . $href . '>; rel=preload; as=' . $as;
            if ($type) {
                $preload_header .= '; type=' . $type;
            }
            header('Link: ' . $preload_header, false);
        }
    }
    
    /**
     * Get performance metrics
     */
    public static function getPerformanceMetrics() {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'included_files' => count(get_included_files()),
            'cache_dir_size' => self::getCacheDirSize()
        ];
    }
    
    /**
     * Get cache directory size
     */
    private static function getCacheDirSize() {
        $size = 0;
        $files = glob(self::$cache_dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }
    
    /**
     * Format bytes for display
     */
    public static function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

// Initialize performance optimizations
PerformanceOptimizer::init();

// Start output buffering for HTML minification
if (!ob_get_level() && !headers_sent()) {
    ob_start(function($buffer) {
        return PerformanceOptimizer::minifyHTML($buffer);
    });
}
?>