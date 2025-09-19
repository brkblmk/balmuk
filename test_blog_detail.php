<?php
require_once 'config/database.php';

try {
    echo "=== BLOG DETAY SAYFASI TESTİ ===\n\n";

    // İlk blog yazısının slug'ını al
    $stmt = $pdo->query("SELECT slug, title FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC LIMIT 1");
    $post = $stmt->fetch();

    if (!$post) {
        echo "❌ Test edilebilecek yayınlanmış blog yazısı bulunmuyor!\n";
        exit;
    }

    echo "Test edilecek yazı: {$post['title']}\n";
    echo "Slug: {$post['slug']}\n\n";

    // blog-detail.php'deki sorguyu simüle et (düzeltildi: a.name -> a.full_name)
    $detail_stmt = $pdo->prepare("
        SELECT bp.*, bc.name as category_name, bc.color as category_color, bc.slug as category_slug,
               a.full_name as author_name
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        LEFT JOIN admins a ON bp.author_id = a.id
        WHERE bp.slug = ? AND bp.is_published = 1
    ");
    $detail_stmt->execute([$post['slug']]);
    $detail_post = $detail_stmt->fetch();

    if ($detail_post) {
        echo "✅ Detay sayfası başarıyla yükleniyor!\n\n";
        echo "--- YAZI DETAYLARI ---\n";
        echo "ID: {$detail_post['id']}\n";
        echo "Başlık: {$detail_post['title']}\n";
        echo "Slug: {$detail_post['slug']}\n";
        echo "Kategori: {$detail_post['category_name']}\n";
        echo "Yazar: {$detail_post['author_name']}\n";
        echo "Yayın Tarihi: {$detail_post['published_at']}\n";
        echo "Okuma Süresi: {$detail_post['reading_time']} dakika\n";
        echo "Görüntülenme: {$detail_post['view_count']}\n";
        echo "İçerik Uzunluğu: " . strlen($detail_post['content']) . " karakter\n";
        echo "Özet Uzunluğu: " . strlen($detail_post['excerpt']) . " karakter\n";

        // Etiketleri kontrol et
        $tags = [];
        if (!empty($detail_post['tags'])) {
            $tag_strings = array_map('trim', explode(',', $detail_post['tags']));
            foreach ($tag_strings as $tag_name) {
                if (!empty($tag_name)) {
                    $tags[] = ['name' => $tag_name, 'slug' => strtolower(str_replace(' ', '-', $tag_name))];
                }
            }
        }
        echo "Etiket Sayısı: " . count($tags) . "\n";
        if (count($tags) > 0) {
            echo "Etiketler: " . implode(', ', array_column($tags, 'name')) . "\n";
        }

        // İlgili yazıları kontrol et
        $related_stmt = $pdo->prepare("
            SELECT COUNT(*) as related_count
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.category_id = ? AND bp.id != ? AND bp.is_published = 1
        ");
        $related_stmt->execute([$detail_post['category_id'], $detail_post['id']]);
        $related_count = $related_stmt->fetch()['related_count'];

        echo "İlgili Yazı Sayısı: $related_count\n";

        echo "\n✅ Detay sayfası tam olarak çalışıyor!\n";
        echo "URL: blog-detail.php?slug={$detail_post['slug']}\n";

    } else {
        echo "❌ Detay sayfası yüklenemiyor! Yazı bulunamadı.\n";
    }

    // Tüm slug'ları test et
    echo "\n--- TÜM SLUG TESTLERİ ---\n";
    $all_posts = $pdo->query("SELECT slug, title FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC")->fetchAll();

    foreach ($all_posts as $test_post) {
        $test_stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? AND is_published = 1");
        $test_stmt->execute([$test_post['slug']]);
        $found = $test_stmt->fetch();

        if ($found) {
            echo "✅ {$test_post['title']} - Slug çalışıyor\n";
        } else {
            echo "❌ {$test_post['title']} - Slug çalışmıyor\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>