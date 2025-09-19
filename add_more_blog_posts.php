<?php
require_once 'config/database.php';

try {
    echo "Daha fazla blog yazısı ekleniyor...\n\n";

    // Admin id'sini alalım
    $admin_stmt = $pdo->query("SELECT id FROM admins LIMIT 1");
    $admin = $admin_stmt->fetch();
    $admin_id = $admin ? $admin['id'] : 1;

    $pdo->exec("INSERT IGNORE INTO blog_posts (title, slug, excerpt, content, category_id, author_id, tags, reading_time, is_published, published_at) VALUES
    ('EMS Teknolojisi: Geleceğin Antrenman Yöntemi', 'ems-teknolojisi-gelecegin-antrenman-yontemi', 'EMS teknolojisinin bilimsel temelleri ve gelecekteki gelişmeleri...', '<p>EMS teknolojisi, elektrik stimulasyonu ile kasları etkinleştiren modern bir antrenman yöntemidir. Almanya\'dan gelen bu teknoloji, 20 dakikalık bir seans ile maksimum etki sağlar.</p><p>Bu teknoloji sayesinde hem zaman kazanır hem de daha sağlıklı bir vücuda kavuşursunuz. Bilimsel araştırmalar, EMS antrenmanının geleneksel yöntemlere göre %30 daha etkili olduğunu gösteriyor.</p>', 3, $admin_id, 'EMS teknolojisi, bilimsel antrenman, gelecek teknolojileri', 6, 1, NOW()),

    ('EMS ile Başarı Hikayesi: 3 Ayda 15 Kilo Kilo Kaybı', 'ems-basari-hikayesi-3-ayda-15-kilo-kilo-kaybi', 'EMS antrenmanı ile şaşırtıcı sonuçlara ulaşan bir müşterimizin hikayesi...', '<p>Ayşe Hanım, yoğun iş temposu nedeniyle spor salonuna gidemiyordu. EMS antrenmanı sayesinde haftada sadece 2 seans ile 3 ayda 15 kilo verdi ve vücut yağ oranını %25\'ten %18\'e düşürdü.</p><p>EMS teknolojisi sayesinde artık daha enerjik ve sağlıklı hissediyor. \'EMS olmadan bu sonuçlara ulaşmam mümkün değildi\' diyor.</p>', 4, $admin_id, 'EMS başarı hikayesi, kilo kaybı, vücut dönüşümü', 4, 1, NOW()),

    ('Sağlıklı Beslenme ve EMS Antrenmanın Birleşimi', 'saglikli-beslenme-ems-antrenman-birlesimi', 'EMS antrenmanı ile beslenme programının nasıl entegre edileceği...', '<p>EMS antrenmanı tek başına yeterli değildir. Sağlıklı beslenme ile birlikte uygulandığında maksimum etki verir.</p><p>Protein ağırlıklı beslenme, yeterli su tüketimi ve EMS seansları ile vücudunuzu ideale yakın hale getirebilirsiniz. Uzman diyetisyenlerimiz, EMS antrenmanınıza uygun beslenme programı hazırlar.</p>', 2, $admin_id, 'sağlıklı beslenme, EMS antrenman, diyet programı', 5, 1, NOW()),

    ('EMS Cihazlarının Teknik Özellikleri', 'ems-cihazlarinin-teknik-ozellikleri', 'Prime EMS cihazlarının teknik detayları ve avantajları...', '<p>Prime EMS cihazları, Almanya\'nın en gelişmiş teknolojisi ile üretilmiştir. CE ve FDA onaylı cihazlarımız, güvenli ve etkili antrenman sağlar.</p><p>20 farklı antrenman programı, kişiselleştirilebilir yoğunluk ayarları ve profesyonel destek ile maksimum sonuç elde edebilirsiniz.</p>', 3, $admin_id, 'EMS cihazları, teknik özellikler, Almanya teknolojisi', 7, 1, NOW())");

    echo "✓ 4 yeni blog yazısı eklendi.\n";

    // Mevcut blog yazılarını güncelle - etiketler ekle
    $pdo->exec("UPDATE blog_posts SET tags = 'EMS antrenman, maksimum sonuç, 20 dakika' WHERE id = 1");
    $pdo->exec("UPDATE blog_posts SET tags = 'sağlıklı yaşam, EMS faydaları, metabolizma' WHERE id = 2");

    echo "✓ Mevcut blog yazılarına etiketler eklendi.\n";

    echo "\n✅ Blog verileri başarıyla güncellendi!\n";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>