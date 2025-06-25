/**
 * Login Error Handler for Profile Page
 * Shows error notifications in the login modal area
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Login error handler loading...');
    
    // Check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const message = urlParams.get('message');
    
    if (error && message) {
        console.log('📢 Error detected:', error, message);
        showLoginError(error, decodeURIComponent(message));
        
        // Clean URL
        cleanUrl();
    }
});

function showLoginError(errorType, message) {
    // Show login modal
    const loginModal = document.getElementById('login-modal');
    const loginBtn = document.getElementById('admin-login-btn');
    
    if (loginModal && loginBtn) {
        // Show the modal
        loginModal.style.display = 'flex';
        
        // Show error in the login form
        showErrorInModal(errorType, message);
    }
}

function showErrorInModal(errorType, message) {
    const errorDiv = document.getElementById('login-error');
    
    if (!errorDiv) {
        console.warn('Login error div not found');
        return;
    }
    
    // Clear any existing content
    errorDiv.innerHTML = '';
    
    // Create error content based on type
    let errorContent = '';
    let errorClass = 'login-error-message';
    
    switch (errorType) {
        case 'login_failed':
            errorContent = `
                <div class="${errorClass} error-type-login">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="error-content">
                        <strong>Login Gagal</strong>
                        <p>${message}</p>
                        <small>Pastikan email dan password yang Anda masukkan benar.</small>
                    </div>
                </div>
            `;
            break;
            
        case 'missing_fields':
            errorContent = `
                <div class="${errorClass} error-type-warning">
                    <i class="fas fa-info-circle"></i>
                    <div class="error-content">
                        <strong>Field Kosong</strong>
                        <p>${message}</p>
                    </div>
                </div>
            `;
            break;
            
        case 'invalid_email':
            errorContent = `
                <div class="${errorClass} error-type-warning">
                    <i class="fas fa-envelope"></i>
                    <div class="error-content">
                        <strong>Email Tidak Valid</strong>
                        <p>${message}</p>
                    </div>
                </div>
            `;
            break;
            
        case 'database_error':
            errorContent = `
                <div class="${errorClass} error-type-error">
                    <i class="fas fa-database"></i>
                    <div class="error-content">
                        <strong>Kesalahan Sistem</strong>
                        <p>${message}</p>
                        <small>Silakan coba lagi dalam beberapa saat.</small>
                    </div>
                </div>
            `;
            break;
            
        default:
            errorContent = `
                <div class="${errorClass} error-type-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="error-content">
                        <strong>Error</strong>
                        <p>${message}</p>
                    </div>
                </div>
            `;
    }
    
    errorDiv.innerHTML = errorContent;
    errorDiv.style.display = 'block';
    
    // Add close button functionality
    const closeBtn = document.createElement('button');
    closeBtn.className = 'error-close-btn';
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.onclick = function() {
        hideLoginError();
    };
    errorDiv.querySelector('.login-error-message').appendChild(closeBtn);
    
    // Auto hide after 10 seconds
    setTimeout(() => {
        hideLoginError();
    }, 10000);
    
    // Focus on the email input
    const emailInput = document.querySelector('#login-form input[name="email"]');
    if (emailInput) {
        setTimeout(() => {
            emailInput.focus();
        }, 500);
    }
}

function hideLoginError() {
    const errorDiv = document.getElementById('login-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
        errorDiv.innerHTML = '';
    }
}

function cleanUrl() {
    const url = new URL(window.location);
    url.searchParams.delete('error');
    url.searchParams.delete('message');
    window.history.replaceState({}, document.title, url.toString());
}

// Global function to show error manually
window.showLoginError = showLoginError;
window.hideLoginError = hideLoginError;

console.log('✅ Login error handler loaded');
