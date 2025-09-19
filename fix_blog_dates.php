<?php
require_once 'config/database.php';

try {
    echo "=== BLOG YAZILARI TARİH DÜZELTME ===\n\n";

    // Geçmiş tarihler oluştur
    $dates = [
        '2024-12-01 10:00:00',
        '2024-12-05 14:30:00',
        '2024-12-10 09:15:00',
        '2024-12-15 16:45:00',
        '2024-12-20 11:20:00',
        '2024-12-25 13:00:00'
    ];

    // Blog yazıları için tarihleri güncelle
    $stmt = $pdo->query("SELECT id, title FROM blog_posts ORDER BY id ASC");
    $posts = $stmt->fetchAll();

    echo "Blog yazıları tarihleri güncelleniyor...\n";

    foreach ($posts as $index => $post) {
        $date = $dates[$index] ?? date('Y-m-d H:i:s', strtotime('-' . ($index + 1) . ' days'));
        $pdo->prepare("UPDATE blog_posts SET published_at = ? WHERE id = ?")
             ->execute([$date, $post['id']]);

        echo "✓ ID {$post['id']}: {$post['title']} - Tarih: {$date}\n";
    }

    echo "\n=== GÜNCEL BLOG YAZILARI ===\n";

    // Güncellenmiş yazıları kontrol et
    $stmt = $pdo->query("SELECT id, title, slug, published_at, is_published FROM blog_posts ORDER BY published_at DESC");
    $posts = $stmt->fetchAll();

    foreach ($posts as $post) {
        $published_date = strtotime($post['published_at']);
        $current_time = time();
        $is_past = $published_date <= $current_time;

        echo "- ID: {$post['id']}\n";
        echo "  Başlık: {$post['title']}\n";
        echo "  Yayınlandı: " . ($post['is_published'] ? 'Evet' : 'Hayır') . "\n";
        echo "  Yayın Tarihi: {$post['published_at']}\n";
        echo "  Geçerli mi: " . ($is_past ? 'Evet (Geçmiş/Şimdiki)' : 'Hayır (Gelecek)') . "\n";
        echo "  URL: blog-detail.php?slug={$post['slug']}\n\n";
    }

    echo "✅ Blog tarihleri başarıyla düzeltildi!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>