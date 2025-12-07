<?php
// dashboard.php - Dashboard Mahasiswa yang diperbaiki

$id_user = $_SESSION['id'];

// ==========================================
// AMBIL DATA MAHASISWA
// ==========================================
$query_mahasiswa = "
    SELECT m.*, u.nama, u.email, u.nim 
    FROM mahasiswa m
    JOIN users u ON m.id_user = u.id
    WHERE m.id_user = ?
";
$stmt_mhs = mysqli_prepare($conn, $query_mahasiswa);
mysqli_stmt_bind_param($stmt_mhs, 'i', $id_user);
mysqli_stmt_execute($stmt_mhs);
$result_mhs = mysqli_stmt_get_result($stmt_mhs);
$data_mahasiswa = mysqli_fetch_assoc($result_mhs);
mysqli_stmt_close($stmt_mhs);

$id_mahasiswa = $data_mahasiswa['id_mahasiswa'] ?? 0;
$nama_mahasiswa = $data_mahasiswa['nama'] ?? 'Mahasiswa';
$nim = $data_mahasiswa['nim'] ?? '-';
$prodi = $data_mahasiswa['prodi'] ?? '-';
$angkatan = $data_mahasiswa['angkatan'] ?? '-';
$status_magang = $data_mahasiswa['status_magang'] ?? 'pra-magang';

// ==========================================
// AMBIL DATA KELOMPOK
// ==========================================
$query_kelompok = "
    SELECT k.*, d.nidn, u.nama as nama_dosen,
    (SELECT COUNT(*) FROM anggota_kelompok WHERE id_kelompok = k.id_kelompok) as jumlah_anggota
    FROM anggota_kelompok ak
    JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN dosen d ON k.id_dosen_pembimbing = d.id_dosen
    LEFT JOIN users u ON d.id_user = u.id
    WHERE ak.id_mahasiswa = ?
";
$stmt_kel = mysqli_prepare($conn, $query_kelompok);
mysqli_stmt_bind_param($stmt_kel, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_kel);
$result_kel = mysqli_stmt_get_result($stmt_kel);
$data_kelompok = mysqli_fetch_assoc($result_kel);
mysqli_stmt_close($stmt_kel);

$nama_kelompok = $data_kelompok['nama_kelompok'] ?? '-';
$nama_dosen = $data_kelompok['nama_dosen'] ?? '-';
$jumlah_anggota = $data_kelompok['jumlah_anggota'] ?? 0;
$id_kelompok = $data_kelompok['id_kelompok'] ?? 0;

// ==========================================
// HITUNG LOGBOOK
// ==========================================
$query_logbook = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_validasi = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
        SUM(CASE WHEN status_validasi = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status_validasi = 'ditolak' THEN 1 ELSE 0 END) as ditolak
    FROM logbook_harian
    WHERE id_mahasiswa = ?
    AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
";
$stmt_logbook = mysqli_prepare($conn, $query_logbook);
mysqli_stmt_bind_param($stmt_logbook, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_logbook);
$result_logbook = mysqli_stmt_get_result($stmt_logbook);
$row_logbook = mysqli_fetch_assoc($result_logbook);
mysqli_stmt_close($stmt_logbook);

$total_logbook = $row_logbook['total'] ?? 0;
$logbook_disetujui = $row_logbook['disetujui'] ?? 0;
$logbook_pending = $row_logbook['pending'] ?? 0;
$logbook_ditolak = $row_logbook['ditolak'] ?? 0;

// ==========================================
// STATUS PENGAJUAN TERBARU
// ==========================================
$status_pengajuan_text = 'Belum ada pengajuan';
$nama_mitra = '-';
$tanggal_pengajuan = '-';

if ($id_kelompok > 0) {
    $query_pengajuan = "
        SELECT pm.*, mp.nama_mitra, pm.status_pengajuan, pm.tanggal_pengajuan
        FROM pengajuan_magang pm
        LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
        WHERE pm.id_kelompok = ?
        ORDER BY pm.tanggal_pengajuan DESC
        LIMIT 1
    ";
    $stmt_pengajuan = mysqli_prepare($conn, $query_pengajuan);
    mysqli_stmt_bind_param($stmt_pengajuan, 'i', $id_kelompok);
    mysqli_stmt_execute($stmt_pengajuan);
    $result_pengajuan = mysqli_stmt_get_result($stmt_pengajuan);
    
    if ($data_pengajuan = mysqli_fetch_assoc($result_pengajuan)) {
        $status_pengajuan_text = $data_pengajuan['status_pengajuan'];
        $nama_mitra = $data_pengajuan['nama_mitra'] ?? '-';
        $tanggal_pengajuan = date('d M Y', strtotime($data_pengajuan['tanggal_pengajuan']));
    }
    mysqli_stmt_close($stmt_pengajuan);
}

// ==========================================
// LOGBOOK TERBARU (2 TERAKHIR)
// ==========================================
$query_recent = "
    SELECT * FROM logbook_harian
    WHERE id_mahasiswa = ?
    ORDER BY tanggal DESC
    LIMIT 2
";
$stmt_recent = mysqli_prepare($conn, $query_recent);
mysqli_stmt_bind_param($stmt_recent, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_recent);
$result_recent = mysqli_stmt_get_result($stmt_recent);
$recent_logbook = [];
while ($row = mysqli_fetch_assoc($result_recent)) {
    $recent_logbook[] = $row;
}
mysqli_stmt_close($stmt_recent);

// Helper Functions
function getStatusBadge($status) {
    $badges = [
        'menunggu' => ['class' => 'warning', 'text' => 'Menunggu', 'icon' => 'clock'],
        'menunggu_mitra' => ['class' => 'warning', 'text' => 'Menunggu Mitra', 'icon' => 'hourglass-half'],
        'diterima' => ['class' => 'success', 'text' => 'Diterima', 'icon' => 'check-circle'],
        'ditolak' => ['class' => 'danger', 'text' => 'Ditolak', 'icon' => 'times-circle'],
        'disetujui' => ['class' => 'success', 'text' => 'Disetujui', 'icon' => 'check'],
        'pending' => ['class' => 'warning', 'text' => 'Pending', 'icon' => 'clock'],
        'pra-magang' => ['class' => 'secondary', 'text' => 'Pra-Magang', 'icon' => 'hourglass-start'],
        'magang_aktif' => ['class' => 'success', 'text' => 'Magang Aktif', 'icon' => 'briefcase'],
        'selesai' => ['class' => 'primary', 'text' => 'Selesai', 'icon' => 'flag-checkered']
    ];
    return $badges[$status] ?? ['class' => 'secondary', 'text' => ucfirst($status), 'icon' => 'info'];
}

$status_badge = getStatusBadge($status_magang);
$pengajuan_badge = getStatusBadge($status_pengajuan_text);
?>

<style>
/* Dashboard Styles - Inline untuk memastikan ter-load */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.dashboard-wrapper {
    padding: 0;
    max-width: 100%;
}

/* Top Cards Grid - 3 Columns */
.top-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 32px;
}

.info-card {
    background: white;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #E5E7EB;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #4270F4 0%, #2C5AE8 100%);
}

.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(66, 112, 244, 0.15);
}

.card-header-dash {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: white;
}

.icon-gradient-1 { background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); }
.icon-gradient-2 { background: linear-gradient(135deg, #F093FB 0%, #F5576C 100%); }
.icon-gradient-3 { background: linear-gradient(135deg, #4FACFE 0%, #00F2FE 100%); }

.card-title-dash {
    font-size: 14px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-content-dash {
    padding-left: 60px;
}

.card-value {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
}

.card-label {
    font-size: 14px;
    color: #9CA3AF;
}

.status-badge-large {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 24px;
    font-size: 15px;
    font-weight: 600;
}

.badge-success { background: #D1FAE5; color: #065F46; }
.badge-warning { background: #FEF3C7; color: #92400E; }
.badge-danger { background: #FEE2E2; color: #991B1B; }
.badge-secondary { background: #F3F4F6; color: #374151; }
.badge-primary { background: #DBEAFE; color: #1E40AF; }

/* Main Grid - 2 Columns */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #E5E7EB;
    overflow: hidden;
}

.card-header-main {
    padding: 20px 24px;
    border-bottom: 1px solid #E5E7EB;
    background: linear-gradient(to right, #F9FAFB, #FFFFFF);
}

.card-header-main h3 {
    font-size: 17px;
    font-weight: 700;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header-main h3 i {
    color: #4270F4;
}

.card-body-main {
    padding: 24px;
}

/* Profile Table */
.profile-table {
    width: 100%;
}

.profile-table tr {
    border-bottom: 1px solid #F3F4F6;
}

.profile-table tr:last-child {
    border-bottom: none;
}

.profile-table td {
    padding: 14px 0;
    font-size: 14px;
}

.profile-table td:first-child {
    font-weight: 600;
    color: #6B7280;
    width: 40%;
}

.profile-table td:last-child {
    color: #111827;
    font-weight: 500;
}

/* Pengajuan Info */
.pengajuan-box {
    background: #F9FAFB;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #4270F4;
}

.pengajuan-mitra-name {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pengajuan-mitra-name i {
    color: #4270F4;
}

.pengajuan-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 12px;
    font-size: 14px;
    color: #6B7280;
}

.empty-state-box {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-box i {
    font-size: 64px;
    color: #D1D5DB;
    margin-bottom: 16px;
}

.empty-state-box p {
    color: #9CA3AF;
    margin-bottom: 20px;
}

/* Logbook Stats Grid */
.logbook-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.stat-box {
    text-align: center;
    padding: 24px 16px;
    border-radius: 12px;
    transition: transform 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-4px);
}

.stat-box-success { background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); }
.stat-box-warning { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); }
.stat-box-danger { background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); }

.stat-icon-box {
    width: 56px;
    height: 56px;
    margin: 0 auto 12px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.stat-box-success .stat-icon-box { background: #10B981; }
.stat-box-warning .stat-icon-box { background: #F59E0B; }
.stat-box-danger .stat-icon-box { background: #EF4444; }

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
}

.stat-text {
    font-size: 13px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
}

/* Recent Logbook List */
.logbook-item-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #F9FAFB;
    border-radius: 10px;
    margin-bottom: 12px;
    border: 1px solid #E5E7EB;
    transition: all 0.3s ease;
}

.logbook-item-box:hover {
    background: #F3F4F6;
    border-color: #4270F4;
    transform: translateX(4px);
}

.logbook-date {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #111827;
}

.logbook-date i {
    color: #4270F4;
    font-size: 18px;
}

.badge-sm {
    padding: 6px 14px;
    font-size: 12px;
    border-radius: 16px;
    font-weight: 600;
}

/* Button */
.btn-primary-dash {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px 24px;
    background: linear-gradient(135deg, #4270F4 0%, #2C5AE8 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(66, 112, 244, 0.3);
}

.btn-primary-dash:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(66, 112, 244, 0.4);
}

.btn-link-dash {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #4270F4;
    font-weight: 600;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-link-dash:hover {
    color: #2C5AE8;
    gap: 12px;
}

.card-footer-main {
    padding: 16px 24px;
    border-top: 1px solid #E5E7EB;
    background: #F9FAFB;
}

/* Responsive */
@media (max-width: 1200px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .top-cards {
        grid-template-columns: 1fr;
    }
    
    .logbook-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-wrapper">
    
    <!-- TOP CARDS -->
    <div class="top-cards">
        
        <!-- Card 1: Status Magang -->
        <div class="info-card">
            <div class="card-header-dash">
                <div class="card-icon icon-gradient-1">
                    <i class="fas fa-<?= $status_badge['icon'] ?>"></i>
                </div>
                <div class="card-title-dash">Status Magang</div>
            </div>
            <div class="card-content-dash">
                <div class="card-value">
                    <span class="status-badge-large badge-<?= $status_badge['class'] ?>">
                        <i class="fas fa-<?= $status_badge['icon'] ?>"></i>
                        <?= $status_badge['text'] ?>
                    </span>
                </div>
                <div class="card-label">Status Saat Ini</div>
            </div>
        </div>

        <!-- Card 2: Kelompok -->
        <div class="info-card">
            <div class="card-header-dash">
                <div class="card-icon icon-gradient-2">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-title-dash">Kelompok</div>
            </div>
            <div class="card-content-dash">
                <div class="card-value"><?= htmlspecialchars($nama_kelompok) ?></div>
                <div class="card-label"><?= $jumlah_anggota ?> Anggota</div>
            </div>
        </div>

        <!-- Card 3: Logbook -->
        <div class="info-card">
            <div class="card-header-dash">
                <div class="card-icon icon-gradient-3">
                    <i class="fas fa-book"></i>
                </div>
                <div class="card-title-dash">Logbook Harian</div>
            </div>
            <div class="card-content-dash">
                <div class="card-value"><?= $total_logbook ?></div>
                <div class="card-label">Entri (30 Hari Terakhir)</div>
            </div>
        </div>

    </div>

    <!-- MAIN GRID -->
    <div class="main-grid">
        
        <!-- LEFT COLUMN -->
        <div>
            <!-- Profil Card -->
            <div class="dashboard-card" style="margin-bottom: 24px;">
                <div class="card-header-main">
                    <h3><i class="fas fa-user-circle"></i> Profil Mahasiswa</h3>
                </div>
                <div class="card-body-main">
                    <table class="profile-table">
                        <tr>
                            <td>NIM</td>
                            <td><?= htmlspecialchars($nim) ?></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td><?= htmlspecialchars($nama_mahasiswa) ?></td>
                        </tr>
                        <tr>
                            <td>Program Studi</td>
                            <td><?= htmlspecialchars($prodi) ?></td>
                        </tr>
                        <tr>
                            <td>Angkatan</td>
                            <td><?= htmlspecialchars($angkatan) ?></td>
                        </tr>
                        <tr>
                            <td>Dosen Pembimbing</td>
                            <td><?= htmlspecialchars($nama_dosen) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer-main">
                    <a href="index.php?page=pribadi" class="btn-link-dash">
                        <i class="fas fa-edit"></i> Edit Profil
                    </a>
                </div>
            </div>

            <!-- Status Pengajuan Card -->
            <div class="dashboard-card">
                <div class="card-header-main">
                    <h3><i class="fas fa-file-alt"></i> Status Pengajuan</h3>
                </div>
                <div class="card-body-main">
                    <?php if ($nama_mitra !== '-'): ?>
                        <div class="pengajuan-box">
                            <div class="pengajuan-mitra-name">
                                <i class="fas fa-building"></i>
                                <?= htmlspecialchars($nama_mitra) ?>
                            </div>
                            <div>
                                <span class="status-badge-large badge-<?= $pengajuan_badge['class'] ?>">
                                    <i class="fas fa-<?= $pengajuan_badge['icon'] ?>"></i>
                                    <?= $pengajuan_badge['text'] ?>
                                </span>
                            </div>
                            <div class="pengajuan-meta">
                                <span><i class="fas fa-calendar"></i> <?= $tanggal_pengajuan ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-box">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada pengajuan magang</p>
                            <a href="index.php?page=berkas_Magang" class="btn-primary-dash">
                                <i class="fas fa-plus"></i> Ajukan Magang
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer-main">
                    <a href="index.php?page=status_pengajuan" class="btn-link-dash">
                        <i class="fas fa-history"></i> Lihat Riwayat
                    </a>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
            <!-- Statistik Logbook -->
            <div class="dashboard-card" style="margin-bottom: 24px;">
                <div class="card-header-main">
                    <h3><i class="fas fa-chart-pie"></i> Statistik Logbook</h3>
                </div>
                <div class="card-body-main">
                    <div class="logbook-stats-grid">
                        <div class="stat-box stat-box-success">
                            <div class="stat-icon-box">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number"><?= $logbook_disetujui ?></div>
                            <div class="stat-text">Disetujui</div>
                        </div>
                        <div class="stat-box stat-box-warning">
                            <div class="stat-icon-box">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-number"><?= $logbook_pending ?></div>
                            <div class="stat-text">Pending</div>
                        </div>
                        <div class="stat-box stat-box-danger">
                            <div class="stat-icon-box">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-number"><?= $logbook_ditolak ?></div>
                            <div class="stat-text">Ditolak</div>
                        </div>
                    </div>
                    <?php if ($can_crud_magang): ?>
                        <a href="index.php?page=logbook" class="btn-primary-dash">
                            <i class="fas fa-plus"></i> Tambah Logbook
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Logbook Terbaru -->
            <div class="dashboard-card">
                <div class="card-header-main">
                    <h3><i class="fas fa-history"></i> Logbook Terbaru</h3>
                </div>
                <div class="card-body-main">
                    <?php if (count($recent_logbook) > 0): ?>
                        <?php foreach ($recent_logbook as $log): 
                            $log_badge = getStatusBadge($log['status_validasi']);
                        ?>
                            <div class="logbook-item-box">
                                <div class="logbook-date">
                                    <i class="fas fa-calendar-day"></i>
                                    <?= date('d M Y', strtotime($log['tanggal'])) ?>
                                </div>
                                <span class="badge-sm badge-<?= $log_badge['class'] ?>">
                                    <?= $log_badge['text'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-box">
                            <i class="fas fa-book-open"></i>
                            <p>Belum ada logbook</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer-main">
                    <a href="index.php?page=logbook" class="btn-link-dash">
                        <i class="fas fa-list"></i> Lihat Semua
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
// Simple animation on load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.info-card, .dashboard-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>