/**
 * MPTI TRAVEL - CONTACT LOADER
 * Script untuk memuat kontak dinamis dari API
 * Version 1.0
 */

console.log('📞 Contact loader starting...');

let websiteSettings = {};

// Load settings on page load
document.addEventListener('DOMContentLoaded', function() {
    loadContactSettings();
});

async function loadContactSettings() {
    console.log('🔧 Loading contact settings...');
    try {
        const response = await fetch('../../BackEnd/get_settings.php');
        const result = await response.json();
        
        if (result.success) {
            websiteSettings = result.data;
            console.log('✅ Contact settings loaded:', websiteSettings);
            updateContactElements();
        } else {
            console.warn('⚠️ Failed to load contact settings, using defaults');
            setDefaultSettings();
            updateContactElements();
        }
    } catch (error) {
        console.error('❌ Error loading contact settings:', error);
        setDefaultSettings();
        updateContactElements();
    }
}

function setDefaultSettings() {
    websiteSettings = {
        whatsapp_number: '6281234567890',
        phone_number: '0812-3456-789',
        email: 'info@mptitravel.com',
        instagram: '@mptitravel',
        website_name: 'MPTI Travel',
        website_url: 'https://mptitravel.com',
        address: 'Yogyakarta, Indonesia',
        whatsapp_message: 'Halo, saya tertarik dengan paket wisata dari MPTI Travel'
    };
}

function updateContactElements() {
    console.log('🔄 Updating contact elements...');
    
    // Update WhatsApp button with custom message
    const whatsappButton = document.querySelector('.whatsapp-button');
    if (whatsappButton) {
        const customMessage = websiteSettings.whatsapp_message || 'Halo, saya tertarik dengan paket wisata dari MPTI Travel';
        const message = encodeURIComponent(customMessage);
        whatsappButton.href = `https://wa.me/${websiteSettings.whatsapp_number}?text=${message}`;
        console.log('✅ WhatsApp button updated with dynamic message');
    }
    
    // Update any WhatsApp links with wa.me
    const whatsappLinks = document.querySelectorAll('a[href*="wa.me"]');
    whatsappLinks.forEach(link => {
        if (link !== whatsappButton) { // Skip the main button we already handled
            const customMessage = websiteSettings.whatsapp_message || 'Halo, saya tertarik dengan paket wisata dari MPTI Travel';
            const message = encodeURIComponent(customMessage);
            link.href = `https://wa.me/${websiteSettings.whatsapp_number}?text=${message}`;
        }
    });
    
    // Update footer contacts
    updateFooterContacts();
    
    // Update any other contact links
    updateOtherContactLinks();
    
    // Update floating WhatsApp button
    updateFloatingWhatsAppButton();
}

function updateFooterContacts() {
    // Update phone number
    const phoneElements = document.querySelectorAll('.contact-item .fas.fa-phone + span, .contact-item .fas.fa-phone-alt + span');
    phoneElements.forEach(el => {
        if (el && websiteSettings.phone_number) {
            el.textContent = websiteSettings.phone_number;
        }
    });
    
    // Update WhatsApp number in footer
    const whatsappElements = document.querySelectorAll('.contact-item .fab.fa-whatsapp + span');
    whatsappElements.forEach(el => {
        if (el && websiteSettings.whatsapp_number) {
            // Format WhatsApp number for display
            const displayNumber = websiteSettings.whatsapp_number.startsWith('62') 
                ? '+' + websiteSettings.whatsapp_number
                : websiteSettings.whatsapp_number;
            el.textContent = displayNumber;
        }
    });
    
    // Update email
    const emailElements = document.querySelectorAll('.contact-item .fas.fa-envelope + span');
    emailElements.forEach(el => {
        if (el && websiteSettings.email) {
            el.textContent = websiteSettings.email;
        }
    });
    
    // Update Instagram
    const instagramElements = document.querySelectorAll('.contact-item .fab.fa-instagram + span');
    instagramElements.forEach(el => {
        if (el && websiteSettings.instagram) {
            el.textContent = websiteSettings.instagram;
        }
    });
    
    // Update website
    const websiteElements = document.querySelectorAll('.contact-item .fas.fa-globe + span');
    websiteElements.forEach(el => {
        if (el && websiteSettings.website_url) {
            el.textContent = websiteSettings.website_url.replace('https://', '').replace('http://', '');
        }
    });
    
    // Update address
    const addressElements = document.querySelectorAll('.contact-item .fas.fa-map-marker-alt + span');
    addressElements.forEach(el => {
        if (el && websiteSettings.address) {
            el.textContent = websiteSettings.address;
        }
    });
    
    // Update website name/logo
    const logoElements = document.querySelectorAll('.footer-logo, .site-logo');
    logoElements.forEach(el => {
        if (el && websiteSettings.website_name) {
            el.textContent = websiteSettings.website_name;
        }
    });
    
    console.log('✅ Footer contacts updated');
}

function updateOtherContactLinks() {
    // Update tel: links
    const telLinks = document.querySelectorAll('a[href^="tel:"]');
    telLinks.forEach(link => {
        if (websiteSettings.phone_number) {
            link.href = `tel:+${websiteSettings.whatsapp_number}`;
        }
    });
    
    // Update mailto: links
    const mailtoLinks = document.querySelectorAll('a[href^="mailto:"]');
    mailtoLinks.forEach(link => {
        if (websiteSettings.email) {
            link.href = `mailto:${websiteSettings.email}`;
        }
    });
    
    console.log('✅ Other contact links updated');
}

function updateFloatingWhatsAppButton() {
    // Update floating WhatsApp button
    const whatsappBtn = document.getElementById('whatsapp-floating-btn');
    if (whatsappBtn && websiteSettings.whatsapp_number) {
        const message = websiteSettings.whatsapp_message || 'Halo, saya tertarik dengan paket wisata';
        const encodedMessage = encodeURIComponent(message);
        whatsappBtn.href = `https://wa.me/${websiteSettings.whatsapp_number}?text=${encodedMessage}`;
        
        // Add click event for analytics
        whatsappBtn.onclick = function() {
            console.log('📱 WhatsApp button clicked');
        };
        
        console.log('✅ Floating WhatsApp button updated:', whatsappBtn.href);
    }
}

// Global functions for other scripts to use
window.getContactSettings = function() {
    return websiteSettings;
};

window.refreshContacts = function() {
    loadContactSettings();
};
