<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// Rate limiting kontrolü
if (!SecurityUtils::checkRateLimit('save_about', 5, 60)) {
    SecurityUtils::logSecurityEvent('API_RATE_LIMIT_EXCEEDED', [
        'action' => 'save_about',
        'ip' => SecurityUtils::getClientIP()
    ]);
    echo json_encode(['success' => false, 'message' => 'Çok sık istek gönderiyorsunuz. Lütfen 1 dakika bekleyiniz.']);
    exit;
}

// POST verilerini al ve sanitize et
$input = $_POST;

// Gerekli alanları kontrol et ve sanitize et
$title = SecurityUtils::sanitizeInput($input['title'] ?? '', 'html');
$subtitle = SecurityUtils::sanitizeInput($input['subtitle'] ?? '', 'html');
$content = SecurityUtils::sanitizeInput($input['content'] ?? '', 'html');
$mission = SecurityUtils::sanitizeInput($input['mission'] ?? '', 'html');
$vision = SecurityUtils::sanitizeInput($input['vision'] ?? '', 'html');
$image = SecurityUtils::sanitizeInput($input['image'] ?? '', 'url');

// JSON alanları için özel işleme
$values = $input['values'] ?? '';
$features = $input['features'] ?? '';

// JSON validasyonu ve sanitization
if (!empty($values)) {
    $valuesDecoded = json_decode($values, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Değerler alanı geçersiz JSON formatında']);
        exit;
    }
    $values = json_encode(array_map(function($item) {
        return SecurityUtils::sanitizeInput($item, 'html');
    }, $valuesDecoded));
} else {
    $values = null;
}

if (!empty($features)) {
    $featuresDecoded = json_decode($features, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Özellikler alanı geçersiz JSON formatında']);
        exit;
    }
    $features = json_encode(array_map(function($item) {
        return SecurityUtils::sanitizeInput($item, 'html');
    }, $featuresDecoded));
} else {
    $features = null;
}

// Validasyon
if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Başlık ve içerik alanları zorunludur']);
    exit;
}

// URL validasyonu (varsa)
if (!empty($image) && !SecurityUtils::validateInput($image, 'url')) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz resim URL\'si']);
    exit;
}

try {
    // About section güncelleme
    $stmt = $pdo->prepare("
        INSERT INTO about_section
        (title, subtitle, content, mission, vision, image, features, values, is_active, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        subtitle = VALUES(subtitle),
        content = VALUES(content),
        mission = VALUES(mission),
        vision = VALUES(vision),
        image = VALUES(image),
        features = VALUES(features),
        values = VALUES(values),
        updated_at = NOW()
    ");

    $result = $stmt->execute([
        $title,
        $subtitle,
        $content,
        $mission,
        $vision,
        $image ?: null,
        $features,
        $values
    ]);

    if ($result) {
        // Activity log
        logActivity('update', 'about_section', 1, $_SESSION['admin_id'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'Hakkımızda içeriği başarıyla kaydedildi'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı güncelleme hatası']);
    }

} catch (PDOException $e) {
    error_log('About save error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
}
?>