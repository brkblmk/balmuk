/**
 * Prime EMS Admin Panel JavaScript
 * Admin paneli için genel JavaScript işlevselliği
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================
    // SIDEBAR FUNCTIONALITY
    // =====================================
    
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const body = document.body;
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        }
    });
    
    // Active nav item highlighting
    const currentPath = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (currentPath === '' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
    
    // =====================================
    // FORM ENHANCEMENTS
    // =====================================
    
    // Auto-save form data to localStorage
    const forms = document.querySelectorAll('form[data-autosave]');
    forms.forEach(form => {
        const formId = form.getAttribute('data-autosave');
        
        // Load saved data
        const savedData = localStorage.getItem(`form_${formId}`);
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(name => {
                const input = form.querySelector(`[name="${name}"]`);
                if (input) {
                    input.value = data[name];
                }
            });
        }
        
        // Save data on input change
        form.addEventListener('input', function() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            localStorage.setItem(`form_${formId}`, JSON.stringify(data));
        });
        
        // Clear saved data on successful submit
        form.addEventListener('submit', function() {
            setTimeout(() => {
                localStorage.removeItem(`form_${formId}`);
            }, 1000);
        });
    });
    
    // =====================================
    // DATA TABLES ENHANCEMENT
    // =====================================
    
    // Simple search functionality for tables
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    });
    
    // =====================================
    // AJAX FUNCTIONALITY
    // =====================================
    
    // Generic AJAX form handler
    const ajaxForms = document.querySelectorAll('form[data-ajax]');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'İşlem yapılıyor...';
            submitBtn.classList.add('loading');
            
            const formData = new FormData(form);
            const action = form.getAttribute('action') || '';
            
            fetch(action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message || 'İşlem başarılı', 'success');
                    
                    // Redirect if specified
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                    
                    // Reset form if specified
                    if (data.reset) {
                        form.reset();
                    }
                } else {
                    showNotification(data.message || 'Bir hata oluştu', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Bir hata oluştu', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.classList.remove('loading');
            });
        });
    });
    
    // =====================================
    // NOTIFICATIONS
    // =====================================
    
    // Show notification function
    window.showNotification = function(message, type = 'info', duration = 5000) {
        // Remove existing notifications
        const existing = document.querySelector('.notification');
        if (existing) {
            existing.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} notification position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideInRight 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill'} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, duration);
    };
    
    // =====================================
    // CONFIRMATION DIALOGS
    // =====================================
    
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const message = this.getAttribute('data-confirm-delete') || 'Bu kaydı silmek istediğinizden emin misiniz?';
            
            if (confirm(message)) {
                // If it's a link, follow it
                if (this.tagName === 'A') {
                    window.location.href = this.href;
                }
                // If it's a form submit button, submit the form
                else if (this.type === 'submit') {
                    this.form.submit();
                }
            }
        });
    });
    
    // =====================================
    // IMAGE PREVIEW
    // =====================================
    
    // Image preview for file inputs
    const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (preview && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // =====================================
    // RICH TEXT EDITOR (Simple)
    // =====================================
    
    // Simple rich text editor for textareas with data-rich attribute
    const richTextareas = document.querySelectorAll('textarea[data-rich]');
    richTextareas.forEach(textarea => {
        // Create toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'rich-toolbar btn-group mb-2';
        toolbar.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="bold"><i class="bi bi-type-bold"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="italic"><i class="bi bi-type-italic"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-command="underline"><i class="bi bi-type-underline"></i></button>
        `;
        
        // Insert toolbar before textarea
        textarea.parentNode.insertBefore(toolbar, textarea);
        
        // Make textarea contenteditable div
        const editor = document.createElement('div');
        editor.contentEditable = true;
        editor.className = textarea.className + ' rich-editor';
        editor.style.minHeight = '150px';
        editor.innerHTML = textarea.value;
        
        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(editor, textarea.nextSibling);
        
        // Toolbar functionality
        toolbar.addEventListener('click', function(e) {
            if (e.target.hasAttribute('data-command')) {
                e.preventDefault();
                const command = e.target.getAttribute('data-command');
                document.execCommand(command, false, null);
                editor.focus();
            }
        });
        
        // Update textarea on change
        editor.addEventListener('input', function() {
            textarea.value = this.innerHTML;
        });
    });
    
    // =====================================
    // STATISTICS ANIMATION
    // =====================================
    
    // Animate numbers on page load
    const statsNumbers = document.querySelectorAll('.stats-value');
    statsNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        if (finalValue) {
            animateNumber(stat, 0, finalValue, 2000);
        }
    });
    
    function animateNumber(element, start, end, duration) {
        const startTime = Date.now();
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.floor(start + (end - start) * easeOutCubic(progress));
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        requestAnimationFrame(animate);
    }
    
    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
    
    // =====================================
    // KEYBOARD SHORTCUTS
    // =====================================
    
    document.addEventListener('keydown', function(e) {
        // Ctrl+S for save
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const saveBtn = document.querySelector('button[type="submit"]');
            if (saveBtn) {
                saveBtn.click();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                const modalInstance = bootstrap.Modal.getInstance(activeModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        }
    });
    
    // =====================================
    // UTILITIES
    // =====================================
    
    // Copy to clipboard functionality
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Panoya kopyalandı', 'success', 2000);
        });
    };
    
    // Format currency
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(amount);
    };
    
    // Format date
    window.formatDate = function(date) {
        return new Intl.DateTimeFormat('tr-TR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    };
    
    // =====================================
    // INITIALIZATION COMPLETE
    // =====================================
    
    console.log('Prime EMS Admin Panel initialized');
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .rich-editor {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 12px 15px;
        }
        
        .rich-editor:focus {
            outline: none;
            border-color: var(--prime-gold);
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.15);
        }
        
        .rich-toolbar {
            border-radius: 5px;
        }
    `;
    document.head.appendChild(style);
    
});

// Export for global access
window.PrimeAdmin = {
    showNotification: window.showNotification,
    copyToClipboard: window.copyToClipboard,
    formatCurrency: window.formatCurrency,
    formatDate: window.formatDate
};