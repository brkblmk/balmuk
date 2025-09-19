class PrimeAnimations {
    
    static observeElements() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("animate-in");
                }
            });
        }, {
            threshold: 0.1
        });
        
        document.querySelectorAll("[data-animate]").forEach(el => {
            observer.observe(el);
        });
    }
    
    static smoothScroll() {
        document.querySelectorAll("a[href^=\"#\"]").forEach(anchor => {
            anchor.addEventListener("click", function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute("href"));
                if (target) {
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            });
        });
    }
    
    static parallaxEffect() {
        window.addEventListener("scroll", () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            document.querySelectorAll("[data-parallax]").forEach(element => {
                element.style.transform = `translateY(${rate}px)`;
            });
        });
    }
    
    static init() {
        this.observeElements();
        this.smoothScroll();
        this.parallaxEffect();
        
        // Initialize when DOM is ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => this.init());
        }
    }
}

// Auto-initialize
PrimeAnimations.init();