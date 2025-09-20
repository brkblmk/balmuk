<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("DESCRIBE ems_devices");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "ems_devices tablosundaki sütunlar:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    // Certifications sütunu var mı kontrol et
    $hasCertifications = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'certifications') {
            $hasCertifications = true;
            break;
        }
    }

    echo "\nCertifications sütunu " . ($hasCertifications ? "VAR" : "YOK") . "\n";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
    echo "ems_devices tablosu bulunamadı veya veritabanı bağlantı hatası.\n";
}
?>