/**
 * Prime EMS Studios - Notification System Client
 * Real-time browser notifications with auto-refresh
 */

class NotificationClient {
    constructor() {
        this.apiUrl = '/api/notifications.php';
        this.pollInterval = 30000; // 30 seconds
        this.pollTimer = null;
        this.lastNotificationId = 0;
        this.notificationPermission = false;
        
        this.init();
    }

    async init() {
        await this.requestNotificationPermission();
        this.setupEventListeners();
        this.startPolling();
        this.loadNotifications();
    }

    async requestNotificationPermission() {
        if ('Notification' in window) {
            const permission = await Notification.requestPermission();
            this.notificationPermission = permission === 'granted';
        }
    }

    setupEventListeners() {
        // Notification bell click
        const notificationBell = document.getElementById('notification-bell');
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                this.toggleNotificationDropdown();
            });
        }

        // Mark all as read button
        const markAllReadBtn = document.getElementById('mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('notification-dropdown');
            const bell = document.getElementById('notification-bell');
            
            if (dropdown && bell && !dropdown.contains(e.target) && !bell.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    startPolling() {
        this.pollTimer = setInterval(() => {
            this.checkForNewNotifications();
        }, this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch(this.apiUrl);
            const result = await response.json();
            
            if (result.success) {
                this.updateNotificationUI(result.data.notifications);
                this.updateNotificationCount(result.data.unread_count);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    async checkForNewNotifications() {
        try {
            const response = await fetch(`${this.apiUrl}?realtime=1&after=${this.lastNotificationId}`);
            const result = await response.json();
            
            if (result.success && result.data.notifications.length > 0) {
                result.data.notifications.forEach(notification => {
                    this.showBrowserNotification(notification);
                    this.lastNotificationId = Math.max(this.lastNotificationId, notification.id);
                });
                
                this.loadNotifications(); // Refresh the dropdown
            }
        } catch (error) {
            console.error('Error checking for new notifications:', error);
        }
    }

    showBrowserNotification(notification) {
        if (!this.notificationPermission) return;

        const browserNotification = new Notification(notification.title, {
            body: notification.message,
            icon: '/assets/images/logo-small.png',
            badge: '/assets/images/notification-badge.png',
            tag: `notification-${notification.id}`,
            requireInteraction: notification.priority === 'high'
        });

        browserNotification.onclick = () => {
            window.focus();
            this.markAsRead(notification.id);
            browserNotification.close();
            
            // Navigate to related page if URL exists
            if (notification.url) {
                window.location.href = notification.url;
            }
        };

        // Auto close after 5 seconds for low priority notifications
        if (notification.priority !== 'high') {
            setTimeout(() => {
                browserNotification.close();
            }, 5000);
        }
    }

    updateNotificationUI(notifications) {
        const container = document.getElementById('notification-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-bell-slash"></i>
                    <p class="mb-0 small">Bildirim bulunmuyor</p>
                </div>
            `;
            return;
        }

        container.innerHTML = notifications.map(notification => `
            <div class="notification-item ${!notification.is_read ? 'unread' : ''}" data-id="${notification.id}">
                <div class="d-flex">
                    <div class="notification-icon me-2">
                        <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${notification.title}</h6>
                        <p class="mb-1 small text-muted">${notification.message}</p>
                        <small class="text-muted">${this.formatDate(notification.created_at)}</small>
                    </div>
                    <div class="notification-actions">
                        ${!notification.is_read ? `
                            <button class="btn btn-sm btn-outline-primary" onclick="notificationClient.markAsRead(${notification.id})">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateNotificationCount(count) {
        const badge = document.getElementById('notification-count');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    toggleNotificationDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_read',
                    notification_id: notificationId
                })
            });

            const result = await response.json();
            if (result.success) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_all_read'
                })
            });

            const result = await response.json();
            if (result.success) {
                this.loadNotifications();
                this.showToast('Tüm bildirimler okundu olarak işaretlendi', 'success');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    async createNotification(title, message, type = 'info', userId = null, url = null, priority = 'normal') {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title,
                    message,
                    type,
                    user_id: userId,
                    url,
                    priority
                })
            });

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error creating notification:', error);
            return false;
        }
    }

    getNotificationIcon(type) {
        const icons = {
            'info': 'fa-info-circle',
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-times-circle',
            'appointment': 'fa-calendar-alt',
            'payment': 'fa-credit-card',
            'message': 'fa-envelope',
            'system': 'fa-cog'
        };
        return icons[type] || 'fa-bell';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Şimdi';
        if (diffMins < 60) return `${diffMins} dakika önce`;
        if (diffHours < 24) return `${diffHours} saat önce`;
        if (diffDays < 7) return `${diffDays} gün önce`;
        
        return date.toLocaleDateString('tr-TR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        // Add to toast container
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        toastContainer.appendChild(toast);

        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove from DOM after hiding
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    destroy() {
        this.stopPolling();
    }
}

// Initialize notification client when DOM is loaded
let notificationClient;
document.addEventListener('DOMContentLoaded', () => {
    notificationClient = new NotificationClient();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (notificationClient) {
        notificationClient.destroy();
    }
});
