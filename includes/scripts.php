    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Performance Bundle Manager -->
    <script src="assets/js/bundle-manager.js"></script>

    <script>
        (function () {
            const doc = document;
            const root = doc.documentElement;

            window.scrollToTop = function scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            window.startChat = function startChat() {
                const width = 500;
                const height = 700;
                const left = (window.screen.width - width) / 2;
                const top = (window.screen.height - height) / 2;

                window.open(
                    'chatbot.php',
                    'PrimeEMSChatbot',
                    `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=no`
                );
            };

            window.hideBubble = function hideBubble() {
                const bubble = doc.getElementById('chatbotBubble');
                if (!bubble) {
                    return;
                }

                bubble.style.display = 'none';
                setTimeout(() => {
                    const target = doc.getElementById('chatbotBubble');
                    if (target) {
                        target.style.display = 'flex';
                    }
                }, 30000);
            };

            function initAOS() {
                if (window.AOS && typeof window.AOS.init === 'function') {
                    window.AOS.init({ duration: 1000, once: true, offset: 100 });
                }
            }

            function registerScrollHandlers() {
                const stickyCTA = doc.getElementById('stickyCTA');
                const scrollBtn = doc.getElementById('scrollToTop');

                if (!stickyCTA && !scrollBtn) {
                    return;
                }

                window.addEventListener('scroll', () => {
                    const scrollY = window.scrollY || window.pageYOffset;

                    if (stickyCTA) {
                        stickyCTA.classList.toggle('show', scrollY > 500);
                    }

                    if (scrollBtn) {
                        scrollBtn.classList.toggle('show', scrollY > 300);
                    }
                }, { passive: true });
            }

            function enableSmoothScroll() {
                doc.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    const href = anchor.getAttribute('href');
                    if (!href || href === '#') {
                        return;
                    }

                    anchor.addEventListener('click', event => {
                        const target = doc.querySelector(href);
                        if (!target) {
                            return;
                        }

                        event.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });
            }

            function registerChatbotBubble() {
                const bubble = doc.getElementById('chatbotBubble');
                if (!bubble) {
                    return;
                }

                bubble.addEventListener('click', event => {
                    if (!event.target.classList.contains('bubble-close')) {
                        window.startChat();
                    }
                });

                window.addEventListener('load', () => {
                    setTimeout(() => {
                        const target = doc.getElementById('chatbotBubble');
                        if (!target) {
                            return;
                        }

                        target.style.display = 'flex';

                        if (!sessionStorage.getItem('bubbleHidden')) {
                            setTimeout(() => {
                                const current = doc.getElementById('chatbotBubble');
                                if (!current) {
                                    return;
                                }

                                current.style.animation = 'fadeOut 0.5s ease';
                                setTimeout(() => {
                                    const latest = doc.getElementById('chatbotBubble');
                                    if (!latest) {
                                        return;
                                    }

                                    latest.style.display = 'none';
                                    sessionStorage.setItem('bubbleHidden', 'true');
                                }, 500);
                            }, 10000);
                        }
                    }, 5000);
                });
            }

            function registerPerformanceObserver() {
                if (!('PerformanceObserver' in window)) {
                    return;
                }

                try {
                    const observer = new PerformanceObserver(list => {
                        list.getEntries().forEach(entry => {
                            if (entry.entryType === 'measure') {
                                console.log('Core Web Vital:', entry.name, `${entry.duration}ms`);
                            }
                        });
                    });
                    observer.observe({ entryTypes: ['measure'] });
                } catch (error) {
                    console.log('Performance observer not supported');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                initAOS();
                registerScrollHandlers();
                enableSmoothScroll();
                registerChatbotBubble();
                registerPerformanceObserver();

                if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
                    navigator.serviceWorker.register('/sw.js').catch(() => {
                        console.warn('Service worker registration failed');
                    });
                }

                if ('ontouchstart' in window) {
                    doc.body.classList.add('touch-device');
                }

                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    root.style.setProperty('--animation-duration', '0.01ms');
                }
            });

            window.addEventListener('load', initAOS);
        }());
    </script>
