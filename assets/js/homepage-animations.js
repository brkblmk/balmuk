// Homepage Modern Animations and Interactions
document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe all animatable elements
    document.querySelectorAll('.device-content, .service-card-new, .testimonial-item, .feature-item, .program-card').forEach(el => {
        observer.observe(el);
    });

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // Parallax effect for hero section
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            heroSection.style.transform = `translateY(${rate}px)`;
        });
    }

    // Counter animation for statistics
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target')) || parseInt(counter.textContent);
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current);
            }, 16);
        });
    }

    // Trigger counter animation when stats section is visible
    const statsSection = document.querySelector('#stats');
    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    statsObserver.unobserve(entry.target);
                }
            });
        });
        statsObserver.observe(statsSection);
    }

    // Enhanced service card hover effects
    document.querySelectorAll('.service-card-new').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
            this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.1)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        });
    });

    // Device image hover effects with 3D rotation
    document.querySelectorAll('.device-img').forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'perspective(1000px) rotateY(5deg) rotateX(5deg) scale(1.05)';
        });

        img.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) scale(1)';
        });
    });

    // Testimonial cards stagger animation
    document.querySelectorAll('.testimonial-item').forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });

    // Progress bars animation (if any exist)
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const progress = bar.getAttribute('data-progress') || '0';
        setTimeout(() => {
            bar.style.width = progress + '%';
        }, 500);
    });

    // Typing effect for hero title
    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle && heroTitle.hasAttribute('data-typing')) {
        const text = heroTitle.textContent;
        heroTitle.textContent = '';
        heroTitle.style.opacity = '1';
        
        let i = 0;
        const typing = setInterval(() => {
            if (i < text.length) {
                heroTitle.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(typing);
            }
        }, 100);
    }

    // Magnetic effect for buttons
    document.querySelectorAll('.btn-primary, .btn-outline-primary').forEach(button => {
        button.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            this.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(0px, 0px)';
        });
    });

    // Enhanced scroll to top button with progress indicator
    const scrollTopBtn = document.querySelector('.scroll-to-top');
    if (scrollTopBtn) {
        // Add progress circle
        const progressPath = document.createElement('svg');
        progressPath.innerHTML = `
            <circle cx="20" cy="20" r="15" fill="none" stroke="rgba(255,215,0,0.3)" stroke-width="2"/>
            <circle cx="20" cy="20" r="15" fill="none" stroke="#FFD700" stroke-width="2" 
                    stroke-dasharray="94.2" stroke-dashoffset="94.2" class="progress-circle"/>
        `;
        progressPath.style.position = 'absolute';
        progressPath.style.top = '0';
        progressPath.style.left = '0';
        progressPath.style.width = '40px';
        progressPath.style.height = '40px';
        scrollTopBtn.appendChild(progressPath);

        window.addEventListener('scroll', () => {
            const scrollTotal = document.documentElement.scrollHeight - window.innerHeight;
            const scrollProgress = (window.pageYOffset / scrollTotal) * 94.2;
            
            const progressCircle = document.querySelector('.progress-circle');
            if (progressCircle) {
                progressCircle.style.strokeDashoffset = 94.2 - scrollProgress;
            }
        });
    }

    // Add intersection observer for fade-in animations
    const fadeElements = document.querySelectorAll('.section-title, .section-subtitle, .feature-item');
    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    fadeElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        fadeObserver.observe(el);
    });

    // Enhanced device showcase animations
    document.querySelectorAll('.device-item').forEach((item, index) => {
        const isReverse = item.classList.contains('device-reverse');
        
        item.addEventListener('mouseenter', function() {
            const content = this.querySelector('.device-content');
            const image = this.querySelector('.device-image');
            
            if (content) {
                content.style.transform = isReverse ? 'translateX(10px)' : 'translateX(-10px)';
            }
            if (image) {
                image.style.transform = isReverse ? 'translateX(-10px)' : 'translateX(10px)';
            }
        });

        item.addEventListener('mouseleave', function() {
            const content = this.querySelector('.device-content');
            const image = this.querySelector('.device-image');
            
            if (content) {
                content.style.transform = 'translateX(0)';
            }
            if (image) {
                image.style.transform = 'translateX(0)';
            }
        });
    });

    // Add smooth transitions to all interactive elements
    const interactiveElements = document.querySelectorAll('.btn, .card, .service-card-new, .device-content, .testimonial-item');
    interactiveElements.forEach(el => {
        el.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });

    console.log('✨ Modern homepage animations initialized!');
});

// Add CSS classes for animations
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .navbar-scrolled {
        background: rgba(255, 255, 255, 0.95) !important;
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    }
    
    .device-content, .device-image {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .scroll-to-top {
        position: relative;
    }
    
    .progress-circle {
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
        transition: stroke-dashoffset 0.1s linear;
    }
`;
document.head.appendChild(style);