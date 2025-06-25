document.addEventListener('DOMContentLoaded', function() {
    const packagesContainer = document.getElementById('packages-container');

    // Pastikan elemen container ada sebelum melanjutkan
    if (!packagesContainer) {
        console.error("Element with ID 'packages-container' not found.");
        return;
    }

    async function fetchPackages() {
        const lang = localStorage.getItem('mpti_language') || 'id';
        
        // Tampilkan status loading
        renderLoading();

        try {
            const response = await fetch(`../../BackEnd/get_paket.php`, {
                headers: {
                    'X-Language': lang
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                renderPackages(result.data);
            } else {
                throw new Error(result.message || 'Gagal memuat data paket.');
            }
        } catch (error) {
            console.error("Fetch error:", error);
            renderError(error.message);
        }
    }

    function renderLoading() {
        packagesContainer.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <p data-translate="common.loading">Memuat paket wisata...</p>
            </div>
        `;
        // Pastikan sistem terjemahan ada sebelum memanggil fungsinya
        if (window.translationSystem && typeof window.translationSystem.translatePage === 'function') {
            window.translationSystem.translatePage();
        }
    }    function renderPackages(packages) {
        packagesContainer.innerHTML = ''; // Hapus loading state
        if (packages.length === 0) {
            packagesContainer.innerHTML = `<p data-translate="packages.no_packages">Tidak ada paket wisata yang tersedia saat ini.</p>`;
            window.translationSystem.translatePage();
            return;
        }

        packages.forEach(pkg => {
            const card = document.createElement('div');
            card.className = 'card';
            card.innerHTML = `
                <div class="card-image-container">
                    <img src="${pkg.fotos[0]}" alt="${pkg.nama}" class="card-image" loading="lazy">
                    <div class="card-duration">${pkg.duration} ${window.translationSystem.getTranslation('packages.days', 'Hari')}</div>
                </div>
                <div class="card-content">
                    <h3 class="card-title">${pkg.nama}</h3>
                    <p class="card-price">
                        <span data-translate="packages.from">Mulai dari</span>
                        <strong>${pkg.formattedPrice}</strong>
                    </p>
                    <a href="package_detail.html?id=${pkg.id}" class="card-button" data-translate="packages.details">Lihat Detail</a>
                </div>
            `;
            packagesContainer.appendChild(card);
            
            // Debug: Add click event listener untuk debugging
            const cardButton = card.querySelector('.card-button');
            if (cardButton) {
                cardButton.addEventListener('click', function(e) {
                    console.log('🔍 Card button clicked:', pkg.nama, pkg.id);
                    console.log('🔍 Link href:', this.href);
                    console.log('🔍 Event details:', e);
                    
                    // Jika href tidak bekerja, fallback ke window.location
                    if (!this.href || this.href === '#') {
                        e.preventDefault();
                        window.location.href = `package_detail.html?id=${pkg.id}`;
                    }
                });
                
                // Debug: Test if button is clickable
                cardButton.addEventListener('mouseenter', function() {
                    console.log('🔍 Card button hover:', pkg.nama);
                });
            }
        });
        
        // Terjemahkan konten kartu yang baru jika sistem terjemahan tersedia
        if (window.translationSystem && typeof window.translationSystem.translatePage === 'function') {
            window.translationSystem.translatePage();
        }
    }

    function renderError(errorMessage) {
        packagesContainer.innerHTML = `
            <div class="error-message">
                <h4 data-translate="packageDetail.error.title">Gagal Memuat Data</h4>
                <p>Error: ${errorMessage}</p>
                <button id="retry-load" data-translate="packageDetail.error.retry">Coba Lagi</button>
            </div>
        `;
        document.getElementById('retry-load').addEventListener('click', fetchPackages);
        if (window.translationSystem && typeof window.translationSystem.translatePage === 'function') {
            window.translationSystem.translatePage();
        }
    }

    // Muat paket saat halaman dimuat
    fetchPackages();

    // Muat ulang paket saat bahasa berubah
    window.addEventListener('languageChanged', fetchPackages);
});
