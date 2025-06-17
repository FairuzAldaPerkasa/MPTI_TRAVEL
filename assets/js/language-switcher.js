document.addEventListener('DOMContentLoaded', function() {
    // Initialize language switchers
    const languageSwitchers = document.querySelectorAll('.language-switcher select, #language-select');
    
    languageSwitchers.forEach(function(switcher) {
        switcher.addEventListener('change', function() {
            const selectedLang = this.value;
            switchLanguage(selectedLang);
            
            // Update all language switchers to maintain consistency
            languageSwitchers.forEach(function(otherSwitcher) {
                if (otherSwitcher !== switcher) {
                    otherSwitcher.value = selectedLang;
                }
            });
        });
    });
    
    // Language switching function
    function switchLanguage(lang) {
        // Store selected language in localStorage
        localStorage.setItem('selectedLanguage', lang);
        
        // Hide all language elements
        document.querySelectorAll('[data-lang]').forEach(function(element) {
            element.style.display = 'none';
        });
        
        // Show elements for selected language
        document.querySelectorAll('[data-lang="' + lang + '"]').forEach(function(element) {
            element.style.display = 'block';
        });
        
        // Update page title if available
        const pageTitle = document.querySelector('[data-lang="' + lang + '"][data-title]');
        if (pageTitle) {
            document.title = pageTitle.getAttribute('data-title');
        }
        
        // Update URL parameter for language
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.history.replaceState({}, '', url);
    }
    
    // Load saved language or default to Indonesian
    const savedLang = localStorage.getItem('selectedLanguage') || 
                     new URLSearchParams(window.location.search).get('lang') || 'id';
    
    // Set initial language
    languageSwitchers.forEach(function(switcher) {
        switcher.value = savedLang;
    });
    
    switchLanguage(savedLang);
});
