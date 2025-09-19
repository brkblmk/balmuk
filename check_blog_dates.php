<?php
require_once 'config/database.php';

try {
    echo "=== BLOG YAZILARI TARİH VE SLUG KONTROL ===\n\n";

    // Blog yazılarında published_at tarihlerini kontrol et
    $stmt = $pdo->query("SELECT id, title, slug, published_at, is_published FROM blog_posts ORDER BY id ASC");
    $posts = $stmt->fetchAll();

    echo "Blog yazıları:\n";
    foreach ($posts as $post) {
        $published_date = strtotime($post['published_at']);
        $current_time = time();
        $is_future = $published_date > $current_time;

        echo "- ID: {$post['id']}\n";
        echo "  Başlık: {$post['title']}\n";
        echo "  Slug: {$post['slug']}\n";
        echo "  Yayınlandı: " . ($post['is_published'] ? 'Evet' : 'Hayır') . "\n";
        echo "  Yayın Tarihi: {$post['published_at']}\n";
        echo "  Geçerli mi: " . ($is_future ? 'Gelecek tarih' : 'Geçmiş/Şimdiki tarih') . "\n";
        echo "  URL Formatı: blog-detail.php?slug={$post['slug']}\n";
        echo "\n";
    }

    // Yayınlanmamış yazıları kontrol et
    $stmt = $pdo->query("SELECT COUNT(*) as unpublished FROM blog_posts WHERE is_published = 0");
    $unpublished = $stmt->fetch();
    echo "Yayınlanmamış yazı sayısı: {$unpublished['unpublished']}\n\n";

    // Slug çakışmalarını kontrol et
    $stmt = $pdo->query("SELECT slug, COUNT(*) as count FROM blog_posts GROUP BY slug HAVING COUNT(*) > 1");
    $duplicates = $stmt->fetchAll();

    if (count($duplicates) > 0) {
        echo "SLUG ÇAKIŞMALARI BULUNDU:\n";
        foreach ($duplicates as $dup) {
            echo "- Slug: {$dup['slug']}, Çakışma sayısı: {$dup['count']}\n";
        }
    } else {
        echo "Slug çakışması bulunmuyor.\n";
    }

    echo "\n✅ Kontrol tamamlandı!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>