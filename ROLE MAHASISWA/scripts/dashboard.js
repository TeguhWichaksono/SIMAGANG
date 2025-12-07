// dashboard.js - JavaScript untuk Dashboard Mahasiswa

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. ANIMATE STATS CARDS ON LOAD
    // ==========================================
    animateStatsCards();
    
    // ==========================================
    // 2. ANIMATE NUMBERS (COUNT UP EFFECT)
    // ==========================================
    animateNumbers();
    
    // ==========================================
    // 3. AUTO REFRESH NOTIFICATIONS
    // ==========================================
    // setInterval(refreshNotifications, 60000); // Refresh setiap 1 menit
    
    // ==========================================
    // 4. SMOOTH SCROLL TO SECTION
    // ==========================================
    initSmoothScroll();
    
    // ==========================================
    // 5. TOOLTIP INITIALIZATION (if using)
    // ==========================================
    initTooltips();
    
});

// ==========================================
// ANIMATE STATS CARDS
// ==========================================
function animateStatsCards() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// ==========================================
// ANIMATE NUMBERS (COUNT UP)
// ==========================================
function animateNumbers() {
    const statValues = document.querySelectorAll('.stat-value, .stat-number');
    
    statValues.forEach(stat => {
        const text = stat.textContent.trim();
        
        // Check if it's a number
        const number = parseInt(text);
        if (!isNaN(number)) {
            animateValue(stat, 0, number, 1000);
        }
    });
}

function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16); // 60 FPS
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// ==========================================
// AUTO REFRESH NOTIFICATIONS
// ==========================================
function refreshNotifications() {
    // Fetch new notifications via AJAX
    fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                updateNotificationList(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
        });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-indicator');
    const statValue = document.querySelector('.notif-card .stat-value');
    
    if (badge && count > 0) {
        badge.style.display = 'block';
    } else if (badge) {
        badge.style.display = 'none';
    }
    
    if (statValue) {
        animateValue(statValue, parseInt(statValue.textContent), count, 500);
    }
}

function updateNotificationList(notifications) {
    const notifList = document.querySelector('.notif-list');
    if (!notifList) return;
    
    if (notifications.length === 0) {
        notifList.innerHTML = `
            <div class="empty-state-small">
                <i class="fas fa-bell-slash"></i>
                <p>Tidak ada notifikasi</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notif => {
        const unreadClass = notif.status_baca === 'baru' ? 'unread' : '';
        const badge = notif.status_baca === 'baru' ? '<div class="notif-badge"></div>' : '';
        
        html += `
            <div class="notif-item ${unreadClass}">
                <div class="notif-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="notif-content">
                    <p class="notif-message">${escapeHtml(notif.pesan)}</p>
                    <span class="notif-time">${timeAgo(notif.tanggal)}</span>
                </div>
                ${badge}
            </div>
        `;
    });
    
    notifList.innerHTML = html;
}

// ==========================================
// TIME AGO HELPER
// ==========================================
function timeAgo(datetime) {
    const timestamp = new Date(datetime).getTime();
    const now = new Date().getTime();
    const diff = Math.floor((now - timestamp) / 1000);
    
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
    if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
    
    const date = new Date(timestamp);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// ==========================================
// ESCAPE HTML (Security)
// ==========================================
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ==========================================
// SMOOTH SCROLL
// ==========================================
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ==========================================
// TOOLTIPS (Optional - if you add tooltips)
// ==========================================
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const text = e.target.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.id = 'active-tooltip';
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    
    setTimeout(() => {
        tooltip.classList.add('show');
    }, 10);
}

function hideTooltip() {
    const tooltip = document.getElementById('active-tooltip');
    if (tooltip) {
        tooltip.classList.remove('show');
        setTimeout(() => {
            tooltip.remove();
        }, 200);
    }
}

// ==========================================
// CARD HOVER EFFECTS
// ==========================================
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transition = 'all 0.3s ease';
    });
});

// ==========================================
// LOGBOOK ITEM CLICK (Navigate to Detail)
// ==========================================
document.querySelectorAll('.logbook-item').forEach(item => {
    item.addEventListener('click', function() {
        // You can add navigation logic here
        // window.location.href = 'index.php?page=logbook&id=' + logbookId;
    });
});

// ==========================================
// NOTIF ITEM CLICK (Mark as Read)
// ==========================================
document.querySelectorAll('.notif-item').forEach(item => {
    item.addEventListener('click', function() {
        if (this.classList.contains('unread')) {
            markAsRead(this);
        }
    });
});

function markAsRead(notifElement) {
    // Get notification ID (you'll need to add data-id attribute in PHP)
    const notifId = notifElement.getAttribute('data-id');
    
    if (!notifId) return;
    
    // Send AJAX request to mark as read
    fetch('api/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: notifId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notifElement.classList.remove('unread');
            const badge = notifElement.querySelector('.notif-badge');
            if (badge) badge.remove();
            
            // Update unread count
            refreshNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// ==========================================
// REFRESH BUTTON (Optional)
// ==========================================
function addRefreshButton() {
    const notifCard = document.querySelector('.notifikasi-card .card-header');
    if (!notifCard) return;
    
    const refreshBtn = document.createElement('button');
    refreshBtn.className = 'btn-refresh';
    refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
    refreshBtn.title = 'Refresh Notifikasi';
    refreshBtn.style.cssText = `
        background: none;
        border: none;
        color: var(--primary-color);
        cursor: pointer;
        padding: 5px 10px;
        font-size: 16px;
        transition: transform 0.3s ease;
    `;
    
    refreshBtn.addEventListener('click', function() {
        this.style.transform = 'rotate(360deg)';
        refreshNotifications();
        setTimeout(() => {
            this.style.transform = 'rotate(0deg)';
        }, 500);
    });
    
    notifCard.appendChild(refreshBtn);
}

// ==========================================
// CONSOLE INFO (Development)
// ==========================================
console.log('%c Dashboard Mahasiswa Loaded Successfully! ', 'background: #4270F4; color: white; padding: 10px; font-size: 14px; font-weight: bold;');

// ==========================================
// PERFORMANCE MONITORING
// ==========================================
window.addEventListener('load', function() {
    const loadTime = window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart;
    console.log(`Dashboard loaded in ${loadTime}ms`);
});

// ==========================================
// ERROR HANDLING
// ==========================================
window.addEventListener('error', function(e) {
    console.error('Dashboard Error:', e.message);
});

// ==========================================
// VISIBILITY CHANGE (Refresh on focus)
// ==========================================
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Tab is active again, refresh data
        console.log('Tab active - refreshing data...');
        // refreshNotifications();
    }
});