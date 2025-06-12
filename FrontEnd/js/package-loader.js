/* =================================
   PACKAGE LOADER FOR INDEX PAGE
   JavaScript untuk memuat dan menampilkan packages
   ================================= */

console.log('📦 Package loader script loaded');

// Package loader functionality
let packagesData = [];
let isLoading = false;

// Load packages from API
async function loadPackages() {
    if (isLoading) return;
    
    console.log('📡 Loading packages from API...');
    isLoading = true;
    
    const packagesContainer = document.getElementById('packages-container');
    if (!packagesContainer) {
        console.error('❌ Packages container not found');
        return;
    }

    try {
        // Show loading state
        showLoadingState(packagesContainer);
        
        const response = await fetch('../../BackEnd/get_paket.php');
        console.log('📡 API Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        console.log('📄 Raw API response:', text.substring(0, 200) + '...');
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('❌ JSON parse error:', parseError);
            console.log('📄 Full response that failed to parse:', text);
            throw new Error('Invalid JSON response from server');
        }
        
        console.log('✅ Parsed API response:', data);
        
        // Handle different response formats
        if (data.success === false) {
            throw new Error(data.message || 'Server returned error');
        }
        
        // Extract packages array
        let packages = [];
        if (data.success && Array.isArray(data.packages)) {
            packages = data.packages;
        } else if (Array.isArray(data)) {
            packages = data;
        } else if (data.data && Array.isArray(data.data)) {
            packages = data.data;
        } else {
            console.warn('⚠️ Unexpected data format:', data);
            packages = [];
        }
        
        console.log(`📦 Found ${packages.length} packages`);
        packagesData = packages;
        
        if (packages.length === 0) {
            showEmptyState(packagesContainer);
        } else {
            displayPackages(packages, packagesContainer);
        }
        
    } catch (error) {
        console.error('🚨 Error loading packages:', error);
        showErrorState(packagesContainer, error.message);
    } finally {
        isLoading = false;
    }
}

// Show loading state
function showLoadingState(container) {
    container.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <h3>Memuat Paket Wisata...</h3>
            <p>Mohon tunggu sebentar</p>
            <p><small><a href="javascript:debugPackages()" style="color: #3498db;">Debug API</a></small></p>
        </div>
    `;
}

// Show error state
function showErrorState(container, message) {
    container.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Gagal Memuat Data</h3>
            <p>Error: ${message}</p>
            <button onclick="loadPackages()" class="cta-button">
                <i class="fas fa-refresh"></i> Coba Lagi
            </button>
        </div>
    `;
}

// Show empty state
function showEmptyState(container) {
    container.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-suitcase-rolling"></i>
            <h3>Belum Ada Paket Tour</h3>
            <p>Silakan tambah paket baru di admin panel.</p>
            <a href="../../BackEnd/admin.php" class="cta-button">
                <i class="fas fa-plus"></i> Tambah Paket
            </a>
        </div>
    `;
}

// Display packages
function displayPackages(packages, container) {
    console.log(`🎨 Displaying ${packages.length} packages`);
    
    if (!container) {
        console.error('❌ Container element not found');
        return;
    }
    
    container.innerHTML = '';
    
    packages.forEach((pkg, index) => {
        try {
            console.log(`🏗️ Creating card ${index + 1}:`, {
                id: pkg.id,
                nama: pkg.nama,
                fotos: pkg.fotos ? pkg.fotos.length : 0
            });
            
            const card = createPackageCard(pkg, index);
            container.appendChild(card);
        } catch (error) {
            console.error(`❌ Error creating card ${index + 1}:`, error, pkg);
        }
    });
    
    // Initialize slideshows after cards are created
    setTimeout(() => {
        if (window.PackageSlideshow) {
            window.PackageSlideshow.init();
        }
        console.log(`✅ ${packages.length} packages displayed`);
    }, 100);
}

// Create package card
function createPackageCard(pkg, index) {
    const card = document.createElement('div');
    card.className = 'tour-card';
    card.setAttribute('data-package-id', pkg.id);
    
    const nama = (pkg.nama || 'Paket Wisata').toString();
    const deskripsi = (pkg.deskripsi || 'Deskripsi tidak tersedia').toString();
    const description = deskripsi.length > 120 ? deskripsi.substring(0, 120) + '...' : deskripsi;
    
    // Process photos
    let slideshowHTML = '';
    if (pkg.fotos && pkg.fotos.length > 0) {
        const imageId = `slideshow-${pkg.id}`;
        const hasRealPhotos = pkg.fotos_exist && pkg.fotos_exist.some(exists => exists === true);
        
        slideshowHTML = `
            <div class="image-slideshow" id="${imageId}">
                ${pkg.fotos.map((foto, idx) => {
                    const isRealPhoto = pkg.fotos_exist && pkg.fotos_exist[idx] === true;
                    return `
                        <img src="${foto}" 
                             alt="${nama} - Foto ${idx + 1}" 
                             data-index="${idx}"
                             data-is-real="${isRealPhoto}"
                             class="${idx === 0 ? 'active' : ''}"
                             loading="lazy"
                             onload="this.setAttribute('data-loaded', 'true');"
                             onerror="handleImageError(this, ${idx});">
                    `;
                }).join('')}
                
                ${pkg.fotos.length > 1 ? `
                    <div class="slideshow-indicators">
                        ${pkg.fotos.map((_, idx) => `
                            <span class="dot ${idx === 0 ? 'active' : ''}" data-slide="${idx}"></span>
                        `).join('')}
                    </div>
                    <div class="photo-counter">
                        <span class="current">1</span>/<span class="total">${pkg.fotos.length}</span>
                    </div>
                ` : ''}
                
                <div class="image-badge ${hasRealPhotos ? 'original' : 'fallback'}">
                    ${hasRealPhotos ? '📷 Foto Asli' : '🖼️ Foto Default'}
                </div>
            </div>
        `;
    } else {
        slideshowHTML = `
            <div class="image-slideshow">
                <div class="no-image-placeholder">
                    <i class="fas fa-image"></i>
                    <p>Foto tidak tersedia</p>
                </div>
            </div>
        `;
    }
    
    card.innerHTML = `
        ${slideshowHTML}
        <div class="content">
            <h3>${nama}</h3>
            <p>${description}</p>
            <div class="package-meta">
                <div class="image-info">
                    <i class="fas fa-images"></i>
                    ${pkg.foto_count || 0} Foto
                    ${pkg.fotos_exist && pkg.fotos_exist.some(exists => exists) ? 
                        '(Ada foto asli)' : '(Semua default)'}
                </div>
            </div>
            <a href="package_detail.html?id=${pkg.id}" class="detail-button">
                <i class="fas fa-info-circle"></i> Lihat Detail
            </a>
        </div>
    `;
    
    return card;
}

// Debug function
window.debugPackages = function() {
    console.log('🔍 Starting package debug...');
    
    fetch('../../BackEnd/get_paket.php')
        .then(response => {
            console.log('📡 Debug - Response status:', response.status);
            console.log('📡 Debug - Response headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('📄 Debug - Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('✅ Debug - Parsed data:', data);
                console.log('📊 Debug - Data structure:', {
                    type: typeof data,
                    isArray: Array.isArray(data),
                    hasSuccess: 'success' in data,
                    hasPackages: 'packages' in data,
                    keys: Object.keys(data)
                });
            } catch (e) {
                console.error('❌ Debug - JSON parse error:', e);
            }
        })
        .catch(error => {
            console.error('🚨 Debug - Fetch error:', error);
        });
};

// Error handling for images
window.handleImageError = function(img, index) {
    console.warn('⚠️ Image failed to load:', img.src);
    
    const fallbackImages = [
        '../../assets/images/borobudur.jpg',
        '../../assets/images/prambanan1.jpg',
        '../../assets/images/keraton.jpg',
        '../../assets/images/tamansari.jpg'
    ];
    
    const fallback = fallbackImages[index % fallbackImages.length];
    if (img.src !== fallback) {
        console.log('🔄 Trying fallback image:', fallback);
        img.src = fallback;
        img.setAttribute('data-is-real', 'false');
    } else {
        console.warn('💀 Fallback image also failed, hiding image');
        img.style.opacity = '0';
        img.style.display = 'none';
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM loaded, initializing package loader...');
    
    // Wait a bit for other scripts to load
    setTimeout(() => {
        loadPackages();
    }, 500);
});

// Auto-reload packages every 30 seconds
setInterval(() => {
    if (!isLoading && document.visibilityState === 'visible') {
        console.log('🔄 Auto-refreshing packages...');
        loadPackages();
    }
}, 30000);

// Reload when page becomes visible
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && !isLoading) {
        console.log('👁️ Page visible, refreshing packages...');
        loadPackages();
    }
});

console.log('✅ Package loader script initialized');
