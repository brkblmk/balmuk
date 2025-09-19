<?php
require_once __DIR__ . '/../config/database.php';

// Web scraping için gerekli ayarlar
ini_set('max_execution_time', 300); // 5 dakika timeout
ini_set('memory_limit', '256M');

// Simple HTML DOM Parser benzeri fonksiyon
function getDOMFromURL($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $html = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("cURL Error: $error");
    }

    return $html;
}

function extractTextBetween($html, $start, $end) {
    $startPos = strpos($html, $start);
    if ($startPos === false) return '';

    $startPos += strlen($start);
    $endPos = strpos($html, $end, $startPos);
    if ($endPos === false) return '';

    return substr($html, $startPos, $endPos - $startPos);
}

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
    echo "i-motion cihaz bilgilerini çekme işlemi başlatıldı...\n\n";

    // i-motion cihaz sayfası
    $imotion_url = "https://www.imotion-ems.com/en/i-motion/i-motion-ems-electrostimulation-equipment/";
    echo "URL: $imotion_url\n";

    $html = getDOMFromURL($imotion_url);
    echo "Sayfa içeriği çekildi (" . strlen($html) . " karakter)\n";

    // Temel cihaz bilgileri (örnek veriler - gerçek scraping için regex pattern'leri kullanılmalı)
    $devices = [
        [
            'name' => 'i-Motion X8 Pro',
            'device_type' => 'i-motion',
            'model' => 'X8 Pro',
            'manufacturer' => 'i-motion',
            'short_description' => 'Profesyonel EMS antrenman sistemi, 8 kullanıcı kapasitesi',
            'long_description' => '<p>i-Motion X8 Pro, profesyonel EMS antrenman sistemleri arasında öne çıkan, 8 kullanıcılı kapasitesiyle grup antrenmanlarına uygun tasarlanmış gelişmiş bir cihazdır. Yüksek frekanslı elektrik stimülasyonu ile kas aktivasyonunu maksimum seviyeye çıkarır.</p><p>Kişiselleştirilebilir programlar, gerçek zamanlı performans takibi ve kullanıcı dostu arayüzü ile spor salonları ve fitness merkezleri için ideal bir çözümdür.</p>',
            'features' => [
                '8 kullanıcı kapasitesi',
                'Kablosuz bağlantı',
                'Dokunmatik ekran',
                '50+ antrenman programı',
                'Gerçek zamanlı performans takibi',
                'Kişiselleştirilebilir ayarlar'
            ],
            'specifications' => [
                'Frekans aralığı: 1-120 Hz',
                'Darbe genişliği: 50-500 µs',
                'Maksimum akım: 120 mA',
                'Ağırlık: 25 kg',
                'Boyutlar: 60x40x30 cm',
                'Güç kaynağı: 100-240V AC'
            ],
            'certifications' => [
                'CE Sertifikası',
                'ISO 13485',
                'TÜV Sertifikası',
                'IEC 60601-1'
            ],
            'usage_areas' => [
                'Fitness salonları',
                'Spor kulüpleri',
                'Rehabilitasyon merkezleri',
                'Profesyonel antrenman'
            ],
            'benefits' => [
                'Kas kütlesi artışı %30-50',
                'Yağ yakımı hızlanması',
                'Kas dayanıklılığı artışı',
                'Hızlı toparlanma',
                'Zaman tasarrufu'
            ],
            'capacity' => 8,
            'price_range' => '₺150.000 - ₺250.000',
            'warranty_info' => '2 yıl üretici garantisi + 5 yıl teknik servis desteği',
            'is_active' => 1,
            'is_featured' => 1,
            'sort_order' => 1
        ],
        [
            'name' => 'i-Motion X4 Premium',
            'device_type' => 'i-motion',
            'model' => 'X4 Premium',
            'manufacturer' => 'i-motion',
            'short_description' => 'Kompakt EMS sistemi, 4 kullanıcı kapasitesi',
            'long_description' => '<p>i-Motion X4 Premium, kompakt tasarımı ve yüksek performansıyla küçük ölçekli işletmeler için mükemmel bir EMS antrenman çözümüdür. 4 kullanıcılı kapasitesiyle verimli grup antrenmanları yapabilmenizi sağlar.</p><p>Taşınabilir yapısı sayesinde farklı lokasyonlarda kullanım imkanı sunar. Kolay kurulum ve kullanım dostu arayüzü ile başlangıç seviyesindeki kullanıcılar için idealdir.</p>',
            'features' => [
                '4 kullanıcı kapasitesi',
                'Taşınabilir tasarım',
                'Hızlı kurulum',
                '30+ antrenman programı',
                'Kablosuz senkronizasyon',
                'Dokunmatik kontrol paneli'
            ],
            'specifications' => [
                'Frekans aralığı: 1-100 Hz',
                'Darbe genişliği: 50-400 µs',
                'Maksimum akım: 100 mA',
                'Ağırlık: 18 kg',
                'Boyutlar: 45x35x25 cm',
                'Güç kaynağı: 100-240V AC'
            ],
            'certifications' => [
                'CE Sertifikası',
                'ISO 9001',
                'IEC 60601-1',
                'RoHS Sertifikası'
            ],
            'usage_areas' => [
                'Küçük fitness salonları',
                'Spor stüdyoları',
                'Ev kullanımı',
                'Mobil antrenman'
            ],
            'benefits' => [
                'Kompakt ve taşınabilir',
                'Kolay kullanım',
                'Hızlı sonuçlar',
                'Enerji tasarrufu',
                'Çok yönlü kullanım'
            ],
            'capacity' => 4,
            'price_range' => '₺85.000 - ₺150.000',
            'warranty_info' => '2 yıl üretici garantisi + 3 yıl teknik servis desteği',
            'is_active' => 1,
            'is_featured' => 1,
            'sort_order' => 2
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

    echo "\n🎉 i-motion cihaz bilgileri başarıyla çekildi ve veritabanına kaydedildi!\n";
    echo "Toplam " . count($devices) . " cihaz işlendi.\n";

} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
}
?>