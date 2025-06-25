/**
 * Simple Notification System for MPTI Travel
 * Handles login errors and notifications
 */

// Check for URL parameters when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Notification system loading...');
    
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const message = urlParams.get('message');
    
    if (error && message) {
        console.log('📢 Found error in URL:', error, message);
        showNotification(error, decodeURIComponent(message));
        
        // Clean URL
        const url = new URL(window.location);
        url.searchParams.delete('error');
        url.searchParams.delete('message');
        window.history.replaceState({}, document.title, url.toString());
    }
});

function showNotification(type, message) {
    console.log('🔔 Showing notification:', type, message);
    
    // Remove any existing notifications
    const existing = document.querySelector('.notification-overlay');
    if (existing) {
        existing.remove();
    }
    
    // Create notification overlay
    const overlay = document.createElement('div');
    overlay.className = 'notification-overlay';
    
    // Create notification content
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Set content based on error type
    let title, icon;
    switch (type) {
        case 'login_failed':
            title = '🚫 Login Gagal';
            icon = '❌';
            break;
        case 'missing_fields':
            title = '⚠️ Field Kosong';
            icon = '⚠️';
            break;
        case 'invalid_email':
            title = '📧 Email Tidak Valid';
            icon = '📧';
            break;
        case 'database_error':
            title = '🔧 Kesalahan Sistem';
            icon = '🔧';
            break;
        default:
            title = '📢 Notifikasi';
            icon = 'ℹ️';
    }
    
    notification.innerHTML = `
        <div class="notification-header">
            <span class="notification-icon">${icon}</span>
            <span class="notification-title">${title}</span>
            <button class="notification-close" onclick="closeNotification()">&times;</button>
        </div>
        <div class="notification-body">
            ${message}
        </div>
        <div class="notification-actions">
            <button class="btn-secondary" onclick="closeNotification()">Tutup</button>
            ${type === 'login_failed' ? '<button class="btn-primary" onclick="goToAdmin()">Coba Lagi</button>' : ''}
        </div>
    `;
    
    overlay.appendChild(notification);
    document.body.appendChild(overlay);
    
    // Show with animation
    setTimeout(() => {
        overlay.classList.add('show');
    }, 10);
    
    // Auto close after 8 seconds
    setTimeout(() => {
        closeNotification();
    }, 8000);
}

function closeNotification() {
    const overlay = document.querySelector('.notification-overlay');
    if (overlay) {
        overlay.classList.add('hide');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

function goToAdmin() {
    closeNotification();
    // Scroll to admin section or redirect to admin login
    window.location.href = '../../BackEnd/ViewLoginAdmin.php';
}

console.log('✅ Simple notification system loaded');
