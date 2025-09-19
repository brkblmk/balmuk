<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Mevcut tablolar:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }

    // Devices tablosunun yapısını kontrol et
    if (in_array('devices', $tables)) {
        echo "\nDevices tablosu yapısı:\n";
        $stmt = $pdo->query("DESCRIBE devices");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']} ({$column['Null']})\n";
        }
    }
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>