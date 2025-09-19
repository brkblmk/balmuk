<?php
require_once 'config/database.php';

// XML header
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

$base_url = 'https://primeemsstudios.com';
$today = date('Y-m-d');

// Ana sayfa
echo "    <url>\n";
echo "        <loc>$base_url/</loc>\n";
echo "        <lastmod>$today</lastmod>\n";
echo "        <changefreq>daily</changefreq>\n";
echo "        <priority>1.0</priority>\n";
echo "    </url>\n";

// Statik sayfalar
$static_pages = [
    ['url' => '/blog.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['url' => '/contact.php', 'changefreq' => 'monthly', 'priority' => '0.8'],
    ['url' => '/about.php', 'changefreq' => 'monthly', 'priority' => '0.8'],
    ['url' => '/services.php', 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['url' => '/packages.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['url' => '/reservation.php', 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['url' => '/faq.php', 'changefreq' => 'monthly', 'priority' => '0.7']
];

foreach ($static_pages as $page) {
    echo "    <url>\n";
    echo "        <loc>$base_url{$page['url']}</loc>\n";
    echo "        <lastmod>$today</lastmod>\n";
    echo "        <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "        <priority>{$page['priority']}</priority>\n";
    echo "    </url>\n";
}

try {
    // Blog yazıları
    $stmt = $pdo->query("
        SELECT slug, updated_at, featured_image 
        FROM blog_posts 
        WHERE is_published = 1 
        ORDER BY published_at DESC
    ");
    $posts = $stmt->fetchAll();
    
    foreach ($posts as $post) {
        $lastmod = date('Y-m-d', strtotime($post['updated_at']));
        echo "    <url>\n";
        echo "        <loc>$base_url/blog-detail.php?slug=" . htmlspecialchars($post['slug']) . "</loc>\n";
        echo "        <lastmod>$lastmod</lastmod>\n";
        echo "        <changefreq>monthly</changefreq>\n";
        echo "        <priority>0.7</priority>\n";
        
        // Blog yazısında resim varsa ekle
        if ($post['featured_image']) {
            echo "        <image:image>\n";
            echo "            <image:loc>$base_url" . htmlspecialchars($post['featured_image']) . "</image:loc>\n";
            echo "        </image:image>\n";
        }
        
        echo "    </url>\n";
    }
    
    // Blog kategorileri
    $cat_stmt = $pdo->query("
        SELECT slug, name FROM blog_categories 
        WHERE is_active = 1 
        ORDER BY sort_order
    ");
    $categories = $cat_stmt->fetchAll();
    
    foreach ($categories as $category) {
        echo "    <url>\n";
        echo "        <loc>$base_url/blog.php?category=" . htmlspecialchars($category['slug']) . "</loc>\n";
        echo "        <lastmod>$today</lastmod>\n";
        echo "        <changefreq>weekly</changefreq>\n";
        echo "        <priority>0.6</priority>\n";
        echo "    </url>\n";
    }
    
    // Hizmetler
    $services_stmt = $pdo->query("
        SELECT slug FROM services 
        WHERE is_active = 1 
        ORDER BY sort_order
    ");
    $services = $services_stmt->fetchAll();
    
    foreach ($services as $service) {
        echo "    <url>\n";
        echo "        <loc>$base_url/service-detail.php?slug=" . htmlspecialchars($service['slug']) . "</loc>\n";
        echo "        <lastmod>$today</lastmod>\n";
        echo "        <changefreq>monthly</changefreq>\n";
        echo "        <priority>0.8</priority>\n";
        echo "    </url>\n";
    }
    
    // Kampanyalar (aktif olanlar)
    $campaigns_stmt = $pdo->query("
        SELECT id FROM campaigns 
        WHERE is_active = 1 
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY sort_order
    ");
    $campaigns = $campaigns_stmt->fetchAll();
    
    foreach ($campaigns as $campaign) {
        echo "    <url>\n";
        echo "        <loc>$base_url/campaign.php?id=" . $campaign['id'] . "</loc>\n";
        echo "        <lastmod>$today</lastmod>\n";
        echo "        <changefreq>weekly</changefreq>\n";
        echo "        <priority>0.8</priority>\n";
        echo "    </url>\n";
    }
    
} catch (PDOException $e) {
    // Veritabanı hatası durumunda sadece statik sayfalar gösterilir
    error_log("Sitemap generation error: " . $e->getMessage());
}

echo "</urlset>\n";
?>