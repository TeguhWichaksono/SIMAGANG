<?php
// pages/dashboard.php
// Dashboard Koordinator Bidang Magang

include '../Koneksi/koneksi.php';

// --- 1. STATS: Pengajuan Magang Pending ---
$q_pending_magang = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengajuan_magang WHERE status_pengajuan IN ('menunggu', 'menunggu_mitra')");
$pending_magang = ($q_pending_magang) ? mysqli_fetch_assoc($q_pending_magang)['total'] : 0;

// --- 2. STATS: Mitra Baru Pending ---
$q_pending_mitra = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengajuan_mitra WHERE status_pengajuan = 'menunggu'");
$pending_mitra = ($q_pending_mitra) ? mysqli_fetch_assoc($q_pending_mitra)['total'] : 0;

// --- 3. STATS: Mahasiswa Magang Aktif ---
$q_mhs_aktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa WHERE status_magang = 'magang_aktif'");
$mhs_aktif = ($q_mhs_aktif) ? mysqli_fetch_assoc($q_mhs_aktif)['total'] : 0;

// --- 4. STATS: Total Mitra Kerjasama ---
$q_total_mitra = mysqli_query($conn, "SELECT COUNT(*) as total FROM mitra_perusahaan WHERE status = 'aktif'");
$total_mitra = ($q_total_mitra) ? mysqli_fetch_assoc($q_total_mitra)['total'] : 0;

// --- 5. CHART DATA: Status Mahasiswa ---
$q_chart_status = mysqli_query($conn, "SELECT status_magang, COUNT(*) as jumlah FROM mahasiswa GROUP BY status_magang");
$chart_status_data = [];
if ($q_chart_status) {
    while($row = mysqli_fetch_assoc($q_chart_status)) {
        $label = ucwords(str_replace('_', ' ', $row['status_magang']));
        $chart_status_data[] = ['label' => $label, 'value' => (int)$row['jumlah']];
    }
}

// --- 6. LIST PRIORITAS: 5 Pengajuan Terbaru ---
$q_list_pending = mysqli_query($conn, "
    SELECT pm.id_pengajuan, k.nama_kelompok, m.nama_mitra, pm.tanggal_pengajuan 
    FROM pengajuan_magang pm
    JOIN kelompok k ON pm.id_kelompok = k.id_kelompok
    JOIN mitra_perusahaan m ON pm.id_mitra = m.id_mitra
    WHERE pm.status_pengajuan = 'menunggu'
    ORDER BY pm.tanggal_pengajuan ASC 
    LIMIT 5
");

// --- 7. DATA MITRA POPULER (Berdasarkan Jumlah Mahasiswa) ---
$q_mitra_populer = mysqli_query($conn, "
    SELECT 
        mp.nama_mitra, 
        mp.bidang,
        COUNT(pm.id_pengajuan) as jumlah_mhs
    FROM mitra_perusahaan mp 
    LEFT JOIN pengajuan_magang pm ON mp.id_mitra = pm.id_mitra 
        AND pm.status_pengajuan = 'diterima'
    WHERE mp.status = 'aktif'
    GROUP BY mp.id_mitra
    ORDER BY jumlah_mhs DESC
    LIMIT 5
");

// --- 8. STATISTIK BIDANG MITRA ---
$q_bidang_stats = mysqli_query($conn, "
    SELECT bidang, COUNT(*) as jumlah 
    FROM mitra_perusahaan 
    WHERE status = 'aktif'
    GROUP BY bidang 
    ORDER BY jumlah DESC 
    LIMIT 6
");
$bidang_stats = [];
if ($q_bidang_stats) {
    while($row = mysqli_fetch_assoc($q_bidang_stats)) {
        $bidang_stats[] = [
            'label' => htmlspecialchars($row['bidang']),
            'value' => (int)$row['jumlah']
        ];
    }
}
?>

<!-- CSS -->
<link rel="stylesheet" href="styles/dashboard.css?v=<?= time(); ?>">

<div class="dashboard-coord">
    <div class="stats-grid">
        <div class="stat-card urgent clickable" onclick="window.location.href='index.php?page=persetujuan_magang_korbid'">
            <div class="stat-icon-bg red"><i class="fas fa-file-contract"></i></div>
            <div class="stat-content">
                <p>Approval Magang</p>
                <h3><?= $pending_magang ?> <span class="unit">Pending</span></h3>
            </div>
            <?php if($pending_magang > 0): ?>
                <div class="notification-badge pulse">!</div>
            <?php endif; ?>
        </div>

        <div class="stat-card warning clickable" onclick="window.location.href='index.php?page=persetujuan_mitra_korbid'">
            <div class="stat-icon-bg orange"><i class="fas fa-handshake"></i></div>
            <div class="stat-content">
                <p>Approval Mitra</p>
                <h3><?= $pending_mitra ?> <span class="unit">Baru</span></h3>
            </div>
        </div>

        <div class="stat-card info clickable" onclick="window.location.href='index.php?page=data_Mahasiswa'">
            <div class="stat-icon-bg blue"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-content">
                <p>Mahasiswa Magang</p>
                <h3><?= $mhs_aktif ?> <span class="unit">Aktif</span></h3>
            </div>
        </div>

        <div class="stat-card success clickable" onclick="window.location.href='index.php?page=data_Mitra'">
            <div class="stat-icon-bg green"><i class="fas fa-building"></i></div>
            <div class="stat-content">
                <p>Total Mitra</p>
                <h3><?= $total_mitra ?> <span class="unit">Instansi</span></h3>
            </div>
        </div>
    </div>

    <div class="dashboard-layout">
        
        <div class="layout-left">
            <!-- CHART: Status Mahasiswa -->
            <div class="content-card chart-section">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie"></i> Statistik Status Mahasiswa</h4>
                </div>
                <div class="card-body">
                    <canvas id="chartStatusMahasiswa" style="max-height: 280px;"></canvas>
                </div>
            </div>

            <!-- MITRA POPULER -->
            <div class="content-card" style="margin-top: 20px;">
                <div class="card-header">
                    <h4><i class="fas fa-trophy"></i> Mitra Paling Diminati</h4>
                    <a href="index.php?page=data_Mitra" class="see-all">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <?php if($q_mitra_populer && mysqli_num_rows($q_mitra_populer) > 0): ?>
                        <div class="mitra-list">
                            <?php 
                            $rank = 1;
                            while($mitra = mysqli_fetch_assoc($q_mitra_populer)): 
                            ?>
                                <div class="mitra-item">
                                    <div class="mitra-rank"><?= $rank++ ?></div>
                                    <div class="mitra-info">
                                        <span class="mitra-name"><?= htmlspecialchars($mitra['nama_mitra']) ?></span>
                                        <span class="mitra-bidang"><?= htmlspecialchars($mitra['bidang']) ?></span>
                                    </div>
                                    <div class="mitra-count">
                                        <i class="fas fa-users"></i> <?= $mitra['jumlah_mhs'] ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-building" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p>Belum ada data mitra.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="layout-right">
            <!-- PENGAJUAN PENDING -->
            <div class="content-card todo-section">
                <div class="card-header">
                    <h4><i class="fas fa-list-ul"></i> Perlu Diproses</h4>
                    <a href="index.php?page=persetujuan_magang_korbid" class="see-all">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <?php if($q_list_pending && mysqli_num_rows($q_list_pending) > 0): ?>
                        <div class="todo-list">
                            <?php while($item = mysqli_fetch_assoc($q_list_pending)): ?>
                                <div class="todo-item" onclick="window.location.href='index.php?page=persetujuan_magang_korbid'">
                                    <div class="todo-icon"><i class="fas fa-clock"></i></div>
                                    <div class="todo-info">
                                        <span class="todo-title"><?= htmlspecialchars($item['nama_kelompok']) ?></span>
                                        <span class="todo-subtitle">Ke: <?= htmlspecialchars($item['nama_mitra']) ?></span>
                                    </div>
                                    <div class="todo-date"><?= date('d M', strtotime($item['tanggal_pengajuan'])) ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p>Tidak ada pengajuan pending.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CHART: Bidang Mitra -->
            <div class="content-card" style="margin-top: 20px;">
                <div class="card-header">
                    <h4><i class="fas fa-chart-bar"></i> Bidang Mitra</h4>
                </div>
                <div class="card-body">
                    <canvas id="chartBidangMitra" style="max-height: 250px;"></canvas>
                </div>
            </div>

            <!-- AKSI CEPAT -->
            <div class="content-card quick-links" style="margin-top: 20px;">
                <div class="card-header">
                    <h4><i class="fas fa-bolt"></i> Aksi Cepat</h4>
                </div>
                <div class="card-body action-grid">
                    <button onclick="window.location.href='index.php?page=data_Mitra'" class="action-btn">
                        <i class="fas fa-building"></i> Data Mitra
                    </button>
                    <button onclick="window.location.href='index.php?page=data_Kelompok'" class="action-btn">
                        <i class="fas fa-layer-group"></i> Kelompok
                    </button>
                    <button onclick="window.location.href='index.php?page=data_Dospem'" class="action-btn">
                        <i class="fas fa-chalkboard-teacher"></i> Dospem
                    </button>
                    <button onclick="window.location.href='index.php?page=persetujuan_mitra_korbid'" class="action-btn">
                        <i class="fas fa-handshake"></i> ACC Mitra
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Tambahan untuk Mitra List -->
<style>
.mitra-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.mitra-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    gap: 15px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s;
}

.mitra-item:last-child {
    border-bottom: none;
}

.mitra-item:hover {
    background: #f8fafc;
}

.mitra-rank {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    flex-shrink: 0;
}

.mitra-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mitra-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

.mitra-bidang {
    font-size: 12px;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 4px;
    width: fit-content;
}

.mitra-count {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #2563eb;
    font-weight: 600;
    font-size: 14px;
}

.mitra-count i {
    font-size: 13px;
}
</style>

<!-- JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// PASSING DATA KE JS
const chartStatusData = <?= json_encode($chart_status_data) ?>;
const bidangStatsData = <?= json_encode($bidang_stats) ?>;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JS Loaded!');
    initCharts();
});

function initCharts() {
    // CHART 1: Status Mahasiswa (Pie Chart)
    const ctxStatus = document.getElementById('chartStatusMahasiswa');
    if (ctxStatus && chartStatusData.length > 0) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: chartStatusData.map(d => d.label),
                datasets: [{
                    data: chartStatusData.map(d => d.value),
                    backgroundColor: [
                        '#3b82f6', // blue
                        '#22c55e', // green
                        '#f59e0b', // orange
                        '#ef4444', // red
                        '#8b5cf6', // purple
                        '#64748b'  // gray
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' mahasiswa';
                            }
                        }
                    }
                }
            }
        });
    }

    // CHART 2: Bidang Mitra (Bar Chart)
    const ctxBidang = document.getElementById('chartBidangMitra');
    if (ctxBidang && bidangStatsData.length > 0) {
        new Chart(ctxBidang, {
            type: 'bar',
            data: {
                labels: bidangStatsData.map(d => d.label),
                datasets: [{
                    label: 'Jumlah Mitra',
                    data: bidangStatsData.map(d => d.value),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' mitra';
                            }
                        }
                    }
                }
            }
        });
    }
}
</script>