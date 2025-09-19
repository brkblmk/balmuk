// WebP Image Optimization and Lazy Loading
class ImageOptimizer {
    constructor() {
        this.webpSupport = this.detectWebPSupport();
        this.lazyImages = document.querySelectorAll('img[data-src]');
        this.init();
    }

    // WebP desteği kontrolü
    detectWebPSupport() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }

    // WebP URL'si oluştur
    getWebPUrl(originalUrl) {
        if (!this.webpSupport) return originalUrl;

        // Dosya uzantısını değiştir
        return originalUrl.replace(/\.(jpg|jpeg|png)$/i, '.webp');
    }

    // Resim yükleme ve optimizasyon
    optimizeImage(img) {
        const originalSrc = img.getAttribute('data-src') || img.src;

        if (this.webpSupport) {
            // WebP versiyonunu dene
            const webpSrc = this.getWebPUrl(originalSrc);

            // WebP yüklenip yüklenmediğini kontrol et
            const webpImg = new Image();
            webpImg.onload = () => {
                img.src = webpSrc;
                img.classList.add('webp-loaded');
            };
            webpImg.onerror = () => {
                // WebP mevcut değilse orijinal resmi kullan
                img.src = originalSrc;
            };
            webpImg.src = webpSrc;
        } else {
            img.src = originalSrc;
        }

        // Loading attribute ekle
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }

        // Alt attribute kontrolü
        if (!img.hasAttribute('alt') || img.getAttribute('alt') === '') {
            img.setAttribute('alt', 'Prime EMS Studios');
        }

        // Lazy class'ını kaldır
        img.classList.remove('lazy');
    }

    // Lazy loading başlat
    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.optimizeImage(img);
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            this.lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            this.lazyImages.forEach(img => this.optimizeImage(img));
        }
    }

    // Tüm resimleri optimize et
    optimizeAllImages() {
        const allImages = document.querySelectorAll('img');
        allImages.forEach(img => {
            if (!img.classList.contains('optimized')) {
                this.optimizeImage(img);
                img.classList.add('optimized');
            }
        });
    }

    // Başlat
    init() {
        // Lazy loading başlat
        this.initLazyLoading();

        // Sayfa yüklendikten sonra tüm resimleri optimize et
        window.addEventListener('load', () => {
            this.optimizeAllImages();
        });

        // Performance metrics log
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    console.log('Page Load Performance:', {
                        domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
                        loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
                        totalTime: perfData.loadEventEnd - perfData.fetchStart
                    });
                }, 0);
            });
        }
    }
}

// Sayfa yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', () => {
    new ImageOptimizer();
});