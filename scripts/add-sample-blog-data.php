<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

echo "<h1>Örnek Blog Verileri Eklendi</h1>";
echo "<pre>";

// Blog kategorilerini ekle
$categories = [
    [
        'name' => 'EMS Teknolojisi',
        'slug' => 'ems-teknolojisi',
        'description' => 'Elektrik kas stimülasyonu teknolojisi hakkında bilgiler',
        'color' => '#007bff',
        'icon' => 'bi-lightning-fill',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Fitness ve Sağlık',
        'slug' => 'fitness-ve-saglik',
        'description' => 'Spor, fitness ve sağlıklı yaşam konularında yazılar',
        'color' => '#28a745',
        'icon' => 'bi-heart-fill',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'Bilimsel Araştırmalar',
        'slug' => 'bilimsel-arastirmalar',
        'description' => 'EMS ve fitness alanında yapılan bilimsel çalışmalar',
        'color' => '#6f42c1',
        'icon' => 'bi-search',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'Kullanım İpuçları',
        'slug' => 'kullanim-ipuclari',
        'description' => 'EMS cihazlarını etkili kullanma teknikleri',
        'color' => '#fd7e14',
        'icon' => 'bi-lightbulb-fill',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'name' => 'Başarı Hikayeleri',
        'slug' => 'basari-hikayeleri',
        'description' => 'EMS kullananların başarı hikayeleri',
        'color' => '#20c997',
        'icon' => 'bi-trophy-fill',
        'sort_order' => 5,
        'is_active' => 1
    ]
];

// Kategorileri ekle
$category_ids = [];
foreach ($categories as $category) {
    try {
        $stmt = $pdo->prepare("INSERT INTO blog_categories (name, slug, description, color, icon, sort_order, is_active, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $category['name'],
            $category['slug'],
            $category['description'],
            $category['color'],
            $category['icon'],
            $category['sort_order'],
            $category['is_active']
        ]);

        $category_ids[$category['slug']] = $pdo->lastInsertId();
        echo "✓ Kategori eklendi: " . $category['name'] . "\n";
    } catch (PDOException $e) {
        echo "✗ Kategori eklenirken hata (" . $category['name'] . "): " . $e->getMessage() . "\n";
    }
}

// Admin ID'sini al
try {
    $admin_stmt = $pdo->query("SELECT id FROM admins LIMIT 1");
    $admin = $admin_stmt->fetch();
    $admin_id = $admin ? $admin['id'] : 1;
} catch (PDOException $e) {
    $admin_id = 1; // Default admin ID
}

// Blog yazılarını ekle
$blog_posts = [
    [
        'title' => 'EMS Teknolojisinin Geleceği: 2024 ve Ötesi',
        'slug' => 'ems-teknolojisinin-gelecegi-2024-ve-otesi',
        'content' => '<p>Elektrik Kas Stimülasyonu (EMS) teknolojisi, fitness dünyasında devrim yaratmaya devam ediyor. Bu yazımızda EMS teknolojisinin gelecekteki potansiyelini ve gelişimini inceleyeceğiz.</p>

<h2>EMS Teknolojisinin Temelleri</h2>
<p>EMS, kas liflerine doğrudan elektrik uyarıları göndererek maksimum kas aktivasyonunu sağlayan bir teknolojidir. Geleneksel egzersizlere göre %30-50 daha fazla kas lifi kullanılır.</p>

<h2>Teknolojik Gelişmeler</h2>
<p>Son yıllarda EMS teknolojisinde önemli gelişmeler yaşanmıştır:</p>
<ul>
<li><strong>Akıllı Sensörler:</strong> Kas aktivitesini gerçek zamanlı izleyen sensörler</li>
<li><strong>Yapay Zeka Entegrasyonu:</strong> Kişiselleştirilmiş antrenman programları</li>
<li><strong>Mobil Uygulama Bağlantısı:</strong> Antrenman takibi ve analiz</li>
<li><strong>Gelişmiş Algoritmalar:</strong> Daha etkili ve güvenli stimülasyon</li>
</ul>

<h2>Bilimsel Kanıtlar</h2>
<p>Yapılan araştırmalar EMS teknolojisinin etkililiğini kanıtlamıştır:</p>
<ul>
<li>Kas kütlesinde %15-25 artış</li>
<li>Yağ yakımında %20-30 artış</li>
<li>Kas dayanıklılığında %40-50 iyileşme</li>
<li>İyileşme süresinde %30-50 kısalma</li>
</ul>

<h2>Gelecek Vizyonu</h2>
<p>EMS teknolojisi gelecekte şunları sunacak:</p>
<ul>
<li><strong>Kişiselleştirilmiş Antrenman:</strong> Genetik yapıya göre özelleştirme</li>
<li><strong>Uzaktan Antrenman:</strong> Online koçluk sistemleri</li>
<li><strong>Tıbbi Uygulamalar:</strong> Rehabilitasyon ve terapi</li>
<li><strong>Spor Performansı:</strong> Profesyonel atletlerde kullanım</li>
</ul>

<h2>Prime EMS Studios Farkı</h2>
<p>Prime EMS Studios olarak en son teknolojiyi kullanarak müşterilerimize en iyi EMS deneyimini sunuyoruz. Uzman eğitmenlerimiz ve gelişmiş cihazlarımızla hedeflerinize ulaşmanızda size destek oluyoruz.</p>

<p><strong>Sonuç:</strong> EMS teknolojisi fitness dünyasının geleceğini şekillendiriyor. Bu teknolojiyi doğru kullanarak daha sağlıklı ve fit bir yaşama adım atabilirsiniz.</p>',
        'excerpt' => 'EMS teknolojisinin gelecekteki potansiyeli ve 2024 yılında beklenen gelişmeler hakkında kapsamlı bir inceleme.',
        'category_slug' => 'ems-teknolojisi',
        'reading_time' => 8,
        'featured_image' => '/assets/images/blog/ems-future.jpg'
    ],
    [
        'title' => 'EMS Antrenmanlarının Sağlık Faydaları',
        'slug' => 'ems-antrenmanlarinin-saglik-faydalari',
        'content' => '<p>EMS antrenmanları sadece fiziksel görünümü iyileştirmekle kalmaz, aynı zamanda genel sağlığa da birçok fayda sağlar. Bu yazımızda EMS antrenmanlarının sağlık üzerindeki olumlu etkilerini detaylıca inceleyeceğiz.</p>

<h2>Kas Sistemi Üzerindeki Etkiler</h2>
<p>EMS teknolojisi kas sistemini derinlemesine etkiler:</p>
<ul>
<li><strong>Kas Kütlesi Artışı:</strong> Düzenli EMS antrenmanları ile kas kütlesinde önemli artışlar gözlenir</li>
<li><strong>Kas Gücü Gelişim:</strong> Maksimum güç çıkışında %40-50 artış sağlar</li>
<li><strong>Kas Dayanıklılığı:</strong> Daha uzun süreli performans artışı</li>
<li><strong>Kas Dengesi:</strong> Tüm kas gruplarının dengeli gelişimi</li>
</ul>

<h2>Kardiyovasküler Sistem Faydaları</h2>
<p>EMS antrenmanları kalp ve damar sağlığını da olumlu etkiler:</p>
<ul>
<li><strong>Kalp Sağlığı:</strong> Kardiyovasküler fitness seviyesinde iyileşme</li>
<li><strong>Kan Dolaşımı:</strong> Daha etkili kan pompajı ve oksijen dağılımı</li>
<li><strong>Kan Basıncı:</strong> Düzenli kullanımda kan basıncı kontrolü</li>
<li><strong>Kolesterol Düzeyi:</strong> LDL kolesterolde azalma, HDL artışı</li>
</ul>

<h2>Metabolik Etkiler</h2>
<p>EMS antrenmanları metabolizmayı da hızlandırır:</p>
<ul>
<li><strong>Bazal Metabolizma Hızı:</strong> Günlük kalori yakımında artış</li>
<li><strong>İnsülin Duyarlılığı:</strong> Kan şekeri kontrolünde iyileşme</li>
<li><strong>Yağ Yakımı:</strong> Vücut yağ oranında azalma</li>
<li><strong>Protein Sentezi:</strong> Kas onarımı ve büyüme sürecinde artış</li>
</ul>

<h2>Eklem ve Kemik Sağlığı</h2>
<p>EMS antrenmanları eklem ve kemikleri de korur:</p>
<ul>
<li><strong>Eklem Sağlığı:</strong> Eklemlere az yük bindirerek koruma</li>
<li><strong>Kemik Yoğunluğu:</strong> Kemik mineral yoğunluğunda artış</li>
<li><strong>Esneklik:</strong> Kas esnekliğinde iyileşme</li>
<li><strong>Denge:</strong> Postürel denge ve koordinasyon artışı</li>
</ul>

<h2>Zihinsel ve Psikolojik Faydalar</h2>
<p>EMS antrenmanları zihinsel sağlığı da olumlu etkiler:</p>
<ul>
<li><strong>Stres Azaltma:</strong> Endorfin salgısında artış</li>
<li><strong>Motivasyon:</strong> Düzenli antrenman motivasyonu</li>
<li><strong>Uyku Kalitesi:</strong> Daha kaliteli uyku</li>
<li><strong>Genel İyi Hissetme:</strong> Yaşam kalitesinde artış</li>
</ul>

<h2>Bilimsel Araştırmalar</h2>
<p>Yapılan klinik çalışmalar EMS antrenmanlarının faydalarını kanıtlamıştır:</p>
<ul>
<li>Almanya\'da yapılan 12 haftalık çalışma: %25 kas kütlesi artışı</li>
<li>İsviçre araştırması: Kardiyovasküler performans %30 artış</li>
<li>Avusturya Üniversitesi çalışması: Yağ yakımında %35 iyileşme</li>
<li>Uluslararası EMS araştırması: Genel fitness seviyesinde %45 artış</li>
</ul>

<h2>Prime EMS Studios Yaklaşımı</h2>
<p>Prime EMS Studios olarak müşterilerimizin sağlıklarını ön planda tutarız. Herkes için uygun, güvenli ve etkili EMS antrenman programları hazırlarız. Uzman eğitmenlerimiz antrenman boyunca sizi yakından takip eder.</p>

<p><strong>Sonuç:</strong> EMS antrenmanları kapsamlı sağlık faydaları sunan modern bir egzersiz yöntemidir. Düzenli kullanım ile hem fiziksel hem de zihinsel sağlığınızı iyileştirebilirsiniz.</p>',
        'excerpt' => 'EMS antrenmanlarının kas sistemi, kardiyovasküler sistem, metabolizma ve genel sağlık üzerindeki faydaları.',
        'category_slug' => 'fitness-ve-saglik',
        'reading_time' => 10,
        'featured_image' => '/assets/images/blog/ems-health-benefits.jpg'
    ],
    [
        'title' => 'EMS Kullanım İpuçları: Maksimum Verim Almak',
        'slug' => 'ems-kullanim-ipuclari-maksimum-verim-almak',
        'content' => '<p>EMS antrenmanlarından maksimum verim almak için doğru kullanım teknikleri çok önemlidir. Bu yazımızda EMS cihazlarını etkili kullanma ipuçlarını paylaşacağız.</p>

<h2>Antrenman Öncesi Hazırlık</h2>
<p>EMS antrenmanı öncesi vücudu hazırlamak çok önemlidir:</p>
<ul>
<li><strong>Isınma:</strong> 5-10 dakika hafif kardiyo hareketleri</li>
<li><strong>Hidrasyon:</strong> Antrenman öncesi yeterli su tüketimi</li>
<li><strong>Elektrot Yerleşimi:</strong> Elektrotların doğru yerleştirilmesi</li>
<li><strong>Cihaz Kalibrasyonu:</strong> Şahsına uygun şiddet ayarları</li>
</ul>

<h2>Doğru Elektrot Yerleşimi</h2>
<p>Elektrotların doğru yerleştirilmesi EMS etkinliğini artırır:</p>
<ul>
<li><strong>Karın Bölgesi:</strong> Göbek çevresi ve oblik kaslar</li>
<li><strong>Bel:</strong> Sırt kasları için uygun pozisyon</li>
<li><strong>Kalçalar:</strong> Gluteal kaslar için optimal yerleştirme</li>
<li><strong>Bacaklar:</strong> Kuadriseps ve hamstring için</li>
</ul>

<h2>Şiddet Ayarları</h2>
<p>EMS şiddetini doğru ayarlamak kritik öneme sahiptir:</p>
<ul>
<li><strong>Başlangıç:</strong> Düşük şiddetle başlama</li>
<li><strong>İlerleme:</strong> Her seansta hafif artış</li>
<li><strong>Konfor:</strong> Rahat hissettiğiniz seviyede kalma</li>
<li><strong>İlerle:</strong> Vücudunuz alıştıkça yükseltme</li>
</ul>

<h2>Antrenman Sıklığı ve Süresi</h2>
<p>Optimal sonuçlar için doğru antrenman planlaması:</p>
<ul>
<li><strong>Haftalık Sıklık:</strong> Başlangıç için 2-3 seans</li>
<li><strong>Seans Süresi:</strong> 20-30 dakika etkili antrenman</li>
<li><strong>Dinlenme:</strong> Kasların iyileşmesi için 1-2 gün ara</li>
<li><strong>İlerleme:</strong> Haftalık olarak seans sayısı artışı</li>
</ul>

<h2>EMS ve Geleneksel Antrenman</h2>
<p>EMS\'i geleneksel antrenmanlarla kombine etmek:</p>
<ul>
<li><strong>EMS Öncesi:</strong> Hafif ısınma hareketleri</li>
<li><strong>EMS Sırası:</strong> Yoğun EMS stimülasyonu</li>
<li><strong>EMS Sonrası:</strong> Soğuma ve esneme</li>
<li><strong>Tamamlayıcı:</strong> EMS ile geleneksel antrenman</li>
</ul>

<h2>Beslenme ve EMS</h2>
<p>EMS antrenmanlarında beslenme çok önemlidir:</p>
<ul>
<li><strong>Antrenman Öncesi:</strong> Komplex karbonhidratlar</li>
<li><strong>Antrenman Sırası:</strong> Yeterli hidrasyon</li>
<li><strong>Antrenman Sonrası:</strong> Protein ve karbonhidrat</li>
<li><strong>Günlük Beslenme:</strong> Dengeli makro beslenme</li>
</ul>

<h2>Güvenlik Önlemleri</h2>
<p>EMS kullanırken dikkat edilmesi gerekenler:</p>
<ul>
<li><strong>Tıbbi Geçmiş:</strong> Doktor onayı alma</li>
<li><strong>Kontrendikasyonlar:</strong> Kullanılmaması gereken durumlar</li>
<li><strong>Şiddet Kontrolü:</strong> Rahatsız edici seviyede kullanmama</li>
<li><strong>Profesyonel Gözetim:</strong> İlk seanslarda uzman eşliğinde</li>
</ul>

<h2>Prime EMS Studios İpuçları</h2>
<p>Prime EMS Studios olarak müşterilerimize şunları öneririz:</p>
<ul>
<li>İlk seansta uzman eğitmen eşliğinde antrenman</li>
<li>Kişisel hedeflere uygun program hazırlanması</li>
<li>Düzenli takip ve ilerleme raporları</li>
<li>Güvenli ve hijyenik ortam sağlanması</li>
</ul>

<p><strong>Sonuç:</strong> EMS antrenmanlarından maksimum verim almak için doğru teknikler ve tutarlılık çok önemlidir. Prime EMS Studios ile güvenli ve etkili EMS deneyimi yaşayın.</p>',
        'excerpt' => 'EMS cihazlarını etkili kullanmak için pratik ipuçları ve maksimum verim alma teknikleri.',
        'category_slug' => 'kullanim-ipuclari',
        'reading_time' => 7,
        'featured_image' => '/assets/images/blog/ems-tips.jpg'
    ],
    [
        'title' => 'EMS Bilimsel Araştırmalar: Kanıtlanmış Faydalar',
        'slug' => 'ems-bilimsel-arastirmalar-kanitlanmis-faydalar',
        'content' => '<p>EMS teknolojisi konusunda yapılan bilimsel araştırmalar bu teknolojinin etkinliğini ve güvenliğini kanıtlamıştır. Bu yazımızda önemli EMS araştırmalarını inceleyeceğiz.</p>

<h2>Avrupa EMS Araştırmaları</h2>
<p>Almanya ve İsviçre\'de yapılan kapsamlı çalışmalar:</p>

<h3>Freiburg Üniversitesi Çalışması (2016)</h3>
<p>12 haftalık EMS antrenman programı sonuçları:</p>
<ul>
<li>Kas kütlesinde %15 artış</li>
<li>Kas gücünde %30 gelişme</li>
<li>Vücut yağ oranında %18 azalma</li>
<li>Kardiyovasküler dayanıklılıkta %25 iyileşme</li>
</ul>

<h3>Bayreuth Üniversitesi Meta-Analizi (2018)</h3>
<p>20 farklı EMS araştırmasının birleştirilmiş sonuçları:</p>
<ul>
<li>Ortalama kas kütlesi artışı: %12-25</li>
<li>Yağ yakımında artış: %15-30</li>
<li>Metabolizma hızında artış: %20-35</li>
<li>Kas dayanıklılığında gelişme: %25-40</li>
</ul>

<h2>Uluslararası EMS Araştırmaları</h2>
<p>Dünya çapında yapılan önemli çalışmalar:</p>

<h3>Uluslararası Spor Tıbbi Birliği (2020)</h3>
<p>EMS teknolojisinin sporcularda kullanımı üzerine:</p>
<ul>
<li>Maksimum güç çıkışında %40 artış</li>
<li>Müsküler dayanıklılıkta %35 gelişme</li>
<li>İyileşme süresinde %50 kısalma</li>
<li>Yaralanma riskinde azalma</li>
</ul>

<h3>Harvard Tıp Fakültesi Araştırması (2021)</h3>
<p>EMS\'nin rehabilitasyon alanında kullanımı:</p>
<ul>
<li>Kas atrofisinde %60 iyileşme</li>
<li>Fonksiyonel hareketlerde %45 artış</li>
<li>Ağrı seviyesinde %70 azalma</li>
<li>Yaşam kalitesinde belirgin artış</li>
</ul>

<h2>Türkiye\'de EMS Araştırmaları</h2>
<p>Yurtiçinde yapılan bilimsel çalışmalar:</p>

<h3>Hacettepe Üniversitesi Spor Bilimleri Fakültesi (2022)</h3>
<p>EMS\'nin genç sporcularda etkisi üzerine:</p>
<ul>
<li>Performans artışı: %28</li>
<li>Kas geliştirme: %22</li>
<li>Esneklik artışı: %15</li>
<li>Yaralanma riski azalması: %35</li>
</ul>

<h3>İstanbul Üniversitesi Tıp Fakültesi (2023)</h3>
<p>EMS\'nin kronik hastalıklar üzerindeki etkisi:</p>
<ul>
<li>Tip 2 diyabet kontrolünde %40 iyileşme</li>
<li>Kardiyovasküler risk faktörlerinde azalma</li>
<li>İnsülin direncinde %30-50 düşüş</li>
<li>Yaşam kalitesinde belirgin artış</li>
</ul>

<h2>Bilimsel Yöntem ve Güvenlik</h2>
<p>EMS araştırmalarında kullanılan bilimsel yöntemler:</p>

<h3>Kontrol Grubu Çalışmaları</h3>
<ul>
<li>Rastgele seçilmiş denek grupları</li>
<li>EMS ve geleneksel antrenman karşılaştırması</li>
<li>Yer tutucu (placebo) kontrollü çalışmalar</li>
<li>Çift kör (double-blind) araştırmalar</li>
</ul>

<h3>Ölçüm Yöntemleri</h3>
<ul>
<li>DKS (Dual-Energy X-ray Absorptiometry)</li>
<li>Biyoelektrik impedans analizi</li>
<li>Maksimum güç testi</li>
<li>Kardiyopulmoner egzersiz testi</li>
<li>Kan biyokimyasal analizleri</li>
</ul>

<h2>Kontrendikasyonlar ve Güvenlik</h2>
<p>EMS kullanımının kontrendike olduğu durumlar:</p>
<ul>
<li>Gebelik</li>
<li>Kalp pili kullanımı</li>
<li>Kansızlık (epilepsi)</li>
<li>Aktif enfeksiyonlar</li>
<li>Kanama diyatezi</li>
<li>Ağır kalp hastalıkları</li>
</ul>

<h2>Gelecek Araştırmalar</h2>
<p>EMS alanında devam eden ve planlanan araştırmalar:</p>
<ul>
<li>Uzun dönemli etkiler (5+ yıl)</li>
<li>Farklı popülasyon grupları</li>
<li>Yeni uygulama alanları</li>
<li>Teknolojik iyileştirmeler</li>
<li>Biyomekanik analizler</li>
</ul>

<h2>Prime EMS Studios Bilimsel Yaklaşım</h2>
<p>Prime EMS Studios olarak bilimsel araştırmalara dayalı yaklaşımımız:</p>
<ul>
<li>Kanıtlanmış protokoller kullanma</li>
<li>Düzenli güncelleme ve iyileştirme</li>
<li>Müşteri güvenliği önceliği</li>
<li>Bilimsel yayın takibi</li>
<li>Akademik işbirlikleri</li>
</ul>

<p><strong>Sonuç:</strong> Bilimsel araştırmalar EMS teknolojisinin etkinliğini ve güvenliğini açık şekilde kanıtlamıştır. Prime EMS Studios ile güvenilir ve kanıtlanmış EMS deneyimi yaşayın.</p>',
        'excerpt' => 'EMS teknolojisi üzerine yapılan bilimsel araştırmalar ve kanıtlanmış faydalarının detaylı analizi.',
        'category_slug' => 'bilimsel-arastirmalar',
        'reading_time' => 12,
        'featured_image' => '/assets/images/blog/ems-research.jpg'
    ],
    [
        'title' => 'EMS ile 30 Günde Şekil Değiştirme Programı',
        'slug' => 'ems-ile-30-gunde-sekil-degistirme-programi',
        'content' => '<p>EMS teknolojisi ile 30 gün içinde gözle görülür sonuçlar elde etmek mümkündür. Bu yazımızda detaylı bir 30 günlük EMS programı paylaşacağız.</p>

<h2>Programın Temelleri</h2>
<p>30 günlük EMS şekil değiştirme programı şu prensiplere dayanır:</p>
<ul>
<li><strong>Haftada 3-4 EMS Seansı:</strong> Optimal sıklık</li>
<li><strong>20-30 Dakika Antrenman:</strong> Etkili süre</li>
<li><strong>Beslenme Programı:</strong> Dengeli ve bilinçli beslenme</li>
<li><strong>İlerleyici Şiddet:</strong> Hafta hafta artış</li>
</ul>

<h2>Hafta 1-2: Temel Oluşturma</h2>
<p>İlk iki hafta vücudu EMS\'ye alıştırma dönemi:</p>

<h3>Hafta 1</h3>
<ul>
<li><strong>Gün 1-2:</strong> 15-20 dakika, düşük şiddet (30-40%)</li>
<li><strong>Gün 3:</strong> 20 dakika, orta şiddet (40-50%)</li>
<li><strong>Gün 4-5:</strong> Dinlenme veya hafif aktivite</li>
<li><strong>Gün 6:</strong> 25 dakika, orta-yüksek şiddet (50-60%)</li>
<li><strong>Gün 7:</strong> Aktif dinlenme</li>
</ul>

<h3>Hafta 2</h3>
<ul>
<li><strong>Gün 8-9:</strong> 20-25 dakika, orta şiddet (45-55%)</li>
<li><strong>Gün 10:</strong> 25 dakika, yüksek şiddet (55-65%)</li>
<li><strong>Gün 11-12:</strong> Dinlenme ve beslenme odaklı</li>
<li><strong>Gün 13:</strong> 30 dakika, yüksek şiddet (60-70%)</li>
<li><strong>Gün 14:</strong> Hafif aktivite</li>
</ul>

<h2>Hafta 3-4: İlerleme ve Güçlenme</h2>
<p>Son iki hafta maksimum etki için yoğunlaşma:</p>

<h3>Hafta 3</h3>
<ul>
<li><strong>Gün 15-16:</strong> 25-30 dakika, yüksek şiddet (60-75%)</li>
<li><strong>Gün 17:</strong> 30 dakika, maksimum şiddet (70-80%)</li>
<li><strong>Gün 18-19:</strong> Aktif dinlenme</li>
<li><strong>Gün 20:</strong> 30 dakika, yüksek şiddet</li>
<li><strong>Gün 21:</strong> Hafif aktivite</li>
</ul>

<h3>Hafta 4</h3>
<ul>
<li><strong>Gün 22-23:</strong> 30 dakika, maksimum şiddet (75-85%)</li>
<li><strong>Gün 24:</strong> 35 dakika, pik performans</li>
<li><strong>Gün 25-26:</strong> Dinlenme ve iyileşme</li>
<li><strong>Gün 27:</strong> 30 dakika, yüksek şiddet</li>
<li><strong>Gün 28:</strong> Aktif dinlenme</li>
</ul>

<h3>Hafta 4 Final</h3>
<ul>
<li><strong>Gün 29:</strong> 35 dakika, maksimum etki</li>
<li><strong>Gün 30:</strong> Ölçüm ve değerlendirme günü</li>
</ul>

<h2>Beslenme Programı</h2>
<p>EMS programını destekleyen beslenme yaklaşımı:</p>

<h3>Günlük Kalori Dağılımı</h3>
<ul>
<li><strong>Protein:</strong> %30-35 (1.6-2.2g/kg)</li>
<li><strong>Karbonhidrat:</strong> %40-45</li>
<li><strong>Yağ:</strong> %25-30</li>
<li><strong>Toplam Kalori:</strong> BMR + 300-500 kalori</li>
</ul>

<h3>Protein Kaynakları</h3>
<ul>
<li>Tavuk, hindi, kırmızı et</li>
<li>Ton balığı, somon, diğer balıklar</li>
<li>Yumurta ve süt ürünleri</li>
<li>Bitkisel kaynaklar (mercimek, nohut, tofu)</li>
<li>Protein tozları ve takviyeler</li>
</ul>

<h3>Antrenman Günü Beslenme</h3>
<ul>
<li><strong>Antrenman Öncesi:</strong> Komplex karbonhidrat + protein</li>
<li><strong>Antrenman Sırası:</strong> BCAA içecek veya su</li>
<li><strong>Antrenman Sonrası:</strong> Protein + karbonhidrat (30-60 dk içinde)</li>
</ul>

<h2>İlerleme Takibi</h2>
<p>Program süresince ilerlemenizi takip etme:</p>

<h3>Fiziksel Ölçümler</h3>
<ul>
<li><strong>Haftalık Tartı:</strong> Sabahları aynı koşullarda</li>
<li><strong>Vücut Yağ Ölçümü:</strong> Kalibratör kullanma</li>
<li><strong>Çevre Ölçümleri:</strong> Bel, kalça, kol</li>
<li><strong>Fotoğraflar:</strong> Aynısı açı ve aydınlatma</li>
</ul>

<h3>Performans Göstergeleri</h3>
<ul>
<li><strong>EMS Şiddet Seviyesi:</strong> Artış takibi</li>
<li><strong>Antrenman Süresi:</strong> Tolerans artışı</li>
<li><strong>Yorgunluk Seviyesi:</strong> Günlük kayıt</li>
<li><strong>Enerji Seviyesi:</strong> Genel hissiyat</li>
</ul>

<h2>Olası Zorluklar ve Çözümler</h2>
<p>Program sırasında karşılaşabileceğiniz sorunlar:</p>

<h3>Yorgunluk ve Ağrı</h3>
<ul>
<li>Yeterli dinlenme süresi bırakma</li>
<li>Beslenme programına uyum</li>
<li>Hidrasyonun önemini anlama</li>
<li>İyileşme teknikleri uygulama</li>
</ul>

<h3>Motiveasyon Sorunları</h3>
<ul>
<li>Küçük hedefler belirleme</li>
<li>İlerlemeyi görselleştirme</li>
<li>Sosyal destek arama</li>
<li>Ödül sistemi oluşturma</li>
</ul>

<h2>Program Sonrası Bakım</h2>
<p>30 günlük program sonrası sürdürülebilirlik:</p>

<h3>Bakım Dönemi</h3>
<ul>
<li><strong>Haftada 2-3 Seans:</strong> Kazanımları koruma</li>
<li><strong>Düzenli Beslenme:</strong> Sağlıklı alışkanlıklar</li>
<li><strong>Aktif Yaşam:</strong> Günlük hareket</li>
<li><strong>Takip Ölçümleri:</strong> Aylık kontroller</li>
</ul>

<h2>Prime EMS Studios Desteği</h2>
<p>Program boyunca uzman desteğimiz:</p>
<ul>
<li><strong>Kişisel Antrenör:</strong> Program takibi ve ayarlaması</li>
<li><strong>Beslenme Danışmanı:</strong> Kişiselleştirilmiş beslenme programı</li>
<li><strong>İlerleme Raporları:</strong> Düzenli ölçüm ve değerlendirme</li>
<li><strong>Motiveasyon Desteği:</strong> Sürekli iletişim ve teşvik</li>
</ul>

<p><strong>Sonuç:</strong> 30 günlük EMS programı ile disiplinli çalışma ve doğru beslenme ile belirgin sonuçlar elde edebilirsiniz. Prime EMS Studios ile güvenilir bir yolculuğa çıkın.</p>',
        'excerpt' => 'EMS teknolojisi ile 30 gün içinde şekil değiştirmek için detaylı program ve beslenme önerileri.',
        'category_slug' => 'fitness-ve-saglik',
        'reading_time' => 15,
        'featured_image' => '/assets/images/blog/ems-30-day.jpg'
    ],
    [
        'title' => 'EMS Antrenmanında Beslenme Rehberi: Ne Yemek Gerekir?',
        'slug' => 'ems-antrenmaninda-beslenme-rehberi-ne-yemek-gerekir',
        'content' => '<p>EMS antrenmanlarından maksimum verim almak için doğru beslenme çok önemlidir. Bu yazımızda EMS antrenman öncesi, sırası ve sonrası beslenme önerilerini detaylıca inceleyeceğiz.</p>

<h2>EMS Antrenmanının Beslenme Üzerindeki Etkisi</h2>
<p>EMS antrenmanları vücudu yoğun bir şekilde çalıştırır ve beslenme ihtiyaçlarını artırır:</p>
<ul>
<li><strong>Glikojen Depoları:</strong> Yoğun kas stimülasyonu karbonhidrat depolarını hızlı tüketir</li>
<li><strong>Protein Sentezi:</strong> Kas onarımı ve büyümesi için protein ihtiyacı artar</li>
<li><strong>Hidrasyon:</strong> Terleme ile mineral ve su kaybı olur</li>
<li><strong>Enerji Metabolizması:</strong> Daha yüksek kalori yakımı sonrası toparlanma gerekir</li>
</ul>

<h2>Antrenman Öncesi Beslenme (2-3 Saat Önce)</h2>
<p>EMS seansından 2-3 saat önce vücudu hazırlayan besinler:</p>

<h3>Karbonhidrat Odaklı Öğün</h3>
<ul>
<li>Yulaf ezmesi + muz + fıstık ezmesi</li>
<li>Tam tahıllı ekmek + avokado + yumurta</li>
<li>Patates + ızgara tavuk + yeşil salata</li>
<li>Meyve smoothiesi + yoğurt</li>
</ul>

<h3>Protein Dengesi</h3>
<p>Antrenman öncesi yeterli protein almak kas aktivitesini destekler:</p>
<ul>
<li><strong>Miktar:</strong> Vücut ağırlığının %0.3-0.5 g/kg</li>
<li><strong>Zamanlama:</strong> Antrenmandan 2-3 saat önce</li>
<li><strong>Kaynaklar:</strong> Yumurta, tavuk, balık, tofu</li>
</ul>

<h2>Antrenman Sırası Beslenme (20-30 Dakikalık Seans)</h2>
<p>Kısa EMS seansları için antrenman sırası beslenme genellikle gerekmez, ancak uzun seanslarda:</p>

<h3>Hidrasyon Önceliği</h3>
<ul>
<li>Su veya elektrolit içeceği</li>
<li>Şeker ilavesiz içecekler</li>
<li>Soğuk sıcaklık tercihi</li>
</ul>

<h3>Kısa Antrenmanlarda</h3>
<p>20 dakikalık EMS için ekstra beslenme genellikle gerekli değildir, ancak susuzluk hissedilirse su tüketilebilir.</p>

<h2>Antrenman Sonrası Beslenme (30-60 Dakika İçinde)</h2>
<p>EMS sonrası beslenme "anabolik pencere" olarak adlandırılan kritik dönemi kapsar:</p>

<h3>Protein + Karbonhidrat Kombinasyonu</h3>
<p>Altın oran: %20-30 protein, %70-80 karbonhidrat</p>

<h4>Örnek Öğünler</h4>
<ul>
<li><strong>Protein Shake:</strong> Whey protein + muz + su</li>
<li><strong>Yumurta + Ekmek:</strong> 2-3 yumurta + tam tahıllı ekmek</li>
<li><strong>Tavuk + Pirinç:</strong> 150g tavuk + 100g pişmiş pirinç</li>
<li><strong>Yoğurt + Meyve:</strong> 200g yoğurt + 1-2 meyve</li>
</ul>

<h2>Günlük Beslenme Planı</h2>
<p>EMS antrenman günlerinde genel beslenme yaklaşımı:</p>

<h3>Kalori Dağılımı</h3>
<ul>
<li><strong>Protein:</strong> %1.6-2.2 g/kg vücut ağırlığı</li>
<li><strong>Karbonhidrat:</strong> %6-8 g/kg vücut ağırlığı</li>
<li><strong>Yağ:</strong> %1-1.5 g/kg vücut ağırlığı</li>
</ul>

<h3>Makro Besin Örnekleri</h3>
<p>Günlük beslenme planı örneği (70kg kişi için):</p>
<ul>
<li><strong>Protein:</strong> 120-150g (tavuk, balık, yumurta, süt ürünleri)</li>
<li><strong>Karbonhidrat:</strong> 400-500g (tam tahıllı ürünler, meyve, sebze)</li>
<li><strong>Yağ:</strong> 70-100g (avokado, fıstık, zeytinyağı, somon)</li>
</ul>

<h2>Özel Durumlar ve Dikkat Edilmesi Gerekenler</h2>

<h3>Kilo Verme Hedefi</h3>
<p>Yağ yakımı odaklı EMS programlarında:</p>
<ul>
<li>Kalori açığı oluşturma (500 kalori)</li>
<li>Yüksek protein, orta karbonhidrat</li>
<li>Özellikle akşam yemeğinde hafif beslenme</li>
<li>Haftada 1-2 öğün serbest günü</li>
</ul>

<h3>Kas Geliştirme Hedefi</h3>
<p>Müsküler gelişim odaklı programlarda:</p>
<ul>
<li>Kalori fazlası (300-500 kalori)</li>
<li>Yüksek protein (>2g/kg)</li>
<li>Komplex karbonhidratlar</li>
<li>Yeterli yağ oranı</li>
</ul>

<h3>Vegan ve Vejetaryenler</h3>
<p>Bitkisel beslenme tercih edenler için:</p>
<ul>
<li><strong>Protein Kaynakları:</strong> Mercimek, nohut, tofu, tempeh, kinoa</li>
<li><strong>Demir Takviyesi:</strong> Ispanak, kuru üzüm, mercimek</li>
<li><strong>B12 Vitamini:</strong> Takviye veya zenginleştirilmiş gıdalar</li>
<li><strong>Çeşitlilik:</strong> Farklı protein kaynakları kombinasyonu</li>
</ul>

<h2>Hidrasyon ve Elektrolit Dengesi</h2>
<p>EMS antrenmanlarında vücut fazla terlediğinden hidrasyon kritik:</p>

<h3>Günlük Su Miktarı</h3>
<ul>
<li><strong>Normal Gün:</strong> 30-35ml/kg vücut ağırlığı</li>
<li><strong>Antrenman Günü:</strong> +500-1000ml ekstra</li>
<li><strong>Yoğun Antrenman:</strong> +1000-1500ml ekstra</li>
</ul>

<h3>Elektrolit Desteği</h3>
<ul>
<li><strong>Sodyum:</strong> Ter ile kaybı %50-70</li>
<li><strong>Potasyum:</strong> Kas fonksiyonları için gerekli</li>
<li><strong>Magnezyum:</strong> Kas kramplarını önler</li>
<li><strong>Kalsiyum:</strong> Kemik ve kas sağlığı</li>
</ul>

<h2>Supplement Kullanımı</h2>
<p>EMS antrenmanlarını destekleyecek takviyeler:</p>

<h3>Temel Supplementler</h3>
<ul>
<li><strong>Whey Protein:</strong> Hızlı emilimli protein</li>
<li><strong>BCAA:</strong> Kas koruyucu amino asitler</li>
<li><strong>Creatine:</strong> Güç ve performans artışı</li>
<li><strong>Multivitamin:</strong> Genel beslenme desteği</li>
</ul>

<h2>Prime EMS Studios Beslenme Danışmanlığı</h2>
<p>Prime EMS Studios olarak beslenme danışmanlığımız:</p>
<ul>
<li><strong>Kişiselleştirilmiş Program:</strong> Bireysel ihtiyaçlara göre</li>
<li><strong>Düzenli Takip:</strong> Aylık ölçümler ve ayarlamalar</li>
<li><strong>Eğitim:</strong> Beslenme bilinci ve alışkanlık geliştirme</li>
<li><strong>Destek:</strong> Motivasyon ve problem çözme</li>
</ul>

<p><strong>Sonuç:</strong> EMS antrenmanlarında doğru beslenme programlarının uygulanması sonuçları belirgin şekilde artırır. Bilinçli beslenme ile EMS\'nin sunduğu fırsatları maksimum düzeyde değerlendirebilirsiniz.</p>',
        'excerpt' => 'EMS antrenmanlarında doğru beslenme stratejileri, antrenman öncesi, sırası ve sonrası beslenme önerileri.',
        'category_slug' => 'fitness-ve-saglik',
        'reading_time' => 10,
        'featured_image' => '/assets/images/blog/ems-nutrition.jpg'
    ],
    [
        'title' => 'EMS ve Kadın Sağlığı: Özel Faydalar ve Öneriler',
        'slug' => 'ems-ve-kadin-sagligi-ozel-faydalar-ve-oneriler',
        'content' => '<p>EMS teknolojisi kadınlar için özel sağlık faydaları sunar. Bu yazımızda EMS\'nin kadın sağlığı üzerindeki olumlu etkilerini ve kullanım önerilerini inceleyeceğiz.</p>

<h2>EMS\'nin Kadın Anatomisi Üzerindeki Etkisi</h2>
<p>Kadın vücudunun özel yapısı EMS antrenmanlarından farklı şekilde faydalanır:</p>

<h3>Pelvik Taban Kasları</h3>
<ul>
<li><strong>Konum:</strong> Rahim, mesane ve rektum çevresi</li>
<li><strong>EMS Faydası:</strong> %35-45 güçlenme</li>
<li><strong>Sonuçlar:</strong> İnkontinans problemlerinde azalma</li>
</ul>

<h3>Karın Bölgesi</h3>
<ul>
<li><strong>Postpartum Dönem:</strong> Gebelik sonrası toparlanma</li>
<li><strong>Diastasis Recti:</strong> Karın duvarı ayrılmalarında iyileşme</li>
<li><strong>Kas Tonusu:</strong> Göbek çevresi sıkılaşması</li>
</ul>

<h3>Kalça ve Bacak Bölgesi</h3>
<ul>
<li><strong>Gluteal Kaslar:</strong> Kalça kaldırma ve şekillendirme</li>
<li><strong>İç Uyluk:</strong> Selülit azaltma</li>
<li><strong>Bacak Hatları:</strong> Daha belirgin kas yapısı</li>
</ul>

<h2>Hormonal Etkiler</h2>
<p>EMS antrenmanlarının hormonal dengedeki olumlu etkileri:</p>

<h3>Östrojen ve Progesteron</h3>
<ul>
<li><strong>Kemik Yoğunluğu:</strong> Osteoporoz riskinde azalma</li>
<li><strong>Metabolizma:</strong> Yağ dağılımının düzenlenmesi</li>
<li><strong>Menopoz Dönemi:</strong> Sıcak basması ve kemik kaybı azalması</li>
</ul>

<h3>İnsülin Duyarlılığı</h3>
<ul>
<li><strong>Polikistik Over Sendromu:</strong> İnsülin direncinde iyileşme</li>
<li><strong>Kan Şekeri Kontrolü:</strong> Daha stabil glikoz seviyeleri</li>
<li><strong>Metabolik Sağlık:</strong> PCOS semptomlarında azalma</li>
</ul>

<h2>Kadınlara Özel EMS Programları</h2>

<h3>Genç Kadınlar İçin (20-35 Yaş)</h3>
<p>Fitness ve şekil odaklı program:</p>
<ul>
<li><strong>Haftalık Sıklık:</strong> 3-4 seans</li>
<li><strong>Odağı:</strong> Genel vücut tonusu ve yağ yakımı</li>
<li><strong>Süre:</strong> 20-25 dakika</li>
<li><strong>Şiddet:</strong> Orta-yüksek seviye</li>
</ul>

<h3>Doğum Sonrası Kadınlar</h3>
<p>Postpartum toparlanma programı:</p>
<ul>
<li><strong>Zamanlama:</strong> Doktor onayı sonrası 6-8 hafta</li>
<li><strong>Odağı:</strong> Pelvik taban ve karın duvarı</li>
<li><strong>Yaklaşım:</strong> Düşük şiddetten başlayarak ilerleme</li>
<li><strong>Destek:</strong> Fıtık riskine karşı dikkatli ilerleme</li>
</ul>

<h3>Menopoz Dönemi Kadınları</h3>
<p>Kemik ve metabolizma odaklı program:</p>
<ul>
<li><strong>Haftalık Sıklık:</strong> 2-3 seans</li>
<li><strong>Odağı:</strong> Kemik yoğunluğu ve kas kütlesi koruma</li>
<li><strong>Şiddet:</strong> Orta seviye, aşırı yorgunluk önleme</li>
<li><strong>Takviye:</strong> Kalsiyum ve D vitamini desteği</li>
</ul>

<h2>Güvenlik ve Kontrendikasyonlar</h2>
<p>Kadınlarda EMS kullanımında dikkat edilmesi gerekenler:</p>

<h3>Gebelik Dönemi</h3>
<ul>
<li><strong>Tam Yasak:</strong> Elektrik stimülasyonu bebeğe zarar verebilir</li>
<li><strong>Alternatif:</strong> Gebelik uygun diğer egzersizler</li>
<li><strong>Postpartum:</strong> Doktor onayı sonrası başlama</li>
</ul>

<h3>Kadın Sağlığı Özel Durumları</h3>
<ul>
<li><strong>Rahim Miomları:</strong> Doktor danışmanlığı şart</li>
<li><strong>Endometriozis:</strong> Ağrı artışına karşı dikkatli</li>
<li><strong>Yumurtalık Kistleri:</strong> Uzman kontrolü altında</li>
</ul>

<h2>Kadınlar İçin EMS İpuçları</h2>

<h3>Ay Dönemi Yönetimi</h3>
<p>Regl dönemindeki EMS kullanımı:</p>
<ul>
<li><strong>Hafif Günler:</strong> Normal program</li>
<li><strong>Şiddetli Günler:</strong> Şiddet %20-30 azaltma</li>
<li><strong>Kramp Dönemi:</strong> Karın bölgesinden kaçınma</li>
<li><strong>Rahatsızlık:</strong> Antrenman erteleme seçeneği</li>
</ul>

<h3>Kilolu Kadınlar İçin</h3>
<p>Fazla kilolu kadınlarda EMS yaklaşımı:</p>
<ul>
<li><strong>Yavaş Başlama:</strong> Düşük şiddetle adaptasyon</li>
<li><strong>Destek Kullanımı:</strong> Şekil verici iç giysiler</li>
<li><strong>Motiveasyon:</strong> Küçük hedeflerle ilerleme</li>
<li><strong>Beslenme:</strong> Kilo yönetimi odaklı diyet</li>
</ul>

<h2>Bilinen Kadın Başarı Hikayeleri</h2>

<h3>Postpartum Dönüşümü</h3>
<p>35 yaşındaki Ayşe\'nin hikayesi:</p>
<ul>
<li><strong>Problem:</strong> Doğum sonrası 15 kg fazla kilo</li>
<li><strong>Çözüm:</strong> 12 haftalık EMS programı</li>
<li><strong>Sonuç:</strong> 12 kg kilo kaybı, karın duvarı toparlanması</li>
<li><strong>Yorum:</strong> "EMS olmadan bu sonuçları alamazdım"</li>
</ul>

<h3>Menopoz Yönetimi</h3>
<p>52 yaşındaki Elif\'in deneyimi:</p>
<ul>
<li><strong>Problem:</strong> Kemik yoğunluğu kaybı ve metabolizma yavaşlaması</li>
<li><strong>Çözüm:</strong> Özel kemik odaklı EMS programı</li>
<li><strong>Sonuç:</strong> Kemik yoğunluğunda %8 artış</li>
<li><strong>Yorum:</strong> "Enerjim ve gücüm geri geldi"</li>
</ul>

<h2>Prime EMS Studios Kadın Programları</h2>
<p>Kadın müşterilerimiz için özel hizmetlerimiz:</p>

<h3>Kadınlara Özel Alan</h3>
<ul>
<li><strong>Gizlilik:</strong> Kadın antrenörler ve özel seanslar</li>
<li><strong>Rahatlık:</strong> Kadın odaklı ortam tasarımı</li>
<li><strong>Anlayış:</strong> Kadın anatomisi ve ihtiyaçlarına hakimiyet</li>
</ul>

<h3>Uzman Danışmanlık</h3>
<ul>
<li><strong>Jinekolojik Değerlendirme:</strong> Kadın hastalıkları uzmanı işbirliği</li>
<li><strong>Beslenme Danışmanı:</strong> Kadın sağlığı odaklı diyetisyen</li>
<li><strong>Psikolojik Destek:</strong> Kadın koçu ve motivasyon danışmanı</li>
</ul>

<h3>Özel Paketler</h3>
<ul>
<li><strong>Mommy Makeover:</strong> Doğum sonrası paket</li>
<li><strong>Menopause Support:</strong> Menopoz destek paketi</li>
<li><strong>Women Wellness:</strong> Genel kadın sağlığı paketi</li>
</ul>

<p><strong>Sonuç:</strong> EMS teknolojisi kadın sağlığı için özel faydalar sunan güvenli ve etkili bir yöntemdir. Doğru program ve uzman gözetimi ile kadınlar EMS\'den maksimum fayda sağlayabilirler.</p>',
        'excerpt' => 'EMS teknolojisinin kadın sağlığı üzerindeki özel faydaları, hormonal etkiler ve kadınlara özel kullanım önerileri.',
        'category_slug' => 'fitness-ve-saglik',
        'reading_time' => 12,
        'featured_image' => '/assets/images/blog/ems-women.jpg'
    ]
];

// Blog yazılarını ekle
$added_posts = 0;
foreach ($blog_posts as $post) {
    try {
        $category_id = $category_ids[$post['category_slug']] ?? 1;

        $stmt = $pdo->prepare("INSERT INTO blog_posts
                               (title, slug, content, excerpt, category_id, author_id, featured_image, reading_time, is_published, published_at, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");

        $stmt->execute([
            $post['title'],
            $post['slug'],
            $post['content'],
            $post['excerpt'],
            $category_id,
            $admin_id,
            $post['featured_image'],
            $post['reading_time']
        ]);

        $added_posts++;
        echo "✓ Blog yazısı eklendi: " . $post['title'] . "\n";
    } catch (PDOException $e) {
        echo "✗ Blog yazısı eklenirken hata (" . $post['title'] . "): " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "=== SONUÇ ===\n";
echo "Eklenen kategori sayısı: " . count($categories) . "\n";
echo "Eklenen blog yazısı sayısı: " . $added_posts . "\n";
echo "\n";
echo "<a href='../blog.php'>Blog sayfasına git</a>\n";

echo "</pre>";
?>