/**
 * MPTI TRAVEL - PACKAGE DETAIL LOADER
 * Version 8.1 - Modern UI with Translation Support
 */

console.log('🔍 Package detail loader starting...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM loaded, initializing package detail loader');
    
    const packageContent = document.getElementById('package-content');
    
    if (!packageContent) {
        console.error("❌ Element 'package-content' not found");
        return;
    }

    // Get package ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const packageId = urlParams.get('id');
    
    console.log('🔍 Package ID from URL:', packageId);
    
    if (!packageId) {
        showError('ID paket tidak valid atau tidak ditemukan di URL.');
        return;
    }    // Load settings and package detail
    loadSettings().then(() => {
        loadPackageDetail(packageId);
    });

    // =========================================================================
    // SETTINGS & CONFIGURATION
    // =========================================================================
    
    let websiteSettings = {};
    
    async function loadSettings() {
        console.log('🔧 Loading website settings...');
        try {
            const response = await fetch('../../BackEnd/get_settings.php');
            const result = await response.json();
            
            if (result.success) {
                websiteSettings = result.data;
                console.log('✅ Settings loaded:', websiteSettings);
            } else {
                console.warn('⚠️ Failed to load settings, using defaults');
                websiteSettings = {
                    whatsapp_number: '6281234567890',
                    phone_number: '0812-3456-789',
                    whatsapp_message: 'Halo, saya tertarik dengan paket wisata'
                };
            }
        } catch (error) {
            console.error('❌ Error loading settings:', error);
            websiteSettings = {
                whatsapp_number: '6281234567890',
                phone_number: '0812-3456-789',
                whatsapp_message: 'Halo, saya tertarik dengan paket wisata'
            };
        }
    }

    // =========================================================================
    // MAIN FUNCTIONS
    // =========================================================================

    async function loadPackageDetail(id) {
        console.log(`📡 Loading package detail for ID: ${id}`);
        
        showLoading();
        
        try {
            const url = `../../BackEnd/get_package_detail.php?id=${id}`;
            console.log(`🔗 Fetching from: ${url}`);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Language': localStorage.getItem('mpti_language') || 'id'
                }
            });

            console.log(`📊 Response status: ${response.status}`);

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`❌ HTTP Error ${response.status}:`, errorText);
                throw new Error(`Server error: ${response.status}`);
            }

            const text = await response.text();
            console.log(`📄 Raw response length: ${text.length} chars`);

            let data;
            try {
                data = JSON.parse(text);
                console.log('✅ JSON parsed successfully');
            } catch (parseError) {
                console.error('❌ JSON parse error:', parseError);
                console.log('📄 Full response:', text);
                throw new Error('Invalid response format from server');
            }

            console.log('📦 API Response:', data);

            if (data.success && data.data) {
                console.log('✅ Package data received');
                displayPackageDetail(data.data);
            } else {
                throw new Error(data.message || 'Failed to load package data');
            }

        } catch (error) {
            console.error('🚨 Error loading package:', error);
            showError(`Error: ${error.message}`);
        }
    }

    function showLoading() {
        console.log('⏳ Showing loading state');
        packageContent.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <p data-translate="common.loading">Memuat detail paket wisata...</p>
            </div>
        `;
    }    function showError(message) {
        console.log('❌ Showing error state:', message);
        packageContent.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h4 data-translate="packageDetail.error.title">Gagal Memuat Detail Paket</h4>
                <p>${message}</p>
                <button onclick="location.reload()" data-translate="packageDetail.error.retry">Coba Lagi</button>
                <br>
                <a href="Index.html" data-translate="packageDetail.error.backToHome">← Kembali ke Beranda</a>
            </div>
        `;
        
        // Apply translations
        if (window.translationSystem && typeof window.translationSystem.translatePage === 'function') {
            window.translationSystem.translatePage();
        }
    }    function getTranslatedWhatsAppMessage() {
        const customMessage = websiteSettings.whatsapp_message || 'Halo, saya tertarik dengan paket wisata';
        if (window.translationSystem && typeof window.translationSystem.getTranslation === 'function') {
            return window.translationSystem.getTranslation('packageDetail.booking.whatsappMessage', customMessage);
        }
        return customMessage;
    }
    
    function getWhatsAppNumber() {
        return websiteSettings.whatsapp_number || '6281234567890';
    }
    
    function getPhoneNumber() {
        return websiteSettings.phone_number || '0812-3456-789';
    }

    // =========================================================================
    // RENDER HELPER FUNCTIONS
    // =========================================================================

    function renderGallery(pkg) {
        if (!pkg.fotos || pkg.fotos.length === 0) {
            return `
                <div class="no-gallery" style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p data-translate="packageDetail.gallery.noGallery">Tidak ada foto galeri tersedia.</p>
                </div>
            `;
        }

        return `
            <div class="gallery-grid">
                ${pkg.fotos.map((foto, index) => `
                    <div class="gallery-item">
                        <img src="${foto}" alt="Gallery ${index + 1}" onerror="this.style.display='none'">
                        <div class="gallery-overlay">
                            <p><span data-translate="packageDetail.gallery.photoLabel">Foto</span> ${index + 1}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function renderFeatures(pkg) {
        return `
            <div class="features-grid">
                <div class="feature-card inclusions">
                    <h3><i class="fas fa-check-circle"></i> <span data-translate="packageDetail.features.included">Yang Termasuk</span></h3>
                    <ul class="feature-list">
                        ${renderInclusions(pkg.inclusions)}
                    </ul>
                </div>
                <div class="feature-card exclusions">
                    <h3><i class="fas fa-times-circle"></i> <span data-translate="packageDetail.features.excluded">Yang Tidak Termasuk</span></h3>
                    <ul class="feature-list">
                        ${renderExclusions(pkg.exclusions)}
                    </ul>
                </div>
            </div>
        `;
    }

    function renderItinerary(itinerary) {
        if (!itinerary || !Array.isArray(itinerary) || itinerary.length === 0) {
            return `
                <div class="no-itinerary" style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-route" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p data-translate="packageDetail.itinerary.noItinerary">Itinerary akan diinformasikan kemudian.</p>
                </div>
            `;
        }
        
        return `
            <div class="itinerary-timeline">
                ${itinerary.map(item => `
                    <div class="itinerary-day">
                        <div class="day-header">
                            <i class="fas fa-calendar-day"></i>
                            <span data-translate="packageDetail.itinerary.day">Hari</span> ${item.day}: ${item.title || `Hari ${item.day}`}
                        </div>
                        <div class="day-activities">
                            ${item.activities && Array.isArray(item.activities) ? 
                                item.activities.map(activity => `
                                    <div class="activity-item">
                                        <div class="activity-time">${activity.time}</div>
                                        <div class="activity-description">${activity.activity}</div>
                                    </div>
                                `).join('') : 
                                '<p style="color: #666; font-style: italic;" data-translate="packageDetail.itinerary.activitiesLater">Detail aktivitas akan diinformasikan kemudian.</p>'
                            }
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function renderInclusions(inclusions) {
        if (!inclusions) {
            return `
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span data-translate="packageDetail.features.infoLater">Informasi akan diperbarui kemudian.</span>
                </li>
            `;
        }
        
        if (typeof inclusions === 'object' && !Array.isArray(inclusions)) {
            return Object.entries(inclusions).map(([icon, text]) => `
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="${icon}"></i>
                    </div>
                    <span>${text}</span>
                </li>
            `).join('');
        }
        
        if (Array.isArray(inclusions)) {
            return inclusions.map(item => `
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>${item}</span>
                </li>
            `).join('');
        }
        
        return `
            <li class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-check"></i>
                </div>
                <span>${inclusions}</span>
            </li>
        `;
    }

    function renderExclusions(exclusions) {
        if (!exclusions) {
            return `
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span data-translate="packageDetail.features.infoLater">Informasi akan diperbarui kemudian.</span>
                </li>
            `;
        }
        
        if (typeof exclusions === 'object' && !Array.isArray(exclusions)) {
            return Object.entries(exclusions).map(([icon, text]) => `
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="${icon}"></i>
                    </div>
                    <span>${text}</span>
                </li>
            `).join('');
        }
        
        if (Array.isArray(exclusions)) {
            return exclusions.map(item => `
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-times"></i>
                    </div>
                    <span>${item}</span>
                </li>
            `).join('');
        }
        
        return `
            <li class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-times"></i>
                </div>
                <span>${exclusions}</span>
            </li>
        `;
    }

    function renderHighlights(highlights) {
        if (!highlights || (Array.isArray(highlights) && highlights.length === 0)) {
            return `
                <div class="no-highlights" style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p data-translate="packageDetail.highlights.noHighlights">Highlights akan diinformasikan kemudian.</p>
                </div>
            `;
        }
        
        if (Array.isArray(highlights)) {
            return `
                <div class="highlights-grid">
                    ${highlights.map((highlight, index) => `
                        <div class="highlight-card">
                            <i class="fas fa-star"></i>
                            <h4><span data-translate="packageDetail.highlights.highlight">Highlight</span> ${index + 1}</h4>
                            <p>${highlight}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        return `
            <div class="highlight-card">
                <i class="fas fa-star"></i>
                <h4 data-translate="packageDetail.highlights.main">Highlight Utama</h4>
                <p>${highlights}</p>
            </div>
        `;
    }

    // =========================================================================
    // TAB FUNCTIONALITY
    // =========================================================================

    function initializeTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanels = document.querySelectorAll('.tab-panel');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and panels
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanels.forEach(panel => panel.classList.remove('active'));
                
                // Add active class to clicked button and corresponding panel
                this.classList.add('active');
                const targetPanel = document.getElementById(targetTab);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
                
                console.log('🔄 Tab switched to:', targetTab);
            });
        });
    }

    function displayPackageDetail(pkg) {
        console.log('🎨 Displaying package detail:', pkg.nama);
        
        // Update page title
        document.title = `${pkg.nama} | Vacationland`;
        
        // Build modern HTML content with translations
        const html = `
            <!-- Hero Section -->
            <div class="package-hero">
                <img src="${pkg.fotos && pkg.fotos[0] ? pkg.fotos[0] : '../../assets/images/borobudur.jpg'}" 
                     alt="${pkg.nama}" 
                     class="package-hero-image"
                     onerror="this.src='../../assets/images/borobudur.jpg'">
                
                <div class="package-hero-content">
                    <h1>${pkg.nama}</h1>
                    <div class="package-meta">
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>${pkg.duration}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span data-translate="packageDetail.meta.location">Yogyakarta</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span class="price-highlight">${pkg.formattedPrice}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Wrapper -->
            <div class="package-content-wrapper">
                <div class="package-content-card">
                    <!-- Navigation Tabs -->
                    <div class="package-tabs">
                        <button class="tab-button active" data-tab="overview">
                            <i class="fas fa-info-circle"></i>
                            <span data-translate="packageDetail.tabs.overview">Overview</span>
                        </button>
                        <button class="tab-button" data-tab="gallery">
                            <i class="fas fa-images"></i>
                            <span data-translate="packageDetail.tabs.gallery">Gallery</span>
                        </button>
                        <button class="tab-button" data-tab="itinerary">
                            <i class="fas fa-route"></i>
                            <span data-translate="packageDetail.tabs.itinerary">Itinerary</span>
                        </button>
                        <button class="tab-button" data-tab="features">
                            <i class="fas fa-list-check"></i>
                            <span data-translate="packageDetail.tabs.features">Features</span>
                        </button>
                        <button class="tab-button" data-tab="highlights">
                            <i class="fas fa-star"></i>
                            <span data-translate="packageDetail.tabs.highlights">Highlights</span>
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div id="overview" class="tab-panel active">
                            <div class="overview-grid">
                                <div class="description-card">
                                    <h3><i class="fas fa-info-circle"></i> <span data-translate="packageDetail.sections.description">Deskripsi Paket</span></h3>
                                    <p>${pkg.deskripsi_singkat || pkg.deskripsi}</p>
                                    ${pkg.deskripsi !== pkg.deskripsi_singkat && pkg.deskripsi ? `<p>${pkg.deskripsi}</p>` : ''}
                                </div>
                                <div class="quick-info-card">
                                    <div class="quick-info-item">
                                        <i class="fas fa-clock"></i>
                                        <strong data-translate="packageDetail.meta.duration">Durasi</strong>
                                        <span>${pkg.duration}</span>
                                    </div>
                                    <div class="quick-info-item">
                                        <i class="fas fa-tag"></i>
                                        <strong data-translate="packageDetail.meta.price">Harga</strong>
                                        <span>${pkg.formattedPrice}</span>
                                    </div>
                                    <div class="quick-info-item">
                                        <i class="fas fa-users"></i>
                                        <strong data-translate="packageDetail.meta.group">Grup</strong>
                                        <span data-translate="packageDetail.meta.minGroup">Min. 2 orang</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Tab -->
                        <div id="gallery" class="tab-panel">
                            ${renderGallery(pkg)}
                        </div>

                        <!-- Itinerary Tab -->
                        <div id="itinerary" class="tab-panel">
                            <h3><i class="fas fa-route"></i> <span data-translate="packageDetail.sections.itinerary">Itinerary Perjalanan</span></h3>
                            ${renderItinerary(pkg.itinerary)}
                        </div>

                        <!-- Features Tab -->
                        <div id="features" class="tab-panel">
                            ${renderFeatures(pkg)}
                        </div>

                        <!-- Highlights Tab -->
                        <div id="highlights" class="tab-panel">
                            <h3><i class="fas fa-star"></i> <span data-translate="packageDetail.sections.highlights">Highlights Perjalanan</span></h3>
                            ${renderHighlights(pkg.highlights)}
                        </div>
                    </div>

                    <!-- Booking Section -->
                    <div class="booking-section">
                        <h3 data-translate="packageDetail.booking.title">Siap untuk Berpetualang?</h3>
                        <p data-translate="packageDetail.booking.subtitle">Jangan lewatkan kesempatan untuk menikmati paket wisata terbaik ini!</p>                        <div class="booking-buttons">
                            <a href="https://wa.me/${getWhatsAppNumber()}?text=${encodeURIComponent(getTranslatedWhatsAppMessage() + ' ' + pkg.nama)}" 
                               target="_blank" 
                               class="btn-booking primary">
                                <i class="fab fa-whatsapp"></i>
                                <span data-translate="packageDetail.booking.whatsapp">Book via WhatsApp</span>
                            </a>
                            <a href="tel:+${getPhoneNumber()}" class="btn-booking secondary">
                                <i class="fas fa-phone"></i>
                                <span data-translate="packageDetail.booking.call">Hubungi Kami</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        packageContent.innerHTML = html;
        
        // Initialize tab functionality
        initializeTabs();        
        // Apply translations
        if (window.translationSystem && typeof window.translationSystem.translatePage === 'function') {
            window.translationSystem.translatePage();
        }
        
        console.log('✅ Package detail displayed successfully');
    }
});
