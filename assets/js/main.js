/* =================================
   VACATIONLAND - MAIN JAVASCRIPT
   Gabungan JS untuk frontend
   ================================= */

// =================================
// MOBILE NAVIGATION MODULE
// =================================
const MobileNavigation = {
    init() {
        this.bindEvents();
        this.initHeaderScroll();
    },

    bindEvents() {
        const toggle = document.querySelector(".mobile-menu-toggle");
        const sidebar = document.querySelector(".mobile-sidebar");
        const overlay = document.querySelector(".sidebar-overlay");
        const body = document.body;

        if (!toggle || !sidebar || !overlay) return;

        // Toggle sidebar
        toggle.addEventListener("click", (e) => {
            e.stopPropagation();
            this.toggleSidebar(toggle, sidebar, overlay, body);
        });

        // Close on overlay click
        overlay.addEventListener("click", () => {
            this.closeSidebar(toggle, sidebar, overlay, body);
        });

        // Close on escape key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                this.closeSidebar(toggle, sidebar, overlay, body);
            }
        });
    },

    toggleSidebar(toggle, sidebar, overlay, body) {
        toggle.classList.toggle("active");
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
        body.classList.toggle("sidebar-open");
    },

    closeSidebar(toggle, sidebar, overlay, body) {
        toggle.classList.remove("active");
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
        body.classList.remove("sidebar-open");
    },

    initHeaderScroll() {
        const header = document.querySelector("header");
        if (!header) return;

        let ticking = false;

        const updateHeader = () => {
            if (window.scrollY > 50) {
                header.classList.add("scrolled");
            } else {
                header.classList.remove("scrolled");
            }
            ticking = false;
        };

        window.addEventListener("scroll", () => {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        });
    }
};

// =================================
// IMAGE SLIDESHOW MODULE
// =================================
const ImageSlideshow = {
    currentIndex: 0,
    images: [],
    autoSlideInterval: null,

    init(container = '.slideshow-container') {
        const slideshowContainer = document.querySelector(container);
        if (!slideshowContainer) return;

        this.images = slideshowContainer.querySelectorAll('.slide');
        if (this.images.length === 0) return;

        this.bindEvents();
        this.startAutoSlide();
        this.showSlide(0);
    },

    bindEvents() {
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousSlide());
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextSlide());
        }

        // Touch events for mobile
        let startX = 0;
        let endX = 0;

        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });

        document.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            this.handleSwipe(startX, endX);
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.previousSlide();
            if (e.key === 'ArrowRight') this.nextSlide();
        });
    },

    handleSwipe(startX, endX) {
        const threshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.nextSlide();
            } else {
                this.previousSlide();
            }
        }
    },

    showSlide(index) {
        this.images.forEach((img, i) => {
            img.classList.toggle('active', i === index);
        });

        // Update indicators if they exist
        const indicators = document.querySelectorAll('.indicator');
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });

        this.currentIndex = index;
    },

    nextSlide() {
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.showSlide(nextIndex);
        this.resetAutoSlide();
    },

    previousSlide() {
        const prevIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.showSlide(prevIndex);
        this.resetAutoSlide();
    },

    startAutoSlide() {
        this.autoSlideInterval = setInterval(() => {
            this.nextSlide();
        }, 5000);
    },

    resetAutoSlide() {
        clearInterval(this.autoSlideInterval);
        this.startAutoSlide();
    },

    stop() {
        clearInterval(this.autoSlideInterval);
    }
};

// =================================
// PACKAGE LOADER MODULE
// =================================
const PackageLoader = {
    init() {
        this.loadPackages();
    },    async loadPackages() {
        const container = document.getElementById('packages-container');
        if (!container) return;

        try {
            container.innerHTML = '<div class="loading">Memuat paket wisata...</div>';
            
            console.log('🚀 Fetching packages from API...');
            const response = await fetch('../../BackEnd/get_paket.php');
            
            console.log('📊 Response status:', response.status);
            console.log('📋 Response headers:', response.headers.get('content-type'));
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('📄 Raw response:', text.substring(0, 200) + '...');
            
            let data;
            try {
                data = JSON.parse(text);
                console.log('✅ JSON parsed successfully:', data);
            } catch (parseError) {
                console.error('❌ JSON parse error:', parseError);
                console.log('🔍 Full response:', text);
                throw new Error('Invalid JSON response from server');
            }

            if (data.success && data.packages && Array.isArray(data.packages)) {
                console.log(`📦 Found ${data.packages.length} packages`);
                if (data.packages.length > 0) {
                    this.renderPackages(data.packages, container);
                } else {
                    this.showError(container, 'Belum ada paket wisata tersedia.');
                }
            } else {
                console.error('❌ Invalid data structure:', data);
                this.showError(container, 'Format data tidak valid.');
            }
        } catch (error) {
            console.error('❌ Error loading packages:', error);
            this.showError(container, `Gagal memuat paket: ${error.message}`);
        }
    },renderPackages(packages, container) {
        const packagesHTML = packages.map(pkg => {
            // Get first photo from fotos array
            const firstPhoto = pkg.fotos && pkg.fotos.length > 0 ? pkg.fotos[0] : '../../assets/images/borobudur.jpg';
            
            return `
            <div class="package-card">
                <div class="package-image">
                    <img src="${firstPhoto}" alt="${pkg.nama}" loading="lazy" onerror="this.src='../../assets/images/borobudur.jpg'">
                    <div class="package-overlay">
                        <a href="package_detail.html?id=${pkg.id}" class="btn btn-view">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
                <div class="package-content">
                    <h3 class="package-title">${pkg.nama}</h3>
                    <p class="package-description">${this.truncateText(pkg.deskripsi, 100)}</p>
                    <div class="package-meta">
                        <div class="package-duration">
                            <i class="fas fa-clock"></i>
                            Paket Wisata
                        </div>
                        <div class="package-price">
                            <i class="fas fa-images"></i>
                            ${pkg.foto_count} Foto
                        </div>
                    </div>
                    <div class="package-stats">
                        <span><i class="fas fa-star"></i> Paket ID: ${pkg.id}</span>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        container.innerHTML = packagesHTML;
    },

    showError(container, message) {
        container.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
                <button onclick="PackageLoader.loadPackages()" class="btn btn-retry">
                    <i class="fas fa-redo"></i> Coba Lagi
                </button>
            </div>
        `;
    },

    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    },

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
};

// =================================
// HERO RESPONSIVE MODULE
// =================================
const HeroResponsive = {
    init() {
        this.adjustHeroHeight();
        this.bindEvents();
    },

    bindEvents() {
        window.addEventListener('resize', () => {
            this.adjustHeroHeight();
        });

        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.adjustHeroHeight();
            }, 100);
        });
    },

    adjustHeroHeight() {
        const hero = document.querySelector('.hero');
        if (!hero) return;

        const windowHeight = window.innerHeight;
        const headerHeight = document.querySelector('header')?.offsetHeight || 0;
        
        hero.style.minHeight = `${windowHeight - headerHeight}px`;
    }
};

// =================================
// FORM UTILS MODULE
// =================================
const FormUtils = {
    init() {
        this.bindValidation();
    },

    bindValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    },

    validateField(field) {
        const value = field.value.trim();
        const type = field.type;

        // Remove previous error
        this.removeError(field);

        // Check if required field is empty
        if (field.hasAttribute('required') && !value) {
            this.showError(field, 'Field ini harus diisi');
            return false;
        }

        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showError(field, 'Email tidak valid');
                return false;
            }
        }

        // Phone validation
        if (field.name === 'phone' && value) {
            const phoneRegex = /^[0-9]{10,13}$/;
            if (!phoneRegex.test(value)) {
                this.showError(field, 'Nomor telepon tidak valid');
                return false;
            }
        }

        return true;
    },

    showError(field, message) {
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    },

    removeError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
};

// =================================
// UTILS MODULE
// =================================
const Utils = {
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    smoothScroll(target) {
        const element = document.querySelector(target);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    },

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
};

// =================================
// PROFILE GALLERY MODULE
// =================================
const ProfileGallery = {
    galleryImages: [
        "../../assets/images/borobudur.jpg",
        "../../assets/images/candimendut.jpg", 
        "../../assets/images/keraton.jpg",
        "../../assets/images/prambanan1.jpg",
        "../../assets/images/restoranjogja.jpg",
        "../../assets/images/tamansari.jpg"
    ],
    
    currentIndex: 0,
    bgImagesElems: [],
    
    init() {
        this.mainPhoto = document.getElementById("main-photo");
        this.galleryBackground = document.querySelector(".gallery-background");
        this.galleryContainer = document.querySelector(".gallery");
        
        if (!this.galleryContainer) return;
        
        this.setupResizeObserver();
        this.createSeamlessBackground();
        this.startImageRotation();
        this.bindAdminLogin();
    },
    
    setupResizeObserver() {
        const resizeObserver = new ResizeObserver(() => {
            this.createSeamlessBackground();
        });
        resizeObserver.observe(this.galleryContainer);
    },
    
    createSeamlessBackground() {
        if (!this.galleryBackground) return;
        
        this.galleryBackground.innerHTML = '';
        this.bgImagesElems = [];
        
        const containerWidth = this.galleryContainer.offsetWidth;
        const containerHeight = this.galleryContainer.offsetHeight;
        const imgSize = 120;
        const cols = Math.ceil(containerWidth / imgSize) * 2;
        const rows = Math.ceil(containerHeight / imgSize);
        
        const duplicatedImages = [...this.galleryImages, ...this.galleryImages, ...this.galleryImages];
        
        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                const imgIndex = (row * cols + col) % duplicatedImages.length;
                const bgImg = document.createElement("img");
                bgImg.src = duplicatedImages[imgIndex];
                bgImg.alt = `Gallery image`;
                bgImg.classList.add("bg-img");
                
                const left = col * imgSize * 0.9 + Math.random() * 20;
                const top = row * imgSize * 0.9 + Math.random() * 20;
                
                Object.assign(bgImg.style, {
                    width: `${imgSize}px`,
                    height: `${imgSize}px`,
                    position: 'absolute',
                    left: `${left}px`,
                    top: `${top}px`,
                    filter: "blur(6px)",
                    objectFit: "cover",
                    opacity: "0.8",
                    borderRadius: "4px"
                });
                
                this.galleryBackground.appendChild(bgImg);
                this.bgImagesElems.push(bgImg);
            }
        }
    },
    
    changeMainImage() {
        if (!this.mainPhoto) return;
        
        this.mainPhoto.style.opacity = "0";
        this.mainPhoto.style.transform = "scale(0.95)";
        
        setTimeout(() => {
            this.mainPhoto.src = this.galleryImages[this.currentIndex];
            this.mainPhoto.style.opacity = "1";
            this.mainPhoto.style.transform = "scale(1)";
            
            this.currentIndex = (this.currentIndex + 1) % this.galleryImages.length;
        }, 300);
    },
    
    startImageRotation() {
        if (this.galleryImages.length === 0 || !this.mainPhoto) return;
        
        // Set initial image
        this.mainPhoto.src = this.galleryImages[0];
        this.currentIndex = 1;
        
        // Start rotation
        setTimeout(() => {
            setInterval(() => this.changeMainImage(), 5000);
        }, 1500);
    },
    
    bindAdminLogin() {
        const adminBtn = document.getElementById('admin-login-btn');
        if (adminBtn) {
            adminBtn.addEventListener('click', () => {
                window.location.href = '../../BackEnd/loginadmin.php';
            });
        }
    }
};

// =================================
// PACKAGE CARDS SLIDESHOW MODULE
// =================================
const PackageSlideshow = {
    init() {
        // Initialize all package slideshows
        document.querySelectorAll('.image-slideshow').forEach((slideshow, index) => {
            this.initSlideshow(slideshow, index);
        });
        
        // Initialize indicators
        this.initIndicators();
    },

    initSlideshow(slideshow, slideshowIndex) {
        const images = slideshow.querySelectorAll('img');
        const indicators = slideshow.querySelectorAll('.slideshow-indicators .dot');
        
        if (images.length <= 1) return;

        let currentIndex = 0;
        let interval = null;

        // Auto-advance slideshow
        const startAutoSlide = () => {
            interval = setInterval(() => {
                currentIndex = (currentIndex + 1) % images.length;
                this.showSlide(slideshow, currentIndex);
            }, 3000);
        };

        const stopAutoSlide = () => {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        };

        // Show specific slide
        const showSlide = (index) => {
            images.forEach((img, i) => {
                img.classList.toggle('active', i === index);
            });
            
            indicators.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            // Update photo counter
            const counter = slideshow.querySelector('.photo-counter .current');
            if (counter) {
                counter.textContent = index + 1;
            }
        };

        // Bind indicator clicks
        indicators.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentIndex = index;
                showSlide(currentIndex);
                stopAutoSlide();
                startAutoSlide();
            });
        });

        // Start/stop on hover
        slideshow.addEventListener('mouseenter', stopAutoSlide);
        slideshow.addEventListener('mouseleave', startAutoSlide);

        // Initialize
        showSlide(0);
        startAutoSlide();

        // Store reference for external access
        slideshow.slideshowController = {
            showSlide,
            startAutoSlide,
            stopAutoSlide,
            currentIndex: () => currentIndex
        };
    },

    showSlide(slideshow, index) {
        const images = slideshow.querySelectorAll('img');
        const indicators = slideshow.querySelectorAll('.slideshow-indicators .dot');
        
        images.forEach((img, i) => {
            img.classList.toggle('active', i === index);
        });
        
        indicators.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });

        // Update photo counter
        const counter = slideshow.querySelector('.photo-counter .current');
        if (counter) {
            counter.textContent = index + 1;
        }
    },

    initIndicators() {
        document.querySelectorAll('.slideshow-indicators .dot').forEach(dot => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const slideshow = dot.closest('.image-slideshow');
                const index = parseInt(dot.dataset.slide);
                
                if (slideshow.slideshowController) {
                    slideshow.slideshowController.showSlide(index);
                }
            });
        });
    }
};

// Error handling for images
window.handleImageError = function(img, index) {
    console.warn('Image failed to load:', img.src);
    
    // Try fallback image
    const fallbackImages = [
        '../../assets/images/borobudur.jpg',
        '../../assets/images/prambanan1.jpg',
        '../../assets/images/keraton.jpg'
    ];
    
    const fallback = fallbackImages[index % fallbackImages.length];
    if (img.src !== fallback) {
        img.src = fallback;
        img.setAttribute('data-is-real', 'false');
    } else {
        // If fallback also fails, hide the image
        img.style.display = 'none';
    }
};

// Debug function for packages
window.debugPackages = function() {
    console.log('🔍 Starting package debug...');
    
    fetch('../../BackEnd/get_paket.php')
        .then(response => {
            console.log('📡 Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('📄 Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('✅ Parsed data:', data);
            } catch (e) {
                console.error('❌ JSON parse error:', e);
            }
        })
        .catch(error => {
            console.error('🚨 Fetch error:', error);
        });
};

// =================================
// INITIALIZATION
// =================================
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modules
    MobileNavigation.init();
    ImageSlideshow.init();
    PackageLoader.init();
    HeroResponsive.init();
    FormUtils.init();
    ProfileGallery.init(); // Add profile gallery
    PackageSlideshow.init(); // Initialize package slideshow
    
    console.log('🎯 Vacationland frontend initialized');
});

// Make modules globally available
window.VacationlandModules = {
    MobileNavigation,
    ImageSlideshow,
    PackageLoader,
    HeroResponsive,
    FormUtils,
    ProfileGallery, // Add profile gallery
    Utils
};
