<?php
/**
 * Geçici ve test dosyalarını temizleme scripti
 */

$files_to_delete = [
    // Test ve kontrol dosyaları
    'check_blog_images.php',
    'check_blog_posts.php',
    'check_faqs.php',
    'check_foreign_keys.php',
    'check_tables.php',
    'check-admin-simple.php',
    'check-db-collation.php',
    'check-devices.php',

    // Fix dosyaları
    'fix_blog_views.php',
    'fix_faqs_active.php',
    'fix-db-collation.php',
    'fix-utf8.php',
    'fix-wrong-chars.php',
    'fix-all-typos.sql',

    // Database işlemleri
    'database_cleanup.php',
    'run-sql.php',
    'run-typo-fixes.php',
    'scan-database.php',

    // Test dosyaları
    'test-email.php',
    'test-report.html',
    'db-test.php',
    'optimize-images.php',
    'quality-check.php',
    'performance-monitor.php',

    // Update dosyaları
    'update-devices.php',
    'update-i-model-devices.php',
    'update-i-motion-devices.php',

    // Diğer geçici dosyalar
    'check_admin.php',
    'cleanup-devices.php',
    'create_faqs_table.php',
    'create-table-manual.php',
    'describe-campaigns.php',
    'device-check.php',
    'find-specific-errors.php',
    'import_faqs.php',
    'apply-index-optimizations.php',

    // ZIP dosyaları
    'phpmailer.zip'
];

$deleted_count = 0;
$skipped_count = 0;

echo "=== DOSYA SİSTEMİ TEMİZLİĞİ BAŞLADI ===\n\n";

foreach ($files_to_delete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✓ Silindi: {$file}\n";
            $deleted_count++;
        } else {
            echo "✗ Silinemedi: {$file}\n";
            $skipped_count++;
        }
    } else {
        echo "- Bulunamadı: {$file}\n";
        $skipped_count++;
    }
}

// Log dosyalarını kontrol et
$log_files = glob('*.log');
if (!empty($log_files)) {
    echo "\nLog dosyaları kontrol ediliyor...\n";
    foreach ($log_files as $log_file) {
        $file_size = filesize($log_file);
        if ($file_size > 1024 * 1024) { // 1MB'dan büyük
            echo "✓ Büyük log dosyası: {$log_file} (" . round($file_size / 1024 / 1024, 2) . " MB)\n";
        }
    }
}

echo "\n=== TEMİZLİK SONUÇLARI ===\n";
echo "Silinen dosyalar: {$deleted_count}\n";
echo "Atlanan dosyalar: {$skipped_count}\n";
echo "Toplam kontrol edilen: " . count($files_to_delete) . "\n";

echo "\n=== TEMİZLİK TAMAMLANDI ===\n";
?>