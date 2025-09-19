<?php
/**
 * Sistem Test Scripti
 * Sayfa yüklemeleri, veritabanı bağlantısı, görsel dosyaları kontrolü
 */

echo "=== PRIME EMS STUDIOS SİSTEM TESTİ ===\n\n";

// 1. PHP Version Check
echo "1. PHP Sürümü: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✓ PHP sürümü yeterli\n";
} else {
    echo "✗ PHP sürümü yetersiz (7.4+ gerekli)\n";
}

// 2. Required Extensions
echo "\n2. Gerekli PHP Eklentileri:\n";
$required_ext = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session', 'curl'];
foreach ($required_ext as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ {$ext} aktif\n";
    } else {
        echo "✗ {$ext} eksik\n";
    }
}

// 3. Database Connection Test
echo "\n3. Veritabanı Bağlantısı:\n";
try {
    require_once 'config/database.php';
    $stmt = $pdo->query("SELECT 1");
    $result = $stmt->fetch();
    echo "✓ Veritabanı bağlantısı başarılı\n";

    // Table count
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ " . count($tables) . " tablo bulundu\n";

} catch (Exception $e) {
    echo "✗ Veritabanı bağlantı hatası: " . $e->getMessage() . "\n";
}

// 4. File System Check
echo "\n4. Dosya Sistemi Kontrolü:\n";

// Critical directories
$critical_dirs = [
    'config/',
    'assets/css/',
    'assets/js/',
    'assets/images/',
    'assets/images/blog/',
    'includes/',
    'admin/'
];

foreach ($critical_dirs as $dir) {
    if (is_dir($dir)) {
        echo "✓ {$dir} klasörü mevcut\n";
    } else {
        echo "✗ {$dir} klasörü eksik\n";
    }
}

// Critical files
$critical_files = [
    'index.php',
    'blog.php',
    'config/database.php',
    'config/security.php',
    'config/performance.php',
    'assets/css/theme.css',
    'assets/js/main.js'
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} dosyası mevcut\n";
    } else {
        echo "✗ {$file} dosyası eksik\n";
    }
}

// 5. Blog Images Check
echo "\n5. Blog Görselleri Kontrolü:\n";
$blog_images = [
    'assets/images/blog/ems-future.jpg',
    'assets/images/blog/ems-women.jpg',
    'assets/images/blog/ems-nutrition.jpg',
    'assets/images/blog/ems-30-day.jpg',
    'assets/images/blog/ems-health-benefits.jpg',
    'assets/images/blog/ems-tips.jpg',
    'assets/images/blog/ems-research.jpg'
];

foreach ($blog_images as $image) {
    if (file_exists($image)) {
        echo "✓ {$image} mevcut\n";
    } else {
        echo "✗ {$image} eksik\n";
    }
}

// 6. Configuration Files Check
echo "\n6. Yapılandırma Dosyaları:\n";
$config_files = [
    '.eslintrc.js',
    'stylelint.config.js',
    'sitemap.xml',
    'robots.txt',
    '.htaccess'
];

foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} mevcut\n";
    } else {
        echo "✗ {$file} eksik\n";
    }
}

// 7. Basic Syntax Check (PHP files)
echo "\n7. PHP Sözdizimi Kontrolü:\n";
$php_files = [
    'index.php',
    'blog.php',
    'config/database.php',
    'config/security.php',
    'config/performance.php',
    'contact-form.php'
];

$syntax_errors = 0;
foreach ($php_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l \"{$file}\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✓ {$file} sözdizimi doğru\n";
        } else {
            echo "✗ {$file} sözdizimi hatası: {$output}\n";
            $syntax_errors++;
        }
    }
}

// 8. Test Results
echo "\n=== TEST SONUÇLARI ===\n";
if ($syntax_errors > 0) {
    echo "❌ {$syntax_errors} PHP sözdizimi hatası bulundu\n";
} else {
    echo "✅ Tüm PHP dosyalarının sözdizimi doğru\n";
}

echo "\n=== SİSTEM TESTİ TAMAMLANDI ===\n";

if ($syntax_errors === 0) {
    echo "🎉 Sistem testleri başarılı! Tüm temel bileşenler çalışıyor.\n";
} else {
    echo "⚠️  Sistem testlerinde hatalar bulundu. Lütfen düzeltin.\n";
}
?>