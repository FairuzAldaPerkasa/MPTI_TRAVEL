/**
 * MPTI TRAVEL - MAIN JAVASCRIPT
 *
 * Berisi skrip utama untuk situs MPTI Travel, termasuk:
 * 1. Navigasi Mobile Universal (Sidebar, Toggle, Overlay)
 * 2. Efek Scroll pada Header
 * 3. Akordeon Itinerary (untuk halaman detail paket)
 *
 * @version 1.1
 * @date 2025-06-22
 */

document.addEventListener("DOMContentLoaded", function () {

    // --- 1. NAVIGASI MOBILE UNIVERSAL ---
    const mobileToggle = document.querySelector(".mobile-menu-toggle");
    const sidebar = document.querySelector(".mobile-sidebar");
    const overlay = document.querySelector(".sidebar-overlay");
    const body = document.body;

    // Fungsi untuk menutup sidebar
    const closeSidebar = () => {
        if (mobileToggle) mobileToggle.classList.remove("active");
        if (sidebar) sidebar.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
        if (body) body.classList.remove("sidebar-open");
    };

    // Fungsi untuk membuka sidebar
    const openSidebar = () => {
        if (mobileToggle) mobileToggle.classList.add("active");
        if (sidebar) sidebar.classList.add("active");
        if (overlay) overlay.classList.add("active");
        if (body) body.classList.add("sidebar-open");
    };
    
    // Hanya jalankan jika elemen navigasi penting ada
    if (mobileToggle && sidebar && overlay) {
        // Event listener untuk tombol toggle
        mobileToggle.addEventListener("click", function (e) {
            e.stopPropagation();
            if (sidebar.classList.contains("active")) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        // Event listener untuk overlay (menutup sidebar)
        overlay.addEventListener("click", closeSidebar);

        // Event listener untuk menutup sidebar saat klik di luar area
        document.addEventListener("click", function (e) {
            if (sidebar.classList.contains("active") && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                closeSidebar();
            }
        });
    } 

    // --- 2. EFEK SCROLL PADA HEADER ---
    const header = document.querySelector("header");
    if (header) {
        window.addEventListener("scroll", function () {
            // Tambah kelas 'scrolled' saat scroll lebih dari 50px
            if (window.scrollY > 50) {
                header.classList.add("scrolled");
            } else {
                header.classList.remove("scrolled");
            }
        });
    }

    // --- 3. AKORDEON ITINERARY (khusus package_detail.html) ---
    const dayHeaders = document.querySelectorAll(".day-header");
    
    // Hanya jalankan jika elemen akordeon ditemukan
    if (dayHeaders.length > 0) {
        dayHeaders.forEach((header) => {
            header.addEventListener("click", function () {
                const content = this.nextElementSibling;
                const isExpanded = content.classList.contains("expanded");

                // Tutup semua bagian lain yang terbuka (fungsi akordeon)
                document.querySelectorAll(".day-content.expanded").forEach((item) => {
                    if (item !== content) {
                        item.classList.remove("expanded");
                        if(item.previousElementSibling) {
                           item.previousElementSibling.classList.remove("active");
                        }
                    }
                });

                // Toggle bagian yang diklik
                this.classList.toggle("active");
                content.classList.toggle("expanded");

                // Scroll ke bagian yang baru dibuka untuk UX yang lebih baik
                if (!isExpanded) {
                    setTimeout(() => {
                        header.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }, 350); // Sesuaikan dengan durasi transisi CSS
                }
            });
        });
    }

    // --- 4. ADMIN LOGIN MODAL ---
    const adminLoginBtn = document.getElementById('admin-login-btn');
    const loginModal = document.getElementById('login-modal');
    const loginClose = document.getElementById('login-close');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');

    if (adminLoginBtn && loginModal) {
        // Show modal when admin login button is clicked
        adminLoginBtn.addEventListener('click', function() {
            loginModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        // Hide modal when close button is clicked
        if (loginClose) {
            loginClose.addEventListener('click', function() {
                loginModal.style.display = 'none';
                document.body.style.overflow = '';
                if (loginError) loginError.style.display = 'none';
            });
        }

        // Hide modal when clicking outside the modal box
        loginModal.addEventListener('click', function(e) {
            if (e.target === loginModal) {
                loginModal.style.display = 'none';
                document.body.style.overflow = '';
                if (loginError) loginError.style.display = 'none';
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && loginModal.style.display === 'flex') {
                loginModal.style.display = 'none';
                document.body.style.overflow = '';
                if (loginError) loginError.style.display = 'none';
            }
        });

        // Handle form submission
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                // Form will submit normally to backend
                // You can add additional validation here if needed
                if (loginError) loginError.style.display = 'none';
            });
        }
    }

    // --- 5. DEBUG UNTUK PACKAGE CARD CLICKS ---
    // Debugging untuk masalah package card clicks
    window.debugPackageClicks = function() {
        console.log('🔍 Debugging package card clicks...');
        
        const cardButtons = document.querySelectorAll('.card-button');
        console.log('🔍 Found card buttons:', cardButtons.length);
        
        cardButtons.forEach((button, index) => {
            console.log(`🔍 Button ${index}:`, {
                href: button.href,
                text: button.textContent,
                styles: {
                    pointerEvents: getComputedStyle(button).pointerEvents,
                    zIndex: getComputedStyle(button).zIndex,
                    position: getComputedStyle(button).position
                }
            });
            
            // Test click
            button.addEventListener('click', function(e) {
                console.log(`🔍 Button ${index} clicked:`, e);
            });
        });
        
        // Check for overlays
        const overlays = document.querySelectorAll('[class*="overlay"], [class*="modal"]');
        console.log('🔍 Found potential overlays:', overlays.length);
        
        overlays.forEach((overlay, index) => {
            const styles = getComputedStyle(overlay);
            if (styles.position === 'fixed' || styles.position === 'absolute') {
                console.log(`🔍 Overlay ${index}:`, {
                    element: overlay,
                    display: styles.display,
                    visibility: styles.visibility,
                    zIndex: styles.zIndex,
                    pointerEvents: styles.pointerEvents
                });
            }
        });
    };
    
    // Debug function untuk console
    window.debugPackages = window.debugPackageClicks;
});
