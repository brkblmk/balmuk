<?php
require_once 'config/database.php';

// Web tarayıcı üzerinden çalıştırılabilir hale getir
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>SSS Güncellemesi</title></head><body>";
echo "<h1>SSS Güncellemesi</h1><pre>";

try {
    echo "SSS güncellemesi başlatılıyor...\n\n";

    // 1. sss.txt dosyasını oku ve parse et
    $sssContent = file_get_contents('sss.txt');
    if ($sssContent === false) {
        throw new Exception("sss.txt dosyası okunamadı!");
    }

    $lines = explode("\n", $sssContent);
    $faqs = [];
    $currentQuestion = '';
    $currentAnswer = '';

    foreach ($lines as $line) {
        $line = trim($line);

        // Soru satırı (** ile başlayan)
        if (strpos($line, '**') === 0 && strpos($line, '?') !== false) {
            // Önceki soruyu kaydet
            if ($currentQuestion && $currentAnswer) {
                $faqs[] = [
                    'question' => $currentQuestion,
                    'answer' => $currentAnswer,
                    'category' => 'Genel'
                ];
            }

            // Yeni soru
            $currentQuestion = trim(str_replace(['**'], '', $line));
            $currentAnswer = '';
        }
        // Cevap satırı (sorudan sonra, ** ile başlamıyor ve boş değil)
        elseif ($currentQuestion && !empty($line) && strpos($line, '**') !== 0 && strpos($line, 'İşte verilen') !== 0) {
            if ($currentAnswer) {
                $currentAnswer .= "\n";
            }
            $currentAnswer .= $line;
        }
    }

    // Son soruyu ekle
    if ($currentQuestion && $currentAnswer) {
        $faqs[] = [
            'question' => $currentQuestion,
            'answer' => $currentAnswer,
            'category' => 'Genel'
        ];
    }

    echo "✓ " . count($faqs) . " SSS bulundu ve parse edildi.\n";

    // 2. Faqs tablosunu temizle
    $pdo->exec("DELETE FROM faqs WHERE category = 'Genel'");
    echo "✓ Faqs tablosu (Genel kategorisi) temizlendi.\n";

    // 3. Yeni SSS verilerini ekle
    $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, is_published, is_active, sort_order) VALUES (?, ?, ?, 1, 1, ?)");

    $sortOrder = 0;
    foreach ($faqs as $faq) {
        $stmt->execute([
            $faq['question'],
            $faq['answer'],
            $faq['category'],
            $sortOrder++
        ]);
    }

    echo "✓ " . count($faqs) . " SSS başarıyla faqs tablosuna eklendi.\n\n";

    echo "✅ SSS güncellemesi tamamlandı!\n";

} catch (PDOException $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}

echo "</pre></body></html>";
?>