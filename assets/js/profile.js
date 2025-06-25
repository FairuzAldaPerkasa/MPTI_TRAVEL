/**
 * MPTI TRAVEL - PROFILE PAGE SCRIPT
 *
 * Skrip ini menangani fungsionalitas khusus untuk halaman profil, termasuk:
 * 1. Tampilan dan logika untuk modal login admin.
 * 2. Pembaruan dinamis item misi berdasarkan data terjemahan.
 *
 * @version 1.0
 * @date 2025-06-22
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi sistem terjemahan secara eksplisit saat halaman profil dimuat
    if (window.translationSystem) {
        console.log('Translation system found, applying translations for profile page...');
        window.translationSystem.applyTranslations();
    } else {
        console.log('Translation system not found, waiting and retrying...');
        // Tunggu sebentar untuk sistem terjemahan dimuat, lalu coba lagi
        setTimeout(() => {
            if (window.translationSystem) {
                console.log('Translation system now available, applying translations...');
                window.translationSystem.applyTranslations();
            } else {
                console.warn('Translation system still not available after timeout');
            }
        }, 1000);
    }

    // Logika spesifik untuk halaman profil bisa ditambahkan di sini
    console.log("Halaman profil berhasil dimuat dan skrip profile.js berjalan.");

    // --- 1. ADMIN LOGIN MODAL ---
    const loginBtn = document.getElementById('admin-login-btn');
    const loginModal = document.getElementById('login-modal');
    const closeBtn = document.getElementById('login-close');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');

    if (loginBtn && loginModal && closeBtn && loginForm) {
        // Tampilkan modal saat tombol login diklik
        loginBtn.addEventListener('click', () => {
            loginModal.style.display = 'flex';
        });

        // Sembunyikan modal saat tombol close diklik
        closeBtn.addEventListener('click', () => {
            loginModal.style.display = 'none';
        });

        // Sembunyikan modal saat klik di luar area modal
        loginModal.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                loginModal.style.display = 'none';
            }
        });

        // Handle form submission
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            loginError.style.display = 'none';

            const formData = new FormData(loginForm);
            
            try {
                const response = await fetch('../../BackEnd/ViewLoginAdmin.php', { // URL fixed
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Jika sukses, redirect ke halaman admin
                    window.location.href = '../../BackEnd/admin.php';
                } else {
                    // Jika gagal, tampilkan pesan error
                    loginError.textContent = result.message || 'Login gagal. Periksa kembali email dan password Anda.';
                    loginError.style.display = 'block';
                }
            } catch (error) {
                console.error('Error during login:', error);
                loginError.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                loginError.style.display = 'block';
            }
        });
    }

    // --- 2. MISSION ITEMS TRANSLATION ---
    // Fungsi ini akan dipanggil oleh event 'languageChanged' dari translations.js
    const updateMissionItems = () => {
        if (!window.translationSystem) return;

        const missionItems = document.querySelectorAll('[data-mission-item]');
        missionItems.forEach((item, index) => {
            const key = 'profile.sections.mission.items';
            const missionData = window.translationSystem.getTranslation(key);

            if (Array.isArray(missionData) && missionData[index]) {
                item.textContent = missionData[index];
            }
        });    };

    // Panggil sekali saat load untuk bahasa default
    // Tunggu translationSystem siap
    const checkTranslationSystem = setInterval(() => {
        if (window.translationSystem) {
            clearInterval(checkTranslationSystem);
            updateMissionItems();
            // Tambahkan event listener untuk perubahan bahasa
            document.addEventListener('languageChanged', updateMissionItems);
        }
    }, 100);

    // Fallback manual untuk terjemahan jika sistem utama gagal
    setTimeout(() => {        const elementsToTranslate = {
            '[data-translate="profile.hero.title"]': 'Tentang MPTI Travel',
            '[data-translate="profile.hero.breadcrumbHome"]': 'Beranda',
            '[data-translate="profile.hero.breadcrumbAbout"]': 'Tentang Kami',
            '[data-translate="profile.about.title"]': 'Cerita Kami',
            '[data-translate="profile.about.subtitle"]': 'Perjalanan Kami Membangun MPTI Travel',
            '[data-translate="profile.about.content"]': 'MPTI Travel lahir dari kecintaan kami pada kekayaan budaya dan alam Indonesia. Sejak didirikan, kami bertekad untuk tidak hanya menjadi penyedia jasa perjalanan, tetapi juga menjadi jembatan yang menghubungkan wisatawan dengan esensi sejati dari setiap destinasi. Kami percaya bahwa perjalanan adalah tentang pengalaman, penemuan, dan kenangan yang abadi.',
            '[data-translate="profile.vision.title"]': 'Visi Kami',
            '[data-translate="profile.vision.content"]': 'Menjadi agen perjalanan terkemuka di Indonesia yang dikenal karena inovasi, layanan berkualitas, dan komitmen terhadap pariwisata berkelanjutan yang mengangkat komunitas lokal.',
            '[data-translate="profile.mission.title"]': 'Misi Kami',
            '[data-translate="profile.team.title"]': 'Tim Profesional Kami',
            '[data-translate="profile.team.subtitle"]': 'Orang-orang di Balik Perjalanan Anda',
            '[data-translate="profile.team.member1.name"]': 'Muhammad Raihan',
            '[data-translate="profile.team.member1.role"]': 'Project Manager',
            '[data-translate="profile.team.member2.name"]': 'Pascal Theophylus',
            '[data-translate="profile.team.member2.role"]': 'System Analyst',
            '[data-translate="profile.team.member3.name"]': 'I Kadek Agus',
            '[data-translate="profile.team.member3.role"]': 'Programmer',
            '[data-translate="profile.team.member4.name"]': 'Theodorus Karsten',
            '[data-translate="profile.team.member4.role"]': 'UI/UX Designer'
        };

        // Terapkan terjemahan manual jika elemen masih memiliki key
        Object.entries(elementsToTranslate).forEach(([selector, text]) => {
            const element = document.querySelector(selector);
            if (element && element.textContent.includes('profile.')) {
                console.log(`Applying manual translation for: ${selector}`);
                element.textContent = text;
            }
        });
    }, 2000); // Tunggu 2 detik untuk memberikan waktu sistem utama

});