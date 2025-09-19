// Prime EMS Analytics Tracker
class PrimeEMSAnalytics {
    constructor() {
        this.apiEndpoint = "/api/analytics.php";
        this.sessionId = this.getOrCreateSessionId();
        this.init();
    }
    
    init() {
        // Track page view automatically
        this.trackPageView();
        
        // Track button clicks
        this.trackButtonClicks();
        
        // Track form submissions
        this.trackFormSubmissions();
        
        // Track scroll depth
        this.trackScrollDepth();
        
        // Track time on page
        this.trackTimeOnPage();
    }
    
    trackPageView() {
        this.sendEvent("page_view", {
            page: window.location.pathname,
            title: document.title,
            referrer: document.referrer,
            timestamp: Date.now()
        });
    }
    
    trackButtonClicks() {
        document.addEventListener("click", (e) => {
            const button = e.target.closest("button, .btn, [role=button]");
            if (button) {
                const buttonText = button.textContent.trim() || button.getAttribute("aria-label") || "Unknown";
                this.sendEvent("button_click", {
                    button: buttonText,
                    page: window.location.pathname,
                    element: button.tagName.toLowerCase(),
                    classes: button.className
                });
            }
        });
    }
    
    trackFormSubmissions() {
        document.addEventListener("submit", (e) => {
            const form = e.target;
            if (form.tagName === "FORM") {
                const formName = form.name || form.className || form.id || "unnamed-form";
                this.sendEvent("form_submit", {
                    form: formName,
                    page: window.location.pathname,
                    action: form.action || window.location.href
                });
            }
        });
    }
    
    trackScrollDepth() {
        let maxScroll = 0;
        let scrollTimeout;
        
        window.addEventListener("scroll", () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const scrollPercent = Math.round(
                    (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
                );
                
                if (scrollPercent > maxScroll && scrollPercent <= 100) {
                    maxScroll = scrollPercent;
                    
                    // Track significant scroll milestones
                    if ([25, 50, 75, 90, 100].includes(scrollPercent)) {
                        this.sendEvent("scroll_depth", {
                            depth: scrollPercent,
                            page: window.location.pathname
                        });
                    }
                }
            }, 500);
        });
    }
    
    trackTimeOnPage() {
        const startTime = Date.now();
        
        window.addEventListener("beforeunload", () => {
            const timeSpent = Math.round((Date.now() - startTime) / 1000);
            if (timeSpent > 5) { // Only track if spent more than 5 seconds
                this.sendEvent("time_on_page", {
                    time_spent: timeSpent,
                    page: window.location.pathname
                });
            }
        });
        
        // Also track every 30 seconds for long sessions
        setInterval(() => {
            const timeSpent = Math.round((Date.now() - startTime) / 1000);
            if (timeSpent > 0 && timeSpent % 30 === 0) {
                this.sendEvent("session_ping", {
                    time_spent: timeSpent,
                    page: window.location.pathname
                });
            }
        }, 30000);
    }
    
    sendEvent(eventType, eventData) {
        const payload = {
            event_type: eventType,
            event_data: eventData,
            session_id: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href,
            user_agent: navigator.userAgent
        };
        
        // Use sendBeacon if available for better reliability
        if (navigator.sendBeacon) {
            navigator.sendBeacon(
                this.apiEndpoint,
                new Blob([JSON.stringify(payload)], { type: "application/json" })
            );
        } else {
            // Fallback to fetch
            fetch(this.apiEndpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            }).catch(() => {
                // Silently fail
            });
        }
    }
    
    getOrCreateSessionId() {
        let sessionId = sessionStorage.getItem("prime_ems_session");
        if (!sessionId) {
            sessionId = "sess_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem("prime_ems_session", sessionId);
        }
        return sessionId;
    }
    
    // Public methods for custom tracking
    trackCustomEvent(eventType, eventData) {
        this.sendEvent(eventType, eventData);
    }
    
    trackAppointmentBooking(serviceId) {
        this.sendEvent("appointment_booked", {
            service_id: serviceId,
            page: window.location.pathname
        });
    }
    
    trackContactFormSubmit() {
        this.sendEvent("contact_form_submit", {
            page: window.location.pathname
        });
    }
}

// Auto-initialize analytics
document.addEventListener("DOMContentLoaded", () => {
    window.primeAnalytics = new PrimeEMSAnalytics();
});

// Google Analytics Integration (if GA ID is set)
if (window.GA_MEASUREMENT_ID) {
    (function() {
        const script = document.createElement("script");
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${window.GA_MEASUREMENT_ID}`;
        document.head.appendChild(script);
        
        script.onload = function() {
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag("js", new Date());
            gtag("config", window.GA_MEASUREMENT_ID);
        };
    })();
}