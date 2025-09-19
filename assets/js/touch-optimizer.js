class TouchOptimizer {
    
    static init() {
        this.setupMobileNav();
        this.optimizeTouchTargets();
        this.handleTouchEvents();
        this.setupSwipeGestures();
        this.setupAdvancedTouchEvents();
    }
    
    static setupMobileNav() {
        const toggle = document.querySelector(".mobile-nav-toggle");
        const nav = document.querySelector(".mobile-nav");
        const overlay = document.createElement("div");
        
        overlay.className = "mobile-nav-overlay";
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        `;
        
        document.body.appendChild(overlay);
        
        toggle?.addEventListener("click", () => {
            const isActive = nav.classList.contains("active");
            
            if (isActive) {
                nav.classList.remove("active");
                toggle.classList.remove("active");
                overlay.style.opacity = "0";
                overlay.style.visibility = "hidden";
                document.body.style.overflow = "";
            } else {
                nav.classList.add("active");
                toggle.classList.add("active");
                overlay.style.opacity = "1";
                overlay.style.visibility = "visible";
                document.body.style.overflow = "hidden";
            }
        });
        
        overlay.addEventListener("click", () => {
            toggle.click();
        });
        
        // Close nav on link click
        document.querySelectorAll(".mobile-nav-item").forEach(link => {
            link.addEventListener("click", () => {
                if (nav.classList.contains("active")) {
                    toggle.click();
                }
            });
        });
    }
    
    static optimizeTouchTargets() {
        // Ensure all interactive elements meet touch target requirements
        const touchTargets = document.querySelectorAll("button, a, input, select, textarea, .btn, [role='button']");

        touchTargets.forEach(element => {
            const rect = element.getBoundingClientRect();

            // Minimum 44px x 44px for all interactive elements
            if (rect.width < 44) {
                element.style.minWidth = "44px";
            }
            if (rect.height < 44) {
                element.style.minHeight = "44px";
            }

            // Links get minimum 48px height for better accessibility
            if (element.tagName === 'A' && rect.height < 48) {
                element.style.minHeight = "48px";
            }

            // Increase padding for smaller elements
            const padding = window.getComputedStyle(element).paddingTop;
            if (parseFloat(padding) < 8) {
                element.style.paddingTop = "8px";
                element.style.paddingBottom = "8px";
            }

            // Ensure proper display for centering content
            if (getComputedStyle(element).display === 'inline') {
                element.style.display = "inline-block";
            }
            element.style.textDecoration = "none";
        });
    }
    
    static handleTouchEvents() {
        // Add touch feedback
        document.addEventListener("touchstart", (e) => {
            if (e.target.matches("button, .btn, .card, a, .mobile-button, .mobile-card, .mobile-nav-item")) {
                e.target.style.transform = "scale(0.98)";
                e.target.style.opacity = "0.8";

                // Add ripple effect
                this.createRippleEffect(e);
            }
        }, { passive: true });

        document.addEventListener("touchend", (e) => {
            if (e.target.matches("button, .btn, .card, a, .mobile-button, .mobile-card, .mobile-nav-item")) {
                setTimeout(() => {
                    e.target.style.transform = "";
                    e.target.style.opacity = "";
                }, 150);
            }
        }, { passive: true });
    }

    static createRippleEffect(event) {
        const element = event.target;
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.touches[0].clientX - rect.left - size / 2;
        const y = event.touches[0].clientY - rect.top - size / 2;

        const ripple = document.createElement("span");
        ripple.className = "ripple-effect";
        ripple.style.width = ripple.style.height = size + "px";
        ripple.style.left = x + "px";
        ripple.style.top = y + "px";

        // Remove existing ripples
        const existingRipple = element.querySelector(".ripple-effect");
        if (existingRipple) {
            existingRipple.remove();
        }

        element.appendChild(ripple);

        // Remove ripple after animation
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
    
    static setupSwipeGestures() {
        let touchStartX = 0;
        let touchStartY = 0;
        let touchEndX = 0;
        let touchEndY = 0;
        let lastTap = 0;

        document.addEventListener("touchstart", (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;

            // Double tap detection
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            if (tapLength < 500 && tapLength > 0) {
                this.handleDoubleTap(e);
            }
            lastTap = currentTime;
        }, { passive: true });

        document.addEventListener("touchend", (e) => {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            this.handleSwipe();
        }, { passive: true });

        // Pinch-to-zoom support
        let initialDistance = 0;
        let initialScale = 1;

        document.addEventListener("touchstart", (e) => {
            if (e.touches.length === 2) {
                initialDistance = this.getDistance(e.touches[0], e.touches[1]);
                initialScale = document.body.style.zoom || 1;
            }
        }, { passive: true });

        document.addEventListener("touchmove", (e) => {
            if (e.touches.length === 2) {
                e.preventDefault();
                const currentDistance = this.getDistance(e.touches[0], e.touches[1]);
                const scale = (currentDistance / initialDistance) * initialScale;
                this.handlePinchZoom(scale);
            }
        }, { passive: false });
    }
    
    static handleSwipe() {
        const swipeThreshold = 100;
        const nav = document.querySelector(".mobile-nav");
        const toggle = document.querySelector(".mobile-nav-toggle");

        // Swipe right to open nav
        if (touchEndX > touchStartX + swipeThreshold) {
            if (!nav.classList.contains("active")) {
                toggle?.click();
            }
        }

        // Swipe left to close nav
        if (touchStartX > touchEndX + swipeThreshold) {
            if (nav.classList.contains("active")) {
                toggle?.click();
            }
        }
    }

    static handleDoubleTap(event) {
        const target = event.target;
        if (target.matches("img, .zoomable")) {
            // Toggle zoom on double tap for images
            const currentScale = target.style.transform || "scale(1)";
            const newScale = currentScale.includes("scale(2)") ? "scale(1)" : "scale(2)";
            target.style.transform = newScale;
            target.style.transformOrigin = "center center";
            target.style.transition = "transform 0.3s ease";
        }
    }

    static handlePinchZoom(scale) {
        // Apply zoom to zoomable content
        const zoomableContent = document.querySelector(".zoomable-content") || document.body;
        const clampedScale = Math.min(Math.max(scale, 0.5), 3); // Limit zoom between 0.5x and 3x
        zoomableContent.style.transform = `scale(${clampedScale})`;
        zoomableContent.style.transformOrigin = "center center";
        zoomableContent.style.transition = "transform 0.1s ease";
    }

    static getDistance(touch1, touch2) {
        return Math.sqrt(
            Math.pow(touch2.clientX - touch1.clientX, 2) +
            Math.pow(touch2.clientY - touch1.clientY, 2)
        );
    }

    static setupAdvancedTouchEvents() {
        // Long press detection
        let longPressTimer;
        let touchStartTime = 0;
        let longPressTarget = null;

        document.addEventListener("touchstart", (e) => {
            touchStartTime = Date.now();
            longPressTarget = e.target;

            longPressTimer = setTimeout(() => {
                this.handleLongPress(e);
            }, 500); // 500ms for long press
        }, { passive: true });

        document.addEventListener("touchend", (e) => {
            clearTimeout(longPressTimer);

            const touchDuration = Date.now() - touchStartTime;
            if (touchDuration < 500 && longPressTarget === e.target) {
                // Short tap
                this.handleShortTap(e);
            }
        }, { passive: true });

        document.addEventListener("touchmove", (e) => {
            // Cancel long press if finger moves
            if (longPressTimer) {
                clearTimeout(longPressTimer);
                longPressTimer = null;
            }
        }, { passive: true });

        // Multi-touch gesture detection
        let multiTouchStart = null;

        document.addEventListener("touchstart", (e) => {
            if (e.touches.length >= 2) {
                multiTouchStart = {
                    touches: Array.from(e.touches),
                    time: Date.now()
                };
            }
        }, { passive: true });

        document.addEventListener("touchend", (e) => {
            if (multiTouchStart && e.touches.length === 0) {
                const duration = Date.now() - multiTouchStart.time;
                if (duration < 300) { // Quick multi-touch release
                    this.handleMultiTouchTap(multiTouchStart.touches.length);
                }
                multiTouchStart = null;
            }
        }, { passive: true });

        // Swipe with velocity detection
        let swipeStartTime = 0;
        let swipeStartPos = { x: 0, y: 0 };

        document.addEventListener("touchstart", (e) => {
            swipeStartTime = Date.now();
            swipeStartPos = {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY
            };
        }, { passive: true });

        document.addEventListener("touchend", (e) => {
            const swipeEndTime = Date.now();
            const swipeEndPos = {
                x: e.changedTouches[0].clientX,
                y: e.changedTouches[0].clientY
            };

            const deltaX = swipeEndPos.x - swipeStartPos.x;
            const deltaY = swipeEndPos.y - swipeStartPos.y;
            const deltaTime = swipeEndTime - swipeStartTime;

            if (deltaTime < 500) { // Fast swipe
                const velocity = Math.sqrt(deltaX * deltaX + deltaY * deltaY) / deltaTime;
                this.handleFastSwipe(deltaX, deltaY, velocity);
            }
        }, { passive: true });
    }

    static handleLongPress(event) {
        const target = event.target;
        if (target.matches("button, .btn, .card, img")) {
            // Vibrate if supported
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }

            // Add visual feedback
            target.style.transform = "scale(0.95)";
            target.style.boxShadow = "inset 0 0 10px rgba(0,0,0,0.3)";

            // Trigger custom event
            const longPressEvent = new CustomEvent('longpress', {
                detail: { target, originalEvent: event }
            });
            target.dispatchEvent(longPressEvent);
        }
    }

    static handleShortTap(event) {
        const target = event.target;
        // Add subtle feedback for short taps
        if (target.matches("button, .btn, a")) {
            target.style.transform = "scale(0.98)";
            setTimeout(() => {
                target.style.transform = "";
            }, 100);
        }
    }

    static handleMultiTouchTap(touchCount) {
        // Handle different multi-touch gestures
        switch (touchCount) {
            case 2:
                // Two-finger tap - could zoom out or show context menu
                this.handleTwoFingerTap();
                break;
            case 3:
                // Three-finger tap - could show navigation or shortcuts
                this.handleThreeFingerTap();
                break;
        }
    }

    static handleTwoFingerTap() {
        // Zoom out or reset zoom
        const zoomableContent = document.querySelector(".zoomable-content") || document.body;
        zoomableContent.style.transform = "scale(1)";
        zoomableContent.style.transformOrigin = "center center";
    }

    static handleThreeFingerTap() {
        // Could show/hide navigation or trigger other actions
        const nav = document.querySelector(".mobile-nav");
        if (nav) {
            nav.classList.toggle("active");
        }
    }

    static handleFastSwipe(deltaX, deltaY, velocity) {
        if (velocity > 0.5) { // Threshold for fast swipe
            if (Math.abs(deltaX) > Math.abs(deltaY)) {
                // Horizontal fast swipe
                if (deltaX > 0) {
                    this.handleSwipeRightFast();
                } else {
                    this.handleSwipeLeftFast();
                }
            } else {
                // Vertical fast swipe
                if (deltaY > 0) {
                    this.handleSwipeDownFast();
                } else {
                    this.handleSwipeUpFast();
                }
            }
        }
    }

    static handleSwipeRightFast() {
        // Fast swipe right - could go to previous page or slide
        const carousel = document.querySelector(".carousel-container");
        if (carousel) {
            carousel.scrollBy({ left: -window.innerWidth / 2, behavior: 'smooth' });
        }
    }

    static handleSwipeLeftFast() {
        // Fast swipe left - could go to next page or slide
        const carousel = document.querySelector(".carousel-container");
        if (carousel) {
            carousel.scrollBy({ left: window.innerWidth / 2, behavior: 'smooth' });
        }
    }

    static handleSwipeUpFast() {
        // Fast swipe up - could scroll to top or close modal
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    static handleSwipeDownFast() {
        // Fast swipe down - could refresh or open menu
        if (window.scrollY === 0) {
            location.reload(); // Pull to refresh simulation
        }
    }
}

// Initialize touch optimizations
document.addEventListener("DOMContentLoaded", () => {
    TouchOptimizer.init();
});