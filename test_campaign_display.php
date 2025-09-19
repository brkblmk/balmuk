<?php
require_once 'config/database.php';

try {
    // Campaigns verilerini çek
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 3");
    $stmt->execute();
    $campaigns = $stmt->fetchAll();

    echo "Kampanya Görüntüleme Testi\n";
    echo "=========================\n\n";

    if (!empty($campaigns)) {
        echo "Bulunan kampanya sayısı: " . count($campaigns) . "\n\n";

        foreach ($campaigns as $index => $campaign) {
            echo "Kampanya #" . ($index + 1) . ":\n";
            echo "- Başlık: " . ($campaign['title'] ?? 'N/A') . "\n";
            echo "- Alt Başlık: " . ($campaign['subtitle'] ?? 'N/A') . "\n";
            echo "- İndirim Metni: " . ($campaign['discount_text'] ?? 'N/A') . "\n";
            echo "- Rozet Metni: " . ($campaign['badge_text'] ?? 'N/A') . "\n";
            echo "- Rozet Rengi: " . ($campaign['badge_color'] ?? 'warning') . "\n";
            echo "- İkon: " . ($campaign['icon'] ?? 'bi-star-fill') . "\n";
            echo "- Açıklama: " . ($campaign['description'] ?? 'N/A') . "\n";
            echo "- Buton Metni: " . ($campaign['button_text'] ?? 'Hemen Rezerve Et') . "\n";
            echo "- Buton Linki: " . ($campaign['button_link'] ?? '#reservation') . "\n";
            echo "- Öne Çıkan: " . ($campaign['is_featured'] ? 'Evet' : 'Hayır') . "\n";
            echo "- Aktif: " . ($campaign['is_active'] ? 'Evet' : 'Hayır') . "\n";
            echo "- Sıralama: " . ($campaign['sort_order'] ?? 0) . "\n";
            echo "- Oluşturulma: " . ($campaign['created_at'] ?? 'N/A') . "\n\n";
        }

        echo "✅ Kampanya verileri başarıyla çekildi ve görüntülenebilir durumda.\n";
    } else {
        echo "❌ Kampanya bulunmuyor.\n";
    }

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}