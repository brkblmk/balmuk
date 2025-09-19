/**
 * Homepage Fixes - Tab functionality, filters, and layout corrections
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Fix Service Filter Tabs
    const serviceFilters = document.querySelectorAll('.service-filters .filter-buttons button');
    const serviceItems = document.querySelectorAll('.service-item');
    
    if (serviceFilters.length > 0) {
        serviceFilters.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                serviceFilters.forEach(btn => btn.classList.remove('active'));
                // Add active to clicked button
                this.classList.add('active');
                
                // Get filter value
                const filterType = this.getAttribute('data-filter');
                
                // Filter service items
                serviceItems.forEach(item => {
                    const serviceType = item.getAttribute('data-service-type');
                    
                    if (filterType === 'all' || serviceType === filterType) {
                        item.style.display = 'block';
                        item.style.opacity = '0';
                        setTimeout(() => {
                            item.style.opacity = '1';
                        }, 10);
                    } else {
                        item.style.opacity = '0';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
        
        // Add 'all' filter button if not exists
        const allButton = document.querySelector('.filter-buttons button[data-filter="all"]');
        if (!allButton) {
            const filterContainer = document.querySelector('.filter-buttons');
            if (filterContainer) {
                const allBtn = document.createElement('button');
                allBtn.className = 'btn btn-outline-primary active';
                allBtn.setAttribute('data-filter', 'all');
                allBtn.textContent = 'Tümü';
                filterContainer.insertBefore(allBtn, filterContainer.firstChild);
                
                allBtn.addEventListener('click', function() {
                    serviceFilters.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    serviceItems.forEach(item => {
                        item.style.display = 'block';
                        item.style.opacity = '1';
                    });
                });
            }
        }
    }
    
    // 2. Fix Blog Category Filters
    const blogCategoryButtons = document.querySelectorAll('.blog-categories .category-buttons button');
    const blogArticles = document.querySelectorAll('.blog-article');
    
    if (blogCategoryButtons.length > 0) {
        blogCategoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                blogCategoryButtons.forEach(btn => btn.classList.remove('active'));
                // Add active to clicked button
                this.classList.add('active');
                
                // Get category value
                const category = this.getAttribute('data-category');
                
                // Filter blog articles
                blogArticles.forEach(article => {
                    const articleCategory = article.getAttribute('data-category');
                    
                    if (category === 'all' || articleCategory === category) {
                        article.parentElement.style.display = 'block';
                        article.style.opacity = '0';
                        setTimeout(() => {
                            article.style.opacity = '1';
                        }, 10);
                    } else {
                        article.style.opacity = '0';
                        setTimeout(() => {
                            article.parentElement.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }
    
    // 3. Blog Search Functionality
    const blogSearch = document.getElementById('blogSearch');
    if (blogSearch) {
        blogSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            blogArticles.forEach(article => {
                const title = article.querySelector('.blog-title')?.textContent.toLowerCase() || '';
                const excerpt = article.querySelector('.blog-excerpt')?.textContent.toLowerCase() || '';
                const tags = article.querySelector('.blog-tags')?.textContent.toLowerCase() || '';
                
                if (title.includes(searchTerm) || excerpt.includes(searchTerm) || tags.includes(searchTerm)) {
                    article.parentElement.style.display = 'block';
                } else {
                    article.parentElement.style.display = 'none';
                }
            });
        });
    }
    
    // 4. Fix horizontal scroll issue
    function fixHorizontalScroll() {
        // Check for elements causing overflow
        const body = document.body;
        const html = document.documentElement;
        
        // Set overflow-x hidden on body and html
        body.style.overflowX = 'hidden';
        html.style.overflowX = 'hidden';
        
        // Find and fix elements wider than viewport
        const allElements = document.querySelectorAll('*');
        const viewportWidth = window.innerWidth;
        
        allElements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.width > viewportWidth || rect.right > viewportWidth) {
                // Check if it's a container that should be full width
                if (element.classList.contains('container-fluid')) {
                    element.style.maxWidth = '100%';
                    element.style.paddingLeft = '15px';
                    element.style.paddingRight = '15px';
                } else if (rect.width > viewportWidth) {
                    element.style.maxWidth = '100%';
                    element.style.width = '100%';
                }
            }
        });
        
        // Fix specific known issues
        const heroSection = document.querySelector('.hero-enhanced');
        if (heroSection) {
            heroSection.style.overflow = 'hidden';
        }
        
        const hexagonBg = document.querySelector('.hexagon-bg');
        if (hexagonBg) {
            hexagonBg.style.overflow = 'hidden';
            hexagonBg.style.position = 'absolute';
            hexagonBg.style.width = '100%';
            hexagonBg.style.height = '100%';
        }
    }
    
    // Run horizontal scroll fix
    fixHorizontalScroll();
    window.addEventListener('resize', fixHorizontalScroll);
    
    // 5. Add smooth transitions to all filter operations
    const style = document.createElement('style');
    style.textContent = `
        .service-item, .blog-article {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .service-item:hover, .blog-article:hover {
            transform: translateY(-5px);
        }
        
        /* Fix blog card layout */
        .blog-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .blog-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .blog-actions {
            margin-top: auto;
            padding-top: 1rem;
        }
        
        /* Fix blog excerpt truncation */
        .blog-excerpt {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 60px;
        }
        
        /* Ensure no horizontal scroll */
        body, html {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }
        
        .container-fluid {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }
        
        /* Fix filter buttons responsiveness */
        @media (max-width: 768px) {
            .filter-buttons, .category-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: center;
            }
            
            .filter-buttons button, .category-buttons button {
                flex: 0 0 auto;
                margin: 0 !important;
            }
        }
    `;
    document.head.appendChild(style);
    
    // 6. Initialize filter with 'all' showing
    const initAllFilters = () => {
        // Show all services initially
        serviceItems.forEach(item => {
            item.style.display = 'block';
            item.style.opacity = '1';
        });
        
        // Show all blog articles initially
        blogArticles.forEach(article => {
            if (article.parentElement) {
                article.parentElement.style.display = 'block';
            }
            article.style.opacity = '1';
        });
    };
    
    initAllFilters();
    
    // 7. Handle newsletter form
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value;
            
            if (email) {
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success mt-3';
                successMsg.textContent = 'Başarıyla abone oldunuz! E-posta adresinize onay maili gönderildi.';
                this.appendChild(successMsg);
                
                // Clear form
                emailInput.value = '';
                
                // Remove message after 5 seconds
                setTimeout(() => {
                    successMsg.remove();
                }, 5000);
            }
        });
    }
    
    // 8. Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offset = 80; // Account for fixed navbar
                    const targetPosition = target.offsetTop - offset;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // 9. Active section highlighting in navbar
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav a[href^="#"]');
    
    function highlightActiveSection() {
        const scrollY = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 100;
            const sectionId = section.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    window.addEventListener('scroll', highlightActiveSection);
    highlightActiveSection();
});

// Export functions for use in other scripts
window.HomepageFixes = {
    filterServices: function(type) {
        const serviceItems = document.querySelectorAll('.service-item');
        serviceItems.forEach(item => {
            const serviceType = item.getAttribute('data-service-type');
            if (type === 'all' || serviceType === type) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    },
    
    filterBlogs: function(category) {
        const blogArticles = document.querySelectorAll('.blog-article');
        blogArticles.forEach(article => {
            const articleCategory = article.getAttribute('data-category');
            if (category === 'all' || articleCategory === category) {
                article.parentElement.style.display = 'block';
            } else {
                article.parentElement.style.display = 'none';
            }
        });
    }
};