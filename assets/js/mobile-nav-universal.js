// Universal Mobile Navigation System
class UniversalMobileNav {
    constructor() {
        this.isOpen = false;
        this.init();
    }

    init() {
        this.createMobileElements();
        this.bindEvents();
        this.handleResize();
    }    createMobileElements() {
        // Remove existing mobile elements to avoid duplicates
        const existingSidebar = document.querySelector('.mobile-sidebar');
        const existingOverlay = document.querySelector('.sidebar-overlay');
        
        if (existingSidebar) existingSidebar.remove();
        if (existingOverlay) existingOverlay.remove();

        // Create mobile sidebar
        const sidebar = document.createElement('div');
        sidebar.className = 'mobile-sidebar';
        sidebar.innerHTML = `
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="../../assets/images/logompti.png" alt="Vacationland">
                    <span>Vacationland</span>
                </div>
                <button class="sidebar-close">&times;</button>
            </div>
            <nav class="sidebar-nav">
                <a href="Index.html" data-translate="nav.home" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="Index.html#paket" data-translate="nav.packages" class="nav-item">
                    <i class="fas fa-plane"></i>
                    <span>Paket Wisata</span>
                </a>
                <a href="profile.html" data-translate="nav.profile" class="nav-item">
                    <i class="fas fa-info-circle"></i>
                    <span>Tentang Kami</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-language-switcher">
                    <div class="mobile-lang-toggle">
                        <button class="mobile-lang-btn active" data-lang="id">
                            <span class="flag">🇮🇩</span>
                            <span class="text">ID</span>
                        </button>
                        <button class="mobile-lang-btn" data-lang="en">
                            <span class="flag">🇺🇸</span>
                            <span class="text">EN</span>
                        </button>
                    </div>
                    <div class="mobile-lang-label">
                        <i class="fas fa-globe"></i>
                        <span>Bahasa / Language</span>
                    </div>
                </div>
            </div>
        `;

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';

        // Add to body
        document.body.appendChild(sidebar);
        document.body.appendChild(overlay);

        // Initialize mobile language switcher
        this.initMobileLanguageSwitcher();
    }

    bindEvents() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.mobile-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const closeBtn = document.querySelector('.sidebar-close');

        if (toggle) {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeSidebar());
        }

        if (overlay) {
            overlay.addEventListener('click', () => this.closeSidebar());
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => this.handleResize());
    }

    toggleSidebar() {
        if (this.isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }

    openSidebar() {
        const sidebar = document.querySelector('.mobile-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggle = document.querySelector('.mobile-menu-toggle');

        if (sidebar && overlay) {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            toggle?.classList.add('active');
            document.body.classList.add('sidebar-open');
            document.body.style.overflow = 'hidden';
            this.isOpen = true;
        }
    }

    closeSidebar() {
        const sidebar = document.querySelector('.mobile-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggle = document.querySelector('.mobile-menu-toggle');

        if (sidebar && overlay) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            toggle?.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            document.body.style.overflow = '';
            this.isOpen = false;
        }
    }

    handleResize() {
        // Close sidebar on desktop view
        if (window.innerWidth > 768 && this.isOpen) {
            this.closeSidebar();
        }
    }

    initMobileLanguageSwitcher() {
        const langButtons = document.querySelectorAll('.mobile-lang-btn');
        
        langButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const lang = btn.getAttribute('data-lang');
                
                // Remove active class from all buttons
                langButtons.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                btn.classList.add('active');
                
                // Change language using translation system
                if (window.translationSystem) {
                    window.translationSystem.setLanguage(lang);
                    
                    // Update navigation text
                    setTimeout(() => {
                        this.updateNavigationText();
                    }, 100);
                }
            });
        });
        
        // Set initial active state based on current language
        if (window.translationSystem) {
            const currentLang = window.translationSystem.currentLanguage;
            const activeBtn = document.querySelector(`.mobile-lang-btn[data-lang="${currentLang}"]`);
            if (activeBtn) {
                langButtons.forEach(b => b.classList.remove('active'));
                activeBtn.classList.add('active');
            }
        }
    }

    updateNavigationText() {
        if (!window.translationSystem) return;
        
        // Update navigation items
        const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
        navItems.forEach(item => {
            const translateKey = item.getAttribute('data-translate');
            if (translateKey) {
                const span = item.querySelector('span');
                if (span) {
                    span.textContent = window.translationSystem.getTranslation(translateKey);
                }
            }
        });
        
        // Update language label
        const langLabel = document.querySelector('.mobile-lang-label span');
        if (langLabel) {
            const currentLang = window.translationSystem.currentLanguage;
            langLabel.textContent = currentLang === 'id' ? 'Bahasa / Language' : 'Language / Bahasa';
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wait a bit for other scripts to load
    setTimeout(() => {
        window.mobileNav = new UniversalMobileNav();
        console.log('✅ Universal Mobile Navigation initialized');
    }, 100);
});
