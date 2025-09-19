<?php
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'config/performance.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Generate CSRF token using security class
$csrf_token = SecurityUtils::generateCSRFToken();

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Sadece POST istekleri kabul edilir.';
    echo json_encode($response);
    exit;
}

// Form verilerini al ve temizle - SecurityUtils ile
$name = SecurityUtils::sanitizeInput($_POST['name'] ?? '', 'string');
$email = SecurityUtils::sanitizeInput($_POST['email'] ?? '', 'email');
$phone = SecurityUtils::sanitizeInput($_POST['phone'] ?? '', 'phone');
$subject = SecurityUtils::sanitizeInput($_POST['subject'] ?? '', 'string');
$message = SecurityUtils::sanitizeInput($_POST['message'] ?? '', 'html');
$csrf_token = $_POST['csrf_token'] ?? '';

// CSRF token kontrolü - SecurityUtils ile
if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
    SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP()]);
    $response['message'] = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
    echo json_encode($response);
    exit;
}

// Validation
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Ad Soyad gereklidir.';
} elseif (strlen($name) < 2) {
    $errors['name'] = 'Ad Soyad en az 2 karakter olmalıdır.';
} elseif (strlen($name) > 100) {
    $errors['name'] = 'Ad Soyad en fazla 100 karakter olabilir.';
}

if (empty($email)) {
    $errors['email'] = 'E-posta adresi gereklidir.';
} elseif (!SecurityUtils::validateInput($email, 'email')) {
    $errors['email'] = 'Geçerli bir e-posta adresi giriniz.';
} elseif (!SecurityUtils::validateInput($email, 'string', ['max_length' => 150])) {
    $errors['email'] = 'E-posta adresi çok uzun.';
}

if (!empty($phone)) {
    // Telefon numarası kontrolü - SecurityUtils ile
    if (!SecurityUtils::validateInput($phone, 'phone')) {
        $errors['phone'] = 'Geçerli bir telefon numarası giriniz.';
    }
}

if (empty($subject)) {
    $errors['subject'] = 'Konu gereklidir.';
} elseif (strlen($subject) > 200) {
    $errors['subject'] = 'Konu en fazla 200 karakter olabilir.';
}

if (empty($message)) {
    $errors['message'] = 'Mesaj gereklidir.';
} elseif (strlen($message) < 10) {
    $errors['message'] = 'Mesaj en az 10 karakter olmalıdır.';
} elseif (strlen($message) > 2000) {
    $errors['message'] = 'Mesaj en fazla 2000 karakter olabilir.';
}

// Spam kontrolü - basit honeypot ve rate limiting
if (!empty($_POST['website'])) { // Honeypot field
    $response['message'] = 'Spam algılandı.';
    echo json_encode($response);
    exit;
}

// Rate limiting - SecurityUtils ile
if (!SecurityUtils::checkRateLimit('contact_form', 3, 60)) { // 3 attempt per minute
    SecurityUtils::logSecurityEvent('RATE_LIMIT_EXCEEDED', [
        'action' => 'contact_form',
        'ip' => SecurityUtils::getClientIP()
    ]);
    $response['message'] = 'Çok sık istek gönderiyorsunuz. Lütfen 1 dakika bekleyiniz.';
    echo json_encode($response);
    exit;
}

if (!empty($errors)) {
    $response['errors'] = $errors;
    $response['message'] = 'Lütfen form hatalarını düzeltin.';
    echo json_encode($response);
    exit;
}

try {
    // Veritabanına kaydet
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $name,
        $email,
        $phone,
        $subject,
        $message,
        SecurityUtils::getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $messageId = $pdo->lastInsertId();
    
    // Log successful contact form submission
    SecurityUtils::logSecurityEvent('CONTACT_FORM_SUCCESS', [
        'message_id' => $messageId,
        'email' => $email
    ]);
    
    // E-posta gönderimi
    $emailSent = false;
    $adminEmail = getSetting('contact_email', 'info@primeems.com');
    
    if ($adminEmail && function_exists('mail')) {
        $emailSubject = "Prime EMS - Yeni İletişim Mesajı: " . $subject;
        $emailBody = "
Prime EMS web sitesinden yeni bir mesaj alındı:

Ad Soyad: {$name}
E-posta: {$email}
Telefon: {$phone}
Konu: {$subject}

Mesaj:
{$message}

---
Gönderim Bilgileri:
IP Adresi: {$client_ip}
Tarih: " . date('d.m.Y H:i:s') . "
Mesaj ID: {$messageId}
        ";
        
        $headers = [
            'From: ' . $email,
            'Reply-To: ' . $email,
            'X-Mailer: Prime EMS Contact Form',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        $emailSent = mail($adminEmail, $emailSubject, $emailBody, implode("\r\n", $headers));
    }
    
    // Otomatik yanıt e-postası (isteğe bağlı)
    if ($emailSent && getSetting('contact_auto_reply', '1') == '1') {
        $autoReplySubject = "Prime EMS - Mesajınız Alındı";
        $autoReplyBody = "
Sayın {$name},

Prime EMS'e ilginiz için teşekkür ederiz. Mesajınız tarafımıza ulaştı ve en kısa sürede size dönüş yapacağız.

Mesajınız:
Konu: {$subject}

İyi günler dileriz,
Prime EMS Ekibi

---
Bu otomatik bir mesajdır. Lütfen bu e-postayı yanıtlamayın.
        ";
        
        $autoHeaders = [
            'From: ' . $adminEmail,
            'Reply-To: ' . $adminEmail,
            'X-Mailer: Prime EMS Contact Form Auto-Reply',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        mail($email, $autoReplySubject, $autoReplyBody, implode("\r\n", $autoHeaders));
    }
    
    // Başarılı yanıt
    $response['success'] = true;
    $response['message'] = 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.';
    
    if (!$emailSent) {
        $response['message'] .= ' (Not: E-posta bildirimi gönderilemedi, ancak mesajınız kaydedildi.)';
    }
    
} catch (PDOException $e) {
    error_log("Contact form database error: " . $e->getMessage());
    $response['message'] = 'Mesajınız kaydedilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    $response['message'] = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>