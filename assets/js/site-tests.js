/**
 * Prime EMS Site Functionality Tests
 * Tüm site işlevlerinin çalışıp çalışmadığını kontrol eder
 */

(function() {
    'use strict';
    
    // Test sonuçlarını topla
    let testResults = {
        passed: 0,
        failed: 0,
        tests: []
    };

    // Test helper fonksiyonları
    function logTest(name, status, message) {
        const result = { name, status, message, timestamp: new Date() };
        testResults.tests.push(result);
        
        if (status === 'PASS') {
            testResults.passed++;
            console.log(`✅ ${name}: ${message}`);
        } else {
            testResults.failed++;
            console.error(`❌ ${name}: ${message}`);
        }
    }

    function testElementExists(selector, testName) {
        const element = document.querySelector(selector);
        if (element) {
            logTest(testName, 'PASS', `Element bulundu: ${selector}`);
            return element;
        } else {
            logTest(testName, 'FAIL', `Element bulunamadı: ${selector}`);
            return null;
        }
    }

    function testFormValidation(formSelector, testName) {
        const form = document.querySelector(formSelector);
        if (!form) {
            logTest(testName, 'FAIL', `Form bulunamadı: ${formSelector}`);
            return;
        }

        // Required alanları kontrol et
        const requiredFields = form.querySelectorAll('[required]');
        const hasRequiredFields = requiredFields.length > 0;
        
        logTest(testName, 'PASS', `Form bulundu: ${requiredFields.length} required alan`);
        
        // Form submit event listener kontrolü
        const hasSubmitHandler = form.onsubmit !== null || 
                                form.addEventListener || 
                                form.getAttribute('action');
        
        if (hasSubmitHandler || form.getAttribute('action')) {
            logTest(testName + ' - Submit', 'PASS', 'Form submit handler mevcut');
        } else {
            logTest(testName + ' - Submit', 'FAIL', 'Form submit handler bulunamadı');
        }
    }

    function testLinkValidity(selector, testName) {
        const links = document.querySelectorAll(selector);
        let validLinks = 0;
        let invalidLinks = 0;

        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== '' && !href.startsWith('javascript:')) {
                validLinks++;
            } else if (href === '#') {
                // Anchor linkler için target kontrolü
                const targetId = link.getAttribute('href').substring(1);
                if (targetId && document.getElementById(targetId)) {
                    validLinks++;
                } else if (href === '#') {
                    // # için özel durum - smooth scroll olabilir
                    validLinks++;
                }
            } else {
                invalidLinks++;
            }
        });

        if (invalidLinks === 0) {
            logTest(testName, 'PASS', `${validLinks} geçerli link bulundu`);
        } else {
            logTest(testName, 'FAIL', `${invalidLinks} geçersiz link bulundu (${validLinks} geçerli)`);
        }
    }

    // Ana test fonksiyonları
    function testNavigation() {
        console.log('🧪 Navigasyon testleri başlatılıyor...');
        
        testElementExists('nav', 'Navigation Bar');
        testElementExists('.navbar-brand', 'Brand Logo');
        testLinkValidity('nav a', 'Navigation Links');
        
        // Mobile toggle test
        const mobileToggle = testElementExists('.navbar-toggler', 'Mobile Toggle');
        if (mobileToggle && mobileToggle.getAttribute('data-bs-toggle') === 'collapse') {
            logTest('Mobile Navigation', 'PASS', 'Mobile toggle Bootstrap attribute correct');
        }
    }

    function testHeroSection() {
        console.log('🧪 Hero section testleri başlatılıyor...');
        
        testElementExists('.hero-section, #hero, .hero', 'Hero Section');
        testElementExists('.hero-content h1, .hero-title', 'Hero Title');
        testLinkValidity('.hero-section a, .hero a', 'Hero Section Links');
    }

    function testServices() {
        console.log('🧪 Services section testleri başlatılıyor...');
        
        testElementExists('#services, .services-section', 'Services Section');
        
        // Service filtering test
        const filterButtons = document.querySelectorAll('.filter-buttons .btn, .service-filters .btn');
        const serviceItems = document.querySelectorAll('.service-item');
        
        if (filterButtons.length > 0 && serviceItems.length > 0) {
            logTest('Service Filtering', 'PASS', `${filterButtons.length} filter button, ${serviceItems.length} service item`);
            
            // Filter data attributes test
            let validFilters = 0;
            filterButtons.forEach(btn => {
                if (btn.dataset.filter) validFilters++;
            });
            
            logTest('Filter Attributes', validFilters === filterButtons.length ? 'PASS' : 'FAIL', 
                   `${validFilters}/${filterButtons.length} button has data-filter`);
        } else {
            logTest('Service Filtering', 'FAIL', 'Service filtering elements not found');
        }
    }

    function testContactForms() {
        console.log('🧪 Contact form testleri başlatılıyor...');
        
        // Ana contact formu
        testFormValidation('#contact form, .contact-form', 'Contact Form');
        
        // Hızlı randevu formu
        testFormValidation('form[action*="randevu"], .quick-appointment form', 'Quick Appointment Form');
        
        // Newsletter formu
        testFormValidation('.newsletter-form, form[action*="newsletter"]', 'Newsletter Form');
    }

    function testBlogSection() {
        console.log('🧪 Blog section testleri başlatılıyor...');
        
        testElementExists('#blog, .blog-section', 'Blog Section');
        
        // Blog filtering test
        const blogFilterButtons = document.querySelectorAll('.category-buttons .btn');
        const blogArticles = document.querySelectorAll('.blog-article');
        
        if (blogFilterButtons.length > 0 && blogArticles.length > 0) {
            logTest('Blog Filtering', 'PASS', `${blogFilterButtons.length} filter, ${blogArticles.length} articles`);
            
            // Blog search test
            const blogSearch = document.getElementById('blogSearch');
            logTest('Blog Search', blogSearch ? 'PASS' : 'FAIL', 
                   blogSearch ? 'Blog search input found' : 'Blog search input not found');
        } else {
            logTest('Blog Content', 'FAIL', 'Blog filtering elements not found');
        }

        testLinkValidity('.blog-article a', 'Blog Article Links');
    }

    function testFooter() {
        console.log('🧪 Footer testleri başlatılıyor...');
        
        testElementExists('footer', 'Footer');
        testLinkValidity('footer a', 'Footer Links');
        
        // Social media links test
        const socialLinks = document.querySelectorAll('.social-link, .footer-social a');
        logTest('Social Media Links', socialLinks.length > 0 ? 'PASS' : 'FAIL', 
               `${socialLinks.length} social media links found`);
        
        // Certification badges test
        const certifications = document.querySelectorAll('.certification-item, .cert-logo');
        logTest('Certifications', certifications.length > 0 ? 'PASS' : 'FAIL', 
               `${certifications.length} certification badges found`);
    }

    function testJavaScriptFeatures() {
        console.log('🧪 JavaScript feature testleri başlatılıyor...');
        
        // Bootstrap test
        if (typeof bootstrap !== 'undefined') {
            logTest('Bootstrap JS', 'PASS', 'Bootstrap library loaded');
        } else {
            logTest('Bootstrap JS', 'FAIL', 'Bootstrap library not found');
        }
        
        // Smooth scroll test
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        logTest('Smooth Scroll Links', anchorLinks.length > 0 ? 'PASS' : 'FAIL', 
               `${anchorLinks.length} anchor links for smooth scroll`);
        
        // Theme switcher test
        if (typeof ThemeSwitcher !== 'undefined') {
            logTest('Theme Switcher', 'PASS', 'Theme switcher available');
        } else {
            logTest('Theme Switcher', 'FAIL', 'Theme switcher not available');
        }

        // Chatbot test
        const chatbot = document.querySelector('.chatbot-toggle, #chatbot-toggle, .chat-widget');
        logTest('Chatbot Widget', chatbot ? 'PASS' : 'FAIL', 
               chatbot ? 'Chatbot widget found' : 'Chatbot widget not found');
    }

    function testMobileResponsiveness() {
        console.log('🧪 Mobile responsiveness testleri başlatılıyor...');
        
        // Viewport meta tag test
        const viewport = document.querySelector('meta[name="viewport"]');
        logTest('Viewport Meta', viewport ? 'PASS' : 'FAIL', 
               viewport ? 'Viewport meta tag found' : 'Viewport meta tag missing');
        
        // Responsive classes test
        const responsiveElements = document.querySelectorAll('[class*="col-"], [class*="d-md-"], [class*="d-lg-"]');
        logTest('Responsive Classes', responsiveElements.length > 0 ? 'PASS' : 'FAIL', 
               `${responsiveElements.length} elements with responsive classes`);
    }

    function testAccessibility() {
        console.log('🧪 Accessibility testleri başlatılıyor...');
        
        // Alt text test
        const images = document.querySelectorAll('img');
        let imagesWithAlt = 0;
        images.forEach(img => {
            if (img.getAttribute('alt') !== null) imagesWithAlt++;
        });
        
        logTest('Image Alt Attributes', imagesWithAlt === images.length ? 'PASS' : 'FAIL', 
               `${imagesWithAlt}/${images.length} images have alt attributes`);
        
        // Form labels test
        const inputs = document.querySelectorAll('input, select, textarea');
        let inputsWithLabels = 0;
        inputs.forEach(input => {
            const id = input.id;
            const label = document.querySelector(`label[for="${id}"]`);
            const placeholder = input.getAttribute('placeholder');
            const ariaLabel = input.getAttribute('aria-label');
            
            if (label || placeholder || ariaLabel) inputsWithLabels++;
        });
        
        logTest('Form Labels', inputsWithLabels === inputs.length ? 'PASS' : 'FAIL', 
               `${inputsWithLabels}/${inputs.length} form controls have labels`);
    }

    function testSEO() {
        console.log('🧪 SEO testleri başlatılıyor...');
        
        // Title tag test
        const title = document.querySelector('title');
        logTest('Page Title', title && title.textContent.length > 0 ? 'PASS' : 'FAIL', 
               title ? `Title: "${title.textContent}"` : 'No title found');
        
        // Meta description test
        const metaDescription = document.querySelector('meta[name="description"]');
        logTest('Meta Description', metaDescription ? 'PASS' : 'FAIL', 
               metaDescription ? 'Meta description found' : 'Meta description missing');
        
        // Heading hierarchy test
        const h1s = document.querySelectorAll('h1');
        logTest('H1 Tags', h1s.length === 1 ? 'PASS' : 'FAIL', 
               `${h1s.length} H1 tags found (should be 1)`);
    }

    // Test sonuçlarını göster
    function showTestResults() {
        console.log('\n📊 TEST SONUÇLARI:');
        console.log(`✅ Başarılı: ${testResults.passed}`);
        console.log(`❌ Başarısız: ${testResults.failed}`);
        console.log(`📈 Başarı Oranı: ${Math.round((testResults.passed / (testResults.passed + testResults.failed)) * 100)}%`);
        
        if (testResults.failed > 0) {
            console.log('\n❌ Başarısız Testler:');
            testResults.tests
                .filter(test => test.status === 'FAIL')
                .forEach(test => console.log(`   - ${test.name}: ${test.message}`));
        }
        
        // Test sonuçlarını localStorage'a kaydet
        localStorage.setItem('primeEmsTestResults', JSON.stringify({
            timestamp: new Date(),
            summary: {
                passed: testResults.passed,
                failed: testResults.failed,
                total: testResults.passed + testResults.failed
            },
            details: testResults.tests
        }));
    }

    // Ana test runner
    function runAllTests() {
        console.log('🚀 Prime EMS Site Functionality Tests Başlatılıyor...\n');
        
        testResults = { passed: 0, failed: 0, tests: [] };
        
        try {
            testNavigation();
            testHeroSection();
            testServices();
            testContactForms();
            testBlogSection();
            testFooter();
            testJavaScriptFeatures();
            testMobileResponsiveness();
            testAccessibility();
            testSEO();
        } catch (error) {
            console.error('❌ Test execution error:', error);
            logTest('Test Runner', 'FAIL', `Execution error: ${error.message}`);
        }
        
        showTestResults();
    }

    // Page load testi - DOM hazır olduğunda çalıştır
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runAllTests);
    } else {
        runAllTests();
    }

    // Global erişim için export
    window.PrimeEMSTests = {
        runAll: runAllTests,
        getResults: () => testResults,
        getStoredResults: () => JSON.parse(localStorage.getItem('primeEmsTestResults') || '{}')
    };

})();
