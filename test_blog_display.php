<?php
require_once 'config/database.php';

try {
    echo "=== BLOG LİSTESİ TESTİ ===\n\n";

    // blog.php'deki sorguyu aynen çalıştır
    $where = "WHERE bp.is_published = 1";
    $params = [];

    // Toplam sayı kontrolü
    $count_sql = "SELECT COUNT(*) FROM blog_posts bp
                  LEFT JOIN blog_categories bc ON bp.category_id = bc.id
                  $where";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_posts = $count_stmt->fetchColumn();

    echo "Toplam yayınlanmış blog yazısı: $total_posts\n\n";

    if ($total_posts > 0) {
        // Blog yazıları çekme sorgusu
        $sql = "SELECT bp.*, bc.name as category_name, bc.color as category_color, bc.slug as category_slug
                FROM blog_posts bp
                LEFT JOIN blog_categories bc ON bp.category_id = bc.id
                $where
                ORDER BY bp.published_at DESC
                LIMIT 9";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        echo "--- GÖRÜNTÜLENECEK BLOG YAZILARI ---\n";
        foreach ($posts as $index => $post) {
            echo ($index + 1) . ". ID: {$post['id']}\n";
            echo "   Başlık: {$post['title']}\n";
            echo "   Slug: {$post['slug']}\n";
            echo "   Yayın Tarihi: {$post['published_at']}\n";
            echo "   Kategori: {$post['category_name']}\n";
            echo "   URL: blog-detail.php?slug={$post['slug']}\n\n";
        }

        echo "✅ Blog listesi başarıyla yükleniyor!\n";
    } else {
        echo "❌ Yayınlanmış blog yazısı bulunmuyor!\n";
    }

    // Kategoriler kontrolü
    $categories = $pdo->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
    echo "\n--- AKTİF KATEGORİLER ---\n";
    if (count($categories) > 0) {
        foreach ($categories as $cat) {
            echo "- {$cat['name']} (ID: {$cat['id']}, Slug: {$cat['slug']})\n";
        }
    } else {
        echo "Aktif kategori bulunmuyor.\n";
    }

    // Popüler yazılar kontrolü
    $popular_posts = $pdo->query("SELECT id, title, slug, featured_image, view_count FROM blog_posts WHERE is_published = 1 ORDER BY view_count DESC LIMIT 5")->fetchAll();
    echo "\n--- POPÜLER YAZILAR ---\n";
    if (count($popular_posts) > 0) {
        foreach ($popular_posts as $popular) {
            echo "- {$popular['title']} (Görüntülenme: {$popular['view_count']})\n";
        }
    } else {
        echo "Popüler yazı bulunmuyor.\n";
    }

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>