<?php
require_once 'config/database.php';

try {
    echo "Ek örnek EMS hizmet verileri ekleniyor...\n\n";

    // Mevcut hizmet sayısını kontrol et
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $count = $countStmt->fetch()['count'];

    echo "Mevcut hizmet sayısı: {$count}\n";

    // Yeni hizmet verilerini ekle
    $newServices = [
        [
            'name' => 'EMS Strength Paketi',
            'slug' => 'ems-strength-paketi',
            'short_description' => 'Güç ve kas geliştirme odaklı EMS antrenmanı',
            'long_description' => 'EMS teknolojisi ile maksimum güç geliştirme ve kas kütle artışı için tasarlanmış özel program. Ağır yük antrenmanının etkisini 20 dakikada yakalayın.',
            'duration' => '25 dk',
            'goal' => 'Güç geliştirme',
            'icon' => 'bi-trophy',
            'price' => 180.00,
            'session_count' => 1,
            'features' => json_encode(['Maksimum güç geliştirme', 'Kas kütle artışı', 'Testosteron seviyesi artış', 'Dayanıklılık artışı']),
            'is_featured' => 1,
            'sort_order' => 4
        ],
        [
            'name' => 'EMS Recovery Paketi',
            'slug' => 'ems-recovery-paketi',
            'short_description' => 'Hızlı toparlanma ve iyileşme odaklı EMS seansı',
            'long_description' => 'EMS teknolojisi ile kas iyileşmesini hızlandırın ve antrenman sonrası toparlanmanızı optimize edin. Aktif iyileşme için özel program.',
            'duration' => '15 dk',
            'goal' => 'İyileşme ve toparlanma',
            'icon' => 'bi-heart-pulse',
            'price' => 100.00,
            'session_count' => 1,
            'features' => json_encode(['Hızlı iyileşme', 'Kas ağrısı azaltma', 'Sirkülasyon artışı', 'Esneklik geliştirme']),
            'is_featured' => 0,
            'sort_order' => 5
        ],
        [
            'name' => 'EMS Endurance Paketi',
            'slug' => 'ems-endurance-paketi',
            'short_description' => 'Dayanıklılık ve kondisyon geliştirme EMS programı',
            'long_description' => 'EMS ile kardiyovasküler dayanıklılığınızı artırın ve uzun süreli performansınızı geliştirin. Sporcular için ideal program.',
            'duration' => '30 dk',
            'goal' => 'Dayanıklılık geliştirme',
            'icon' => 'bi-speedometer2',
            'price' => 160.00,
            'session_count' => 1,
            'features' => json_encode(['Kardiyovasküler dayanıklılık', 'Oksijen kapasitesi artışı', 'Yorgunluk direnci', 'Performans artışı']),
            'is_featured' => 1,
            'sort_order' => 6
        ]
    ];

    $addedCount = 0;
    foreach ($newServices as $service) {
        // Slug kontrolü - aynı slug varsa ekleme
        $stmt = $pdo->prepare("SELECT id FROM services WHERE slug = ?");
        $stmt->execute([$service['slug']]);
        if ($stmt->fetch()) {
            echo "⚠️  '{$service['name']}' zaten mevcut, atlandı.\n";
            continue;
        }

        // Hizmet ekleme
        $insertStmt = $pdo->prepare("INSERT INTO services (name, slug, short_description, long_description, duration, goal, icon, price, session_count, features, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([
            $service['name'],
            $service['slug'],
            $service['short_description'],
            $service['long_description'],
            $service['duration'],
            $service['goal'],
            $service['icon'],
            $service['price'],
            $service['session_count'],
            $service['features'],
            $service['is_featured'],
            $service['sort_order']
        ]);

        echo "✓ '{$service['name']}' eklendi.\n";
        $addedCount++;
    }

    echo "\n✅ {$addedCount} yeni hizmet başarıyla eklendi!\n";
    echo "Toplam hizmet sayısı: " . ($count + $addedCount) . "\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>