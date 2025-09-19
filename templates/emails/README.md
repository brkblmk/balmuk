# E-posta Şablonları

Bu klasörde Prime EMS Studios için hazırlanan e-posta şablonları bulunmaktadır.

## Kurulum

1. PHPMailer kütüphanesini yükleyin:
   ```bash
   composer require phpmailer/phpmailer
   ```

2. `config/email.php` dosyasında SMTP ayarlarınızı yapılandırın

## Mevcut Şablonlar

### 1. Randevu Onayı (appointment-confirmation.php)
- Kullanım: Yeni randevu onaylandığında gönderilir
- Değişkenler: `$customer_name`, `$appointment_date`, `$appointment_time`, `$service_name`, `$device_name`, `$trainer_name`, `$duration_minutes`

### 2. İletişim Formu Yanıtı (contact-response.php)
- Kullanım: İletişim formu gönderildiğinde otomatik yanıt
- Değişkenler: `$name`, `$subject`, `$message`

### 3. Kampanya Bildirimi (campaign-notification.php)
- Kullanım: Özel kampanya ve indirim bildirimleri
- Değişkenler: `$campaign_title`, `$campaign_subtitle`, `$campaign_description`, `$discount_text`, `$features`, `$start_date`, `$end_date`, `$button_link`, `$button_text`

## Kullanım Örneği

```php
<?php
require_once 'config/email.php';

// Randevu onayı gönderme
$template = loadEmailTemplate('appointment-confirmation', [
    'customer_name' => 'Ahmet Yılmaz',
    'appointment_date' => '2024-01-15',
    'appointment_time' => '14:00',
    'service_name' => 'Prime Slim',
    'device_name' => 'i-motion Pro',
    'trainer_name' => 'Ayşe Demir',
    'duration_minutes' => 20
]);

$result = sendEmail('customer@example.com', 'Randevu Onayı', $template);
?>
```

## Özellikler

- Responsive tasarım
- Prime EMS Studios branding
- HTML formatında profesyonel görünüm
- Kolay özelleştirilebilir değişkenler