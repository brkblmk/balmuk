<?php
session_start();
require_once 'config/database.php';

// SSS verilerini çek (chatbot için kullanılacak)
try {
    $faq_stmt = $pdo->query("SELECT question, answer FROM faq WHERE is_active = 1");
    $faqs = $faq_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $faqs = [];
}
?>
<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="tr-TR">

    <!-- Primary Meta Tags -->
    <title>AI Asistan - EMS Teknolojisi ve Sağlık Danışmanlığı | Prime EMS Studios İzmir</title>
    <meta name="title" content="AI Asistan - EMS Teknolojisi ve Sağlık Danışmanlığı | Prime EMS Studios İzmir">
    <meta name="description" content="Prime EMS Studios AI asistanı ile EMS cihazları, tıbbi ekipmanlar ve sağlık teknolojileri hakkında anında bilgi alın. Fitness, kilo verme ve wellness danışmanlığı.">
    <meta name="keywords" content="EMS cihazları, tıbbi ekipmanlar, sağlık teknolojileri, AI asistan, chatbot, fitness danışmanlığı, kilo verme, EMS İzmir, Prime EMS Studios">
    <meta name="author" content="Prime EMS Studios">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://primeemsstudios.com/chatbot.php">
    <meta property="og:title" content="AI Asistan - EMS Teknolojisi ve Sağlık Danışmanlığı | Prime EMS Studios İzmir">
    <meta property="og:description" content="EMS cihazları ve sağlık teknolojileri hakkında AI destekli danışmanlık. Anında bilgi ve randevu desteği alın.">
    <meta property="og:image" content="https://primeemsstudios.com/assets/images/logo.png">
    <meta property="og:site_name" content="Prime EMS Studios">
    <meta property="og:locale" content="tr_TR">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://primeemsstudios.com/chatbot.php">
    <meta property="twitter:title" content="AI Asistan - EMS Teknolojisi ve Sağlık Danışmanlığı | Prime EMS Studios İzmir">
    <meta property="twitter:description" content="EMS cihazları ve sağlık teknolojileri hakkında AI destekli danışmanlık. Anında bilgi ve randevu desteği alın.">
    <meta property="twitter:image" content="https://primeemsstudios.com/assets/images/logo.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://primeemsstudios.com/chatbot.php">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <!-- Structured Data - FAQPage Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "name": "Prime EMS Studios AI Asistan - SSS",
        "description": "EMS teknolojisi, fitness ve sağlık konularında sık sorulan sorular ve cevapları",
        "publisher": {
            "@type": "Organization",
            "name": "Prime EMS Studios",
            "logo": "https://primeemsstudios.com/assets/images/logo.png"
        },
        "mainEntity": [
            {
                "@type": "Question",
                "name": "EMS teknolojisi nedir?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "EMS (Elektrik Kas Stimülasyonu), kaslarınıza kontrollü elektrik impulsu göndererek onları aktive eden ileri bir teknolojidir. 20 dakikalık bir EMS seansı, 90 dakikalık geleneksel antrenmana eşdeğerdir."
                }
            },
            {
                "@type": "Question",
                "name": "EMS antrenmanının faydaları nelerdir?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "EMS antrenmanı zaman tasarrufu, kas kütlesi artışı, yağ yakımı, metabolizma hızlanması, eklem dostu antrenman, selülit azalması, duruş düzelmesi ve güç artışı gibi faydalar sağlar."
                }
            },
            {
                "@type": "Question",
                "name": "EMS güvenli midir?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "EMS teknolojisi FDA onaylı ve tamamen güvenlidir. Hamileler, kalp pili kullananlar, epilepsi hastaları ve aktif kanser tedavisi görenler dışında herkese uygundur."
                }
            }
        ]
    }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .chat-container {
            max-width: 500px;
            height: 100vh;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            background: white;
            box-shadow: 0 0 50px rgba(0,0,0,0.2);
        }
        
        .chat-header {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #333;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chat-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .bot-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.bot {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.bot .message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .typing-indicator {
            display: none;
            padding: 15px;
            background: white;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .typing-indicator.show {
            display: inline-block;
        }
        
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #999;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }
        
        .input-group {
            position: relative;
        }
        
        .chat-input input {
            border-radius: 25px;
            border: 1px solid #ddd;
            padding: 12px 20px;
            padding-right: 50px;
            font-size: 15px;
        }
        
        .chat-input input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .send-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .send-btn:hover {
            transform: translateY(-50%) scale(1.1);
        }
        
        .quick-replies {
            padding: 10px 20px;
            background: white;
            border-top: 1px solid #f0f0f0;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        .quick-reply {
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
        }
        
        .quick-reply:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }
        
        .close-chat {
            background: rgba(255,255,255,0.2);
            border: none;
            color: #333;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .close-chat:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .chat-container {
                max-width: 100%;
                height: 100vh;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="bot-avatar">
                    <i class="bi bi-robot" style="font-size: 24px; color: #667eea;"></i>
                </div>
                <div>
                    <h4>Prime AI Asistan</h4>
                    <small style="opacity: 0.8;">EMS & Fitness Uzmanı</small>
                </div>
            </div>
            <button class="close-chat" onclick="window.close()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <!-- Welcome Message -->
            <div class="message bot">
                <div class="message-content">
                    Merhaba! 👋 Ben Prime EMS Studios AI asistanıyım. Size EMS teknolojisi, fitness programları, randevu ve üyelik konularında yardımcı olabilirim. Nasıl yardımcı olabilirim?
                </div>
            </div>
        </div>
        
        <!-- Typing Indicator -->
        <div class="message bot" id="typingIndicator" style="display: none;">
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        
        <!-- Quick Replies -->
        <div class="quick-replies" id="quickReplies">
            <button class="quick-reply" onclick="sendQuickReply('EMS nedir?')">EMS nedir?</button>
            <button class="quick-reply" onclick="sendQuickReply('Fiyatlar nedir?')">Fiyatlar</button>
            <button class="quick-reply" onclick="sendQuickReply('Randevu almak istiyorum')">Randevu Al</button>
            <button class="quick-reply" onclick="sendQuickReply('Kaç kilo verebilirim?')">Kilo Verme</button>
            <button class="quick-reply" onclick="sendQuickReply('Çalışma saatleri')">Çalışma Saatleri</button>
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input">
            <div class="input-group">
                <input type="text" 
                       id="messageInput" 
                       class="form-control" 
                       placeholder="Mesajınızı yazın..." 
                       autocomplete="off"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="send-btn" onclick="sendMessage()">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // FAQ verileri
        const faqData = <?php echo json_encode($faqs); ?>;
        
        // Chatbot bilgi bankası
        const knowledgeBase = {
            greetings: {
                patterns: ['merhaba', 'selam', 'hey', 'günaydın', 'iyi günler', 'iyi akşamlar', 'selamlar', 'merhabalar'],
                response: 'Merhaba! 😊 Size nasıl yardımcı olabilirim? EMS antrenmanı, fiyatlar veya randevu konusunda bilgi almak ister misiniz?'
            },
            ems: {
                patterns: ['ems nedir', 'ems ne', 'elektrik kas', 'elektrik stimülasyon', 'ems teknoloji', 'ems sistem'],
                response: 'EMS (Elektrik Kas Stimülasyonu), kaslarınıza kontrollü elektrik impulsu göndererek onları aktive eden ileri bir teknolojidir. 20 dakikalık bir EMS seansı, 90 dakikalık geleneksel antrenmana eşdeğerdir! 💪\n\nEMS ile:\n• Haftada sadece 2 seans\n• Her seans 20 dakika\n• 10 büyük kas grubu aynı anda çalışır\n• Eklemlere minimum yük\n\nDaha detaylı bilgi için randevu alabilir veya ücretsiz deneme seansımıza katılabilirsiniz!'
            },
            pricing: {
                patterns: ['fiyat', 'ücret', 'kaç para', 'kaç tl', 'paket', 'kampanya', 'indirim', 'maliyet', 'tutar'],
                response: 'Prime EMS Studios olarak çeşitli paket seçeneklerimiz mevcuttur:\n\n📦 **Başlangıç Paketi:** 4 seans\n📦 **Standart Paket:** 8 seans\n📦 **Premium Paket:** 12 seans\n📦 **VIP Üyelik:** Sınırsız aylık\n\n🎉 **Açılış Kampanyası:** İlk 30 üyemize %45 indirim!\n\nDetaylı fiyat bilgisi için WhatsApp\'tan ulaşabilir veya ücretsiz keşif seansımıza katılabilirsiniz. Randevu almak ister misiniz?'
            },
            appointment: {
                patterns: ['randevu', 'rezervasyon', 'seans', 'deneme', 'ücretsiz deneme', 'keşif seansı', 'ne zaman gelebilirim'],
                response: 'Harika! Ücretsiz keşif seansınız için randevu almak çok kolay! 🗓️\n\n**Randevu Seçenekleri:**\n• 📱 WhatsApp: Hemen mesaj gönderin\n• 📞 Telefon: Bizi arayın\n• 🌐 Online: Web sitemizden randevu alın\n\n**Çalışma Saatlerimiz:**\n• Pazartesi-Cumartesi: 09:00 - 21:00\n• Pazar: 10:00 - 18:00\n\nHemen randevu almak için [buraya tıklayın](/reservation.php) veya WhatsApp\'tan bize ulaşın!'
            },
            weightLoss: {
                patterns: ['kilo', 'zayıfla', 'yağ yak', 'kilo ver', 'kaç kilo', 'forma gir', 'incel'],
                response: 'EMS antrenmanı kilo verme konusunda çok etkilidir! 🔥\n\n**Kilo Verme Süreciniz:**\n• Haftada 2 seans ile ayda 2-4 kg sağlıklı kilo kaybı\n• Metabolizma hızında %17 artış\n• Antrenman sonrası 48 saat yağ yakımı devam eder\n• Bel çevresinde 4-6 haftada görünür incelme\n\n**Başarı Hikayeleri:**\n• 3 ayda 10-15 kg verenler\n• 6 haftada 2 beden incelen üyelerimiz\n\nKişiye özel beslenme desteği de sağlıyoruz! Ücretsiz vücut analizi için randevu almak ister misiniz?'
            },
            benefits: {
                patterns: ['fayda', 'yarar', 'ne işe yarar', 'avantaj', 'neden ems', 'neden tercih'],
                response: 'EMS antrenmanının sayısız faydası var! ✨\n\n**Ana Faydalar:**\n• ⏱️ Zaman tasarrufu (20 dk = 90 dk)\n• 💪 Kas kütlesi artışı\n• 🔥 Yağ yakımı ve metabolizma hızlanması\n• 🦴 Eklem dostu antrenman\n• 🎯 Selülit azalması\n• 🧘 Duruş bozukluğu düzelmesi\n• ⚡ Güç ve dayanıklılık artışı\n• 🩺 Sırt ağrılarında azalma\n\nHangi hedefiniz için EMS\'i denemek istersiniz?'
            },
            location: {
                patterns: ['nerede', 'adres', 'konum', 'lokasyon', 'nasıl gel', 'yol tarif', 'neredesiniz'],
                response: '📍 **Prime EMS Studios Konumu:**\n\nAdresimiz: [Tam adres bilgisi]\n\n**Ulaşım:**\n• 🚇 Metro: [En yakın metro durağı]\n• 🚌 Otobüs: [Otobüs hatları]\n• 🚗 Araç: Ücretsiz otopark mevcut\n\nGoogle Maps\'te kolayca bulabilirsiniz! Yol tarifi için WhatsApp\'tan bize ulaşabilirsiniz.'
            },
            workingHours: {
                patterns: ['saat', 'çalışma saati', 'açık mı', 'kaçta açık', 'kaçta kapanıyor', 'hafta sonu'],
                response: '🕐 **Çalışma Saatlerimiz:**\n\n• Pazartesi-Cuma: 09:00 - 21:00\n• Cumartesi: 09:00 - 21:00\n• Pazar: 10:00 - 18:00\n\n📅 Bayram ve özel günlerde çalışma saatlerimiz değişebilir.\n\nRandevu almak için size uygun bir saat seçebilirsiniz!'
            },
            safety: {
                patterns: ['güvenli', 'zararlı', 'yan etki', 'risk', 'tehlike', 'hamile', 'kalp', 'tansiyon'],
                response: '🏥 **EMS Güvenliği:**\n\nEMS teknolojisi tamamen güvenlidir ve FDA onaylıdır!\n\n**Kimlere Uygun Değil:**\n• Hamileler\n• Kalp pili kullananlar\n• Epilepsi hastaları\n• Aktif kanser tedavisi görenler\n\n**Güvenlik Önlemlerimiz:**\n• Sağlık formu ve ön değerlendirme\n• Sertifikalı uzman eğitmenler\n• Kişiye özel yoğunluk ayarı\n• Sürekli takip ve kontrol\n\nHerhangi bir sağlık durumunuz varsa mutlaka belirtiniz. Güvenliğiniz bizim önceliğimiz!'
            },
            equipment: {
                patterns: ['cihaz', 'ekipman', 'makine', 'teknoloji', 'marka', 'model', 'kıyafet'],
                response: '🎯 **Kullandığımız Teknoloji:**\n\n• **i-motion** ve **i-model** cihazları\n• Almanya\'dan ithal, CE sertifikalı\n• Kablosuz teknoloji ile özgür hareket\n• Özel EMS takım elbisesi (hijyenik ve kişiye özel)\n\n**Antrenman Ekipmanı:**\n• Size özel temiz takım elbise\n• Tek kullanımlık hijyen seti\n• Profesyonel monitörleme sistemi\n\nTüm ekipmanlarımız düzenli olarak dezenfekte edilir ve bakımı yapılır!'
            },
            results: {
                patterns: ['sonuç', 'ne zaman', 'kaç seans', 'değişim', 'fark', 'etkisi', 'süre'],
                response: '📈 **Sonuç Beklentileri:**\n\n**İlk 2 Hafta:**\n• Enerji artışı\n• Kas tonusunda sıkılaşma\n\n**4-6 Hafta:**\n• Görünür kilo kaybı (2-4 kg)\n• Bel/kalça ölçülerinde azalma\n• Güç artışı\n\n**8-12 Hafta:**\n• Belirgin vücut şekillenmesi\n• 5-10 kg kilo kaybı\n• Selülitte azalma\n• Duruş düzelmesi\n\nDüzenli antrenman ve beslenme ile sonuçlar garanti! 💪'
            },
            nutrition: {
                patterns: ['beslenme', 'diyet', 'yemek', 'protein', 'ne yiyeyim', 'öğün'],
                response: '🥗 **Beslenme Desteği:**\n\nEMS antrenmanının etkisini artırmak için:\n\n**Antrenman Öncesi (2-3 saat):**\n• Hafif karbonhidrat\n• Bol su\n\n**Antrenman Sonrası:**\n• Protein ağırlıklı beslenme\n• 30 dk içinde protein alımı\n• Bol su tüketimi\n\n**Genel Öneriler:**\n• Günde 2-3 litre su\n• Düzenli öğünler\n• İşlenmiş gıdalardan uzak durma\n\nÜyelerimize özel beslenme programı desteği sağlıyoruz! 🎯'
            },
            memberShip: {
                patterns: ['üyelik', 'üye ol', 'kayıt', 'başla', 'yazıl'],
                response: '🌟 **Üyelik Avantajları:**\n\n• Kişiye özel antrenman programı\n• Beslenme danışmanlığı\n• Düzenli vücut analizi\n• Esnek randevu saatleri\n• Üyelere özel kampanyalar\n• Online takip sistemi\n\n**Üyelik Adımları:**\n1. Ücretsiz keşif seansı\n2. Vücut analizi\n3. Hedef belirleme\n4. Paket seçimi\n5. Antrenmanlara başlama\n\nHemen başlamak için randevu alın! 🚀'
            },
            contact: {
                patterns: ['iletişim', 'telefon', 'whatsapp', 'ulaş', 'ara', 'numara'],
                response: '📞 **İletişim Bilgilerimiz:**\n\n• 📱 WhatsApp: [Tıklayarak mesaj gönderin]\n• ☎️ Telefon: 0XXX XXX XX XX\n• 📧 Email: info@primeemsstudios.com\n• 📍 Adres: [Detaylı adres]\n\n**Sosyal Medya:**\n• Instagram: @primeemsstudios\n• Facebook: /primeemsstudios\n\nSize en uygun iletişim kanalından bize ulaşabilirsiniz! 💬'
            },
            goodbye: {
                patterns: ['görüşürüz', 'hoşça kal', 'bay bay', 'güle güle', 'teşekkür', 'sağol', 'iyi günler'],
                response: 'Görüşmek üzere! 👋 Prime EMS Studios\'da sizi görmekten mutluluk duyarız. Başka bir sorunuz olursa her zaman buradayım. İyi günler dilerim! 😊'
            }
        };
        
        // Yasaklı konular
        const restrictedTopics = {
            patterns: ['politika', 'din', 'cinsel', 'kumar', 'bahis', 'alkol', 'sigara', 'uyuşturucu', 'şiddet', 'ırkçı', 'hakaret'],
            response: 'Üzgünüm, bu konu hakkında bilgi veremem. Ben sadece EMS teknolojisi, fitness, wellness ve Prime EMS Studios hizmetleri hakkında yardımcı olabilirim. Size başka nasıl yardımcı olabilirim? 🙂'
        };
        
        // Mesaj gönderme
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Kullanıcı mesajını ekle
            addMessage(message, 'user');
            input.value = '';
            
            // Bot yanıtını gecikmeyle göster
            showTyping();
            setTimeout(() => {
                hideTyping();
                const response = generateResponse(message);
                addMessage(response, 'bot');
            }, 1000 + Math.random() * 1000);
        }
        
        // Hızlı yanıt gönderme
        function sendQuickReply(text) {
            document.getElementById('messageInput').value = text;
            sendMessage();
        }
        
        // Mesaj ekleme
        function addMessage(text, sender) {
            const messagesDiv = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = text.replace(/\n/g, '<br>');
            
            messageDiv.appendChild(contentDiv);
            messagesDiv.appendChild(messageDiv);
            
            // Scroll to bottom
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        // Yazıyor göstergesi
        function showTyping() {
            document.getElementById('typingIndicator').style.display = 'flex';
            const messagesDiv = document.getElementById('chatMessages');
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        function hideTyping() {
            document.getElementById('typingIndicator').style.display = 'none';
        }
        
        // Yanıt üretme
        function generateResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            // Yasaklı konuları kontrol et
            for (const pattern of restrictedTopics.patterns) {
                if (lowerMessage.includes(pattern)) {
                    return restrictedTopics.response;
                }
            }
            
            // FAQ'den cevap ara
            for (const faq of faqData) {
                if (lowerMessage.includes(faq.question.toLowerCase().substring(0, 10))) {
                    return faq.answer;
                }
            }
            
            // Bilgi bankasından cevap bul
            for (const [key, data] of Object.entries(knowledgeBase)) {
                for (const pattern of data.patterns) {
                    if (lowerMessage.includes(pattern)) {
                        return data.response;
                    }
                }
            }
            
            // Randevu ile ilgili genel sorgular
            if (lowerMessage.includes('randevu') || lowerMessage.includes('gel') || lowerMessage.includes('başla')) {
                return knowledgeBase.appointment.response;
            }
            
            // Fiyat ile ilgili genel sorgular
            if (lowerMessage.includes('fiyat') || lowerMessage.includes('ücret') || lowerMessage.includes('kaç')) {
                return knowledgeBase.pricing.response;
            }
            
            // EMS ile ilgili genel sorgular
            if (lowerMessage.includes('ems') || lowerMessage.includes('elektrik')) {
                return knowledgeBase.ems.response;
            }
            
            // Varsayılan yanıt
            return 'Sorunuzu tam anlayamadım, ancak size yardımcı olmaya çalışayım! 😊\n\nAşağıdaki konularda detaylı bilgi verebilirim:\n• EMS teknolojisi nedir?\n• Fiyatlar ve paketler\n• Randevu alma\n• Kilo verme ve fitness\n• Çalışma saatleri\n• Üyelik avantajları\n\nHangi konuda bilgi almak istersiniz?';
        }
        
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('messageInput').focus();
        });
    </script>
</body>
</html>