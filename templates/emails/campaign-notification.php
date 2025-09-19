<?php
// Campaign Notification Email Template
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Özel Kampanya - Prime EMS Studios</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .campaign-box { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .discount-badge { background: #FFD700; color: #000; padding: 10px 20px; border-radius: 20px; display: inline-block; font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 15px 30px; background: #FFD700; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .features { margin: 20px 0; }
        .features ul { list-style: none; padding: 0; }
        .features li { padding: 5px 0; }
        .features li:before { content: "✓"; color: #FFD700; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Prime EMS Studios</h1>
            <p>Özel Kampanya Fırsatı!</p>
        </div>

        <div class="content">
            <h2><?php echo htmlspecialchars($campaign_title); ?></h2>

            <div class="campaign-box">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span class="discount-badge"><?php echo htmlspecialchars($discount_text); ?></span>
                </div>

                <h3><?php echo htmlspecialchars($campaign_subtitle); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($campaign_description)); ?></p>

                <div class="features">
                    <h4>Kampanya Avantajları:</h4>
                    <ul>
                        <li>20 dakikalık profesyonel EMS seansı</li>
                        <li>Kişiye özel programlama</li>
                        <li>Vücut analizi ve raporlama</li>
                        <li>Uzman eğitmen desteği</li>
                        <li>Hijyenik ve güvenli ortam</li>
                    </ul>
                </div>

                <?php if (!empty($features)): ?>
                <div class="features">
                    <h4>Özel Özellikler:</h4>
                    <ul>
                        <?php foreach ($features as $feature): ?>
                        <li><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <p><strong>Kampanya Süresi:</strong> <?php echo date('d.m.Y', strtotime($start_date)); ?> - <?php echo date('d.m.Y', strtotime($end_date)); ?></p>

            <p>Sınırlı kontenjanımız olduğu için yerinizi hemen ayırtın!</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo htmlspecialchars($button_link); ?>" class="button">
                    <?php echo htmlspecialchars($button_text); ?>
                </a>
            </div>

            <p style="text-align: center; color: #666; font-size: 14px;">
                * Kampanya koşulları hakkında detaylı bilgi için bize ulaşabilirsiniz.
            </p>
        </div>

        <div class="footer">
            <p>Prime EMS Studios<br>
            Balçova Marina AVM, Kat:2, No:205<br>
            Balçova/İzmir<br>
            Tel: +90 232 555 66 77 | WhatsApp: +90 532 555 66 77<br>
            Email: info@primeems.com</p>
        </div>
    </div>
</body>
</html>