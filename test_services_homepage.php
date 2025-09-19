<?php
require_once 'config/database.php';

try {
    echo "Ana sayfadaki hizmet bölümünü kontrol ediyorum...\n\n";

    // Ana sayfadaki gibi hizmetleri çek
    $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = count($services);
    echo "📊 Ana sayfada {$count} aktif hizmet görünecek:\n\n";

    if ($count > 0) {
        foreach ($services as $index => $service) {
            echo ($index + 1) . ". {$service['name']}\n";
            echo "   İkon: {$service['icon']}\n";
            echo "   Hedef: {$service['goal']}\n";
            echo "   Süre: {$service['duration']}\n";
            echo "   Kısa Açıklama: {$service['short_description']}\n";
            echo "   Fiyat: ₺{$service['price']}\n";
            echo "   Öne Çıkan: " . ($service['is_featured'] ? 'Evet' : 'Hayır') . "\n";
            echo "   Sıralama: {$service['sort_order']}\n\n";
        }
    } else {
        echo "⚠️  Hiç aktif hizmet bulunmadı!\n";
    }

    echo "✅ Ana sayfa kontrolü tamamlandı.\n";

    // Toplam hizmet sayısı kontrolü
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM services");
    $total = $totalStmt->fetch()['total'];
    echo "Toplam hizmet sayısı: {$total} (aktif: {$count}, pasif: " . ($total - $count) . ")\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>