<?php
// Appointment Confirmation Email Template
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Onayı - Prime EMS Studios</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .details { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #FFD700; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 10px 20px; background: #FFD700; color: #000; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Prime EMS Studios</h1>
            <p>Randevunuz Onaylandı!</p>
        </div>

        <div class="content">
            <h2>Merhaba <?php echo htmlspecialchars($customer_name); ?>,</h2>

            <p>Randevunuz başarıyla onaylandı. Aşağıda randevu detaylarınız yer almaktadır:</p>

            <div class="details">
                <h3>Randevu Detayları:</h3>
                <p><strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($appointment_date)); ?></p>
                <p><strong>Saat:</strong> <?php echo $appointment_time; ?></p>
                <p><strong>Hizmet:</strong> <?php echo htmlspecialchars($service_name); ?></p>
                <p><strong>Cihaz:</strong> <?php echo htmlspecialchars($device_name); ?></p>
                <p><strong>Eğitmen:</strong> <?php echo htmlspecialchars($trainer_name); ?></p>
                <p><strong>Süre:</strong> <?php echo $duration_minutes; ?> dakika</p>
            </div>

            <h3>Önemli Hatırlatmalar:</h3>
            <ul>
                <li>Randevu saatinden 15 dakika önce geliniz</li>
                <li>Kişisel eşyalarınızı vestiyere bırakınız</li>
                <li>EMS seansı öncesi ve sonrası bol su içiniz</li>
                <li>Randevunuzu değiştirmek isterseniz, en az 24 saat öncesinden haber veriniz</li>
            </ul>

            <p>Randevunuz için teşekkür ederiz. Sizi görmek için sabırsızlanıyoruz!</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="tel:+902325556677" class="button">Bizi Arayın</a>
                <a href="https://wa.me/905325556677" class="button" style="margin-left: 10px;">WhatsApp</a>
            </p>
        </div>

        <div class="footer">
            <p>Prime EMS Studios<br>
            Balçova Marina AVM, Kat:2, No:205<br>
            Balçova/İzmir<br>
            Tel: +90 232 555 66 77<br>
            Email: info@primeems.com</p>
        </div>
    </div>
</body>
</html>