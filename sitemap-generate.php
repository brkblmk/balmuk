<?php
require_once 'config/database.php';

// Fonksiyon: Blog yazıları çek
function getBlogPosts() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT slug, updated_at FROM blog_posts WHERE is_active = 1 ORDER BY updated_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Fonksiyon: Hizmetler çek (varsa)
function getServices() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT slug, updated_at FROM services WHERE is_active = 1 ORDER BY updated_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Basit PHP sayfalar listesi (admin hariç)
$pages = [
    'index.php' => ['priority' => 1.0, 'changefreq' => 'daily'],
    'blog.php' => ['priority' => 0.8, 'changefreq' => 'weekly'],
    'contact-form.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'chatbot.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'device-check.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'performance-monitor.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'optimize-images.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'db-test.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'test-email.php' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    'update-devices.php' => ['priority' => 0.6, 'changefreq' => 'monthly']
];

// XML header
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
$xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

// Ana sayfa
$xml .= '    <url>' . "\n";
$xml .= '        <loc>https://primeemsstudios.com/</loc>' . "\n";
$xml .= '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
$xml .= '        <changefreq>daily</changefreq>' . "\n";
$xml .= '        <priority>1.0</priority>' . "\n";
$xml .= '    </url>' . "\n";

// Diğer sayfalar
foreach ($pages as $page => $settings) {
    if ($page === 'index.php') continue; // Ana sayfa zaten eklendi
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>https://primeemsstudios.com/' . $page . '</loc>' . "\n";
    $xml .= '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '        <changefreq>' . $settings['changefreq'] . '</changefreq>' . "\n";
    $xml .= '        <priority>' . $settings['priority'] . '</priority>' . "\n";
    $xml .= '    </url>' . "\n";
}

// Dinamik blog yazıları
$blogPosts = getBlogPosts();
foreach ($blogPosts as $post) {
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>https://primeemsstudios.com/blog-detail.php?slug=' . htmlspecialchars($post['slug']) . '</loc>' . "\n";
    $xml .= '        <lastmod>' . date('Y-m-d', strtotime($post['updated_at'])) . '</lastmod>' . "\n";
    $xml .= '        <changefreq>weekly</changefreq>' . "\n";
    $xml .= '        <priority>0.8</priority>' . "\n";
    $xml .= '    </url>' . "\n";
}

// Dinamik hizmetler (varsa)
$services = getServices();
foreach ($services as $service) {
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>https://primeemsstudios.com/service-detail.php?slug=' . htmlspecialchars($service['slug']) . '</loc>' . "\n";
    $xml .= '        <lastmod>' . date('Y-m-d', strtotime($service['updated_at'])) . '</lastmod>' . "\n";
    $xml .= '        <changefreq>monthly</changefreq>' . "\n";
    $xml .= '        <priority>0.8</priority>' . "\n";
    $xml .= '    </url>' . "\n";
}

$xml .= '</urlset>' . "\n";

// XML'i dosyaya yaz
file_put_contents('sitemap.xml', $xml);
echo 'Sitemap başarıyla güncellendi!';
?>