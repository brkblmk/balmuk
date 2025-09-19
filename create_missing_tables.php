<?php
require_once 'config/database.php';

try {
    echo "Eksik tablolar oluşturuluyor...\n\n";

    // Hero Section tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS hero_section (
        id INT PRIMARY KEY DEFAULT 1,
        title VARCHAR(255),
        subtitle VARCHAR(255),
        description TEXT,
        button1_text VARCHAR(100),
        button1_link VARCHAR(255),
        button2_text VARCHAR(100),
        button2_link VARCHAR(255),
        overlay_opacity DECIMAL(3,2) DEFAULT 0.5,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ hero_section tablosu oluşturuldu.\n";

    // FAQs tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        question VARCHAR(500) NOT NULL,
        answer TEXT NOT NULL,
        category VARCHAR(100) DEFAULT 'Genel',
        is_published TINYINT(1) DEFAULT 1,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ faqs tablosu oluşturuldu.\n";

    // Blog Categories tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        color VARCHAR(20) DEFAULT '#FFD700',
        icon VARCHAR(50) DEFAULT 'bi-bookmark',
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ blog_categories tablosu oluşturuldu.\n";

    // Blog Tags tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_tags (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        color VARCHAR(20) DEFAULT '#6c757d',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ blog_tags tablosu oluşturuldu.\n";

    // Blog Post Tags tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_post_tags (
        id INT PRIMARY KEY AUTO_INCREMENT,
        post_id INT NOT NULL,
        tag_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE,
        UNIQUE KEY unique_post_tag (post_id, tag_id)
    )");
    echo "✓ blog_post_tags tablosu oluşturuldu.\n";

    // Blog Comments tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        post_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        comment TEXT NOT NULL,
        ip_address VARCHAR(45),
        is_approved TINYINT(1) DEFAULT 0,
        parent_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE
    )");
    echo "✓ blog_comments tablosu oluşturuldu.\n";

    // Blog Posts tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT,
        content LONGTEXT NOT NULL,
        category_id INT,
        author_id INT,
        featured_image VARCHAR(255),
        tags VARCHAR(500),
        reading_time INT DEFAULT 5,
        view_count INT DEFAULT 0,
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        is_published TINYINT(1) DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        is_ai_generated TINYINT(1) DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
    )");
    echo "✓ blog_posts tablosu oluşturuldu.\n";

    // EMS Devices tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS ems_devices (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        device_type VARCHAR(50) DEFAULT 'i-motion',
        model VARCHAR(100),
        manufacturer VARCHAR(100) DEFAULT 'Prime EMS',
        short_description TEXT,
        long_description LONGTEXT,
        main_image VARCHAR(255),
        gallery_images JSON,
        features JSON,
        specifications JSON,
        exercise_programs JSON,
        capacity INT DEFAULT 1,
        price_range VARCHAR(50),
        sort_order INT DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ ems_devices tablosu oluşturuldu.\n";

    // Services tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        short_description TEXT,
        long_description LONGTEXT,
        duration VARCHAR(50) DEFAULT '20 dk',
        goal VARCHAR(255),
        icon VARCHAR(50) DEFAULT 'bi-star',
        price DECIMAL(10,2),
        session_count INT DEFAULT 1,
        features JSON,
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ services tablosu oluşturuldu.\n";

    // Testimonials tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_name VARCHAR(255) NOT NULL,
        customer_title VARCHAR(255),
        content TEXT NOT NULL,
        rating INT DEFAULT 5,
        service_used VARCHAR(255),
        result_achieved VARCHAR(255),
        video_url VARCHAR(255),
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ testimonials tablosu oluşturuldu.\n";

    echo "\n✅ Tüm eksik tablolar başarıyla oluşturuldu!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>