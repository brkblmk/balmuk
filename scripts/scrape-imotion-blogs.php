<?php
require_once __DIR__ . '/../config/database.php';

// Web scraping için gerekli ayarlar
ini_set('max_execution_time', 600); // 10 dakika timeout
ini_set('memory_limit', '512M');

// Simple HTML DOM Parser benzeri fonksiyon
function getDOMFromURL($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9,tr;q=0.8',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]);

    $html = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "  HTTP Code: $httpCode, Error: $error, Content Length: " . strlen($html) . "\n";

    if ($httpCode !== 200 && $httpCode !== 301 && $httpCode !== 302) {
        throw new Exception("HTTP Error: $httpCode for URL: $url");
    }

    if ($error && empty($html)) {
        throw new Exception("cURL Error: $error for URL: $url");
    }

    return $html;
}

function extractBlogPostsFromPage($html) {
    $posts = [];

    // Blog postları için regex pattern'leri
    // Bu pattern'ler i-motion websitesinin yapısına göre ayarlanmalı
    // Örnek pattern - gerçek sitenin HTML'ine göre düzenlenecek

    // Başlık pattern'i
    $titlePattern = '/<h[1-6][^>]*class="[^"]*title[^"]*"[^>]*>([^<]+)<\/h[1-6]>/i';
    // Tarih pattern'i
    $datePattern = '/<time[^>]*datetime="([^"]+)"[^>]*>([^<]+)<\/time>/i';
    // Özet pattern'i
    $excerptPattern = '/<div[^>]*class="[^"]*excerpt[^"]*"[^>]*>([^<]+)<\/div>/i';
    // Link pattern'i
    $linkPattern = '/<a[^>]*href="([^"]*news[^"]*)"[^>]*title="([^"]*)"[^>]*>/i';

    // HTML'den postları çıkar
    $lines = explode("\n", $html);
    $currentPost = null;

    foreach ($lines as $line) {
        // Başlık bul
        if (preg_match($titlePattern, $line, $titleMatch)) {
            if ($currentPost) {
                $posts[] = $currentPost;
            }
            $currentPost = [
                'title' => trim(strip_tags($titleMatch[1])),
                'date' => null,
                'excerpt' => null,
                'url' => null
            ];
        }

        // Tarih bul
        if ($currentPost && preg_match($datePattern, $line, $dateMatch)) {
            $currentPost['date'] = trim($dateMatch[1]);
        }

        // Özet bul
        if ($currentPost && preg_match($excerptPattern, $line, $excerptMatch)) {
            $currentPost['excerpt'] = trim(strip_tags($excerptMatch[1]));
        }

        // Link bul
        if ($currentPost && preg_match($linkPattern, $line, $linkMatch)) {
            $currentPost['url'] = trim($linkMatch[1]);
            if (strpos($currentPost['url'], 'http') !== 0) {
                $currentPost['url'] = 'https://www.imotion-ems.com' . $currentPost['url'];
            }
        }
    }

    if ($currentPost) {
        $posts[] = $currentPost;
    }

    return $posts;
}

function getBlogPostContent($url) {
    try {
        $html = getDOMFromURL($url);

        // İçerik pattern'i - gerçek sitenin yapısına göre düzenlenecek
        $contentPattern = '/<div[^>]*class="[^"]*content[^"]*"[^>]*>(.*?)<\/div>/is';

        if (preg_match($contentPattern, $html, $contentMatch)) {
            $content = $contentMatch[1];

            // Temizleme
            $content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);
            $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
            $content = preg_replace('/<[^>]+>/', ' ', $content);
            $content = preg_replace('/\s+/', ' ', $content);

            return trim($content);
        }

        return '';
    } catch (Exception $e) {
        echo "İçerik alınamadı ($url): " . $e->getMessage() . "\n";
        return '';
    }
}

function saveBlogPostToDatabase($post, $pdo) {
    try {
        // Slug oluştur
        $slug = strtolower(str_replace(' ', '-', $post['title']));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Benzersiz slug yap
        $originalSlug = $slug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() == 0) break;
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // İçerik al (varsa)
        $fullContent = isset($post['content']) ? $post['content'] : '';

        // Yayın tarihi belirle
        $publishedAt = null;
        if (isset($post['date']) && !empty($post['date'])) {
            try {
                $publishedAt = date('Y-m-d H:i:s', strtotime($post['date']));
            } catch (Exception $e) {
                $publishedAt = date('Y-m-d H:i:s');
            }
        } else {
            $publishedAt = date('Y-m-d H:i:s');
        }

        // Kategori ID (varsayılan EMS Antrenman)
        $categoryId = 1; // EMS Antrenman kategorisi

        // Veritabanına kaydet
        $stmt = $pdo->prepare("INSERT INTO blog_posts (
            title, slug, excerpt, content, category_id, meta_title, meta_description,
            reading_time, is_published, published_at, ai_generated
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            excerpt = VALUES(excerpt),
            content = VALUES(content),
            meta_title = VALUES(meta_title),
            meta_description = VALUES(meta_description),
            reading_time = VALUES(reading_time),
            published_at = VALUES(published_at)");

        // Reading time hesapla (ortalama 200 kelime/dakika)
        $wordCount = str_word_count(strip_tags($fullContent));
        $readingTime = max(1, ceil($wordCount / 200));

        // Meta title ve description
        $metaTitle = strlen($post['title']) > 60 ? substr($post['title'], 0, 57) . '...' : $post['title'];
        $metaDescription = isset($post['excerpt']) && !empty($post['excerpt']) ?
            (strlen($post['excerpt']) > 160 ? substr($post['excerpt'], 0, 157) . '...' : $post['excerpt']) :
            substr(strip_tags($fullContent), 0, 157) . '...';

        $stmt->execute([
            $post['title'],
            $slug,
            isset($post['excerpt']) ? $post['excerpt'] : '',
            $fullContent,
            $categoryId,
            $metaTitle,
            $metaDescription,
            $readingTime,
            1, // is_published
            $publishedAt,
            0  // ai_generated
        ]);

        return true;
    } catch (Exception $e) {
        echo "Veritabanı hatası: " . $e->getMessage() . "\n";
        return false;
    }
}

try {
    echo "i-motion blog içeriklerini çekme işlemi başlatıldı...\n\n";

    $allPosts = [];
    $totalPages = 18;

    // Ana blog sayfası - tüm postlar burada olabilir
    echo "Ana blog sayfası işleniyor...\n";
    $url = "https://www.imotion-ems.com/en/news/";

    try {
        $html = getDOMFromURL($url);
        echo "  Sayfa içeriği çekildi (" . strlen($html) . " karakter)\n";

        // Sayfa içeriğinin bir kısmını göster
        echo "  İlk 1000 karakter:\n" . substr($html, 0, 1000) . "\n\n";

        $posts = extractBlogPostsFromPage($html);
        echo "  " . count($posts) . " blog postu bulundu\n";

        foreach ($posts as &$post) {
            // İçerik al
            if (isset($post['url']) && !empty($post['url'])) {
                echo "    İçerik alınıyor: " . $post['title'] . "\n";
                try {
                    $post['content'] = getBlogPostContent($post['url']);
                } catch (Exception $e) {
                    echo "      İçerik alınamadı: " . $e->getMessage() . "\n";
                    $post['content'] = '';
                }
            }
        }

        $allPosts = array_merge($allPosts, $posts);

    } catch (Exception $e) {
        echo "  Ana sayfa işlenirken hata: " . $e->getMessage() . "\n";
    }

    echo "\nToplam " . count($allPosts) . " blog postu toplandı.\n\n";

    // Veritabanına kaydet
    $savedCount = 0;
    foreach ($allPosts as $post) {
        if (saveBlogPostToDatabase($post, $pdo)) {
            $savedCount++;
            echo "✓ Kaydedildi: " . $post['title'] . "\n";
        } else {
            echo "✗ Kaydedilemedi: " . $post['title'] . "\n";
        }
    }

    echo "\n🎉 İşlem tamamlandı! $savedCount blog postu başarıyla kaydedildi.\n";

} catch (Exception $e) {
    echo "❌ Genel hata: " . $e->getMessage() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
}
?>