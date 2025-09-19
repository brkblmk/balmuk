<?php
require_once 'config/database.php';

try {
    // Campaigns tablosunu oluştur
    $pdo->exec("CREATE TABLE IF NOT EXISTS campaigns (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255),
        subtitle VARCHAR(255),
        description TEXT,
        discount_text VARCHAR(100),
        badge_text VARCHAR(100),
        badge_color VARCHAR(20) DEFAULT 'warning',
        icon VARCHAR(50),
        start_date DATE,
        end_date DATE,
        image VARCHAR(255),
        button_text VARCHAR(100) DEFAULT 'Hemen Rezerve Et',
        button_link VARCHAR(255),
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    echo "✓ campaigns tablosu başarıyla oluşturuldu.\n";

    // Örnek kampanya verilerini ekle
    $campaigns = [
        [
            'title' => 'EMS 30 Günlük Paket Fırsatı',
            'subtitle' => 'Sağlıkla Tanışın',
            'description' => '30 günlük EMS antrenman paketi ile sağlığınızı yeniden keşfedin. Profesyonel eğitmenler eşliğinde maksimum sonuçlar.',
            'discount_text' => '%25 İndirim',
            'badge_text' => 'SÜPER FIRSAT',
            'badge_color' => 'danger',
            'icon' => 'bi-lightning-charge',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'button_text' => 'Hemen Başla',
            'button_link' => '#reservation',
            'is_featured' => 1,
            'is_active' => 1,
            'sort_order' => 1
        ],
        [
            'title' => 'Grup Antrenman Avantajı',
            'subtitle' => 'Arkadaşlarınızla Birlikte',
            'description' => 'Arkadaşlarınızla birlikte EMS antrenmanları yapın ve %30 daha fazla indirim kazanın.',
            'discount_text' => '%30 Grup İndirimi',
            'badge_text' => 'SOSYAL',
            'badge_color' => 'success',
            'icon' => 'bi-people',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+60 days')),
            'button_text' => 'Grup Rezervasyonu',
            'button_link' => '#reservation',
            'is_featured' => 0,
            'is_active' => 1,
            'sort_order' => 2
        ],
        [
            'title' => 'Yeni Üye Özel Paketi',
            'subtitle' => 'İlk Ay Ücretsiz',
            'description' => 'Prime EMS ailesine yeni katılan üyelerimize ilk ay ücretsiz antrenman fırsatı.',
            'discount_text' => 'İlk Ay Ücretsiz',
            'badge_text' => 'YENİ ÜYE',
            'badge_color' => 'primary',
            'icon' => 'bi-star-fill',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+90 days')),
            'button_text' => 'Ücretsiz Başla',
            'button_link' => '#reservation',
            'is_featured' => 1,
            'is_active' => 1,
            'sort_order' => 3
        ],
        [
            'title' => 'VIP Üyelik Kampanyası',
            'subtitle' => 'Özel Hizmetler',
            'description' => 'VIP üyelik ile özel antrenman programları, beslenme danışmanlığı ve daha fazlası.',
            'discount_text' => '%15 VIP Avantaj',
            'badge_text' => 'VIP',
            'badge_color' => 'warning',
            'icon' => 'bi-crown',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+45 days')),
            'button_text' => 'VIP Ol',
            'button_link' => '#reservation',
            'is_featured' => 0,
            'is_active' => 1,
            'sort_order' => 4
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO campaigns (title, subtitle, description, discount_text, badge_text, badge_color, icon, start_date, end_date, button_text, button_link, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($campaigns as $campaign) {
        $stmt->execute([
            $campaign['title'],
            $campaign['subtitle'],
            $campaign['description'],
            $campaign['discount_text'],
            $campaign['badge_text'],
            $campaign['badge_color'],
            $campaign['icon'],
            $campaign['start_date'],
            $campaign['end_date'],
            $campaign['button_text'],
            $campaign['button_link'],
            $campaign['is_featured'],
            $campaign['is_active'],
            $campaign['sort_order']
        ]);
        echo "✓ '{$campaign['title']}' kampanyası eklendi.\n";
    }

    echo "\n✅ Kampanya tablosu ve örnek veriler başarıyla oluşturuldu!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}