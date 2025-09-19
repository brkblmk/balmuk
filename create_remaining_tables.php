<?php
require_once 'config/database.php';

try {
    echo "Kalan eksik tablolar oluşturuluyor...\n\n";

    // Site Settings tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ site_settings tablosu oluşturuldu.\n";

    // About Section tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS about_section (
        id INT PRIMARY KEY DEFAULT 1,
        title VARCHAR(255),
        subtitle VARCHAR(255),
        description TEXT,
        features JSON,
        stats JSON,
        video_url VARCHAR(255),
        image VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ about_section tablosu oluşturuldu.\n";

    // Statistics tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS statistics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255),
        value VARCHAR(50),
        icon VARCHAR(50),
        suffix VARCHAR(10),
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ statistics tablosu oluşturuldu.\n";

    // Admins tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE,
        full_name VARCHAR(100),
        role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
        is_active TINYINT(1) DEFAULT 1,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ admins tablosu oluşturuldu.\n";

    // Activity Logs tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        admin_id INT,
        action VARCHAR(100) NOT NULL,
        module VARCHAR(50),
        record_id INT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
    )");
    echo "✓ activity_logs tablosu oluşturuldu.\n";

    // Hero section'a eksik sütunlar ekle
    try {
        $pdo->exec("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS media_type VARCHAR(20) DEFAULT 'video'");
        $pdo->exec("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS media_path VARCHAR(255)");
        echo "✓ hero_section tablosuna eksik sütunlar eklendi.\n";
    } catch (PDOException $e) {
        echo "Hero section güncelleme uyarısı: " . $e->getMessage() . "\n";
    }

    // Temel settings ekle
    $settings = [
        ['site_url', 'https://primeemsstudios.com', 'Site URL'],
        ['site_title', 'Prime EMS Studios İzmir', 'Site Başlığı'],
        ['contact_phone', '+90 232 555 66 77', 'İletişim Telefonu'],
        ['contact_whatsapp', '905XXXXXXXXX', 'WhatsApp Numarası'],
        ['contact_email', 'info@primeems.com', 'İletişim E-posta'],
        ['contact_address', 'Balçova, İzmir, Türkiye', 'Adres'],
        ['working_hours_weekday', '07:00 - 22:00', 'Hafta İçi Çalışma Saatleri'],
        ['working_hours_weekend', '09:00 - 20:00', 'Hafta Sonu Çalışma Saatleri'],
        ['social_share_image', '/assets/images/logo.png', 'Sosyal Medya Paylaşım Görseli']
    ];

    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($settings as $setting) {
        $stmt->execute($setting);
        echo "✓ '{$setting[0]}' ayarı eklendi/güncellendi.\n";
    }

    // Temel admin kullanıcısı oluştur
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO admins (username, password, email, full_name, role) VALUES ('admin', '$adminPassword', 'admin@primeems.com', 'Yönetici', 'super_admin')");
    echo "✓ Temel admin kullanıcısı oluşturuldu.\n";

    echo "\n✅ Tüm kalan tablolar ve temel veriler başarıyla oluşturuldu!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}