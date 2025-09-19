<?php
// Contact Form Response Email Template
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim Formu Yanıtı - Prime EMS Studios</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .message-box { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #FFD700; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 10px 20px; background: #FFD700; color: #000; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Prime EMS Studios</h1>
            <p>Mesajınız İçin Teşekkür Ederiz!</p>
        </div>

        <div class="content">
            <h2>Merhaba <?php echo htmlspecialchars($name); ?>,</h2>

            <p>İletişim formunuz aracılığıyla gönderdiğiniz mesajınız için teşekkür ederiz. Mesajınız başarıyla tarafımıza ulaşmıştır.</p>

            <div class="message-box">
                <h3>Gönderdiğiniz Mesaj:</h3>
                <p><strong>Konu:</strong> <?php echo htmlspecialchars($subject); ?></p>
                <p><strong>Mesajınız:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
                <p><strong>Gönderim Tarihi:</strong> <?php echo date('d.m.Y H:i'); ?></p>
            </div>

            <p>En kısa sürede sizinle iletişime geçeceğiz. Genellikle 24 saat içerisinde yanıt vermeye çalışıyoruz.</p>

            <p>Ayrıca aşağıdaki iletişim kanallarından da bize ulaşabilirsiniz:</p>

            <ul>
                <li><strong>Telefon:</strong> +90 232 555 66 77</li>
                <li><strong>WhatsApp:</strong> +90 532 555 66 77</li>
                <li><strong>E-posta:</strong> info@primeems.com</li>
            </ul>

            <p>Prime EMS Studios olarak, siz değerli müşterilerimize en iyi hizmeti sunmak için çalışıyoruz.</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="https://wa.me/905325556677" class="button">WhatsApp'tan Ulaşın</a>
                <a href="tel:+902325556677" class="button" style="margin-left: 10px;">Hemen Arayın</a>
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