/**
 * admin_dashboard.js
 * Logic untuk chart dan interaksi dashboard admin
 */

document.addEventListener('DOMContentLoaded', function() {
    initUserChart();
});

function initUserChart() {
    const ctx = document.getElementById('userRoleChart');
    
    // Safety check
    if (!ctx || typeof Chart === 'undefined') {
        console.error('Canvas or Chart.js not found');
        return;
    }

    // Mengambil data dari variabel PHP 'chartData'
    const labels = chartData.map(item => item.label);
    const dataValues = chartData.map(item => item.value);

    // Warna statis untuk role standar
    const backgroundColors = [
        '#3498db', // Blue
        '#2ecc71', // Green
        '#f1c40f', // Yellow
        '#e74c3c', // Red
        '#9b59b6'  // Purple
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: dataValues,
                backgroundColor: backgroundColors.slice(0, dataValues.length),
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Penting agar tidak gepeng
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                title: {
                    display: false
                }
            },
            cutout: '65%' // Membuat lubang tengah (Doughnut style)
        }
    });
}