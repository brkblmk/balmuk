<?php
require_once '../config/database.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Gemini API Ayarları (config/gemini.php dosyasından alınacak)
define('GEMINI_API_KEY', 'AIzaSyC_bwPwMt0evZWgoirtijGg-Q6mWkQYk6s'); // Buraya Gemini API key eklenecek
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// Blog kategorilerini çek
$categories = $pdo->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

// AI önerilerini çek
$ai_suggestions = $pdo->query("SELECT * FROM blog_ai_suggestions WHERE is_used = 0 ORDER BY seo_score DESC LIMIT 5")->fetchAll();

// Blog kaydetme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_blog') {
        $title = $_POST['title'] ?? '';
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $excerpt = $_POST['excerpt'] ?? '';
        $content = $_POST['content'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        $meta_title = $_POST['meta_title'] ?? $title;
        $meta_description = $_POST['meta_description'] ?? $excerpt;
        $meta_keywords = $_POST['meta_keywords'] ?? '';
        $reading_time = $_POST['reading_time'] ?? 5;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $ai_generated = isset($_POST['ai_generated']) ? 1 : 0;
        $ai_prompt = $_POST['ai_prompt'] ?? '';
        
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, category_id, author_id, meta_title, meta_description, meta_keywords, reading_time, is_featured, is_published, published_at, ai_generated, ai_prompt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $published_at = $is_published ? date('Y-m-d H:i:s') : null;
            $author_id = $_SESSION['admin_id'] ?? 1;
            
            $stmt->execute([$title, $slug, $excerpt, $content, $category_id, $author_id, $meta_title, $meta_description, $meta_keywords, $reading_time, $is_featured, $is_published, $published_at, $ai_generated, $ai_prompt]);
            
            $post_id = $pdo->lastInsertId();
            
            // Etiketleri kaydet
            if (!empty($_POST['tags'])) {
                $tags = explode(',', $_POST['tags']);
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if ($tag) {
                        // Etiket var mı kontrol et
                        $stmt = $pdo->prepare("SELECT id FROM blog_tags WHERE slug = ?");
                        $tag_slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $tag));
                        $stmt->execute([$tag_slug]);
                        $tag_id = $stmt->fetchColumn();
                        
                        if (!$tag_id) {
                            // Yeni etiket ekle
                            $stmt = $pdo->prepare("INSERT INTO blog_tags (name, slug) VALUES (?, ?)");
                            $stmt->execute([$tag, $tag_slug]);
                            $tag_id = $pdo->lastInsertId();
                        }
                        
                        // Blog-etiket ilişkisi ekle
                        $stmt = $pdo->prepare("INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
                        $stmt->execute([$post_id, $tag_id]);
                    }
                }
            }
            
            logActivity('create', 'blog_posts', $post_id);
            
            echo json_encode(['success' => true, 'message' => 'Blog yazısı başarıyla kaydedildi!', 'post_id' => $post_id]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Blog Yazma - Prime EMS Admin</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        .ai-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .ai-suggestion {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .ai-suggestion:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .ai-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .ai-loading.active {
            display: block;
        }
        
        .writing-tools {
            position: sticky;
            top: 20px;
        }
        
        .seo-score {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .seo-score.good { color: #28a745; }
        .seo-score.medium { color: #ffc107; }
        .seo-score.poor { color: #dc3545; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-robot me-2"></i>AI Destekli Blog Yazma
                    </h1>
                    <div>
                        <button class="btn btn-outline-primary" onclick="saveDraft()">
                            <i class="bi bi-save me-2"></i>Taslak Kaydet
                        </button>
                        <button class="btn btn-primary ms-2" onclick="publishBlog()">
                            <i class="bi bi-send me-2"></i>Yayınla
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Sol Panel - AI Asistan -->
                    <div class="col-lg-4">
                        <div class="writing-tools">
                            <!-- AI Panel -->
                            <div class="ai-panel">
                                <h5 class="mb-3">
                                    <i class="bi bi-stars me-2"></i>Gemini AI Asistan
                                </h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Ne hakkında yazmak istersiniz?</label>
                                    <textarea id="ai-prompt" class="form-control" rows="3" placeholder="Örn: EMS antrenmanının faydaları, 20 dakikada nasıl kilo verilir, protein tüketimi rehberi..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">İçerik Tipi</label>
                                    <select id="content-type" class="form-select">
                                        <option value="informative">Bilgilendirici Yazı</option>
                                        <option value="how-to">Nasıl Yapılır Rehberi</option>
                                        <option value="listicle">Liste Yazısı</option>
                                        <option value="comparison">Karşılaştırma</option>
                                        <option value="case-study">Başarı Hikayesi</option>
                                        <option value="scientific">Bilimsel Makale</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Hedef Kitle</label>
                                    <select id="target-audience" class="form-select">
                                        <option value="beginners">Yeni Başlayanlar</option>
                                        <option value="intermediate">Orta Seviye</option>
                                        <option value="advanced">İleri Seviye</option>
                                        <option value="general">Genel Kitle</option>
                                    </select>
                                </div>
                                
                                <button class="btn btn-light w-100" onclick="generateWithAI()">
                                    <i class="bi bi-magic me-2"></i>AI ile İçerik Oluştur
                                </button>
                                
                                <div class="ai-loading mt-3">
                                    <div class="spinner-border text-light" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                    <p class="mt-2">AI içerik üretiyor...</p>
                                </div>
                            </div>
                            
                            <!-- Hazır Öneriler -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-lightbulb me-2"></i>Hazır Blog Önerileri
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($ai_suggestions as $suggestion): ?>
                                    <div class="ai-suggestion" onclick='useSuggestion(<?php echo json_encode($suggestion); ?>)'>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($suggestion['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($suggestion['category']); ?></small>
                                        <div class="mt-1">
                                            <span class="badge bg-success">SEO: <?php echo $suggestion['seo_score']; ?>/100</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- SEO Skoru -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-graph-up me-2"></i>SEO Analizi
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div id="seo-score" class="seo-score good">85</div>
                                    <p class="mb-0">SEO Skoru</p>
                                    
                                    <div class="mt-3 text-start">
                                        <div class="seo-check" id="seo-checks">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="seo-title" checked>
                                                <label class="form-check-label" for="seo-title">
                                                    Başlık 60 karakterden kısa
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="seo-desc" checked>
                                                <label class="form-check-label" for="seo-desc">
                                                    Meta açıklama 160 karakterden kısa
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="seo-keywords">
                                                <label class="form-check-label" for="seo-keywords">
                                                    Anahtar kelimeler eklenmiş
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="seo-content" checked>
                                                <label class="form-check-label" for="seo-content">
                                                    İçerik 300+ kelime
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sağ Panel - Blog Editörü -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <form id="blog-form">
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label class="form-label">Blog Başlığı *</label>
                                            <input type="text" id="blog-title" class="form-control form-control-lg" placeholder="Dikkat çekici bir başlık yazın..." required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Kategori *</label>
                                            <select id="blog-category" class="form-select form-select-lg" required>
                                                <option value="">Seçiniz</option>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Özet (Excerpt)</label>
                                        <textarea id="blog-excerpt" class="form-control simple-editor" rows="2" placeholder="Blog yazısının kısa özeti..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">İçerik</label>
                                        <textarea id="blog-content" class="form-control"></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Etiketler</label>
                                            <input type="text" id="blog-tags" class="form-control" placeholder="ems, fitness, sağlık (virgülle ayırın)">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Okuma Süresi (dakika)</label>
                                            <input type="number" id="reading-time" class="form-control" value="5" min="1">
                                        </div>
                                    </div>
                                    
                                    <!-- SEO Ayarları -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">SEO Ayarları</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label">Meta Başlık</label>
                                                <input type="text" id="meta-title" class="form-control" maxlength="60">
                                                <small class="text-muted">Maks. 60 karakter</small>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Meta Açıklama</label>
                                                <textarea id="meta-description" class="form-control" rows="2" maxlength="160"></textarea>
                                                <small class="text-muted">Maks. 160 karakter</small>
                                            </div>
                                            <div>
                                                <label class="form-label">Anahtar Kelimeler</label>
                                                <input type="text" id="meta-keywords" class="form-control" placeholder="ems, elektrik kas stimülasyonu, fitness">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="form-check me-3">
                                            <input type="checkbox" class="form-check-input" id="is-featured">
                                            <label class="form-check-label" for="is-featured">
                                                Öne Çıkan Yazı
                                            </label>
                                        </div>
                                        <div class="form-check me-3">
                                            <input type="checkbox" class="form-check-input" id="ai-generated">
                                            <label class="form-check-label" for="ai-generated">
                                                AI ile Oluşturuldu
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" id="ai-prompt-used" value="">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Basit editor için TinyMCE
        tinymce.init({
            selector: 'textarea.simple-editor',
            height: 120,
            menubar: false,
            plugins: ['lists', 'link', 'charmap'],
            toolbar: 'bold italic | bullist numlist | link | removeformat',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }'
        });

        // Ana içerik editörü için TinyMCE
        tinymce.init({
            selector: '#blog-content',
            height: 500,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            language: 'tr',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; line-height: 1.6; }'
        });
        
        // Gemini AI ile içerik oluştur
        async function generateWithAI() {
            const prompt = document.getElementById('ai-prompt').value;
            const contentType = document.getElementById('content-type').value;
            const audience = document.getElementById('target-audience').value;
            
            if (!prompt) {
                alert('Lütfen ne hakkında yazmak istediğinizi belirtin.');
                return;
            }
            
            // Loading göster
            document.querySelector('.ai-loading').classList.add('active');
            
            // API isteği için prompt hazırla
            const fullPrompt = `
                Prime EMS Studios için ${contentType} türünde bir blog yazısı oluştur.
                Konu: ${prompt}
                Hedef Kitle: ${audience}
                
                Gereksinimler:
                1. SEO optimizasyonlu başlık
                2. İlgi çekici giriş paragrafı
                3. Alt başlıklar (H2, H3)
                4. Detaylı içerik (minimum 800 kelime)
                5. Sonuç ve CTA (Call to Action)
                6. Meta açıklama önerisi
                7. Anahtar kelimeler
                
                Yazı EMS teknolojisi, fitness ve wellness konularında uzman bir ton kullanmalı.
                HTML formatında döndür.
            `;
            
            try {
                // Gemini API çağrısı (gerçek implementasyon için API key gerekli)
                const response = await fetch('/api/gemini-generate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        prompt: fullPrompt,
                        api_key: '<?php echo GEMINI_API_KEY; ?>'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Editöre içeriği yerleştir
                    document.getElementById('blog-title').value = data.title || '';
                    document.getElementById('blog-excerpt').value = data.excerpt || '';
                    tinymce.get('blog-content').setContent(data.content || '');
                    document.getElementById('meta-title').value = data.meta_title || '';
                    document.getElementById('meta-description').value = data.meta_description || '';
                    document.getElementById('meta-keywords').value = data.keywords || '';
                    document.getElementById('ai-prompt-used').value = prompt;
                    document.getElementById('ai-generated').checked = true;
                    
                    // SEO skorunu güncelle
                    updateSEOScore();
                } else {
                    // Demo içerik (API key yoksa)
                    generateDemoContent(prompt, contentType, audience);
                }
            } catch (error) {
                console.error('AI içerik oluşturma hatası:', error);
                // Demo içerik göster
                generateDemoContent(prompt, contentType, audience);
            } finally {
                document.querySelector('.ai-loading').classList.remove('active');
            }
        }
        
        // Demo içerik oluştur (Gemini API yoksa)
        function generateDemoContent(prompt, contentType, audience) {
            const demoContent = {
                title: `${prompt} - Kapsamlı Rehber`,
                excerpt: `${prompt} hakkında bilmeniz gereken her şey. Prime EMS Studios uzmanlarından detaylı bilgiler ve öneriler.`,
                content: `
                    <h2>${prompt} Nedir?</h2>
                    <p>Modern fitness dünyasında ${prompt.toLowerCase()} konusu giderek daha fazla önem kazanıyor. Prime EMS Studios olarak, bu konuda sizlere en güncel ve bilimsel bilgileri sunuyoruz.</p>
                    
                    <h3>Temel Bilgiler</h3>
                    <p>EMS teknolojisi ile birleştiğinde, ${prompt.toLowerCase()} konusunda muhteşem sonuçlar elde edilebilir. İşte dikkat edilmesi gerekenler:</p>
                    
                    <ul>
                        <li>Düzenli antrenman programı</li>
                        <li>Doğru beslenme alışkanlıkları</li>
                        <li>Yeterli dinlenme ve toparlanma</li>
                        <li>Profesyonel rehberlik</li>
                    </ul>
                    
                    <h2>Prime EMS Studios'da ${prompt}</h2>
                    <p>Uzman kadromuz ve son teknoloji ekipmanlarımızla, ${prompt.toLowerCase()} konusunda size özel çözümler sunuyoruz.</p>
                    
                    <h3>Neden Prime EMS?</h3>
                    <ul>
                        <li><strong>Kişiye Özel Programlar:</strong> Her bireyin ihtiyacına göre özelleştirilmiş antrenman planları</li>
                        <li><strong>Uzman Kadro:</strong> Alanında deneyimli ve sertifikalı eğitmenler</li>
                        <li><strong>Hızlı Sonuçlar:</strong> 20 dakikalık seanslarla maksimum verim</li>
                        <li><strong>Bilimsel Yaklaşım:</strong> Kanıta dayalı yöntemler ve sürekli takip</li>
                    </ul>
                    
                    <h2>Başarı İçin İpuçları</h2>
                    <p>İşte ${prompt.toLowerCase()} konusunda başarılı olmanız için önerilerimiz:</p>
                    
                    <ol>
                        <li>Hedeflerinizi net bir şekilde belirleyin</li>
                        <li>Düzenli olun ve programınıza sadık kalın</li>
                        <li>İlerlemenizi takip edin ve kaydedin</li>
                        <li>Uzman tavsiyelerine uyun</li>
                        <li>Sabırlı olun ve sürece güvenin</li>
                    </ol>
                    
                    <h2>Sonuç</h2>
                    <p>${prompt} konusunda doğru yaklaşım ve profesyonel destek ile hedeflerinize ulaşabilirsiniz. Prime EMS Studios olarak, bu yolculukta sizinle olmaktan mutluluk duyarız.</p>
                    
                    <div class="cta-box">
                        <h3>Hemen Başlayın!</h3>
                        <p>Ücretsiz deneme seansımızdan yararlanın ve ${prompt.toLowerCase()} hedeflerinize ulaşın.</p>
                        <a href="/reservation" class="btn btn-primary">Randevu Al</a>
                    </div>
                `,
                meta_title: `${prompt} Rehberi | Prime EMS Studios`,
                meta_description: `${prompt} hakkında detaylı bilgi ve öneriler. Prime EMS Studios'da profesyonel destek.`,
                keywords: `${prompt.toLowerCase()}, ems, fitness, wellness, prime ems studios`
            };
            
            // Formu doldur
            document.getElementById('blog-title').value = demoContent.title;
            document.getElementById('blog-excerpt').value = demoContent.excerpt;
            tinymce.get('blog-content').setContent(demoContent.content);
            document.getElementById('meta-title').value = demoContent.meta_title;
            document.getElementById('meta-description').value = demoContent.meta_description;
            document.getElementById('meta-keywords').value = demoContent.keywords;
            document.getElementById('ai-prompt-used').value = prompt;
            document.getElementById('ai-generated').checked = true;
            
            updateSEOScore();
        }
        
        // Öneriyi kullan
        function useSuggestion(suggestion) {
            document.getElementById('ai-prompt').value = suggestion.topic;
            document.getElementById('blog-title').value = suggestion.title;
            
            // Otomatik AI ile içerik oluştur
            generateWithAI();
        }
        
        // SEO Skorunu güncelle
        function updateSEOScore() {
            let score = 0;
            const checks = {
                'seo-title': document.getElementById('meta-title').value.length > 0 && document.getElementById('meta-title').value.length <= 60,
                'seo-desc': document.getElementById('meta-description').value.length > 0 && document.getElementById('meta-description').value.length <= 160,
                'seo-keywords': document.getElementById('meta-keywords').value.length > 0,
                'seo-content': tinymce.get('blog-content').getContent({format: 'text'}).split(' ').length > 300
            };
            
            Object.keys(checks).forEach(key => {
                document.getElementById(key).checked = checks[key];
                if (checks[key]) score += 25;
            });
            
            const scoreElement = document.getElementById('seo-score');
            scoreElement.textContent = score;
            scoreElement.className = 'seo-score ' + (score >= 75 ? 'good' : score >= 50 ? 'medium' : 'poor');
        }
        
        // Blog kaydet
        async function saveBlog(publish = false) {
            const formData = new FormData();
            formData.append('action', 'save_blog');
            formData.append('title', document.getElementById('blog-title').value);
            formData.append('excerpt', document.getElementById('blog-excerpt').value);
            formData.append('content', tinymce.get('blog-content').getContent());
            formData.append('category_id', document.getElementById('blog-category').value);
            formData.append('tags', document.getElementById('blog-tags').value);
            formData.append('reading_time', document.getElementById('reading-time').value);
            formData.append('meta_title', document.getElementById('meta-title').value);
            formData.append('meta_description', document.getElementById('meta-description').value);
            formData.append('meta_keywords', document.getElementById('meta-keywords').value);
            formData.append('is_featured', document.getElementById('is-featured').checked ? 1 : 0);
            formData.append('is_published', publish ? 1 : 0);
            formData.append('ai_generated', document.getElementById('ai-generated').checked ? 1 : 0);
            formData.append('ai_prompt', document.getElementById('ai-prompt-used').value);
            
            try {
                const response = await fetch('blog-write.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    if (publish) {
                        setTimeout(() => {
                            window.location.href = 'blogs.php';
                        }, 1500);
                    }
                } else {
                    showNotification(data.message || 'Bir hata oluştu', 'error');
                }
            } catch (error) {
                console.error('Kaydetme hatası:', error);
                showNotification('Blog kaydedilirken bir hata oluştu', 'error');
            }
        }
        
        function saveDraft() {
            saveBlog(false);
        }
        
        function publishBlog() {
            if (!document.getElementById('blog-title').value || !document.getElementById('blog-category').value) {
                showNotification('Lütfen başlık ve kategori alanlarını doldurun', 'error');
                return;
            }
            saveBlog(true);
        }
        
        // Input değişikliklerinde SEO skorunu güncelle
        document.getElementById('meta-title').addEventListener('input', updateSEOScore);
        document.getElementById('meta-description').addEventListener('input', updateSEOScore);
        document.getElementById('meta-keywords').addEventListener('input', updateSEOScore);
    </script>
</body>
</html>