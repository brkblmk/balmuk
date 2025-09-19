// CSS/JS Bundling and Minification Manager
class BundleManager {
    constructor() {
        this.bundles = {
            css: {
                critical: [
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
                    'assets/css/theme.css'
                ],
                nonCritical: [
                    'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                    'https://unpkg.com/aos@2.3.1/dist/aos.css'
                ]
            },
            js: {
                critical: [
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
                ],
                deferred: [
                    'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                    'https://unpkg.com/aos@2.3.1/dist/aos.js',
                    'assets/js/image-optimization.js',
                    'assets/js/lazy-loader.js'
                ]
            }
        };
        this.cache = new Map();
        this.init();
    }

    elementExists(tagName, attribute, value) {
        const elements = document.getElementsByTagName(tagName);
        for (let i = 0; i < elements.length; i += 1) {
            if (elements[i].getAttribute(attribute) === value) {
                return true;
            }
        }
        return false;
    }

    // Critical CSS'yi preload et
    preloadCriticalResources() {
        // Preload critical CSS
        this.bundles.css.critical.forEach(href => {
            if (href.includes('http')) {
                this.createPreloadLink(href, 'style');
            }
        });

        // Preload critical JS
        this.bundles.js.critical.forEach(href => {
            if (href.includes('http')) {
                this.createPreloadLink(href, 'script');
            }
        });
    }

    // Preload link oluştur
    createPreloadLink(href, as) {
        if (this.elementExists('link', 'href', href)) {
            return;
        }
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = as;
        link.href = href;
        link.crossOrigin = 'anonymous';
        document.head.appendChild(link);
    }

    // CSS bundle'larını yükle
    loadCSSBundles() {
        // Critical CSS'yi hemen yükle
        this.bundles.css.critical.forEach(href => {
            this.loadCSS(href, true);
        });

        // Non-critical CSS'yi defer ile yükle
        setTimeout(() => {
            this.bundles.css.nonCritical.forEach(href => {
                this.loadCSS(href, false);
            });
        }, 100);
    }

    // CSS yükleme
    loadCSS(href, isCritical = false) {
        if (this.elementExists('link', 'href', href)) {
            return;
        }
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;

        if (!isCritical) {
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
        }

        document.head.appendChild(link);
    }

    // JS bundle'larını yükle
    loadJSBundles() {
        // Critical JS'yi hemen yükle
        this.bundles.js.critical.forEach(src => {
            this.loadJS(src, true);
        });

        // Deferred JS'yi defer ile yükle
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                this.bundles.js.deferred.forEach(src => {
                    this.loadJS(src, false);
                });
            });
        } else {
            setTimeout(() => {
                this.bundles.js.deferred.forEach(src => {
                    this.loadJS(src, false);
                });
            }, 2000);
        }
    }

    // JS yükleme
    loadJS(src, isCritical = false) {
        if (this.elementExists('script', 'src', src)) {
            return;
        }
        const script = document.createElement('script');
        script.src = src;

        if (!isCritical) {
            script.defer = true;
        }

        document.head.appendChild(script);
    }

    // Resource hints ekle
    addResourceHints() {
        const hints = [
            // DNS prefetch
            { rel: 'dns-prefetch', href: '//cdn.jsdelivr.net' },
            { rel: 'dns-prefetch', href: '//unpkg.com' },
            { rel: 'dns-prefetch', href: '//fonts.googleapis.com' },
            { rel: 'dns-prefetch', href: '//fonts.gstatic.com' },

            // Preconnect
            { rel: 'preconnect', href: '//cdn.jsdelivr.net', crossorigin: true },
            { rel: 'preconnect', href: '//unpkg.com', crossorigin: true }
        ];

        hints.forEach(hint => {
            if (this.elementExists('link', 'href', hint.href)) {
                return;
            }
            const link = document.createElement('link');
            link.rel = hint.rel;
            link.href = hint.href;
            if (hint.crossorigin) {
                link.crossOrigin = 'anonymous';
            }
            document.head.appendChild(link);
        });
    }

    // Bundle performansını izle
    monitorPerformance() {
        if ('performance' in window && 'PerformanceObserver' in window) {
            // Resource timing observer
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.name.includes('.css') || entry.name.includes('.js')) {
                        console.log(`Resource: ${entry.name}, Load time: ${entry.responseEnd - entry.requestStart}ms`);
                    }
                });
            });

            observer.observe({ entryTypes: ['resource'] });

            // Store for cleanup
            this.performanceObserver = observer;
        }
    }

    // Cleanup
    destroy() {
        if (this.performanceObserver) {
            this.performanceObserver.disconnect();
        }
        this.cache.clear();
    }

    // Başlat
    init() {
        // Resource hints ekle
        this.addResourceHints();

        // Critical resources için preload
        this.preloadCriticalResources();

        // CSS bundle'larını yükle
        this.loadCSSBundles();

        // JS bundle'larını yükle
        this.loadJSBundles();

        // Performans monitoring
        this.monitorPerformance();
    }
}

// Sayfa yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', () => {
    window.bundleManager = new BundleManager();
});