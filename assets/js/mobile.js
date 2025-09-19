// Touch Gestures Handler
class TouchGestureHandler {
    constructor() {
        this.startX = 0;
        this.startY = 0;
        this.endX = 0;
        this.endY = 0;
        this.minDistance = 50;
        this.init();
    }
    
    init() {
        // Add touch event listeners
        document.addEventListener("touchstart", this.handleTouchStart.bind(this), { passive: false });
        document.addEventListener("touchmove", this.handleTouchMove.bind(this), { passive: false });
        document.addEventListener("touchend", this.handleTouchEnd.bind(this), { passive: false });
        
        // Initialize swipe containers
        this.initializeSwipeContainers();
        
        // Initialize pull to refresh
        this.initializePullToRefresh();
    }
    
    handleTouchStart(e) {
        this.startX = e.touches[0].clientX;
        this.startY = e.touches[0].clientY;
    }
    
    handleTouchMove(e) {
        if (!this.startX || !this.startY) return;
        
        this.endX = e.touches[0].clientX;
        this.endY = e.touches[0].clientY;
        
        // Prevent default scroll behavior for horizontal swipes
        const deltaX = Math.abs(this.endX - this.startX);
        const deltaY = Math.abs(this.endY - this.startY);
        
        if (deltaX > deltaY) {
            e.preventDefault();
        }
    }
    
    handleTouchEnd(e) {
        if (!this.startX || !this.startY) return;
        
        const deltaX = this.endX - this.startX;
        const deltaY = this.endY - this.startY;
        
        // Determine swipe direction
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            if (Math.abs(deltaX) > this.minDistance) {
                if (deltaX > 0) {
                    this.onSwipeRight(e);
                } else {
                    this.onSwipeLeft(e);
                }
            }
        } else {
            if (Math.abs(deltaY) > this.minDistance) {
                if (deltaY > 0) {
                    this.onSwipeDown(e);
                } else {
                    this.onSwipeUp(e);
                }
            }
        }
        
        // Reset values
        this.startX = 0;
        this.startY = 0;
        this.endX = 0;
        this.endY = 0;
    }
    
    onSwipeLeft(e) {
        // Handle left swipe - next slide/page
        const carousel = e.target.closest(".carousel");
        if (carousel) {
            const nextBtn = carousel.querySelector(".carousel-control-next");
            if (nextBtn) nextBtn.click();
        }
        
        // Custom event
        document.dispatchEvent(new CustomEvent("swipeLeft", { detail: e }));
    }
    
    onSwipeRight(e) {
        // Handle right swipe - previous slide/page
        const carousel = e.target.closest(".carousel");
        if (carousel) {
            const prevBtn = carousel.querySelector(".carousel-control-prev");
            if (prevBtn) prevBtn.click();
        }
        
        // Custom event
        document.dispatchEvent(new CustomEvent("swipeRight", { detail: e }));
    }
    
    onSwipeUp(e) {
        document.dispatchEvent(new CustomEvent("swipeUp", { detail: e }));
    }
    
    onSwipeDown(e) {
        // Pull to refresh check
        if (window.scrollY === 0) {
            this.triggerPullToRefresh();
        }
        document.dispatchEvent(new CustomEvent("swipeDown", { detail: e }));
    }
    
    initializeSwipeContainers() {
        const containers = document.querySelectorAll(".swipe-container");
        containers.forEach(container => {
            container.style.scrollBehavior = "smooth";
        });
    }
    
    initializePullToRefresh() {
        let isPulling = false;
        let pullDistance = 0;
        
        document.addEventListener("touchstart", (e) => {
            if (window.scrollY === 0) {
                isPulling = true;
                pullDistance = 0;
            }
        });
        
        document.addEventListener("touchmove", (e) => {
            if (isPulling && window.scrollY === 0) {
                pullDistance = e.touches[0].clientY;
                if (pullDistance > 60) {
                    document.body.classList.add("pulling");
                }
            }
        });
        
        document.addEventListener("touchend", (e) => {
            if (isPulling && pullDistance > 100) {
                this.triggerPullToRefresh();
            }
            isPulling = false;
            document.body.classList.remove("pulling");
        });
    }
    
    triggerPullToRefresh() {
        // Show loading indicator
        const loadingEl = document.createElement("div");
        loadingEl.className = "pull-refresh-loading";
        loadingEl.innerHTML = "Yenileniyor...";
        document.body.prepend(loadingEl);
        
        // Simulate refresh
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

// Mobile Menu Handler
class MobileMenuHandler {
    constructor() {
        this.isOpen = false;
        this.init();
    }
    
    init() {
        this.createMobileMenuButton();
        this.createMobileMenu();
        this.bindEvents();
    }
    
    createMobileMenuButton() {
        const header = document.querySelector("header");
        if (!header) return;
        
        const menuBtn = document.createElement("button");
        menuBtn.className = "mobile-menu-toggle d-md-none";
        menuBtn.innerHTML = `
            <span class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </span>
        `;
        header.appendChild(menuBtn);
    }
    
    createMobileMenu() {
        const existingNav = document.querySelector("nav ul");
        if (!existingNav) return;
        
        const mobileMenu = document.createElement("div");
        mobileMenu.className = "mobile-menu";
        mobileMenu.innerHTML = existingNav.outerHTML;
        document.body.appendChild(mobileMenu);
    }
    
    bindEvents() {
        const toggleBtn = document.querySelector(".mobile-menu-toggle");
        const mobileMenu = document.querySelector(".mobile-menu");
        
        if (toggleBtn && mobileMenu) {
            toggleBtn.addEventListener("click", () => {
                this.toggle();
            });
            
            // Close on link click
            mobileMenu.addEventListener("click", (e) => {
                if (e.target.tagName === "A") {
                    this.close();
                }
            });
            
            // Close on outside click
            document.addEventListener("click", (e) => {
                if (this.isOpen && !mobileMenu.contains(e.target) && !toggleBtn.contains(e.target)) {
                    this.close();
                }
            });
        }
    }
    
    toggle() {
        this.isOpen ? this.close() : this.open();
    }
    
    open() {
        document.querySelector(".mobile-menu").classList.add("active");
        document.body.style.overflow = "hidden";
        this.isOpen = true;
    }
    
    close() {
        document.querySelector(".mobile-menu").classList.remove("active");
        document.body.style.overflow = "";
        this.isOpen = false;
    }
}

// Initialize on DOM ready
document.addEventListener("DOMContentLoaded", () => {
    new TouchGestureHandler();
    new MobileMenuHandler();
});

// Service Worker Registration for PWA
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/sw.js")
            .then((registration) => {
                console.log("SW registered: ", registration);
            })
            .catch((registrationError) => {
                console.log("SW registration failed: ", registrationError);
            });
    });
}