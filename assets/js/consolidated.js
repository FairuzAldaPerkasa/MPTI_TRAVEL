// ==========================================
// SMOOTH SCROLLING & HERO CTA ANIMATIONS
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scrolling untuk semua link dengan class smooth-scroll
    document.querySelectorAll('.smooth-scroll').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerOffset = 80; // Offset untuk fixed header jika ada
                const elementPosition = targetSection.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Animasi entrance untuk hero CTA
    const heroCTA = document.querySelector('.hero-cta');
    if (heroCTA) {
        // Delay animation untuk efek dramatic
        setTimeout(() => {
            heroCTA.style.opacity = '0';
            heroCTA.style.transform = 'translateY(30px)';
            heroCTA.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            
            setTimeout(() => {
                heroCTA.style.opacity = '1';
                heroCTA.style.transform = 'translateY(0)';
            }, 300);
        }, 1500); // Muncul setelah 1.5 detik
    }
    
    // Parallax effect ringan untuk hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        const heroContent = document.querySelector('.hero-content');
        
        if (hero && heroContent) {
            const rate = scrolled * -0.3;
            heroContent.style.transform = `translateY(${rate}px)`;
        }
    });
});

// ==========================================
// ADDITIONAL ANIMATIONS
// ==========================================

// Intersection Observer untuk animasi scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe semua section untuk animasi
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        observer.observe(section);
    });
});