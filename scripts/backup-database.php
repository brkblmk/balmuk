<?php
/**
 * Prime EMS Database Backup Script
 * Otomatik veritabanı yedekleme sistemi
 */

// Config dosyalarını dahil et
require_once '../config/database.php';

// Yedekleme ayarları
$backupDir = '../backups';
$maxBackups = 30; // Maksimum yedek sayısı

// Yedekleme dizinini oluştur
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Yedek dosya adı
$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDir . '/backup_' . $timestamp . '.sql';

// mysqldump path'ini otomatik tespit et
$mysqldumpPath = null;
$possiblePaths = [
    'C:/xampp/mysql/bin/mysqldump.exe',
    'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe',
    'C:/Program Files/MySQL/MySQL Server 5.7/bin/mysqldump.exe',
    'C:/Program Files/MySQL/MySQL Workbench 8.0 CE/mysqldump.exe'
];

// PATH'den mysqldump'u ara
exec('where mysqldump 2>nul', $pathOutput, $pathReturn);
if ($pathReturn === 0 && !empty($pathOutput)) {
    $mysqldumpPath = trim($pathOutput[0]);
} else {
    // Olası path'leri dene
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $mysqldumpPath = $path;
            break;
        }
    }
}

$backupSuccess = false;
$method = '';

if (!$mysqldumpPath) {
    echo "mysqldump bulunamadı! Alternatif yedekleme kullanılıyor.\n";
    // Alternatif: PHP ile veritabanı yedekleme
    $backupSuccess = createDatabaseBackupPHP($backupFile);
    $method = 'PHP';
} else {
    // mysqldump komutu (Windows için)
    $command = "\"$mysqldumpPath\" --user=" . escapeshellarg(DB_USER) . " --password=" . escapeshellarg(DB_PASS) . " --host=" . escapeshellarg(DB_HOST) . " " . escapeshellarg(DB_NAME) . " > \"$backupFile\"";

    // Komutu çalıştır
    exec($command, $output, $returnCode);
    $backupSuccess = ($returnCode === 0);
    $method = 'mysqldump';
}

if ($backupSuccess && file_exists($backupFile)) {
    echo "Veritabanı yedeği ($method) başarıyla oluşturuldu: $backupFile\n";

    // Eski yedekleri temizle
    $backupFiles = glob($backupDir . '/backup_*.sql');
    if (count($backupFiles) > $maxBackups) {
        // En eski yedekleri sil
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $filesToDelete = array_slice($backupFiles, $maxBackups);
        foreach ($filesToDelete as $file) {
            unlink($file);
            echo "Eski yedek silindi: $file\n";
        }
    }

    // Yedekleme logunu yaz
    logBackupActivity('SUCCESS', "$method yedek oluşturuldu: " . basename($backupFile));

} else {
    echo "Yedekleme hatası! ";
    if ($method === 'mysqldump') {
        echo "Çıkış kodu: $returnCode\n";
        logBackupActivity('ERROR', "mysqldump yedekleme başarısız: $returnCode");
    } else {
        echo "PHP yedekleme başarısız.\n";
        logBackupActivity('ERROR', "PHP yedekleme başarısız");
    }
}

/**
 * PHP ile veritabanı yedekleme (mysqldump alternatifi)
 */
function createDatabaseBackupPHP($backupFile) {
    global $pdo;

    try {
        $sql = "-- Prime EMS Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        // Tüm tabloları al
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Tablo yapısını al
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch();
            $sql .= "-- Table structure for `$table`\n";
            $sql .= $createTable['Create Table'] . ";\n\n";

            // Tablo verilerini al
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "-- Dumping data for `$table`\n";
                $sql .= "INSERT INTO `$table` VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = $pdo->quote($value);
                        }
                    }
                    $values[] = "(" . implode(", ", $rowValues) . ")";
                }

                $sql .= implode(",\n", $values) . ";\n\n";
            } else {
                $sql .= "-- No data for `$table`\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        // Dosyaya yaz
        file_put_contents($backupFile, $sql);
        return true;

    } catch (Exception $e) {
        echo "PHP backup error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Yedekleme aktivitesini logla
 */
function logBackupActivity($status, $message) {
    $logFile = '../logs/backup.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = date('Y-m-d H:i:s') . " [$status] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}