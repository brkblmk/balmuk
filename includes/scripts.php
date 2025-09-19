    <!-- Performance Bundle Manager -->
    <script src="assets/js/bundle-manager.js"></script>

    <!-- Inline Critical Scripts -->
    <script>
        // Critical path JavaScript - blocking but essential
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS immediately for critical content
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 1000,
                    once: true,
                    offset: 100
                });
            }

            // Mobile optimizations
            if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
                navigator.serviceWorker.register('/sw.js');
            }

            // Touch device optimizations
            if ('ontouchstart' in window) {
                document.body.classList.add('touch-device');
            }

            // Reduce motion for accessibility
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.style.setProperty('--animation-duration', '0.01ms');
            }
        });

        // Performance observer for Core Web Vitals
        if ('PerformanceObserver' in window) {
            try {
                new PerformanceObserver(function(list) {
                    list.getEntries().forEach(function(entry) {
                        if (entry.entryType === 'measure') {
                            console.log('Core Web Vital:', entry.name, entry.duration + 'ms');
                        }
                    });
                }).observe({ entryTypes: ['measure'] });
            } catch (e) {
                console.log('Performance observer not supported');
            }
        }
    </script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Show sticky CTA and scroll button after scroll
        window.addEventListener('scroll', function() {
            const stickyCTA = document.getElementById('stickyCTA');
            const scrollBtn = document.getElementById('scrollToTop');

            if (window.scrollY > 500) {
                stickyCTA.classList.add('show');
            } else {
                stickyCTA.classList.remove('show');
            }

            // Show/hide scroll to top button
            if (window.scrollY > 300) {
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.remove('show');
            }
        });

        // Smooth scroll for anchor links
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

        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Chatbot functions
        function startChat() {
            // Chatbot sayfasını popup olarak aç
            const width = 500;
            const height = 700;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;

            window.open(
                'chatbot.php',
                'PrimeEMSChatbot',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=no`
            );
        }

        function hideBubble() {
            const bubble = document.getElementById('chatbotBubble');
            bubble.style.display = 'none';

            // Bubble'ı 30 saniye sonra tekrar göster
            setTimeout(() => {
                bubble.style.display = 'flex';
            }, 30000);
        }

        // Bubble'ı sayfa yüklendikten 5 saniye sonra göster
        window.addEventListener('load', function() {
            setTimeout(() => {
                const bubble = document.getElementById('chatbotBubble');
                if (bubble) {
                    bubble.style.display = 'flex';

                    // 10 saniye sonra otomatik gizle
                    setTimeout(() => {
                        if (!sessionStorage.getItem('bubbleHidden')) {
                            bubble.style.animation = 'fadeOut 0.5s ease';
                            setTimeout(() => {
                                bubble.style.display = 'none';
                                sessionStorage.setItem('bubbleHidden', 'true');
                            }, 500);
                        }
                    }, 10000);
                }
            }, 5000);
        });

        // Chatbot bubble click event
        document.getElementById('chatbotBubble').addEventListener('click', function(e) {
            if (!e.target.classList.contains('bubble-close')) {
                startChat();
            }
        });
    </script>