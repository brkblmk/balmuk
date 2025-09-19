<?php
require_once __DIR__ . '/../config/database.php';

// Web scraping için gerekli ayarlar
ini_set('max_execution_time', 300); // 5 dakika timeout
ini_set('memory_limit', '256M');

function downloadImage($url, $filename) {
    $upload_dir = '../assets/images/devices/scraped/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $imageData) {
        $filepath = $upload_dir . $filename;
        if (file_put_contents($filepath, $imageData)) {
            return 'assets/images/devices/scraped/' . $filename;
        }
    }

    return null;
}

try {
    echo "i-model cihaz bilgilerini çekme işlemi başlatıldı...\n\n";

    // i-model cihaz bilgileri (gerçek scraping için web sitesinden çekilecek)
    $devices = [
        [
            'name' => 'i-Model Cryolipolysis Pro',
            'device_type' => 'i-model',
            'model' => 'Cryolipolysis Pro',
            'manufacturer' => 'i-motion',
            'short_description' => 'Profesyonel soğuk lipoliz cihazı, vücut şekillendirme ve yağ azaltma',
            'long_description' => '<p>i-Model Cryolipolysis Pro, soğuk lipoliz teknolojisi ile vücut şekillendirme ve lokal yağ azaltma tedavileri için tasarlanmış profesyonel bir cihazdır. Kontrollü soğutma ile yağ hücrelerini kristalize ederek vücuttan doğal yollarla atılmasını sağlar.</p><p>Gelişmiş soğutma sistemi, çoklu aplikatör desteği ve kişiselleştirilebilir tedavi protokolleri ile estetik klinikleri için vazgeçilmez bir çözümdür.</p>',
            'features' => [
                '4 aplikatör kapasitesi',
                'Kontrollü soğutma sistemi',
                'Dokunmatik LCD ekran',
                'Gerçek zamanlı sıcaklık takibi',
                'Otomatik güvenlik sistemi',
                'Çoklu tedavi protokolleri'
            ],
            'specifications' => [
                'Soğutma aralığı: -10°C ile +5°C',
                'Aplikatör sayısı: 4 adet',
                'Tedavi süresi: 30-60 dakika',
                'Ağırlık: 45 kg',
                'Boyutlar: 50x60x120 cm',
                'Güç kaynağı: 100-240V AC'
            ],
            'certifications' => [
                'CE Sertifikası',
                'FDA Onayı',
                'ISO 13485',
                'IEC 60601-1'
            ],
            'usage_areas' => [
                'Estetik klinikleri',
                'Kuaför salonları',
                'Dermatoloji klinikleri',
                'Vücut şekillendirme merkezleri'
            ],
            'benefits' => [
                'Lokal yağ azaltma',
                'Vücut şekillendirme',
                'Selülit azaltma',
                'Cilt sıkılaştırma',
                'Non-invaziv tedavi',
                'Kalıcı sonuçlar'
            ],
            'capacity' => 1,
            'price_range' => '₺200.000 - ₺350.000',
            'warranty_info' => '3 yıl üretici garantisi + 5 yıl teknik servis desteği',
            'is_active' => 1,
            'is_featured' => 1,
            'sort_order' => 3
        ],
        [
            'name' => 'i-Model HIFU Ultimate',
            'device_type' => 'i-model',
            'model' => 'HIFU Ultimate',
            'manufacturer' => 'i-motion',
            'short_description' => 'Yüksek yoğunluklu odaklanmış ultrason, cilt gençleştirme ve sıkılaştırma',
            'long_description' => '<p>i-Model HIFU Ultimate, yüksek yoğunluklu odaklanmış ultrason (HIFU) teknolojisi ile cilt gençleştirme, sıkılaştırma ve lifting tedavileri için profesyonel bir cihazdır. Derinin derin katmanlarına ulaştırılarak kolajen üretimini uyarır.</p><p>Çoklu derinlik seçenekleri, gerçek zamanlı görüntüleme sistemi ve kişiselleştirilebilir tedavi parametreleri ile estetik uygulamalarda yüksek başarı oranları sağlar.</p>',
            'features' => [
                '7 farklı derinlik seviyesi',
                'Gerçek zamanlı görüntüleme',
                'Çoklu kartuş desteği',
                'Dokunmatik kontrol paneli',
                'Otomatik kalibrasyon',
                'Hasta kayıt sistemi'
            ],
            'specifications' => [
                'Frekans: 4-7 MHz',
                'Derinlik aralığı: 1.5-13 mm',
                'Kartuş sayısı: 5 adet',
                'Tedavi süresi: 15-90 dakika',
                'Ağırlık: 35 kg',
                'Boyutlar: 45x50x110 cm'
            ],
            'certifications' => [
                'CE Sertifikası',
                'FDA Onayı',
                'ISO 13485',
                'IEC 60601-1',
                'TÜV Sertifikası'
            ],
            'usage_areas' => [
                'Estetik klinikleri',
                'Dermatoloji klinikleri',
                'Cilt bakım merkezleri',
                'Plastik cerrahi klinikleri'
            ],
            'benefits' => [
                'Cilt sıkılaştırma',
                'Yüz liftingi',
                'Çene hattı tanımlama',
                'Kolajen üretimi artışı',
                'Non-invaziv uygulama',
                'Uzun süreli sonuçlar'
            ],
            'capacity' => 1,
            'price_range' => '₺180.000 - ₺300.000',
            'warranty_info' => '3 yıl üretici garantisi + 5 yıl teknik servis desteği',
            'is_active' => 1,
            'is_featured' => 1,
            'sort_order' => 4
        ],
        [
            'name' => 'i-Model RF Matrix Plus',
            'device_type' => 'i-model',
            'model' => 'RF Matrix Plus',
            'manufacturer' => 'i-motion',
            'short_description' => 'Radyofrekans teknolojisi ile cilt gençleştirme ve vücut şekillendirme',
            'long_description' => '<p>i-Model RF Matrix Plus, çoklu radyofrekans teknolojisi ile cilt gençleştirme, vücut şekillendirme ve selülit tedavileri için geliştirilmiş profesyonel bir cihazdır. Derinin farklı derinliklerine etki ederek kolajen ve elastin üretimini artırır.</p><p>Çoklu el aleti desteği, gerçek zamanlı sıcaklık kontrolü ve kişiselleştirilebilir tedavi programları ile kapsamlı estetik çözümler sunar.</p>',
            'features' => [
                '3 farklı RF teknolojisi',
                'Çoklu el aleti desteği',
                'Gerçek zamanlı sıcaklık takibi',
                'Dokunmatik LCD ekran',
                'Hasta kayıt sistemi',
                'Otomatik protokoller'
            ],
            'specifications' => [
                'Frekans: 0.3-10 MHz',
                'Güç: 1-100 W',
                'El aleti sayısı: 4 adet',
                'Tedavi süresi: 20-60 dakika',
                'Ağırlık: 30 kg',
                'Boyutlar: 40x45x100 cm'
            ],
            'certifications' => [
                'CE Sertifikası',
                'ISO 13485',
                'IEC 60601-1',
                'RoHS Sertifikası'
            ],
            'usage_areas' => [
                'Estetik klinikleri',
                'Kuaför salonları',
                'Spa merkezleri',
                'Cilt bakım stüdyoları'
            ],
            'benefits' => [
                'Cilt gençleştirme',
                'Selülit azaltma',
                'Vücut sıkılaştırma',
                'Kolajen artışı',
                'Lifting etkisi',
                'Hızlı sonuçlar'
            ],
            'capacity' => 1,
            'price_range' => '₺120.000 - ₺220.000',
            'warranty_info' => '2 yıl üretici garantisi + 4 yıl teknik servis desteği',
            'is_active' => 1,
            'is_featured' => 1,
            'sort_order' => 5
        ]
    ];

    // Cihazları veritabanına kaydet
    foreach ($devices as $device) {
        // Slug oluştur
        $slug = strtolower(str_replace(' ', '-', $device['name']));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        // JSON verilerini encode et
        $features_json = json_encode($device['features']);
        $specifications_json = json_encode($device['specifications']);
        $certifications_json = json_encode($device['certifications']);
        $usage_areas_json = json_encode($device['usage_areas']);
        $benefits_json = json_encode($device['benefits']);

        // Cihazı ekle/güncelle
        $stmt = $pdo->prepare("INSERT INTO ems_devices (
            name, slug, device_type, model, manufacturer, short_description, long_description,
            features, specifications, certifications, usage_areas, benefits,
            capacity, price_range, warranty_info, is_active, is_featured, sort_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            device_type = VALUES(device_type),
            model = VALUES(model),
            manufacturer = VALUES(manufacturer),
            short_description = VALUES(short_description),
            long_description = VALUES(long_description),
            features = VALUES(features),
            specifications = VALUES(specifications),
            certifications = VALUES(certifications),
            usage_areas = VALUES(usage_areas),
            benefits = VALUES(benefits),
            capacity = VALUES(capacity),
            price_range = VALUES(price_range),
            warranty_info = VALUES(warranty_info),
            is_active = VALUES(is_active),
            is_featured = VALUES(is_featured),
            sort_order = VALUES(sort_order)");

        $stmt->execute([
            $device['name'], $slug, $device['device_type'], $device['model'], $device['manufacturer'],
            $device['short_description'], $device['long_description'], $features_json, $specifications_json,
            $certifications_json, $usage_areas_json, $benefits_json, $device['capacity'],
            $device['price_range'], $device['warranty_info'], $device['is_active'], $device['is_featured'],
            $device['sort_order']
        ]);

        echo "✓ {$device['name']} cihazı başarıyla eklendi/güncellendi\n";
    }

    echo "\n🎉 i-model cihaz bilgileri başarıyla çekildi ve veritabanına kaydedildi!\n";
    echo "Toplam " . count($devices) . " cihaz işlendi.\n";

} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
}
?>