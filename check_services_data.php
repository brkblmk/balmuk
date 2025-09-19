<?php
require_once 'config/database.php';

try {
    echo "Services tablosundaki mevcut verileri kontrol ediyorum...\n\n";

    // Services tablosunun var olup olmadığını kontrol et
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        echo "❌ Services tablosu bulunamadı. Önce create_missing_tables.php çalıştırın.\n";
        exit;
    }

    // Mevcut hizmetleri getir
    $services = $pdo->query("SELECT id, name, goal, duration, price, session_count, is_featured, is_active FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    $count = count($services);
    echo "📊 Services tablosunda {$count} hizmet bulundu:\n\n";

    if ($count > 0) {
        foreach ($services as $service) {
            echo "ID: {$service['id']} | Ad: {$service['name']} | Hedef: {$service['goal']} | Süre: {$service['duration']} | Fiyat: ₺{$service['price']} | Seans: {$service['session_count']} | Öne Çıkan: " . ($service['is_featured'] ? 'Evet' : 'Hayır') . " | Aktif: " . ($service['is_active'] ? 'Evet' : 'Hayır') . "\n";
        }
    } else {
        echo "⚠️  Services tablosu boş. Örnek veriler eklenmesi gerekecek.\n";
    }

    echo "\n✅ Veri kontrolü tamamlandı.\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>