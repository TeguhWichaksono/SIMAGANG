/**
 * dashboard.js
 * JavaScript untuk Dashboard Dosen Pembimbing
 * 
 * Features:
 * - Chart.js visualization
 * - Chart filter
 * - Contact mahasiswa
 * - Real-time updates
 */

// ========================================
// INITIALIZE CHART
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initValidasiChart();
});

function initValidasiChart() {
    const ctx = document.getElementById('validasiChart');
    
    if (!ctx || typeof Chart === 'undefined') {
        console.error('Chart.js not loaded or canvas not found');
        return;
    }
    
    // Process chart data from PHP
    const labels = chartData.map(item => {
        const [year, month] = item.bulan.split('-');
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        return monthNames[parseInt(month) - 1] + ' ' + year;
    });
    
    const pendingData = chartData.map(item => parseInt(item.pending));
    const disetujuiData = chartData.map(item => parseInt(item.disetujui));
    const ditolakData = chartData.map(item => parseInt(item.ditolak));
    
    // Create chart
    window.validasiChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pending',
                    data: pendingData,
                    borderColor: '#ff9800',
                    backgroundColor: 'rgba(255, 152, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#ff9800',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Disetujui',
                    data: disetujuiData,
                    borderColor: '#2dbf78',
                    backgroundColor: 'rgba(45, 191, 120, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#2dbf78',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Ditolak',
                    data: ditolakData,
                    borderColor: '#f36c6c',
                    backgroundColor: 'rgba(243, 108, 108, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#f36c6c',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y + ' logbook';
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// ========================================
// CHART FILTER
// ========================================
document.getElementById('chartFilter')?.addEventListener('change', function(e) {
    const months = parseInt(e.target.value);
    
    // Filter data based on selected months
    const filteredData = chartData.slice(-months);
    
    // Update chart
    if (window.validasiChart) {
        const labels = filteredData.map(item => {
            const [year, month] = item.bulan.split('-');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            return monthNames[parseInt(month) - 1] + ' ' + year;
        });
        
        const pendingData = filteredData.map(item => parseInt(item.pending));
        const disetujuiData = filteredData.map(item => parseInt(item.disetujui));
        const ditolakData = filteredData.map(item => parseInt(item.ditolak));
        
        window.validasiChart.data.labels = labels;
        window.validasiChart.data.datasets[0].data = pendingData;
        window.validasiChart.data.datasets[1].data = disetujuiData;
        window.validasiChart.data.datasets[2].data = ditolakData;
        window.validasiChart.update();
    }
});

// ========================================
// CONTACT MAHASISWA
// ========================================
function contactMahasiswa(idMahasiswa) {
    // Redirect to daftar bimbingan with detail modal
    window.location.href = `index.php?page=daftar_Bimbingan&detail=${idMahasiswa}`;
}

// ========================================
// AUTO REFRESH ACTIVITIES (OPTIONAL)
// ========================================
let refreshInterval = null;

function startAutoRefresh(intervalMinutes = 5) {
    // Clear existing interval
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    // Set new interval
    refreshInterval = setInterval(() => {
        // Reload only the activities section
        fetch('index.php?page=dashboard')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newActivities = doc.querySelector('.activities-list');
                const currentActivities = document.querySelector('.activities-list');
                
                if (newActivities && currentActivities) {
                    currentActivities.innerHTML = newActivities.innerHTML;
                    showNotification('info', 'Aktivitas diperbarui');
                }
            })
            .catch(error => {
                console.error('Error refreshing activities:', error);
            });
    }, intervalMinutes * 60 * 1000);
}

// Uncomment to enable auto-refresh every 5 minutes
// startAutoRefresh(5);

// ========================================
// NOTIFICATION SYSTEM
// ========================================
function showNotification(type, message) {
    const colors = {
        'success': '#2dbf78',
        'error': '#f36c6c',
        'info': '#4270f4',
        'warning': '#ff9800'
    };
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'info': 'fa-info-circle',
        'warning': 'fa-exclamation-triangle'
    };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${colors[type] || colors.info};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInRight 0.3s ease;
        max-width: 350px;
    `;
    
    notification.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    // Remove after 4 seconds
    setTimeout(() => {
        notification.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

// Format number with thousand separator
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Get time ago
function timeAgo(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " tahun lalu";
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " bulan lalu";
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " hari lalu";
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " jam lalu";
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " menit lalu";
    
    return "Baru saja";
}

// Smooth scroll to element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

// ========================================
// KEYBOARD SHORTCUTS
// ========================================
document.addEventListener('keydown', function(e) {
    // Ctrl + M = Go to Monitoring
    if (e.ctrlKey && e.key === 'm') {
        e.preventDefault();
        window.location.href = 'index.php?page=monitoring';
    }
    
    // Ctrl + D = Go to Daftar Bimbingan
    if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        window.location.href = 'index.php?page=daftar_Bimbingan';
    }
});

// ========================================
// INITIALIZE
// ========================================
console.log('Dashboard Dosen Pembimbing Loaded ✓');
console.log('Keyboard shortcuts:');
console.log('- Ctrl + M: Monitoring');
console.log('- Ctrl + D: Daftar Bimbingan');