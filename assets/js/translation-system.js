// Translation System
(function() {
    'use strict';
    
    window.translationSystem = {
        currentLanguage: localStorage.getItem('selectedLanguage') || 'id',
        
        init: function() {
            this.loadLanguage(this.currentLanguage);
            this.bindEvents();
        },
        
        setLanguage: function(lang) {
            if (!lang) return;
            
            this.currentLanguage = lang;
            localStorage.setItem('selectedLanguage', lang);
            this.loadLanguage(lang);
            this.updateSwitchers(lang);
            this.updateURL(lang);
        },
        
        loadLanguage: function(lang) {
            // Hide all language elements
            document.querySelectorAll('[data-lang]').forEach(element => {
                element.style.display = 'none';
            });
            
            // Show elements for selected language
            document.querySelectorAll(`[data-lang="${lang}"]`).forEach(element => {
                element.style.display = 'block';
            });
        },
        
        updateSwitchers: function(lang) {
            // Update all language switchers
            document.querySelectorAll('select[id*="language"], .language-switcher select, #language-select').forEach(select => {
                select.value = lang;
            });
            
            // Update language buttons
            document.querySelectorAll('.language-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-lang') === lang) {
                    btn.classList.add('active');
                }
            });
        },
        
        updateURL: function(lang) {
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.history.replaceState({}, '', url);
        },
        
        bindEvents: function() {
            // Bind language switcher events
            document.addEventListener('change', (e) => {
                if (e.target.matches('select[id*="language"], .language-switcher select')) {
                    this.setLanguage(e.target.value);
                }
            });
            
            document.addEventListener('click', (e) => {
                if (e.target.matches('.language-btn')) {
                    const lang = e.target.getAttribute('data-lang');
                    if (lang) {
                        this.setLanguage(lang);
                    }
                }
            });
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.translationSystem.init();
        });
    } else {
        window.translationSystem.init();
    }
})();
