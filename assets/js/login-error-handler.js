/**
 * MPTI TRAVEL - LOGIN ERROR HANDLER
 * Script untuk menangani error notifikasi dari admin login
 * Version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check for error parameters in URL
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const message = urlParams.get('message');
    
    if (error && message) {
        showLoginError(message);
        
        // Clean URL without refreshing page
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

function showLoginError(message) {
    // Remove existing error notifications
    const existingError = document.querySelector('.login-error-notification');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error notification
    const errorDiv = document.createElement('div');
    errorDiv.className = 'login-error-notification';
    errorDiv.innerHTML = `
        <div class="error-content">
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
            <button class="error-close" onclick="closeLoginError()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Insert at top of page
    document.body.insertBefore(errorDiv, document.body.firstChild);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        closeLoginError();
    }, 5000);
    
    // Add animation
    setTimeout(() => {
        errorDiv.classList.add('show');
    }, 100);
}

function closeLoginError() {
    const errorDiv = document.querySelector('.login-error-notification');
    if (errorDiv) {
        errorDiv.classList.add('hide');
        setTimeout(() => {
            errorDiv.remove();
        }, 300);
    }
}

// Make function globally available
window.closeLoginError = closeLoginError;
