<?php
require_once 'config/database.php';

try {
    // Campaigns tablosunun var olup olmadığını kontrol et
    $result = $pdo->query("SHOW TABLES LIKE 'campaigns'");
    if ($result->rowCount() == 0) {
        echo "campaigns tablosu bulunamadı.\n";
        exit;
    }

    // Mevcut kampanyaları listele
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM campaigns");
    $count = $stmt->fetch()['total'];

    echo "Toplam kampanya sayısı: $count\n";

    if ($count == 0) {
        echo "Kampanya verisi bulunmuyor.\n";
    } else {
        $campaigns = $pdo->query("SELECT id, title, is_active FROM campaigns LIMIT 5")->fetchAll();
        echo "İlk 5 kampanya:\n";
        foreach ($campaigns as $camp) {
            echo "- ID: {$camp['id']}, Başlık: {$camp['title']}, Aktif: " . ($camp['is_active'] ? 'Evet' : 'Hayır') . "\n";
        }
    }
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}