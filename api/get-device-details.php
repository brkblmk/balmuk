<?php
require_once '../config/database.php';
require_once '../config/security.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Geçersiz cihaz ID');
    }

    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM ems_devices WHERE id = ?");
    $stmt->execute([$id]);
    $device = $stmt->fetch();

    if (!$device) {
        throw new Exception('Cihaz bulunamadı');
    }

    // Güvenli HTML render fonksiyonu
    function safeHtmlRender($content) {
        if (empty($content)) {
            return '';
        }

        // Tehlikeli HTML etiketlerini kaldır, ancak güvenli olanları koru
        $allowed_tags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><code><pre>';
        $clean_html = strip_tags($content, $allowed_tags);

        // XSS koruması için ek filtreleme
        $clean_html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $clean_html);
        $clean_html = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi', '', $clean_html);
        $clean_html = preg_replace('/on\w+="[^"]*"/i', '', $clean_html);

        return $clean_html;
    }

    // JSON verilerini decode et
    $json_fields = ['features', 'specifications', 'certifications', 'usage_areas', 'benefits', 'gallery_images'];
    foreach ($json_fields as $field) {
        if ($device[$field]) {
            $device[$field] = json_decode($device[$field], true);
        }
    }

    // HTML içeriği için güvenli render
    if (!empty($device['long_description'])) {
        $device['long_description'] = safeHtmlRender($device['long_description']);
    }

    echo json_encode([
        'success' => true,
        'device' => $device
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>