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
        // Check if mobile sidebar already exists
        let sidebar = document.querySelector('.mobile-sidebar');
        let overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar) {
            console.log('Mobile sidebar already exists, enhancing it...');
            this.enhanceExistingSidebar(sidebar);
            
            // Create overlay if it doesn't exist
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
            }
            return;
        }

        // Create mobile sidebar from scratch
        sidebar = document.createElement('div');
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
                        <button class="lang-btn active" data-lang="id">
                            <span class="flag">🇮🇩</span>
                            <span class="text">ID</span>
                        </button>
                        <button class="lang-btn" data-lang="en">
                            <span class="flag">🇺🇸</span>
                            <span class="text">EN</span>
                        </button>
                    </div>
                    <div class="mobile-lang-label">
                        <i class="fas fa-globe"></i>
                        <span data-translate="common.language">Bahasa / Language</span>
                    </div>
                </div>
            </div>
        `;

        // Create overlay
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';

        // Add to body
        document.body.appendChild(sidebar);
        document.body.appendChild(overlay);

        // Add CSS for mobile language switcher
        this.addMobileLanguageSwitcherStyles();
    }

    enhanceExistingSidebar(sidebar) {
        // Check if sidebar already has language switcher
        if (sidebar.querySelector('.sidebar-language-switcher')) {
            console.log('Sidebar already has language switcher');
            return;
        }

        // Add header if it doesn't exist
        if (!sidebar.querySelector('.sidebar-header')) {
            const header = document.createElement('div');
            header.className = 'sidebar-header';
            header.innerHTML = `
                <div class="sidebar-logo">
                    <img src="../../assets/images/logompti.png" alt="Vacationland">
                    <span>Vacationland</span>
                </div>
                <button class="sidebar-close">&times;</button>
            `;
            sidebar.insertBefore(header, sidebar.firstChild);
        }

        // Add language switcher to existing sidebar
        const footer = document.createElement('div');
        footer.className = 'sidebar-footer';
        footer.innerHTML = `
            <div class="sidebar-language-switcher">
                <div class="mobile-lang-toggle">
                    <button class="lang-btn active" data-lang="id">
                        <span class="flag">🇮🇩</span>
                        <span class="text">ID</span>
                    </button>
                    <button class="lang-btn" data-lang="en">
                        <span class="flag">🇺🇸</span>
                        <span class="text">EN</span>
                    </button>
                </div>
                <div class="mobile-lang-label">
                    <i class="fas fa-globe"></i>
                    <span data-translate="common.language">Bahasa / Language</span>
                </div>
            </div>
        `;
        
        sidebar.appendChild(footer);
        
        // Add CSS for mobile language switcher
        this.addMobileLanguageSwitcherStyles();
    }

    addMobileLanguageSwitcherStyles() {
        if (document.getElementById('mobile-lang-switcher-styles')) {
            return;
        }

        const styles = document.createElement('style');
        styles.id = 'mobile-lang-switcher-styles';
        styles.textContent = `
            .sidebar-language-switcher {
                padding: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            .mobile-lang-toggle {
                display: flex;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 25px;
                padding: 4px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                margin-bottom: 0.5rem;
            }

            .mobile-lang-toggle .lang-btn {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.25rem;
                padding: 0.5rem 0.75rem;
                border: none;
                background: transparent;
                color: rgba(255, 255, 255, 0.8);
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .mobile-lang-toggle .lang-btn:hover {
                background: rgba(255, 255, 255, 0.1);
                color: white;
            }

            .mobile-lang-toggle .lang-btn.active {
                background: rgba(255, 255, 255, 0.9);
                color: #2563eb;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .mobile-lang-toggle .lang-btn .flag {
                font-size: 1rem;
                line-height: 1;
            }

            .mobile-lang-toggle .lang-btn .text {
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.5px;
            }

            .mobile-lang-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: rgba(255, 255, 255, 0.7);
                font-size: 0.8rem;
                justify-content: center;
            }

            .mobile-lang-label i {
                font-size: 0.9rem;
            }
        `;

        document.head.appendChild(styles);
    }    bindEvents() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.mobile-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        if (toggle) {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            });
        }

        // Use event delegation for close button since it might be added dynamically
        document.addEventListener('click', (e) => {
            if (e.target.matches('.sidebar-close')) {
                this.closeSidebar();
            }
        });

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
    }    handleResize() {
        // Close sidebar on desktop view
        if (window.innerWidth > 768 && this.isOpen) {
            this.closeSidebar();
        }
    }
}

// Initialize the mobile navigation system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new UniversalMobileNav();
});
