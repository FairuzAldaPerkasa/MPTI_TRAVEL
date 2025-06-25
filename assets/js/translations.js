// 🌐 Enhanced Translation System for MPTI Travel Website
// Multi-language support: Indonesian (id) and English (en)

class TranslationSystem {
    constructor() {
        // Get saved language or default to Indonesian
        this.currentLanguage = localStorage.getItem('mpti_language') || 'id';
        this.translations = {};
        this.observers = [];
        this.isLoading = false;
        this.fallbackLanguage = 'id';
        this.init();
    }

    async init() {
        console.log('🌐 Initializing Translation System...');
        await this.loadTranslations();
        this.setupLanguageSwitcher();
        this.updateDocumentLanguage();
        this.applyTranslations();
        this.observeContentChanges();
        console.log(`✅ Translation System ready - Current language: ${this.currentLanguage}`);
    }    async loadTranslations() {        // Mencoba beberapa path yang mungkin untuk memastikan file ditemukan
        const possiblePaths = [
            '../../assets/js/translations.json',  // Untuk file di FrontEnd/html/ (ini yang benar)
            '../js/translations.json',  // Dari assets/css/
            './translations.json',  // Jika sudah di direktori assets/js/
            '/MPTI_TRAVEL/assets/js/translations.json',  // Path absolut
            '../translations.json'  // Fallback lama
        ];
        
        let jsonPath = null;
        let response = null;
          // Coba setiap path sampai menemukan yang benar
        for (const path of possiblePaths) {
            try {
                console.log(`📝 Mencoba memuat terjemahan dari: ${path}`);
                response = await fetch(path);
                console.log(`📝 Response status untuk ${path}:`, response.status);
                if (response.ok) {
                    jsonPath = path;
                    console.log(`✅ Path berhasil: ${path}`);
                    break;
                }
            } catch (error) {
                console.log(`❌ Path ${path} gagal:`, error.message);
            }
        }
        
        if (jsonPath && response) {
            try {
                console.log(`✅ File terjemahan ditemukan di: ${jsonPath}`);
                const data = await response.json();
                this.translations = data;
                console.log(`✅ Terjemahan berhasil dimuat dari ${jsonPath}.`);
                return;
            } catch (error) {
                console.error(`❌ Error parsing JSON dari ${jsonPath}:`, error);
            }
        }        
        // Jika semua path gagal, gunakan fallback
        console.error(`❌ Gagal memuat terjemahan dari semua path yang dicoba`);
        console.warn('⚠️ Menggunakan terjemahan fallback.');
        this.loadFallbackTranslations();
    }

    loadFallbackTranslations() {
        // Comprehensive fallback translations
        this.translations = {
            id: {
                nav: {
                    home: "Beranda",
                    packages: "Paket Wisata",
                    about: "Tentang Kami",
                    contact: "Kontak",
                    login: "Login",
                    profile: "Tentang Kami"
                },
                profile: {
                    pageTitle: "Tentang Kami | MPTI Travel",
                    hero: {
                        title: "Tentang MPTI Travel",
                        breadcrumbHome: "Beranda",
                        breadcrumbAbout: "Tentang Kami"
                    },
                    about: {
                        title: "Cerita Kami",
                        subtitle: "Perjalanan Kami Membangun MPTI Travel",
                        content: "MPTI Travel lahir dari kecintaan kami pada kekayaan budaya dan alam Indonesia. Sejak didirikan, kami bertekad untuk tidak hanya menjadi penyedia jasa perjalanan, tetapi juga menjadi jembatan yang menghubungkan wisatawan dengan esensi sejati dari setiap destinasi. Kami percaya bahwa perjalanan adalah tentang pengalaman, penemuan, dan kenangan yang abadi."
                    },
                    vision: {
                        title: "Visi Kami",
                        content: "Menjadi agen perjalanan terkemuka di Indonesia yang dikenal karena inovasi, layanan berkualitas, dan komitmen terhadap pariwisata berkelanjutan yang mengangkat komunitas lokal."
                    },
                    mission: {
                        title: "Misi Kami",
                        items: [
                            "Menyusun paket wisata yang unik dan otentik.",
                            "Memberikan pelayanan pelanggan yang personal dan responsif.",
                            "Berkolaborasi dengan masyarakat lokal untuk menciptakan dampak positif.",
                            "Mempromosikan praktik pariwisata yang ramah lingkungan."
                        ]
                    },
                    team: {
                        title: "Tim Profesional Kami",
                        subtitle: "Orang-orang di Balik Perjalanan Anda",
                        member1: {
                            name: "Muhammad Raihan",
                            role: "Project Manager"
                        },
                        member2: {
                            name: "Pascal Theophylus",
                            role: "System Analyst"
                        },
                        member3: {
                            name: "I Kadek Agus",
                            role: "Programmer"
                        },
                        member4: {
                            name: "Theodorus Karsten",
                            role: "UI/UX Designer"
                        }
                    }
                },
                footer: {
                    tagline: "Temukan Keindahan Yogyakarta Bersama Kami",
                    operatingHours: "Jam Operasional",
                    mondayFriday: "Senin - Jumat",
                    saturday: "Sabtu",
                    sundayHoliday: "Minggu & Hari Libur",
                    closed: "Tutup",
                    address: "Jl. Malioboro No. 123, Yogyakarta",
                    ourServices: "Layanan Kami",
                    services: {
                        cultural: "Paket Wisata Budaya",
                        historical: "Wisata Sejarah",
                        adventure: "Petualangan Alam",
                        culinary: "Tur Kuliner",
                        transport: "Transportasi Wisata"
                    },
                    paymentMethods: "Metode Pembayaran",
                    newsletter: "Newsletter",
                    newsletterDesc: "Dapatkan penawaran spesial dengan berlangganan",
                    emailPlaceholder: "Alamat email Anda",
                    subscribe: "Berlangganan",
                    copyright: "Copyright ©2025 Vacationland | All rights reserved | Created with ❤️ by ARK"
                },                profile: {
                    pageTitle: "Tentang Kami | Vacationland",
                    title: "Vacationland - Jelajahi Dunia Bersama Kami",
                    description: "Vacationland adalah perusahaan travel yang berkomitmen memberikan pengalaman liburan tak terlupakan dengan layanan terbaik, paket wisata lengkap, dan customer care yang ramah. Kami memadukan keindahan budaya lokal dan destinasi wisata populer untuk Anda.",
                    sections: {
                        about: {
                            title: "Tentang Kami",
                            content: "Vacationland adalah perusahaan travel dengan pengalaman lebih dari 10 tahun di industri pariwisata Indonesia. Kami mengkhususkan diri dalam memberikan pengalaman perjalanan yang tak terlupakan dengan standar pelayanan tertinggi."
                        },
                        vision: {
                            title: "Visi Kami",
                            content: "Menjadi perusahaan travel terdepan di Indonesia yang memberikan pengalaman wisata berkualitas tinggi dan berkelanjutan."
                        },
                        mission: {
                            title: "Misi Kami",
                            items: [
                                "Memberikan pelayanan wisata terbaik dengan standar internasional",
                                "Mempromosikan keindahan dan budaya Indonesia ke dunia",
                                "Menciptakan lapangan kerja dan memberdayakan masyarakat lokal",
                                "Melestarikan alam dan budaya di setiap destinasi"
                            ]
                        }
                    }
                },
                admin: {
                    login: "Login Admin"
                }
            },
            en: {
                nav: {
                    home: "Home",
                    packages: "Tour Packages",
                    about: "About Us",
                    contact: "Contact",
                    login: "Login",
                    profile: "About Us"
                },
                profile: {
                    pageTitle: "About Us | MPTI Travel",
                    hero: {
                        title: "About MPTI Travel",
                        breadcrumbHome: "Home",
                        breadcrumbAbout: "About Us"
                    },
                    about: {
                        title: "Our Story",
                        subtitle: "Our Journey Building MPTI Travel",
                        content: "MPTI Travel was born from our love for the cultural and natural wealth of Indonesia. Since our founding, we have been determined not only to be a travel service provider, but also to be a bridge connecting tourists with the true essence of each destination. We believe that travel is about experience, discovery, and lasting memories."
                    },
                    vision: {
                        title: "Our Vision",
                        content: "To become the leading travel agency in Indonesia known for innovation, quality service, and a commitment to sustainable tourism that uplifts local communities."
                    },
                    mission: {
                        title: "Our Mission",
                        items: [
                            "Curating unique and authentic tour packages.",
                            "Providing personal and responsive customer service.",
                            "Collaborating with local communities to create a positive impact.",
                            "Promoting environmentally friendly tourism practices."
                        ]
                    },
                    team: {
                        title: "Our Professional Team",
                        subtitle: "The People Behind Your Journey",
                        member1: {
                            name: "Muhammad Raihan",
                            role: "Project Manager"
                        },
                        member2: {
                            name: "Pascal Theophylus",
                            role: "System Analyst"
                        },
                        member3: {
                            name: "I Kadek Agus",
                            role: "Programmer"
                        },
                        member4: {
                            name: "Theodorus Karsten",
                            role: "UI/UX Designer"
                        }
                    }
                },
                footer: {
                    tagline: "Discover the Beauty of Yogyakarta with Us",
                    operatingHours: "Operating Hours",
                    mondayFriday: "Monday - Friday",
                    saturday: "Saturday",
                    sundayHoliday: "Sunday & Holidays",
                    closed: "Closed",
                    address: "Jl. Malioboro No. 123, Yogyakarta",
                    ourServices: "Our Services",
                    services: {
                        cultural: "Cultural Tour Packages",
                        historical: "Historical Tourism",
                        adventure: "Nature Adventure",
                        culinary: "Culinary Tour",
                        transport: "Tourism Transportation"
                    },
                    paymentMethods: "Payment Methods",
                    newsletter: "Newsletter",
                    newsletterDesc: "Get special offers by subscribing",
                    emailPlaceholder: "Your email address",
                    subscribe: "Subscribe",
                    copyright: "Copyright ©2025 Vacationland | All rights reserved | Created with ❤️ by ARK"
                },                profile: {
                    pageTitle: "About Us | Vacationland",
                    title: "Vacationland - Explore the World with Us",
                    description: "Vacationland is a travel company committed to delivering unforgettable holiday experiences with the best service, comprehensive tour packages, and friendly customer care. We combine local cultural beauty and popular travel destinations for you.",
                    sections: {
                        about: {
                            title: "About Us",
                            content: "Vacationland is a travel company with more than 10 years of experience in the Indonesian tourism industry. We specialize in providing unforgettable travel experiences with the highest service standards."
                        },
                        vision: {
                            title: "Our Vision",
                            content: "To become the leading travel company in Indonesia that provides high-quality and sustainable tourism experiences."
                        },
                        mission: {
                            title: "Our Mission",
                            items: [
                                "Provide the best tourism services with international standards",
                                "Promote the beauty and culture of Indonesia to the world",
                                "Create jobs and empower local communities",
                                "Preserve nature and culture at every destination"
                            ]
                        }
                    }
                },
                admin: {
                    login: "Admin Login"
                }
            }
        };
    }

    setupLanguageSwitcher() {
        console.log('🔧 Setting up language switcher...');
        
        // Create language switcher if it doesn't exist
        this.createLanguageSwitcher();
        
        // Find and setup existing language buttons
        this.setupLanguageButtons();
        
        // Update language switcher state
        this.updateLanguageSwitcherState();
    }

    createLanguageSwitcher() {
        // Check if language switcher already exists
        if (document.getElementById('language-switcher')) {
            return;
        }

        // Create language switcher HTML
        const languageSwitcher = document.createElement('div');
        languageSwitcher.id = 'language-switcher';
        languageSwitcher.className = 'language-switcher';
        languageSwitcher.innerHTML = `
            <div class="language-toggle">
                <button class="lang-btn ${this.currentLanguage === 'id' ? 'active' : ''}" 
                        data-lang="id" title="Bahasa Indonesia">
                    <span class="flag">🇮🇩</span>
                    <span class="text">ID</span>
                </button>
                <button class="lang-btn ${this.currentLanguage === 'en' ? 'active' : ''}" 
                        data-lang="en" title="English">
                    <span class="flag">🇺🇸</span>
                    <span class="text">EN</span>
                </button>
            </div>
        `;

        // Add CSS styles
        this.addLanguageSwitcherStyles();

        // Try to add to header, navigation, or body
        const possibleParents = [
            document.querySelector('.header-right'),
            document.querySelector('.nav-content'),
            document.querySelector('nav'),
            document.querySelector('header'),
            document.body
        ];

        for (const parent of possibleParents) {
            if (parent) {
                parent.appendChild(languageSwitcher);
                console.log('✅ Language switcher added to:', parent.className || parent.tagName);
                break;
            }
        }

        // Setup event listeners for new buttons
        this.setupLanguageButtons();
    }

    addLanguageSwitcherStyles() {
        if (document.getElementById('language-switcher-styles')) {
            return;
        }

        const styles = document.createElement('style');
        styles.id = 'language-switcher-styles';
        styles.textContent = `
            .language-switcher {
                display: flex;
                align-items: center;
                margin-left: 1rem;
                z-index: 1000;
            }

            .language-toggle {
                display: flex;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 25px;
                padding: 4px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                overflow: hidden;
            }

            .lang-btn {
                display: flex;
                align-items: center;
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
                min-width: 50px;
                justify-content: center;
            }

            .lang-btn:hover {
                background: rgba(255, 255, 255, 0.1);
                color: white;
                transform: translateY(-1px);
            }

            .lang-btn.active {
                background: rgba(255, 255, 255, 0.9);
                color: #2563eb;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .lang-btn .flag {
                font-size: 1rem;
                line-height: 1;
            }

            .lang-btn .text {
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.5px;
            }

            /* Mobile responsive */
            @media (max-width: 768px) {
                .language-switcher {
                    margin-left: 0.5rem;
                }

                .lang-btn {
                    padding: 0.4rem 0.6rem;
                    min-width: 45px;
                }

                .lang-btn .text {
                    font-size: 0.7rem;
                }
            }
        `;

        document.head.appendChild(styles);
    }    setupLanguageButtons() {
        // Only target actual language switcher buttons, not all elements with data-lang
        const langButtons = document.querySelectorAll('.lang-btn[data-lang]');
        langButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const lang = button.getAttribute('data-lang');
                if (lang && lang !== this.currentLanguage) {
                    this.switchLanguage(lang);
                }
            });
        });
        console.log(`🔧 Setup ${langButtons.length} language buttons`);
    }

    updateLanguageSwitcherState() {
        const langButtons = document.querySelectorAll('.lang-btn');
        langButtons.forEach(button => {
            const lang = button.getAttribute('data-lang');
            if (lang === this.currentLanguage) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }

    async switchLanguage(newLanguage) {
        if (this.isLoading || newLanguage === this.currentLanguage) {
            return;
        }

        console.log(`🔄 Switching language from ${this.currentLanguage} to ${newLanguage}`);
        
        this.isLoading = true;
        this.currentLanguage = newLanguage;
        
        // Save to localStorage
        localStorage.setItem('mpti_language', newLanguage);
        
        // Update document language
        this.updateDocumentLanguage();
        
        // Update language switcher state
        this.updateLanguageSwitcherState();
        
        // Apply translations
        this.applyTranslations();
        
        // Notify observers
        this.notifyLanguageChange(newLanguage);
        
        // Show loading indicator briefly
        this.showLanguageChangeIndicator();
        
        setTimeout(() => {
            this.isLoading = false;
        }, 300);

        console.log(`✅ Language switched to: ${newLanguage}`);
    }

    updateDocumentLanguage() {
        document.documentElement.lang = this.currentLanguage;
        document.documentElement.setAttribute('data-lang', this.currentLanguage);
    }

    showLanguageChangeIndicator() {
        // Create a brief loading indicator
        const indicator = document.createElement('div');
        indicator.className = 'language-change-indicator';
        indicator.innerHTML = `
            <div class="indicator-content">
                <i class="fas fa-globe-americas"></i>
                <span>${this.currentLanguage === 'id' ? 'Bahasa Indonesia' : 'English'}</span>
            </div>
        `;
        
        // Add styles
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            box-shadow: 0 4px 20px rgba(0, 130, 246, 0.3);
            z-index: 10000;
            font-size: 0.9rem;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        indicator.querySelector('.indicator-content').style.cssText = `
            display: flex;
            align-items: center;
            gap: 8px;
        `;

        document.body.appendChild(indicator);

        // Animate in
        setTimeout(() => {
            indicator.style.transform = 'translateX(0)';
        }, 100);

        // Remove after delay
        setTimeout(() => {
            indicator.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (indicator.parentNode) {
                    indicator.parentNode.removeChild(indicator);
                }
            }, 300);
        }, 2000);
    }

    applyTranslations() {
        console.log('🔄 Applying translations...');
        
        // Find all elements with translation attributes
        const elementsToTranslate = document.querySelectorAll('[data-translate], [data-translate-placeholder], [data-translate-title]');
        
        let translatedCount = 0;
        
        elementsToTranslate.forEach(element => {
            // Text content translation
            const translateKey = element.getAttribute('data-translate');
            if (translateKey) {
                const translation = this.getTranslation(translateKey);
                if (translation) {
                    if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                        element.value = translation;
                    } else {
                        element.textContent = translation;
                    }
                    translatedCount++;
                }
            }

            // Placeholder translation
            const placeholderKey = element.getAttribute('data-translate-placeholder');
            if (placeholderKey) {
                const translation = this.getTranslation(placeholderKey);
                if (translation) {
                    element.placeholder = translation;
                    translatedCount++;
                }
            }

            // Title translation
            const titleKey = element.getAttribute('data-translate-title');
            if (titleKey) {
                const translation = this.getTranslation(titleKey);
                if (translation) {
                    element.title = translation;
                    translatedCount++;
                }
            }
        });

        console.log(`✅ Applied ${translatedCount} translations`);

        // Apply special content translations
        this.applyContentTranslations();
    }

    getTranslation(key, defaultValue = null) {
        const keys = key.split('.');
        let translation = this.translations[this.currentLanguage];
        
        for (const k of keys) {
            if (translation && typeof translation === 'object' && k in translation) {
                translation = translation[k];
            } else {
                // Try fallback language
                translation = this.translations[this.fallbackLanguage];
                for (const fallbackKey of keys) {
                    if (translation && typeof translation === 'object' && fallbackKey in translation) {
                        translation = translation[fallbackKey];
                    } else {
                        translation = defaultValue || key;
                        break;
                    }
                }
                break;
            }
        }
        
        return typeof translation === 'string' ? translation : (defaultValue || key);
    }

    // Public utility methods
    getCurrentLanguage() {
        return this.currentLanguage;
    }

    formatCurrency(amount, currency = 'IDR') {
        const locales = {
            'id': 'id-ID',
            'en': 'en-US'
        };
        
        return new Intl.NumberFormat(locales[this.currentLanguage] || 'id-ID', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0
        }).format(amount);
    }

    formatDate(date, options = {}) {
        const locales = {
            'id': 'id-ID',
            'en': 'en-US'
        };
        
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        return new Intl.DateTimeFormat(
            locales[this.currentLanguage] || 'id-ID', 
            { ...defaultOptions, ...options }
        ).format(new Date(date));
    }

    applyContentTranslations() {
        // Apply translations for common content
        this.translateNavigation();
        this.translatePackageContent();
        this.translateButtons();
    }

    translateNavigation() {
        // Navigation links
        const navTranslations = {
            'beranda': 'nav.home',
            'home': 'nav.home',
            'paket': 'nav.packages',
            'packages': 'nav.packages',
            'tentang': 'nav.about',
            'about': 'nav.about',
            'kontak': 'nav.contact',
            'contact': 'nav.contact'
        };

        Object.entries(navTranslations).forEach(([identifier, key]) => {
            const elements = document.querySelectorAll(`a[href*="${identifier}"], .nav-${identifier}`);
            elements.forEach(element => {
                const translation = this.getTranslation(key);
                if (translation && !element.dataset.originalText) {
                    element.dataset.originalText = element.textContent;
                    element.textContent = translation;
                }
            });
        });
    }

    translatePackageContent() {
        // Package content
        const packageSelectors = [
            { selector: '.price-from, .from-price', key: 'packages.from' },
            { selector: '.book-btn, .book-now, .pesan-btn', key: 'packages.book' },
            { selector: '.detail-btn, .view-details', key: 'packages.details' },
            { selector: '.days-text', key: 'packages.days' },
            { selector: '.nights-text', key: 'packages.nights' }
        ];

        packageSelectors.forEach(({selector, key}) => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                const translation = this.getTranslation(key);
                if (translation) {
                    element.textContent = translation;
                }
            });
        });
    }

    translateButtons() {
        // Common buttons
        const buttonTranslations = [
            { selector: '.close-btn, .tutup-btn', key: 'common.close' },
            { selector: '.save-btn, .simpan-btn', key: 'common.save' },
            { selector: '.edit-btn', key: 'common.edit' },
            { selector: '.delete-btn, .hapus-btn', key: 'common.delete' }
        ];

        buttonTranslations.forEach(({selector, key}) => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                const translation = this.getTranslation(key);
                if (translation) {
                    element.textContent = translation;
                }
            });
        });
    }

    observeContentChanges() {
        // Observe DOM changes to auto-translate new content
        const observer = new MutationObserver((mutations) => {
            let shouldReapply = false;
            
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const hasTranslateAttrs = node.querySelector && (
                                node.querySelector('[data-translate]') ||
                                node.hasAttribute('data-translate')
                            );
                            
                            if (hasTranslateAttrs) {
                                shouldReapply = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldReapply) {
                setTimeout(() => this.applyTranslations(), 100);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    notifyLanguageChange(newLanguage) {
        // Notify other parts of the application
        window.dispatchEvent(new CustomEvent('languageChanged', { 
            detail: { language: newLanguage } 
        }));
    }
}

// Initialize and expose the translation system globally
document.addEventListener('DOMContentLoaded', () => {
    window.translationSystem = new TranslationSystem();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TranslationSystem;
}