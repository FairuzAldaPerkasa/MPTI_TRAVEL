/**
 * Newsletter Subscription Handler
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all newsletter forms
    const newsletterForms = document.querySelectorAll('.newsletter-form');
    
    newsletterForms.forEach(form => {
        form.addEventListener('submit', handleNewsletterSubmit);
    });
});

async function handleNewsletterSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const emailInput = form.querySelector('input[type="email"]');
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (!emailInput || !emailInput.value.trim()) {
        showNotification('Silakan masukkan alamat email yang valid', 'error');
        return;
    }
    
    const email = emailInput.value.trim();
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Format email tidak valid', 'error');
        return;
    }
    
    // Disable form during submission
    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Mengirim...';
    emailInput.disabled = true;
      try {
        // Determine correct API path based on current location
        let apiPath;
        if (window.location.pathname.includes('/FrontEnd/html/')) {
            apiPath = '../../BackEnd/newsletter_subscribe.php';
        } else {
            apiPath = '../BackEnd/newsletter_subscribe.php';
        }
        
        const response = await fetch(apiPath, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('Server returned non-JSON response');
        }
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Berhasil berlangganan newsletter!', 'success');
            emailInput.value = '';
        } else {
            showNotification(result.message || 'Gagal berlangganan newsletter', 'error');
        }
        
    } catch (error) {
        console.error('Newsletter subscription error:', error);
        if (error.message.includes('JSON')) {
            showNotification('Terjadi kesalahan server. Silakan coba lagi.', 'error');
        } else {
            showNotification('Terjadi kesalahan koneksi. Silakan coba lagi.', 'error');
        }
    } finally {
        // Re-enable form
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
        emailInput.disabled = false;
    }
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.newsletter-notification');
    existingNotifications.forEach(notif => notif.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `newsletter-notification newsletter-notification--${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// CSS styles for notifications (will be injected)
const notificationStyles = `
<style>
.newsletter-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    padding: 12px 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10000;
    min-width: 300px;
    max-width: 400px;
    border-left: 4px solid #3b82f6;
    animation: slideIn 0.3s ease-out;
}

.newsletter-notification--success {
    border-left-color: #10b981;
}

.newsletter-notification--success i {
    color: #10b981;
}

.newsletter-notification--error {
    border-left-color: #ef4444;
}

.newsletter-notification--error i {
    color: #ef4444;
}

.newsletter-notification--info {
    border-left-color: #3b82f6;
}

.newsletter-notification--info i {
    color: #3b82f6;
}

.newsletter-notification span {
    flex: 1;
    font-size: 14px;
    color: #374151;
}

.notification-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
}

.notification-close:hover {
    background: #f3f4f6;
    color: #374151;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .newsletter-notification {
        top: 10px;
        right: 10px;
        left: 10px;
        min-width: auto;
        max-width: none;
    }
}
</style>
`;

// Inject styles
document.head.insertAdjacentHTML('beforeend', notificationStyles);
