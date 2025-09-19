/**
 * Theme Switcher for Prime EMS Admin Panel
 * Supports light/dark theme switching with localStorage persistence
 */

(function() {
    'use strict';

    // Theme configuration
    const THEME_KEY = 'prime-ems-theme';
    const THEMES = {
        LIGHT: 'light',
        DARK: 'dark'
    };

    // Default to light theme
    let currentTheme = localStorage.getItem(THEME_KEY) || THEMES.LIGHT;

    // Apply theme to document
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.body.className = document.body.className.replace(/theme-\w+/g, '');
        document.body.classList.add(`theme-${theme}`);
        
        // Update theme icon
        const themeIcon = document.querySelector('[data-theme-icon]');
        if (themeIcon) {
            themeIcon.className = theme === THEMES.DARK 
                ? 'bi bi-sun-fill' 
                : 'bi bi-moon-fill';
        }
        
        // Update dropdown theme options
        document.querySelectorAll('[data-theme-option]').forEach(option => {
            const isActive = option.dataset.themeOption === theme;
            option.classList.toggle('active', isActive);
        });
        
        // Store preference
        localStorage.setItem(THEME_KEY, theme);
        currentTheme = theme;
    }

    // Toggle between themes
    function toggleTheme() {
        const newTheme = currentTheme === THEMES.LIGHT ? THEMES.DARK : THEMES.LIGHT;
        applyTheme(newTheme);
    }

    // Initialize theme on page load
    function initializeTheme() {
        applyTheme(currentTheme);
    }

    // Add keyboard shortcut (Ctrl+Shift+D)
    function handleKeyboardShortcut(event) {
        if (event.ctrlKey && event.shiftKey && event.key === 'D') {
            event.preventDefault();
            toggleTheme();
        }
    }

    // DOM Content Loaded Event
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize theme
        initializeTheme();
        
        // Theme toggle button
        const themeToggle = document.querySelector('[data-theme-toggle]');
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }
        
        // Theme dropdown options
        document.querySelectorAll('[data-theme-option]').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedTheme = this.dataset.themeOption;
                if (selectedTheme && selectedTheme !== currentTheme) {
                    applyTheme(selectedTheme);
                }
            });
        });
        
        // Add keyboard shortcut listener
        document.addEventListener('keydown', handleKeyboardShortcut);
        
        // Initialize tooltips if Bootstrap is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // Sidebar toggle functionality
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main');
        
        if (sidebar && main) {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('expanded');
        }
    };

    // Export for global access
    window.ThemeSwitcher = {
        toggle: toggleTheme,
        apply: applyTheme,
        current: () => currentTheme,
        THEMES: THEMES
    };

})();
