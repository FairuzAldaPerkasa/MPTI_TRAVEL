/**
 * MPTI TRAVEL - PACKAGE DETAIL LOADER
 * Version 8.0 - Modern UI Implementation
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
    }

    // Load package detail
    loadPackageDetail(packageId);

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
                <p>Memuat detail paket wisata...</p>
            </div>
        `;
    }

    function showError(message) {
        console.log('❌ Showing error state:', message);
        packageContent.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Gagal Memuat Detail Paket</h4>
                <p>${message}</p>
                <button onclick="location.reload()">Coba Lagi</button>
                <br>
                <a href="Index.html">← Kembali ke Beranda</a>
            </div>
        `;
    }

    function displayPackageDetail(pkg) {
        console.log('🎨 Displaying package detail:', pkg.nama);
        
        // Update page title
        document.title = `${pkg.nama} | Vacationland`;
        
        // Build modern HTML content
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
                            <span>Yogyakarta</span>
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
                            Overview
                        </button>
                        <button class="tab-button" data-tab="gallery">
                            <i class="fas fa-images"></i>
                            Gallery
                        </button>
                        <button class="tab-button" data-tab="itinerary">
                            <i class="fas fa-route"></i>
                            Itinerary
                        </button>
                        <button class="tab-button" data-tab="features">
                            <i class="fas fa-list-check"></i>
                            Features
                        </button>
                        <button class="tab-button" data-tab="highlights">
                            <i class="fas fa-star"></i>
                            Highlights
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div id="overview" class="tab-panel active">
                            <div class="overview-grid">
                                <div class="description-card">
                                    <h3><i class="fas fa-info-circle"></i> Deskripsi Paket</h3>
                                    <p>${pkg.deskripsi_singkat || pkg.deskripsi}</p>
                                    ${pkg.deskripsi !== pkg.deskripsi_singkat && pkg.deskripsi ? `<p>${pkg.deskripsi}</p>` : ''}
                                </div>
                                <div class="quick-info-card">
                                    <div class="quick-info-item">
                                        <i class="fas fa-clock"></i>
                                        <strong>Durasi</strong>
                                        <span>${pkg.duration}</span>
                                    </div>
                                    <div class="quick-info-item">
                                        <i class="fas fa-tag"></i>
                                        <strong>Harga</strong>
                                        <span>${pkg.formattedPrice}</span>
                                    </div>
                                    <div class="quick-info-item">
                                        <i class="fas fa-users"></i>
                                        <strong>Grup</strong>
                                        <span>Min. 2 orang</span>
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
                            <h3><i class="fas fa-route"></i> Itinerary Perjalanan</h3>
                            ${renderItinerary(pkg.itinerary)}
                        </div>

                        <!-- Features Tab -->
                        <div id="features" class="tab-panel">
                            ${renderFeatures(pkg)}
                        </div>

                        <!-- Highlights Tab -->
                        <div id="highlights" class="tab-panel">
                            <h3><i class="fas fa-star"></i> Highlights Perjalanan</h3>
                            ${renderHighlights(pkg.highlights)}
                        </div>
                    </div>

                    <!-- Booking Section -->
                    <div class="booking-section">
                        <h3>Siap untuk Berpetualang?</h3>
                        <p>Jangan lewatkan kesempatan untuk menikmati paket wisata terbaik ini!</p>
                        <div class="booking-buttons">
                            <a href="https://wa.me/6281234567890?text=Halo%2C%20saya%20tertarik%20dengan%20paket%20${encodeURIComponent(pkg.nama)}" 
                               target="_blank" 
                               class="btn-booking primary">
                                <i class="fab fa-whatsapp"></i>
                                Book via WhatsApp
                            </a>
                            <a href="tel:+6281234567890" class="btn-booking secondary">
                                <i class="fas fa-phone"></i>
                                Hubungi Kami
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        packageContent.innerHTML = html;
        
        // Initialize tab functionality
        initializeTabs();
        
        console.log('✅ Package detail displayed successfully');
    }

    // =========================================================================
    // RENDER HELPER FUNCTIONS
    // =========================================================================

    function renderGallery(pkg) {
        if (!pkg.fotos || pkg.fotos.length === 0) {
            return `
                <div class="no-gallery" style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>Tidak ada foto galeri tersedia.</p>
                </div>
            `;
        }

        return `
            <div class="gallery-grid">
                ${pkg.fotos.map((foto, index) => `
                    <div class="gallery-item">
                        <img src="${foto}" alt="Gallery ${index + 1}" onerror="this.style.display='none'">
                        <div class="gallery-overlay">
                            <p>Foto ${index + 1}</p>
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
                    <h3><i class="fas fa-check-circle"></i> Yang Termasuk</h3>
                    <ul class="feature-list">
                        ${renderInclusions(pkg.inclusions)}
                    </ul>
                </div>
                <div class="feature-card exclusions">
                    <h3><i class="fas fa-times-circle"></i> Yang Tidak Termasuk</h3>
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
                    <p>Itinerary akan diinformasikan kemudian.</p>
                </div>
            `;
        }
        
        return `
            <div class="itinerary-timeline">
                ${itinerary.map(item => `
                    <div class="itinerary-day">
                        <div class="day-header">
                            <i class="fas fa-calendar-day"></i>
                            Hari ${item.day}: ${item.title || `Hari ${item.day}`}
                        </div>
                        <div class="day-activities">
                            ${item.activities && Array.isArray(item.activities) ? 
                                item.activities.map(activity => `
                                    <div class="activity-item">
                                        <div class="activity-time">${activity.time}</div>
                                        <div class="activity-description">${activity.activity}</div>
                                    </div>
                                `).join('') : 
                                '<p style="color: #666; font-style: italic;">Detail aktivitas akan diinformasikan kemudian.</p>'
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
                    <span>Informasi akan diperbarui kemudian.</span>
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
                    <span>Informasi akan diperbarui kemudian.</span>
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
                    <p>Highlights akan diinformasikan kemudian.</p>
                </div>
            `;
        }
        
        if (Array.isArray(highlights)) {
            return `
                <div class="highlights-grid">
                    ${highlights.map((highlight, index) => `
                        <div class="highlight-card">
                            <i class="fas fa-star"></i>
                            <h4>Highlight ${index + 1}</h4>
                            <p>${highlight}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        return `
            <div class="highlight-card">
                <i class="fas fa-star"></i>
                <h4>Highlight Utama</h4>
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
});
