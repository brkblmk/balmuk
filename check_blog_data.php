<?php
require_once 'config/database.php';

try {
    echo "=== BLOG VERİLERİ KONTROL ===\n\n";

    // Blog posts kontrol
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_posts");
    $result = $stmt->fetch();
    echo "Blog yazısı sayısı: " . $result['total'] . "\n";

    if ($result['total'] > 0) {
        echo "\n--- Mevcut Blog Yazıları ---\n";
        $stmt = $pdo->query("SELECT id, title, slug, category_id, tags, is_published FROM blog_posts ORDER BY id ASC");
        $posts = $stmt->fetchAll();
        foreach ($posts as $post) {
            echo "- ID: {$post['id']}, Başlık: {$post['title']}, Yayınlandı: " . ($post['is_published'] ? 'Evet' : 'Hayır') . ", Etiketler: {$post['tags']}\n";
        }
    }

    echo "\n--- Blog Kategorileri ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_categories");
    $result = $stmt->fetch();
    echo "Blog kategori sayısı: " . $result['total'] . "\n";

    if ($result['total'] > 0) {
        $stmt = $pdo->query("SELECT id, name, slug FROM blog_categories ORDER BY id ASC");
        $categories = $stmt->fetchAll();
        foreach ($categories as $cat) {
            echo "- ID: {$cat['id']}, İsim: {$cat['name']}, Slug: {$cat['slug']}\n";
        }
    }

    echo "\n--- Blog Etiketleri ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_tags");
    $result = $stmt->fetch();
    echo "Blog etiketi sayısı: " . $result['total'] . "\n";

    if ($result['total'] > 0) {
        $stmt = $pdo->query("SELECT id, name FROM blog_tags ORDER BY id ASC");
        $tags = $stmt->fetchAll();
        foreach ($tags as $tag) {
            echo "- ID: {$tag['id']}, İsim: {$tag['name']}\n";
        }
    }

    echo "\n✅ Kontrol tamamlandı!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>