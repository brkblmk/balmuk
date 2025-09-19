<?php
// Email Configuration
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'noreply@example.com');
define('SMTP_PASSWORD', 'examplepassword');
define('FROM_EMAIL', 'info@primeems.com');
define('FROM_NAME', 'Prime EMS Studios');

// Email templates base path
define('EMAIL_TEMPLATES_PATH', __DIR__ . '/../templates/emails/');

// PHPMailer configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

// Email sending function
function sendEmail($to, $subject, $body, $isHtml = true, $attachments = []) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        // Attachments
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        // Content
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return ['success' => true, 'message' => 'E-posta başarıyla gönderildi'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "E-posta gönderilemedi: {$mail->ErrorInfo}"];
    }
}

// Template loading function
function loadEmailTemplate($templateName, $variables = []) {
    $templatePath = EMAIL_TEMPLATES_PATH . $templateName . '.php';

    if (!file_exists($templatePath)) {
        return false;
    }

    // Extract variables for template
    extract($variables);

    // Start output buffering
    ob_start();
    include $templatePath;
    $content = ob_get_clean();

    return $content;
}

// Test email sending function
function testSendEmail($to = 'test@example.com') {
    $subject = 'Test E-posta';
    $body = 'Bu bir test e-postasıdır.';
    return sendEmail($to, $subject, $body);
}
?>