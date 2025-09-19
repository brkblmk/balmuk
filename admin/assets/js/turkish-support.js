/**
 * Turkish Character Support for Admin Panel
 * Türkçe karakter desteği ve yazım kontrolü
 */

document.addEventListener('DOMContentLoaded', function() {

    // Türkçe karakter haritası
    const turkishChars = {
        'İ': 'i', 'ı': 'ı', 'ğ': 'ğ', 'ş': 'ş', 'ü': 'ü', 'ö': 'ö', 'ç': 'ç',
        'Aİİlİİ': 'Aylık', 'Yaİ': 'Yağ', 'kapsamlİ': 'kapsamlı',
        'Bİlgesel': 'Bölgesel', 'Sİkİlaİma': 'Sıkılaştırma',
        'İndirimi': 'İndirim', 'İzel': 'Özel'
    };

    // Yazım hataları kontrolü
    const spellingErrors = {
        'indirim': 'indirim',
        'aylik': 'aylık',
        'ozel': 'özel',
        'yas': 'yağ',
        'yakimi': 'yakımı',
        'bolgesel': 'bölgesel',
        'kapsamli': 'kapsamlı',
        'guclu': 'güçlü',
        'gucu': 'gücü',
        'seanslari': 'seansları',
        'gun': 'gün',
        'yasam': 'yaşam',
        'guzel': 'güzel',
        'fit': 'fit'
    };

    // =====================================
    // TÜRKÇE KARAKTER KONTROLÜ
    // =====================================

    function checkTurkishChars(text) {
        const issues = [];
        let correctedText = text;

        // Yanlış karakterleri kontrol et
        for (const [wrong, correct] of Object.entries(turkishChars)) {
            if (text.includes(wrong)) {
                issues.push({
                    type: 'char_error',
                    wrong: wrong,
                    correct: correct,
                    message: `'${wrong}' yerine '${correct}' kullanın`
                });
                correctedText = correctedText.replace(new RegExp(wrong.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), correct);
            }
        }

        // Yazım kontrolü
        const words = text.toLowerCase().match(/\b\w+\b/g) || [];
        for (const word of words) {
            if (spellingErrors[word]) {
                issues.push({
                    type: 'spelling',
                    wrong: word,
                    correct: spellingErrors[word],
                    message: `'${word}' yerine '${spellingErrors[word]}' yazın`
                });
            }
        }

        return { issues, correctedText };
    }

    // =====================================
    // FORM ELEMANLARINA TÜRKÇE DESTEĞİ EKLE
    // =====================================

    const textInputs = document.querySelectorAll('input[type="text"], textarea');

    textInputs.forEach(input => {
        let timeoutId;

        // Gerçek zamanlı kontrol
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                validateInput(this);
            }, 500);
        });

        // Odak kaybında detaylı kontrol
        input.addEventListener('blur', function() {
            validateInput(this, true);
        });
    });

    function validateInput(input, showSuggestions = false) {
        const text = input.value;
        if (!text.trim()) return;

        const { issues, correctedText } = checkTurkishChars(text);

        // Mevcut uyarıyı temizle
        removeValidationMessage(input);

        if (issues.length > 0) {
            // Uyarı sınıfı ekle
            input.classList.add('is-invalid');

            // İlk sorunu göster
            const firstIssue = issues[0];
            showValidationMessage(input, firstIssue.message, 'warning');

            // Önerileri göster (blur durumunda)
            if (showSuggestions && issues.length > 1) {
                showSuggestionsPanel(input, issues, correctedText);
            }
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    }

    function showValidationMessage(input, message, type = 'warning') {
        const wrapper = input.parentElement;
        let messageEl = wrapper.querySelector('.validation-message');

        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.className = `validation-message text-${type} mt-1 small`;
            wrapper.appendChild(messageEl);
        }

        messageEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>${message}`;
    }

    function removeValidationMessage(input) {
        const wrapper = input.parentElement;
        const messageEl = wrapper.querySelector('.validation-message');
        if (messageEl) {
            messageEl.remove();
        }
        input.classList.remove('is-valid', 'is-invalid');
    }

    function showSuggestionsPanel(input, issues, correctedText) {
        // Mevcut paneli kaldır
        const existingPanel = document.querySelector('.suggestions-panel');
        if (existingPanel) existingPanel.remove();

        const panel = document.createElement('div');
        panel.className = 'suggestions-panel card position-absolute mt-1';
        panel.style.cssText = `
            z-index: 1000;
            width: ${input.offsetWidth}px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        `;

        let html = '<div class="card-body p-3"><h6 class="card-title mb-2">Düzeltme Önerileri</h6><ul class="list-unstyled mb-0">';

        issues.forEach(issue => {
            html += `<li class="mb-1 small">
                <i class="bi bi-${issue.type === 'char_error' ? 'keyboard' : 'spellcheck'} me-1"></i>
                ${issue.message}
            </li>`;
        });

        html += '</ul>';

        // Otomatik düzeltme butonu
        html += `<button class="btn btn-sm btn-primary mt-2" onclick="applyCorrection('${correctedText.replace(/'/g, "\\'")}')">
            <i class="bi bi-magic me-1"></i>Otomatik Düzelt
        </button>`;

        html += '</div>';

        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(panel);

        // Panel dışına tıklandığında kapat
        document.addEventListener('click', function closePanel(e) {
            if (!panel.contains(e.target) && e.target !== input) {
                panel.remove();
                document.removeEventListener('click', closePanel);
            }
        });

        // Global fonksiyon
        window.applyCorrection = function(text) {
            input.value = text;
            panel.remove();
            validateInput(input);
            showNotification('Metin düzeltildi!', 'success');
        };
    }

    // =====================================
    // KLAVYE KISAYOLU DESTEĞİ
    // =====================================

    document.addEventListener('keydown', function(e) {
        // Ctrl+Shift+T: Türkçe karakter kontrolü
        if (e.ctrlKey && e.shiftKey && e.key === 'T') {
            e.preventDefault();
            checkAllInputs();
        }
    });

    function checkAllInputs() {
        const inputs = document.querySelectorAll('input[type="text"], textarea');
        let totalIssues = 0;

        inputs.forEach(input => {
            const { issues } = checkTurkishChars(input.value);
            totalIssues += issues.length;
        });

        if (totalIssues > 0) {
            showNotification(`${totalIssues} adet yazım/ karakter sorunu bulundu. Lütfen kontrol edin.`, 'warning');
        } else {
            showNotification('✓ Tüm metinler doğru görünüyor!', 'success');
        }
    }

    // =====================================
    // FORM GÖNDERİMİ ÖNCESİ KONTROL
    // =====================================

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[type="text"], textarea');
            let hasIssues = false;

            inputs.forEach(input => {
                const { issues } = checkTurkishChars(input.value);
                if (issues.length > 0) {
                    hasIssues = true;
                    validateInput(input, true);
                }
            });

            if (hasIssues) {
                e.preventDefault();
                showNotification('Lütfen yazım hatalarını düzelttikten sonra tekrar deneyin.', 'warning');
                return false;
            }
        });
    });

    // =====================================
    // CSS EKLE
    // =====================================

    const style = document.createElement('style');
    style.textContent = `
        .is-invalid {
            border-color: #fd7e14 !important;
            box-shadow: 0 0 0 0.25rem rgba(253, 126, 20, 0.25) !important;
        }

        .is-valid {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25) !important;
        }

        .validation-message {
            font-weight: 500;
        }

        .suggestions-panel .card-body {
            max-height: 200px;
            overflow-y: auto;
        }

        .suggestions-panel li:hover {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 2px 4px;
        }
    `;
    document.head.appendChild(style);

    // =====================================
    // İLK KONTROL
    // =====================================

    // Sayfa yüklendiğinde mevcut değerleri kontrol et
    setTimeout(() => {
        textInputs.forEach(input => {
            if (input.value.trim()) {
                validateInput(input);
            }
        });
    }, 1000);

    console.log('Turkish character support initialized');

});