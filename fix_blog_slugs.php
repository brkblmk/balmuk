<?php
require_once 'config/database.php';

try {
    echo "=== BLOG SLUG'LARI DÜZELTME ===\n\n";

    // Sorunlu slug'ları düzelt
    $pdo->exec("UPDATE blog_posts SET slug = 'ai-destekli-icerik-ems' WHERE id = 11");
    $pdo->exec("UPDATE blog_posts SET slug = 'ai-destekli-icerik-ems-sonrasi' WHERE id = 16");

    echo "✓ Sorunlu slug'lar düzeltildi.\n";

    // Tüm slug'ları kontrol et - gereksiz tire'leri temizle
    $stmt = $pdo->query("SELECT id, title, slug FROM blog_posts");
    $posts = $stmt->fetchAll();

    echo "\n--- Güncellenmiş Slug'lar ---\n";
    foreach ($posts as $post) {
        // Slug'ın sonunda gereksiz tire varsa temizle
        $clean_slug = rtrim($post['slug'], '-');

        if ($clean_slug !== $post['slug']) {
            $pdo->prepare("UPDATE blog_posts SET slug = ? WHERE id = ?")->execute([$clean_slug, $post['id']]);
            echo "- ID: {$post['id']}, Başlık: {$post['title']}, Slug: {$clean_slug} (düzeltildi)\n";
        } else {
            echo "- ID: {$post['id']}, Başlık: {$post['title']}, Slug: {$post['slug']}\n";
        }
    }

    echo "\n✅ Blog slug'ları başarıyla düzeltildi!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>