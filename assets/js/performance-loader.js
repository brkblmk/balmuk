/**
 * Prime EMS Performance Loader
 * Advanced lazy loading and performance optimization utilities
 */

class PerformanceLoader {
    constructor() {
        this.observers = new Map();
        this.loadedImages = new Set();
        this.loadedSections = new Set();
        this.isOnline = navigator.onLine;
        this.connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        
        this.init();
    }

    init() {
        this.setupIntersectionObserver();
        this.setupImageLazyLoading();
        this.setupContentLazyLoading();
        this.setupConnectionMonitoring();
        this.setupPerformanceMonitoring();
        this.setupCriticalResourcePreloading();
        this.deferNonCriticalCSS();
        this.setupCodeSplitting();
        this.loadCriticalJS();
        this.deferNonCriticalScripts();

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.onDOMReady());
        } else {
            this.onDOMReady();
        }
    }

    onDOMReady() {
        this.optimizeImages();
        this.optimizeForms();
        this.setupVirtualScrolling();
        this.preloadCriticalResources();
    }

    setupIntersectionObserver() {
        // Image lazy loading observer
        this.observers.set('images', new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.observers.get('images').unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        }));

        // Content lazy loading observer
        this.observers.set('content', new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadContent(entry.target);
                    this.observers.get('content').unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '100px 0px',
            threshold: 0.01
        }));

        // Animation trigger observer
        this.observers.set('animations', new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('io-visible');
                    entry.target.classList.remove('io-hidden');
                }
            });
        }, {
            rootMargin: '20px 0px',
            threshold: 0.1
        }));
    }

    setupImageLazyLoading() {
        // Find all lazy images
        const lazyImages = document.querySelectorAll('img[data-src], img[loading="lazy"]');
        
        lazyImages.forEach(img => {
            // Add placeholder
            if (!img.src && !img.dataset.src) return;
            
            // Add loading placeholder
            img.classList.add('lazy');
            
            // Observe for intersection
            this.observers.get('images').observe(img);
        });
    }

    setupContentLazyLoading() {
        // Find all lazy content sections
        const lazyContent = document.querySelectorAll('[data-lazy-content]');
        
        lazyContent.forEach(section => {
            this.observers.get('content').observe(section);
        });

        // Setup animation triggers
        const animationElements = document.querySelectorAll('.io-hidden');
        animationElements.forEach(el => {
            this.observers.get('animations').observe(el);
        });
    }

    loadImage(img) {
        return new Promise((resolve, reject) => {
            const imageUrl = img.dataset.src || img.src;
            
            if (this.loadedImages.has(imageUrl)) {
                resolve();
                return;
            }

            // Create a new image to preload
            const tempImg = new Image();
            
            tempImg.onload = () => {
                // Apply the source
                img.src = imageUrl;
                img.classList.remove('lazy');
                img.classList.add('lazy-loaded');
                
                // Mark as loaded
                this.loadedImages.add(imageUrl);
                
                // Remove data-src
                if (img.dataset.src) {
                    delete img.dataset.src;
                }

                resolve();
            };

            tempImg.onerror = () => {
                console.error('Failed to load image:', imageUrl);
                img.classList.add('lazy-error');
                reject(new Error(`Failed to load image: ${imageUrl}`));
            };

            // Start loading
            tempImg.src = imageUrl;
        });
    }

    loadContent(section) {
        const contentType = section.dataset.lazyContent;
        const contentUrl = section.dataset.contentUrl;

        if (this.loadedSections.has(section)) return;

        switch (contentType) {
            case 'iframe':
                this.loadIframe(section);
                break;
            case 'ajax':
                this.loadAjaxContent(section, contentUrl);
                break;
            case 'script':
                this.loadScript(section.dataset.scriptSrc);
                break;
            default:
                section.style.display = 'block';
        }

        this.loadedSections.add(section);
    }

    loadIframe(container) {
        const iframe = container.querySelector('iframe[data-src]');
        if (iframe) {
            iframe.src = iframe.dataset.src;
            delete iframe.dataset.src;
        }
    }

    async loadAjaxContent(container, url) {
        try {
            container.innerHTML = '<div class="loading-spinner"></div>';
            
            const response = await fetch(url);
            const html = await response.text();
            
            container.innerHTML = html;
        } catch (error) {
            console.error('Failed to load content:', error);
            container.innerHTML = '<div class="error-message">Content could not be loaded.</div>';
        }
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;

            script.onload = resolve;
            script.onerror = reject;

            document.head.appendChild(script);
        });
    }

    // Code Splitting and Dynamic Loading
    async loadModule(moduleName, chunkName) {
        try {
            const module = await import(`./${moduleName}.js`);
            return module;
        } catch (error) {
            console.error(`Failed to load module ${moduleName}:`, error);
            throw error;
        }
    }

    // Lazy load heavy components
    setupCodeSplitting() {
        // Load non-critical modules on user interaction
        const loadHeavyComponents = () => {
            this.loadModule('heavy-components', 'heavy')
                .then(module => {
                    if (module.initHeavyComponents) {
                        module.initHeavyComponents();
                    }
                })
                .catch(err => console.warn('Heavy components not available'));
        };

        // Load on scroll or user interaction
        let loaded = false;
        const triggerLoad = () => {
            if (!loaded) {
                loaded = true;
                loadHeavyComponents();
            }
        };

        window.addEventListener('scroll', triggerLoad, { once: true, passive: true });
        window.addEventListener('click', triggerLoad, { once: true });
        window.addEventListener('touchstart', triggerLoad, { once: true });
    }

    // Critical JS Inline Loading
    loadCriticalJS() {
        const criticalJS = `
            // Critical inline JavaScript for performance
            (function() {
                // Fast click for mobile
                if ('addEventListener' in document) {
                    document.addEventListener('DOMContentLoaded', function() {
                        FastClick.attach(document.body);
                    }, false);
                }

                // Critical resource hints
                const hints = [
                    { rel: 'preconnect', href: '//fonts.googleapis.com' },
                    { rel: 'preconnect', href: '//fonts.gstatic.com', crossorigin: true },
                    { rel: 'dns-prefetch', href: '//cdn.jsdelivr.net' }
                ];

                hints.forEach(hint => {
                    const link = document.createElement('link');
                    Object.assign(link, hint);
                    document.head.appendChild(link);
                });
            })();
        `;

        const script = document.createElement('script');
        script.text = criticalJS;
        document.head.appendChild(script);
    }

    // Defer non-critical scripts
    deferNonCriticalScripts() {
        const nonCriticalScripts = [
            '/assets/js/analytics.js',
            '/assets/js/notifications.js'
        ];

        nonCriticalScripts.forEach(src => {
            const script = document.createElement('script');
            script.src = src;
            script.defer = true;
            script.setAttribute('data-defer', 'true');
            document.body.appendChild(script);
        });
    }

    setupConnectionMonitoring() {
        // Monitor network connection
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.handleConnectionChange();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.handleConnectionChange();
        });

        // Monitor connection quality
        if (this.connection) {
            this.connection.addEventListener('change', () => {
                this.handleConnectionChange();
            });
        }
    }

    handleConnectionChange() {
        const body = document.body;
        
        if (!this.isOnline) {
            body.classList.add('offline');
            this.showOfflineMessage();
        } else {
            body.classList.remove('offline');
            this.hideOfflineMessage();
            
            // Adapt loading strategy based on connection
            this.adaptLoadingStrategy();
        }
    }

    adaptLoadingStrategy() {
        if (!this.connection) return;

        const connection = this.connection;
        const isSlowConnection = connection.saveData || 
                               connection.effectiveType === 'slow-2g' || 
                               connection.effectiveType === '2g';

        if (isSlowConnection) {
            // Disable non-essential animations and effects
            document.body.classList.add('reduced-motion');
            
            // Load only critical images
            this.loadOnlyCriticalImages();
        } else {
            document.body.classList.remove('reduced-motion');
        }
    }

    loadOnlyCriticalImages() {
        const criticalImages = document.querySelectorAll('img[data-critical="true"]');
        criticalImages.forEach(img => this.loadImage(img));
    }

    setupPerformanceMonitoring() {
        // Monitor Web Vitals
        this.observeWebVitals();
        
        // Monitor long tasks
        if ('PerformanceObserver' in window) {
            const longTaskObserver = new PerformanceObserver((entries) => {
                entries.getEntries().forEach(entry => {
                    if (entry.duration > 50) {
                        console.warn('Long task detected:', entry.duration, 'ms');
                    }
                });
            });

            try {
                longTaskObserver.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                console.warn('Long task monitoring not supported');
            }
        }
    }

    observeWebVitals() {
        // Largest Contentful Paint
        if ('PerformanceObserver' in window) {
            try {
                const lcpObserver = new PerformanceObserver((entries) => {
                    const lcp = entries.getEntries().pop();
                    console.log('LCP:', lcp.startTime);
                });
                lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
            } catch (e) {
                console.warn('LCP monitoring not supported');
            }
        }

        // First Input Delay
        if ('PerformanceEventTiming' in window) {
            const fidObserver = new PerformanceObserver((entries) => {
                entries.getEntries().forEach(entry => {
                    const fid = entry.processingStart - entry.startTime;
                    console.log('FID:', fid);
                });
            });

            try {
                fidObserver.observe({ entryTypes: ['first-input'] });
            } catch (e) {
                console.warn('FID monitoring not supported');
            }
        }
    }

    setupCriticalResourcePreloading() {
        // Preload critical fonts
        const criticalFonts = [
            '/assets/fonts/inter.woff2',
            '/assets/fonts/poppins.woff2'
        ];

        criticalFonts.forEach(font => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'font';
            link.type = 'font/woff2';
            link.crossOrigin = 'anonymous';
            link.href = font;
            document.head.appendChild(link);
        });
    }

    deferNonCriticalCSS() {
        const nonCriticalCSS = [
            '/assets/css/animations.css',
            '/assets/css/print.css'
        ];

        nonCriticalCSS.forEach(css => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = css;
            link.media = 'print';
            
            link.onload = function() {
                this.media = 'all';
            };
            
            document.head.appendChild(link);
        });
    }

    optimizeImages() {
        // Add responsive image loading
        const images = document.querySelectorAll('img:not([data-optimized])');
        
        images.forEach(img => {
            // Add responsive classes
            img.classList.add('responsive-img');
            
            // Add loading optimization
            if (!img.loading) {
                img.loading = 'lazy';
            }

            // Mark as optimized
            img.dataset.optimized = 'true';
        });
    }

    optimizeForms() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.classList.add('fast-input');
                
                // Debounce validation
                let validationTimeout;
                input.addEventListener('input', () => {
                    clearTimeout(validationTimeout);
                    validationTimeout = setTimeout(() => {
                        this.validateField(input);
                    }, 300);
                });
            });
        });
    }

    validateField(field) {
        // Fast field validation
        if (field.checkValidity()) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    }

    setupVirtualScrolling() {
        const virtualContainers = document.querySelectorAll('.virtual-scroll');
        
        virtualContainers.forEach(container => {
            this.initVirtualScroll(container);
        });
    }

    initVirtualScroll(container) {
        const items = Array.from(container.children);
        const itemHeight = 50; // Adjust based on your content
        const containerHeight = container.clientHeight;
        const visibleItems = Math.ceil(containerHeight / itemHeight) + 2;

        let scrollTop = 0;
        let startIndex = 0;

        const updateVirtualScroll = () => {
            startIndex = Math.floor(scrollTop / itemHeight);
            const endIndex = Math.min(startIndex + visibleItems, items.length);

            // Hide all items
            items.forEach((item, index) => {
                if (index >= startIndex && index < endIndex) {
                    item.style.display = 'block';
                    item.style.transform = `translateY(${index * itemHeight}px)`;
                } else {
                    item.style.display = 'none';
                }
            });
        };

        container.addEventListener('scroll', () => {
            scrollTop = container.scrollTop;
            requestAnimationFrame(updateVirtualScroll);
        });

        // Initial render
        updateVirtualScroll();
    }

    preloadCriticalResources() {
        // Preload next page resources based on user behavior
        const criticalLinks = document.querySelectorAll('a[data-preload]');
        
        criticalLinks.forEach(link => {
            link.addEventListener('mouseenter', () => {
                this.preloadPage(link.href);
            }, { once: true });
        });
    }

    preloadPage(url) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    }

    showOfflineMessage() {
        if (!document.getElementById('offline-message')) {
            const message = document.createElement('div');
            message.id = 'offline-message';
            message.className = 'alert alert-warning position-fixed top-0 start-50 translate-middle-x mt-3';
            message.style.zIndex = '9999';
            message.innerHTML = `
                <i class="bi bi-wifi-off me-2"></i>
                Bağlantı kesildi. Bazı özellikler çalışmayabilir.
            `;
            document.body.appendChild(message);
        }
    }

    hideOfflineMessage() {
        const message = document.getElementById('offline-message');
        if (message) {
            message.remove();
        }
    }

    // Public API methods
    loadAllImages() {
        const lazyImages = document.querySelectorAll('img[data-src], img.lazy');
        lazyImages.forEach(img => this.loadImage(img));
    }

    destroy() {
        // Clean up observers
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        
        // Clear caches
        this.loadedImages.clear();
        this.loadedSections.clear();
    }

    // Get performance metrics
    getPerformanceMetrics() {
        const navigation = performance.getEntriesByType('navigation')[0];
        
        return {
            domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
            loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
            firstPaint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-paint')?.startTime || 0,
            firstContentfulPaint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-contentful-paint')?.startTime || 0
        };
    }
}

// Initialize Performance Loader
window.performanceLoader = new PerformanceLoader();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.performanceLoader) {
        window.performanceLoader.destroy();
    }
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceLoader;
}