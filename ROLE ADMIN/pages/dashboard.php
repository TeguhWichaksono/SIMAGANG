<?php
// pages/dashboard.php
// Dashboard khusus Administrator
// Fokus: Monitoring User, Keamanan, dan Kesehatan Sistem

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek sesi (pastikan admin)
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Admin') {
    // Redirect atau handle error
}

include '../Koneksi/koneksi.php';

// ========================================
// 1. STATISTIK TOTAL USER
// ========================================
// Total semua user
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$total_users = mysqli_fetch_assoc($q_total)['total'];

// Total Mahasiswa
$q_mhs = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='Mahasiswa'");
$total_mhs = mysqli_fetch_assoc($q_mhs)['total'];

// Total Dosen & Korbid (Staff)
$q_staff = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role IN ('Dosen Pembimbing', 'Koordinator Bidang')");
$total_staff = mysqli_fetch_assoc($q_staff)['total'];

// ========================================
// 2. DATA UNTUK CHART (Komposisi User)
// ========================================
$q_chart = mysqli_query($conn, "SELECT role, COUNT(*) as jumlah FROM users GROUP BY role");
$chart_data = [];
while($row = mysqli_fetch_assoc($q_chart)) {
    // Normalisasi nama role untuk label
    $label = str_replace('_', ' ', $row['role']);
    $label = ucwords($label);
    $chart_data[] = [
        'label' => $label,
        'value' => $row['jumlah']
    ];
}

// ========================================
// 3. USER TERBARU (Baru ditambahkan)
// ========================================
$q_recent = mysqli_query($conn, "SELECT nama, role, email, foto_profil FROM users ORDER BY id DESC LIMIT 5");
?>

<link rel="stylesheet" href="styles/dashboard.css?v=<?= time(); ?>">

<div class="admin-dashboard">
    
    <div class="stats-overview">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_users ?></h3>
                <p>Total User Sistem</p>
            </div>
            <div class="stat-chart-mini">
                <div class="pulse-dot"></div> Online
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_mhs ?></h3>
                <p>Akun Mahasiswa</p>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_staff ?></h3>
                <p>Akun Staff (Dosen/Korbid)</p>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-info">
                <h3>Stabil</h3>
                <p>Status Server</p>
            </div>
        </div>
    </div>

    <div class="content-grid">
        
        <div class="main-card chart-section">
            <div class="card-header">
                <h4><i class="fas fa-chart-pie"></i> Komposisi Pengguna Sistem</h4>
                <button class="btn-refresh" onclick="location.reload()"><i class="fas fa-sync-alt"></i></button>
            </div>
            <div class="card-body">
                <canvas id="userRoleChart"></canvas>
            </div>
            <div class="card-footer-info">
                <small><i class="fas fa-info-circle"></i> Grafik ini membantu memantau beban kapasitas user.</small>
            </div>
        </div>

        <div class="right-column-wrapper">
            
            <div class="main-card actions-section">
                <div class="card-header">
                    <h4><i class="fas fa-bolt"></i> Aksi Cepat</h4>
                </div>
                <div class="quick-actions-grid">
                    <button onclick="window.location.href='index.php?page=manajemen_User&tab=pengurus'" class="qa-btn">
                        <i class="fas fa-user-plus"></i> Tambah Admin/Dosen
                    </button>
                    <button onclick="window.location.href='index.php?page=manajemen_User&tab=mahasiswa'" class="qa-btn">
                        <i class="fas fa-file-import"></i> Import Mahasiswa
                    </button>
                    <button onclick="window.location.href='index.php?page=laporan_Sistem'" class="qa-btn secondary">
                        <i class="fas fa-clipboard-list"></i> Lihat Log Sistem
                    </button>
                    <button class="qa-btn danger">
                        <i class="fas fa-database"></i> Backup Database
                    </button>
                </div>
            </div>

            <div class="main-card recent-section">
                <div class="card-header">
                    <h4><i class="fas fa-history"></i> User Baru Ditambahkan</h4>
                </div>
                <div class="recent-list">
                    <?php while($user = mysqli_fetch_assoc($q_recent)): ?>
                    <div class="recent-item">
                        <div class="recent-avatar">
                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                        </div>
                        <div class="recent-details">
                            <span class="recent-name"><?= htmlspecialchars($user['nama']) ?></span>
                            <span class="recent-role badge-role <?= strtolower(str_replace(' ', '-', $user['role'])) ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </div>
                        <div class="recent-action">
                            <i class="fas fa-check text-success"></i>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass data PHP ke JS
    const chartData = <?= json_encode($chart_data) ?>;
</script>
<script src="scripts/dashboard.js?v=<?= time(); ?>"></script>