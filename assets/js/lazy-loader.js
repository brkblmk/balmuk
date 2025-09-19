/**
 * Prime EMS Lazy Loading System
 * Optimized image loading with performance monitoring
 */

class LazyLoader {
    constructor(options = {}) {
        this.options = {
            rootMargin: '50px 0px',
            threshold: 0.1,
            placeholder: '/assets/images/placeholder.svg',
            ...options
        };

        this.observer = null;
        this.images = new Map();
        this.loadedImages = new Set();
        this.loadingImages = new Set();

        this.init();
    }

    init() {
        // Create Intersection Observer
        this.observer = new IntersectionObserver(
            this.handleIntersection.bind(this),
            {
                rootMargin: this.options.rootMargin,
                threshold: this.options.threshold
            }
        );

        // Observe all lazy images
        this.observeImages();

        // Performance monitoring
        this.setupPerformanceMonitoring();
    }

    observeImages() {
        // Find all images with data-src attribute
        const lazyImages = document.querySelectorAll('img[data-src]');

        lazyImages.forEach(img => {
            // Skip if already processed
            if (this.images.has(img)) return;

            // Store original attributes
            const originalSrc = img.getAttribute('data-src');
            const originalSrcset = img.getAttribute('data-srcset');

            this.images.set(img, {
                src: originalSrc,
                srcset: originalSrcset,
                loaded: false,
                attempted: false
            });

            // Set placeholder
            if (this.options.placeholder && !img.src) {
                img.src = this.options.placeholder;
                img.classList.add('lazy-placeholder');
            }

            // Add loading class
            img.classList.add('lazy-loading');

            // Start observing
            this.observer.observe(img);
        });
    }

    handleIntersection(entries) {
        entries.forEach(entry => {
            const img = entry.target;

            if (entry.isIntersecting && !this.loadedImages.has(img)) {
                this.loadImage(img);
            }
        });
    }

    async loadImage(img) {
        if (this.loadingImages.has(img) || this.loadedImages.has(img)) return;

        const imageData = this.images.get(img);
        if (!imageData || imageData.attempted) return;

        this.loadingImages.add(img);
        imageData.attempted = true;

        try {
            // Create new image to preload
            const preloadImg = new Image();

            // Set up load event
            preloadImg.onload = () => {
                // Set actual source
                img.src = imageData.src;
                if (imageData.srcset) {
                    img.srcset = imageData.srcset;
                }

                // Update classes
                img.classList.remove('lazy-loading', 'lazy-placeholder');
                img.classList.add('lazy-loaded');

                // Mark as loaded
                this.loadedImages.add(img);
                this.loadingImages.delete(img);
                imageData.loaded = true;

                // Remove data attributes
                img.removeAttribute('data-src');
                img.removeAttribute('data-srcset');

                // Trigger custom event
                img.dispatchEvent(new CustomEvent('lazyloaded', {
                    detail: { src: imageData.src }
                }));

                // Performance tracking
                this.trackPerformance(img, 'success');
            };

            preloadImg.onerror = () => {
                // Handle error
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-error');

                this.loadingImages.delete(img);

                // Fallback to placeholder or regular src
                if (img.hasAttribute('src')) {
                    // Keep original src if available
                } else {
                    img.src = '/assets/images/error-placeholder.png';
                }

                // Trigger error event
                img.dispatchEvent(new CustomEvent('lazyerror', {
                    detail: { src: imageData.src }
                }));

                // Performance tracking
                this.trackPerformance(img, 'error');
            };

            // Start loading
            preloadImg.src = imageData.src;
            if (imageData.srcset) {
                preloadImg.srcset = imageData.srcset;
            }

        } catch (error) {
            console.error('Lazy loading error:', error);
            this.loadingImages.delete(img);
            this.trackPerformance(img, 'error');
        }
    }

    setupPerformanceMonitoring() {
        // Track loading performance
        this.performanceData = {
            totalImages: 0,
            loadedImages: 0,
            failedImages: 0,
            averageLoadTime: 0,
            loadTimes: []
        };

        // Listen for performance events
        document.addEventListener('lazyloaded', (e) => {
            this.performanceData.loadedImages++;
        });

        document.addEventListener('lazyerror', (e) => {
            this.performanceData.failedImages++;
        });
    }

    trackPerformance(img, status) {
        const loadTime = performance.now();

        this.performanceData.loadTimes.push({
            src: img.src,
            status: status,
            time: loadTime,
            userAgent: navigator.userAgent,
            connection: navigator.connection ? navigator.connection.effectiveType : 'unknown'
        });
    }

    // Get performance report
    getPerformanceReport() {
        return {
            ...this.performanceData,
            totalImages: this.images.size,
            successRate: this.images.size > 0 ?
                (this.performanceData.loadedImages / this.images.size * 100).toFixed(2) + '%' : '0%'
        };
    }

    // Force load all images (useful for print media)
    loadAllImages() {
        this.images.forEach((data, img) => {
            if (!this.loadedImages.has(img)) {
                this.loadImage(img);
            }
        });
    }

    // Disconnect observer
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        this.images.clear();
        this.loadedImages.clear();
        this.loadingImages.clear();
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize lazy loading
    window.lazyLoader = new LazyLoader();

    // Add print media support
    if (window.matchMedia) {
        const printMediaQuery = window.matchMedia('print');
        printMediaQuery.addEventListener('change', (e) => {
            if (e.matches) {
                // Load all images for print
                window.lazyLoader.loadAllImages();
            }
        });
    }

    // Performance logging (development only)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        setTimeout(() => {
            console.log('Lazy Loading Performance Report:', window.lazyLoader.getPerformanceReport());
        }, 5000);
    }
});

// Utility function to convert regular images to lazy images
window.makeLazy = function(selector = 'img') {
    const images = document.querySelectorAll(selector);

    images.forEach(img => {
        if (!img.hasAttribute('data-src') && img.src && !img.classList.contains('lazy-loaded')) {
            img.setAttribute('data-src', img.src);
            img.removeAttribute('src');
            img.classList.add('lazy-loading');
        }
    });

    // Re-observe new images
    if (window.lazyLoader) {
        window.lazyLoader.observeImages();
    }
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LazyLoader;
}