<?php
// dashboard.php
// Dashboard untuk Dosen Pembimbing

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: ../Login/login.php');
    exit;
}

include '../Koneksi/koneksi.php';

$id_user_login = $_SESSION['id'];
$nama_dosen = $_SESSION['nama'] ?? 'Dosen';

// ========================================
// AMBIL ID DOSEN
// ========================================
$query_dosen = "SELECT id_dosen FROM dosen WHERE id_user = ?";
$stmt_dosen = mysqli_prepare($conn, $query_dosen);
mysqli_stmt_bind_param($stmt_dosen, 'i', $id_user_login);
mysqli_stmt_execute($stmt_dosen);
$result_dosen = mysqli_stmt_get_result($stmt_dosen);
$row_dosen = mysqli_fetch_assoc($result_dosen);

if (!$row_dosen) {
    echo "<div style='padding:20px; color:red;'>Error: Data Dosen tidak ditemukan.</div>";
    exit;
}

$id_dosen = $row_dosen['id_dosen'];

// ========================================
// STATS: TOTAL MAHASISWA BIMBINGAN
// ========================================
$query_total_mhs = "
    SELECT COUNT(DISTINCT m.id_mahasiswa) as total
    FROM mahasiswa m
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    WHERE k.id_dosen_pembimbing = ?
";
$stmt_total_mhs = mysqli_prepare($conn, $query_total_mhs);
mysqli_stmt_bind_param($stmt_total_mhs, 'i', $id_dosen);
mysqli_stmt_execute($stmt_total_mhs);
$result_total_mhs = mysqli_stmt_get_result($stmt_total_mhs);
$total_mahasiswa = mysqli_fetch_assoc($result_total_mhs)['total'];

// ========================================
// STATS: TOTAL KELOMPOK
// ========================================
$query_total_kelompok = "
    SELECT COUNT(DISTINCT k.id_kelompok) as total
    FROM kelompok k
    WHERE k.id_dosen_pembimbing = ?
";
$stmt_total_kelompok = mysqli_prepare($conn, $query_total_kelompok);
mysqli_stmt_bind_param($stmt_total_kelompok, 'i', $id_dosen);
mysqli_stmt_execute($stmt_total_kelompok);
$result_total_kelompok = mysqli_stmt_get_result($stmt_total_kelompok);
$total_kelompok = mysqli_fetch_assoc($result_total_kelompok)['total'];

// ========================================
// STATS: LOGBOOK PENDING
// ========================================
$query_pending = "
    SELECT COUNT(lh.id_logbook) as total
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    WHERE k.id_dosen_pembimbing = ?
    AND lh.status_validasi = 'pending'
";
$stmt_pending = mysqli_prepare($conn, $query_pending);
mysqli_stmt_bind_param($stmt_pending, 'i', $id_dosen);
mysqli_stmt_execute($stmt_pending);
$result_pending = mysqli_stmt_get_result($stmt_pending);
$logbook_pending = mysqli_fetch_assoc($result_pending)['total'];

// ========================================
// STATS: LOGBOOK DISETUJUI BULAN INI
// ========================================
$query_disetujui = "
    SELECT COUNT(lh.id_logbook) as total
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    WHERE k.id_dosen_pembimbing = ?
    AND lh.status_validasi = 'disetujui'
    AND MONTH(lh.tanggal) = MONTH(CURDATE())
    AND YEAR(lh.tanggal) = YEAR(CURDATE())
";
$stmt_disetujui = mysqli_prepare($conn, $query_disetujui);
mysqli_stmt_bind_param($stmt_disetujui, 'i', $id_dosen);
mysqli_stmt_execute($stmt_disetujui);
$result_disetujui = mysqli_stmt_get_result($stmt_disetujui);
$logbook_disetujui = mysqli_fetch_assoc($result_disetujui)['total'];

// ========================================
// RECENT ACTIVITIES: LOGBOOK TERBARU
// ========================================
$query_recent = "
    SELECT 
        lh.id_logbook,
        lh.tanggal,
        lh.jam_absensi,
        lh.status_validasi,
        m.id_mahasiswa,
        u.nama AS nama_mahasiswa,
        u.nim,
        k.nama_kelompok
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    WHERE k.id_dosen_pembimbing = ?
    ORDER BY lh.tanggal DESC, lh.jam_absensi DESC
    LIMIT 10
";
$stmt_recent = mysqli_prepare($conn, $query_recent);

// Debugging check
if (!$stmt_recent) {
    die("Query Recent Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_recent, 'i', $id_dosen);
mysqli_stmt_execute($stmt_recent);
$result_recent = mysqli_stmt_get_result($stmt_recent);
$recent_activities = [];
while ($row = mysqli_fetch_assoc($result_recent)) {
    $recent_activities[] = $row;
}

// ========================================
// CHART DATA: VALIDASI PER BULAN (6 BULAN TERAKHIR)
// ========================================
$query_chart = "
    SELECT 
        DATE_FORMAT(lh.tanggal, '%Y-%m') as bulan,
        COUNT(CASE WHEN lh.status_validasi = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN lh.status_validasi = 'disetujui' THEN 1 END) as disetujui,
        COUNT(CASE WHEN lh.status_validasi = 'ditolak' THEN 1 END) as ditolak
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    WHERE k.id_dosen_pembimbing = ?
    AND lh.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY bulan
    ORDER BY bulan ASC
";
$stmt_chart = mysqli_prepare($conn, $query_chart);
mysqli_stmt_bind_param($stmt_chart, 'i', $id_dosen);
mysqli_stmt_execute($stmt_chart);
$result_chart = mysqli_stmt_get_result($stmt_chart);
$chart_data = [];
while ($row = mysqli_fetch_assoc($result_chart)) {
    $chart_data[] = $row;
}

// ========================================
// MAHASISWA YANG PERLU PERHATIAN (FIXED VERSION)
// ========================================
$query_perhatian = "
    SELECT 
        m.id_mahasiswa,
        u.nama AS nama_mahasiswa,
        u.nim,
        COUNT(CASE WHEN lh.status_validasi = 'pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN lh.status_validasi = 'ditolak' THEN 1 END) as ditolak_count
    FROM mahasiswa m
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN logbook_harian lh ON m.id_mahasiswa = lh.id_mahasiswa
    WHERE k.id_dosen_pembimbing = ?
    GROUP BY m.id_mahasiswa, u.nama, u.nim
    HAVING COUNT(CASE WHEN lh.status_validasi = 'pending' THEN 1 END) > 5 
       OR COUNT(CASE WHEN lh.status_validasi = 'ditolak' THEN 1 END) > 3
    ORDER BY (COUNT(CASE WHEN lh.status_validasi = 'pending' THEN 1 END) + COUNT(CASE WHEN lh.status_validasi = 'ditolak' THEN 1 END)) DESC
    LIMIT 5
";
$stmt_perhatian = mysqli_prepare($conn, $query_perhatian);

// Debugging check
if (!$stmt_perhatian) {
    die("Query Perhatian Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_perhatian, 'i', $id_dosen);
mysqli_stmt_execute($stmt_perhatian);
$result_perhatian = mysqli_stmt_get_result($stmt_perhatian);
$mahasiswa_perhatian = [];
while ($row = mysqli_fetch_assoc($result_perhatian)) {
    $mahasiswa_perhatian[] = $row;
}
?>

<link rel="stylesheet" href="styles/dashboard.css?v=<?= time(); ?>">

<div class="dashboard-container">
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?= $total_mahasiswa ?></h3>
                <p>Total Mahasiswa Bimbingan</p>
            </div>
            <div class="stat-trend">
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon kelompok">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-content">
                <h3><?= $total_kelompok ?></h3>
                <p>Kelompok Bimbingan</p>
            </div>
            <div class="stat-trend">
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <div class="stat-card urgent">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?= $logbook_pending ?></h3>
                <p>Logbook Perlu Validasi</p>
            </div>
            <div class="stat-trend urgent">
                <?php if ($logbook_pending > 0): ?>
                    <span class="badge-urgent"><?= $logbook_pending ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon approved">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?= $logbook_disetujui ?></h3>
                <p>Disetujui Bulan Ini</p>
            </div>
            <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>

    <div class="main-grid">
        <div class="left-column">
            <div class="card chart-card">
                <div class="card-header">
                    <div>
                        <h2>Progress Validasi Logbook</h2>
                        <p class="card-subtitle">Statistik 6 bulan terakhir</p>
                    </div>
                    <select id="chartFilter" class="chart-filter">
                        <option value="6">6 Bulan</option>
                        <option value="3">3 Bulan</option>
                        <option value="1">1 Bulan</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="validasiChart"></canvas>
                </div>
            </div>

            <div class="card activities-card">
                <div class="card-header">
                    <h2>Aktivitas Terbaru</h2>
                    <a href="index.php?page=monitoring" class="link-all">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="activities-list">
                    <?php if (empty($recent_activities)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada aktivitas</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <?= strtoupper(substr($activity['nama_mahasiswa'], 0, 2)) ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <strong><?= htmlspecialchars($activity['nama_mahasiswa']) ?></strong>
                                        <span class="activity-action">mengirim logbook</span>
                                    </div>
                                    <div class="activity-meta">
                                        <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($activity['tanggal'])) ?></span>
                                        <span><i class="fas fa-clock"></i> <?= date('H:i', strtotime($activity['jam_absensi'])) ?> WIB</span>
                                    </div>
                                </div>
                                <div class="activity-status">
                                    <?php
                                    $status_class = [
                                        'pending' => 'warning',
                                        'disetujui' => 'success',
                                        'ditolak' => 'danger'
                                    ];
                                    $status_text = [
                                        'pending' => 'Pending',
                                        'disetujui' => 'Disetujui',
                                        'ditolak' => 'Ditolak'
                                    ];
                                    ?>
                                    <span class="badge-status <?= $status_class[$activity['status_validasi']] ?>">
                                        <?= $status_text[$activity['status_validasi']] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="card quick-actions-card">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="index.php?page=monitoring" class="action-btn">
                        <div class="action-icon blue">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <span>Validasi Logbook</span>
                        <?php if ($logbook_pending > 0): ?>
                            <span class="action-badge"><?= $logbook_pending ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="index.php?page=daftar_Bimbingan" class="action-btn">
                        <div class="action-icon green">
                            <i class="fas fa-users"></i>
                        </div>
                        <span>Daftar Mahasiswa</span>
                    </a>

                    <a href="index.php?page=notifikasi" class="action-btn">
                        <div class="action-icon yellow">
                            <i class="fas fa-bell"></i>
                        </div>
                        <span>Notifikasi</span>
                    </a>

                    <a href="index.php?page=evaluasi_Nilai" class="action-btn">
                        <div class="action-icon purple">
                            <i class="fas fa-star"></i>
                        </div>
                        <span>Evaluasi & Nilai</span>
                    </a>
                </div>
            </div>

            <div class="card attention-card">
                <div class="card-header">
                    <h2>⚠️ Perlu Perhatian</h2>
                    <p class="card-subtitle">Mahasiswa dengan banyak pending/ditolak</p>
                </div>
                <div class="attention-list">
                    <?php if (empty($mahasiswa_perhatian)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-check-circle"></i>
                            <p>Semua mahasiswa baik-baik saja! 👍</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($mahasiswa_perhatian as $mhs): ?>
                            <div class="attention-item">
                                <div class="attention-avatar">
                                    <?= strtoupper(substr($mhs['nama_mahasiswa'], 0, 2)) ?>
                                </div>
                                <div class="attention-content">
                                    <strong><?= htmlspecialchars($mhs['nama_mahasiswa']) ?></strong>
                                    <small><?= htmlspecialchars($mhs['nim']) ?></small>
                                    <div class="attention-stats">
                                        <?php if ($mhs['pending_count'] > 0): ?>
                                            <span class="stat-badge warning">
                                                <i class="fas fa-clock"></i> <?= $mhs['pending_count'] ?> pending
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($mhs['ditolak_count'] > 0): ?>
                                            <span class="stat-badge danger">
                                                <i class="fas fa-times-circle"></i> <?= $mhs['ditolak_count'] ?> ditolak
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button class="btn-contact-small" onclick="contactMahasiswa(<?= $mhs['id_mahasiswa'] ?>)">
                                    <i class="fas fa-envelope"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Pass PHP data to JavaScript
const chartData = <?= json_encode($chart_data) ?>;
</script>
<script src="scripts/dashboard.js?v=<?= time(); ?>"></script>