<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/security.php';

// Admin kontrolü
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// POST verilerini al ve sanitize et
$input = json_decode(file_get_contents('php://input'), true);
$prompt = SecurityUtils::sanitizeInput($input['prompt'] ?? '', 'html');
$api_key = SecurityUtils::sanitizeInput($input['api_key'] ?? '', 'string');

// Rate limiting kontrolü
if (!SecurityUtils::checkRateLimit('api_gemini', 10, 60)) { // 10 istek per minute
    SecurityUtils::logSecurityEvent('API_RATE_LIMIT_EXCEEDED', [
        'action' => 'gemini_generate',
        'ip' => SecurityUtils::getClientIP()
    ]);
    echo json_encode(['success' => false, 'message' => 'Çok sık istek gönderiyorsunuz. Lütfen 1 dakika bekleyiniz.']);
    exit;
}

if (!$prompt) {
    echo json_encode(['success' => false, 'message' => 'Prompt gereklidir']);
    exit;
}

// Gemini API'ye istek gönder (gerçek implementasyon)
function callGeminiAPI($prompt, $api_key) {
    if (!$api_key || $api_key === 'YOUR_GEMINI_API_KEY_HERE') {
        // API key yoksa demo içerik döndür
        return false;
    }
    
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
            'topP' => 0.8,
            'topK' => 10
        ]
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        return false;
    }
    
    $response = json_decode($result, true);
    
    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }
    
    return false;
}

// API'yi çağır
$gemini_response = callGeminiAPI($prompt, $api_key);

if ($gemini_response) {
    // Gerçek API yanıtını parse et
    // Burada Gemini'nin döndürdüğü metni HTML formatına çevirip,
    // başlık, özet vs. çıkarabiliriz
    
    $response = [
        'success' => true,
        'title' => extractTitle($gemini_response),
        'excerpt' => extractExcerpt($gemini_response),
        'content' => formatAsHTML($gemini_response),
        'meta_title' => extractMetaTitle($gemini_response),
        'meta_description' => extractMetaDescription($gemini_response),
        'keywords' => extractKeywords($gemini_response)
    ];
} else {
    // Demo içerik döndür
    $response = [
        'success' => false,
        'message' => 'Demo modda çalışıyor. Gerçek içerik için Gemini API key ekleyin.'
    ];
}

echo json_encode($response);

// Yardımcı fonksiyonlar
function extractTitle($text) {
    // İlk satırı veya H1 etiketini başlık olarak al
    $lines = explode("\n", $text);
    return trim(str_replace(['#', '*'], '', $lines[0]));
}

function extractExcerpt($text) {
    // İlk paragrafı özet olarak al
    $paragraphs = explode("\n\n", $text);
    return isset($paragraphs[1]) ? trim(substr($paragraphs[1], 0, 200)) : '';
}

function formatAsHTML($text) {
    // Markdown benzeri formatı HTML'e çevir
    $html = $text;
    
    // Başlıklar
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    
    // Bold ve italic
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    
    // Listeler
    $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
    
    // Paragraflar
    $html = '<p>' . preg_replace('/\n\n/', '</p><p>', $html) . '</p>';
    
    return $html;
}

function extractMetaTitle($text) {
    $title = extractTitle($text);
    return substr($title . ' | Prime EMS Studios', 0, 60);
}

function extractMetaDescription($text) {
    $excerpt = extractExcerpt($text);
    return substr($excerpt, 0, 160);
}

function extractKeywords($text) {
    // Basit keyword çıkarma
    $keywords = ['ems', 'fitness', 'wellness', 'prime ems studios'];
    
    // Sık geçen kelimeleri bul
    $words = str_word_count(strtolower($text), 1);
    $word_count = array_count_values($words);
    arsort($word_count);
    
    // En sık kullanılan 5 kelimeyi ekle
    $top_words = array_slice(array_keys($word_count), 0, 5);
    $keywords = array_merge($keywords, $top_words);
    
    return implode(', ', array_unique($keywords));
}
?>