/**
 * Enhanced Notification System
 * Modern, beautiful notifications for MPTI Travel
 */

class NotificationManager {
    constructor() {
        this.container = null;
        this.notifications = [];
        this.defaultDuration = 5000;
        this.init();
    }

    init() {
        // Create notification container
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
        
        // Check for URL parameters on page load
        this.checkUrlParams();
    }

    checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const message = urlParams.get('message');
        
        if (error && message) {
            const decodedMessage = decodeURIComponent(message);
            
            switch (error) {
                case 'login_failed':
                    this.show({
                        type: 'error',
                        title: 'Login Gagal',
                        message: decodedMessage,
                        icon: 'fas fa-exclamation-triangle',
                        duration: 8000,
                        actions: [
                            {
                                text: 'Coba Lagi',
                                action: () => this.redirectToAdmin()
                            }
                        ]
                    });
                    break;
                    
                case 'database_error':
                    this.show({
                        type: 'error',
                        title: 'Kesalahan Sistem',
                        message: decodedMessage,
                        icon: 'fas fa-database',
                        duration: 10000
                    });
                    break;
                    
                case 'missing_fields':
                    this.show({
                        type: 'warning',
                        title: 'Data Tidak Lengkap',
                        message: decodedMessage,
                        icon: 'fas fa-exclamation-circle',
                        duration: 6000
                    });
                    break;
                    
                case 'invalid_email':
                    this.show({
                        type: 'warning',
                        title: 'Email Tidak Valid',
                        message: decodedMessage,
                        icon: 'fas fa-envelope',
                        duration: 6000
                    });
                    break;
                    
                default:
                    this.show({
                        type: 'error',
                        title: 'Terjadi Kesalahan',
                        message: decodedMessage,
                        icon: 'fas fa-times-circle'
                    });
            }
            
            // Clean URL after showing notification
            this.cleanUrl();
        }
    }

    cleanUrl() {
        const url = new URL(window.location);
        url.searchParams.delete('error');
        url.searchParams.delete('message');
        window.history.replaceState({}, document.title, url.toString());
    }

    show(options = {}) {
        const {
            type = 'info',
            title = '',
            message = '',
            icon = this.getDefaultIcon(type),
            duration = this.defaultDuration,
            actions = [],
            closable = true,
            autoClose = true
        } = options;

        const notification = this.createNotification({
            type, title, message, icon, duration, actions, closable, autoClose
        });

        this.container.appendChild(notification);
        this.notifications.push(notification);

        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto close
        if (autoClose && duration > 0) {
            this.startProgressBar(notification, duration);
            setTimeout(() => {
                this.hide(notification);
            }, duration);
        }

        // Limit max notifications
        if (this.notifications.length > 3) {
            this.hide(this.notifications[0]);
        }

        return notification;
    }

    createNotification({ type, title, message, icon, actions, closable }) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.pointerEvents = 'auto';

        notification.innerHTML = `
            <div class="notification-header">
                <i class="${icon} notification-icon"></i>
                <h4 class="notification-title">${title}</h4>
                ${closable ? '<button class="notification-close" type="button"><i class="fas fa-times"></i></button>' : ''}
            </div>
            <p class="notification-message">${message}</p>
            ${actions.length > 0 ? `
                <div class="notification-actions">
                    ${actions.map(action => `
                        <button class="notification-btn primary" data-action="${action.text}">
                            ${action.text}
                        </button>
                    `).join('')}
                </div>
            ` : ''}
            <div class="notification-progress">
                <div class="notification-progress-bar"></div>
            </div>
        `;

        // Add event listeners
        if (closable) {
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => this.hide(notification));
        }

        // Add action listeners
        actions.forEach(action => {
            const btn = notification.querySelector(`[data-action="${action.text}"]`);
            if (btn) {
                btn.addEventListener('click', () => {
                    action.action();
                    this.hide(notification);
                });
            }
        });

        return notification;
    }

    startProgressBar(notification, duration) {
        const progressBar = notification.querySelector('.notification-progress-bar');
        if (progressBar) {
            progressBar.style.transition = `transform ${duration}ms linear`;
            progressBar.style.transform = 'scaleX(1)';
        }
    }

    hide(notification) {
        notification.classList.add('hide');
        notification.classList.remove('show');

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            const index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        }, 400);
    }

    hideAll() {
        this.notifications.forEach(notification => this.hide(notification));
    }

    getDefaultIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    redirectToAdmin() {
        window.location.href = '../../BackEnd/ViewLoginAdmin.php';
    }

    cleanUrl() {
        const url = new URL(window.location);
        url.searchParams.delete('error');
        url.searchParams.delete('message');
        window.history.replaceState({}, '', url);
    }

    // Convenience methods
    success(message, title = 'Berhasil!') {
        return this.show({ type: 'success', title, message });
    }

    error(message, title = 'Terjadi Kesalahan!') {
        return this.show({ type: 'error', title, message });
    }

    warning(message, title = 'Peringatan!') {
        return this.show({ type: 'warning', title, message });
    }

    info(message, title = 'Informasi') {
        return this.show({ type: 'info', title, message });
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.notifications = new NotificationManager();
    
    console.log('✅ Enhanced notification system loaded');
});

// Global functions for easy access
window.showSuccess = function(message, title) {
    if (window.notifications) {
        window.notifications.success(message, title);
    }
};

window.showError = function(message, title) {
    if (window.notifications) {
        window.notifications.error(message, title);
    }
};

window.showWarning = function(message, title) {
    if (window.notifications) {
        window.notifications.warning(message, title);
    }
};

window.showInfo = function(message, title) {
    if (window.notifications) {
        window.notifications.info(message, title);
    }
};
