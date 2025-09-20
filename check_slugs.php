<?php
require_once 'config/database.php';

try {
    echo "=== BLOG SLUG KONTROL ===\n\n";

    $stmt = $pdo->query("SELECT id, title, slug, is_published FROM blog_posts ORDER BY id ASC");
    $posts = $stmt->fetchAll();

    foreach ($posts as $post) {
        $status = $post['is_published'] ? 'Yayınlandı' : 'Taslak';
        echo "ID: {$post['id']}\n";
        echo "Başlık: {$post['title']}\n";
        echo "Slug: {$post['slug']}\n";
        echo "Durum: $status\n";
        echo "URL: blog-detail.php?slug={$post['slug']}\n\n";
    }

    echo "✅ Slug kontrolü tamamlandı!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>