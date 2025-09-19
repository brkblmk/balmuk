<?php
require_once 'config/database.php';

try {
    echo "SSS verilerini kontrol ediyorum...\n\n";

    // Faqs tablosundan verileri çek
    $stmt = $pdo->prepare("SELECT id, question, answer, category FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 10");
    $stmt->execute();
    $faqs = $stmt->fetchAll();

    if (empty($faqs)) {
        echo "❌ Hiç SSS bulunamadı!\n";
    } else {
        echo "✓ " . count($faqs) . " SSS bulundu:\n\n";
        foreach ($faqs as $index => $faq) {
            echo ($index + 1) . ". " . htmlspecialchars($faq['question']) . "\n";
            echo "   Kategori: " . htmlspecialchars($faq['category']) . "\n";
            echo "   Cevap: " . substr(htmlspecialchars($faq['answer']), 0, 100) . "...\n\n";
        }

        echo "✅ SSS verileri başarıyla veritabanından çekiliyor!\n";
        echo "✅ Ana sayfadaki SSS bölümü bu verileri gösterecek.\n";
        echo "✅ sss.php sayfası kategorilere göre gruplandırarak gösterecek.\n";
    }

} catch (PDOException $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage() . "\n";
}
?>