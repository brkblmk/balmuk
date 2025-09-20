<?php
require_once 'config/database.php';

try {
    echo "Başlangıç verileri ekleniyor...\n\n";

    // Hero Section verisi
    $pdo->exec("INSERT IGNORE INTO hero_section (id, title, subtitle, description, button1_text, button1_link, button2_text, button2_link, overlay_opacity) VALUES
    (1, 'Prime EMS Studios İzmir', '20 Dakikada Hedeflerinize Ulaşın', 'Almanya\'nın önde gelen EMS teknolojisi ile güvenli ve etkili antrenman deneyimi yaşayın. Kişisel antrenörünüzle birlikte profesyonel EMS cihazlarımızla maksimum sonuç elde edin.', 'Hemen Başla', '#contact', 'Daha Fazla Bilgi', '#services', 0.5)");
    echo "✓ Hero section verisi eklendi.\n";

    // Blog Categories verisi
    $pdo->exec("INSERT IGNORE INTO blog_categories (name, slug, description, color, icon, sort_order) VALUES
    ('EMS Antrenmanı', 'ems-antrenmani', 'EMS antrenmanı hakkında bilgiler ve ipuçları', '#FFD700', 'bi-lightning-charge', 1),
    ('Sağlık ve Beslenme', 'saglik-beslenme', 'Sağlıklı yaşam ve beslenme önerileri', '#00FF88', 'bi-heart', 2),
    ('EMS Teknolojisi', 'ems-teknolojisi', 'EMS teknolojisi hakkında teknik bilgiler', '#0088FF', 'bi-cpu', 3),
    ('Başarı Hikayeleri', 'basari-hikayeleri', 'EMS ile elde edilen başarı hikayeleri', '#FF6B00', 'bi-trophy', 4)");
    echo "✓ Blog kategorileri eklendi.\n";

    // FAQs verisi
    $pdo->exec("INSERT IGNORE INTO faqs (question, answer, category, sort_order) VALUES
    ('EMS antrenmanı nedir?', 'EMS (Elektro Muskül Stimülasyonu), elektrik akımları ile kasların uyarılması prensibine dayanan bir antrenman yöntemidir. 20 dakikalık bir seans, geleneksel 2 saatlik antrenmana eş değerdedir.', 'Genel', 1),
    ('EMS antrenmanı güvenli midir?', 'Evet, Almanya\'nın önde gelen EMS teknolojisi ile üretilen cihazlarımız CE ve FDA onaylıdır. Profesyonel antrenörlerimiz eşliğinde güvenli bir şekilde uygulanmaktadır.', 'Güvenlik', 2),
    ('Kaç seans yapmalıyım?', 'İlk sonuçları görmek için haftada 2-3 seans öneriyoruz. Kişisel hedeflerinize göre programınız şekillendirilir.', 'Program', 3),
    ('EMS kimlere uygundur?', '18-65 yaş arası sağlıklı bireylere uygundur. Hamileler, kalp hastaları ve bazı kronik hastalıkları olan kişiler için doktor onayı gerekir.', 'Uygunluk', 4)");
    echo "✓ SSS verileri eklendi.\n";

    // Blog Posts verisi - önce admin id'sini alalım
    $admin_stmt = $pdo->query("SELECT id FROM admins LIMIT 1");
    $admin = $admin_stmt->fetch();
    $admin_id = $admin ? $admin['id'] : 1;

    $pdo->exec("INSERT IGNORE INTO blog_posts (title, slug, excerpt, content, category_id, author_id, reading_time, is_published, published_at) VALUES
    ('EMS Antrenmanı ile 20 Dakikada Maksimum Sonuç', 'ems-antrenmani-20-dakika-maksimum-sonuclar', 'EMS teknolojisi ile geleneksel antrenmanın ötesine geçin...', '<p>EMS antrenmanı, elektrik akımları ile kas stimulasyonu sağlayarak 20 dakikalık bir seans ile geleneksel 2 saatlik antrenman sonucunu verir.</p><p>Bu teknoloji sayesinde hem zaman kazanır hem de daha etkili sonuçlar elde edersiniz.</p>', 1, $admin_id, 5, 1, NOW()),
    ('Sağlıklı Yaşam İçin EMS', 'saglikli-yasam-ems', 'EMS antrenmanı ile sağlıklı yaşam yolculuğunuzu başlatın...', '<p>EMS antrenmanı sadece kas geliştirme değil, aynı zamanda genel sağlık için de faydalıdır.</p><p>Metabolizma hızlanması, kardiyovasküler sistemin güçlenmesi gibi faydalar sağlar.</p>', 2, $admin_id, 4, 1, NOW())");
    echo "✓ Blog yazıları eklendi.\n";

    // Services verisi
    $pdo->exec("INSERT IGNORE INTO services (name, slug, short_description, long_description, duration, goal, icon, price, session_count, is_featured, sort_order) VALUES
    ('EMS Full Body Paketi', 'ems-full-body-paketi', '20 dakikalık tam vücut EMS antrenmanı', 'Almanya teknolojisi ile üretilen EMS cihazlarımız ile 20 dakikalık bir seans ile tüm vücut kaslarınızı çalıştırın. Kişisel antrenör eşliğinde maksimum etki.', '20 dk', 'Tam vücut geliştirme', 'bi-lightning-charge', 150.00, 1, 1, 1),
    ('EMS Core Paketi', 'ems-core-paketi', 'Karın ve bel bölgesine odaklanan EMS antrenmanı', 'Karın kasları ve bel bölgesine özel odaklanan EMS seansı ile 6 pack karın elde edin.', '20 dk', 'Karın kasları', 'bi-bullseye', 120.00, 1, 1, 2),
    ('EMS Slimming Paketi', 'ems-slimming-paketi', 'Yağ yakımı odaklı EMS antrenmanı', 'EMS teknolojisi ile yağ yakımını hızlandırın ve istediğiniz forma kavuşun.', '20 dk', 'Yağ yakımı', 'bi-fire', 130.00, 1, 1, 3)");
    echo "✓ Hizmetler eklendi.\n";

    // EMS Devices verisi
    $pdo->exec("INSERT IGNORE INTO ems_devices (name, slug, device_type, model, manufacturer, short_description, long_description, main_image, capacity, price_range, is_featured, sort_order) VALUES
    ('i-Motion Pro', 'i-motion-pro', 'i-motion', 'i-Motion Pro 2024', 'Prime EMS', 'Profesyonel EMS antrenman cihazı', 'Almanya\'nın en gelişmiş EMS teknolojisi ile donatılmış profesyonel antrenman cihazı. Tam vücut kapsama ve 20 farklı antrenman programı.', 'assets/images/devices/main_1758225092_68cc62c4331e4.webp', 1, '150-200 TL', 1, 1),
    ('i-Model Premium', 'i-model-premium', 'i-model', 'i-Model Premium', 'Prime EMS', 'Premium EMS deneyim cihazı', 'Premium özelliklerle donatılmış EMS cihazı. Gelişmiş sensörler ve kişiselleştirilebilir programlar.', 'assets/images/devices/main_1758226254_68cc674e1c6f6.webp', 1, '180-250 TL', 1, 2)");
    echo "✓ EMS cihazları eklendi.\n";

    // Testimonials verisi
    $pdo->exec("INSERT IGNORE INTO testimonials (customer_name, customer_title, content, rating, service_used, result_achieved, is_featured, sort_order) VALUES
    ('Ahmet Yılmaz', 'Yazılımcı', 'EMS antrenmanı sayesinde 3 ayda 12 kilo verdim ve kas kütlem arttı. Kesinlikle tavsiye ederim!', 5, 'EMS Full Body Paketi', '12 kg kilo kaybı, kas artışı', 1, 1),
    ('Ayşe Kaya', 'Doktor', 'Profesyonel hizmet ve EMS teknolojisinin etkisi gerçekten şaşırttı. Zamanında sonuç almak isteyenlere ideal.', 5, 'EMS Core Paketi', 'Karın kaslarında belirgin gelişme', 1, 2),
    ('Mehmet Demir', 'İş Adamı', 'Yoğun iş temposu nedeniyle uzun antrenman yapamıyordum. EMS ile 20 dakikada mükemmel sonuçlar alıyorum.', 5, 'EMS Slimming Paketi', '8 kg yağ yakımı', 1, 3)");
    echo "✓ Müşteri yorumları eklendi.\n";

    // Members verisi
    $pdo->exec("INSERT IGNORE INTO members (name, email, phone, birth_date, gender, membership_date) VALUES
    ('Ali Yıldız', 'ali.yildiz@email.com', '0555 123 4567', '1990-05-15', 'male', '2024-01-15'),
    ('Fatma Şahin', 'fatma.sahin@email.com', '0555 234 5678', '1988-08-22', 'female', '2024-02-01'),
    ('Mehmet Kara', 'mehmet.kara@email.com', '0555 345 6789', '1992-12-10', 'male', '2024-02-15'),
    ('Ayşe Demir', 'ayse.demir@email.com', '0555 456 7890', '1995-03-28', 'female', '2024-03-01'),
    ('Mustafa Öz', 'mustafa.oz@email.com', '0555 567 8901', '1987-11-05', 'male', '2024-03-10')");
    echo "✓ Örnek üyeler eklendi.\n";

    // Packages verisi
    $pdo->exec("INSERT IGNORE INTO packages (name, description, duration_days, price, session_count) VALUES
    ('Temel Paket', '20 dakikalık tek seanlık EMS antrenmanı', 1, 150.00, 1),
    ('Haftalık Paket', 'Haftada 2 seans olmak üzere 4 haftalık paket', 30, 500.00, 8),
    ('Aylık Paket', 'Her gün seans hakkı ile aylık paket', 30, 1200.00, 30),
    ('Premium Paket', 'Sınırsız seans hakkı ile 3 aylık paket', 90, 3000.00, 90),
    ('VIP Paket', 'Özel antrenör eşliğinde sınırsız seans', 90, 4500.00, 90)");
    echo "✓ Örnek paketler eklendi.\n";

    // Member Payments verisi
    $pdo->exec("INSERT IGNORE INTO member_payments (member_id, package_id, amount, currency, sessions_purchased, payment_date, payment_method, reference_code, notes) VALUES
    (1, 2, 500.00, 'TRY', 8, '2024-01-15', 'card', 'REF001', 'İlk üyelik ödemesi'),
    (2, 3, 1200.00, 'TRY', 30, '2024-02-01', 'online', 'REF002', 'Aylık paket ödemesi'),
    (3, 1, 150.00, 'TRY', 1, '2024-02-15', 'cash', 'REF003', 'Tek seans ödemesi'),
    (4, 4, 3000.00, 'TRY', 90, '2024-03-01', 'bank_transfer', 'REF004', 'Premium paket ödemesi'),
    (5, 5, 4500.00, 'TRY', 90, '2024-03-10', 'card', 'REF005', 'VIP paket ödemesi'),
    (1, 3, 1200.00, 'TRY', 30, '2024-08-01', 'online', 'REF006', 'Paket yenileme'),
    (2, 2, 500.00, 'TRY', 8, '2024-08-15', 'card', 'REF007', 'Haftalık paket')");
    echo "✓ Örnek ödemeler eklendi.\n";

    echo "\n✅ Tüm başlangıç verileri başarıyla eklendi!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>