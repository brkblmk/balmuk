<?php
require_once 'config/database.php';

try {
    echo "=== ADMINS TABLOSU KONTROL ===\n\n";

    $stmt = $pdo->query('DESCRIBE admins');
    $columns = $stmt->fetchAll();

    echo "Admins tablosu sütunları:\n";
    foreach($columns as $col) {
        echo "- {$col['Field']} (Type: {$col['Type']})\n";
    }

    echo "\n--- ADMINS TABLOSU VERİLERİ ---\n";
    $stmt = $pdo->query('SELECT * FROM admins LIMIT 5');
    $admins = $stmt->fetchAll();

    foreach($admins as $admin) {
        echo "ID: {$admin['id']}\n";
        foreach($columns as $col) {
            $field = $col['Field'];
            echo "  $field: {$admin[$field]}\n";
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>