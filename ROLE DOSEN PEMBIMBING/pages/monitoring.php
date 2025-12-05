<?php
// monitoring.php
// Monitoring & Validasi Logbook Mahasiswa Bimbingan

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: ../Login/login.php');
    exit;
}

include '../Koneksi/koneksi.php';

$id_user_login = $_SESSION['id'];

// ========================================
// 1. AMBIL ID DOSEN DARI ID USER
// ========================================
// Kita perlu id_dosen (bukan id_user) untuk mencocokkan dengan tabel kelompok
$query_dosen = "SELECT id_dosen FROM dosen WHERE id_user = ?";
$stmt_dosen = mysqli_prepare($conn, $query_dosen);
mysqli_stmt_bind_param($stmt_dosen, 'i', $id_user_login);
mysqli_stmt_execute($stmt_dosen);
$result_dosen = mysqli_stmt_get_result($stmt_dosen);
$row_dosen = mysqli_fetch_assoc($result_dosen);

if (!$row_dosen) {
    // Jika user login bukan dosen atau data belum ada di tabel dosen
    echo "<div style='padding:20px; color:red;'>Error: Data Dosen tidak ditemukan untuk User ID ini. Pastikan akun ini terdaftar di tabel dosen.</div>";
    exit;
}

$id_dosen = $row_dosen['id_dosen']; // Ini ID yang dipakai untuk filter kelompok

// ========================================
// GET LOGBOOK PENDING (Butuh Validasi)
// ========================================
$query_pending = "
    SELECT 
        lh.id_logbook,
        lh.tanggal,
        lh.jam_absensi,
        lh.lokasi_absensi,
        lh.foto_absensi,
        lh.status_validasi,
        m.id_mahasiswa,
        u.nama AS nama_mahasiswa,
        nim,
        m.prodi,
        k.nama_kelompok,
        mp.nama_mitra,
        COUNT(dk.id_detail) as jumlah_kegiatan
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN pengajuan_magang pm ON k.id_kelompok = pm.id_kelompok
    LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    LEFT JOIN detail_kegiatan dk ON lh.id_logbook = dk.id_logbook
    WHERE k.id_dosen_pembimbing = ?
    AND lh.status_validasi = 'pending'
    GROUP BY 
        lh.id_logbook, lh.tanggal, lh.jam_absensi, lh.lokasi_absensi, lh.foto_absensi, lh.status_validasi,
        m.id_mahasiswa, u.nama, nim, m.prodi, k.nama_kelompok, mp.nama_mitra
    ORDER BY lh.tanggal DESC, lh.jam_absensi DESC
    LIMIT 50
";

$stmt_pending = mysqli_prepare($conn, $query_pending);

// Cek jika query error syntax
if (!$stmt_pending) {
    die("Error Query Pending: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_pending, 'i', $id_dosen);
mysqli_stmt_execute($stmt_pending);
$result_pending = mysqli_stmt_get_result($stmt_pending);
$logbook_pending = [];
while ($row = mysqli_fetch_assoc($result_pending)) {
    $logbook_pending[] = $row;
}

// ========================================
// GET RIWAYAT LOGBOOK (Semua Status)
// ========================================
$query_riwayat = "
    SELECT 
        lh.id_logbook,
        lh.tanggal,
        lh.jam_absensi,
        lh.lokasi_absensi,
        lh.foto_absensi,
        lh.status_validasi,
        lh.catatan_dosen,
        m.id_mahasiswa,
        u.nama AS nama_mahasiswa,
        nim,
        k.nama_kelompok,
        mp.nama_mitra,
        COUNT(dk.id_detail) as jumlah_kegiatan
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN pengajuan_magang pm ON k.id_kelompok = pm.id_kelompok
    LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    LEFT JOIN detail_kegiatan dk ON lh.id_logbook = dk.id_logbook
    WHERE k.id_dosen_pembimbing = ?
    GROUP BY 
        lh.id_logbook, lh.tanggal, lh.jam_absensi, lh.lokasi_absensi, lh.foto_absensi, lh.status_validasi, lh.catatan_dosen,
        m.id_mahasiswa, u.nama, nim, k.nama_kelompok, mp.nama_mitra
    ORDER BY lh.tanggal DESC, lh.jam_absensi DESC
    LIMIT 100
";

$stmt_riwayat = mysqli_prepare($conn, $query_riwayat);

// Cek jika query error syntax
if (!$stmt_riwayat) {
    die("Error Query Riwayat: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_riwayat, 'i', $id_dosen);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
$logbook_riwayat = [];
while ($row = mysqli_fetch_assoc($result_riwayat)) {
    $logbook_riwayat[] = $row;
}
?>

<link rel="stylesheet" href="styles/monitoring.css?v=<?= time(); ?>">

<div class="monitoring-container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">📊</div>
            <div>
                <h1 class="header-title">Monitoring Logbook Mahasiswa</h1>
                <p class="header-subtitle">Validasi kegiatan magang mahasiswa bimbingan Anda</p>
            </div>
        </div>
    </div>

    <div class="tabs-nav">
        <div class="tabs-nav-inner">
            <button class="tab-button active" onclick="switchTab('pending')" id="tab-btn-pending">
                <i class="fas fa-clock"></i>
                <span>Perlu Validasi</span>
                <?php if (count($logbook_pending) > 0): ?>
                    <span class="badge-count"><?= count($logbook_pending) ?></span>
                <?php endif; ?>
            </button>
            <button class="tab-button" onclick="switchTab('riwayat')" id="tab-btn-riwayat">
                <i class="fas fa-history"></i>
                <span>Riwayat Logbook</span>
            </button>
        </div>
    </div>

    <div class="tab-content active" id="tab-pending">
        <div class="card">
            <div class="card-header">
                <h2>Logbook yang Perlu Divalidasi</h2>
                <p class="card-subtitle">
                    <i class="fas fa-info-circle"></i>
                    Validasi logbook mahasiswa sebelum batas waktu
                </p>
            </div>

            <?php if (empty($logbook_pending)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>Semua Logbook Sudah Divalidasi!</h3>
                    <p>Tidak ada logbook yang menunggu validasi saat ini</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Tanggal</th>
                                <th class="text-center">Kegiatan</th>
                                <th>Tempat Magang</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logbook_pending as $index => $log): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="mahasiswa-info">
                                            <div class="mahasiswa-avatar">
                                                <?= strtoupper(substr($log['nama_mahasiswa'], 0, 2)) ?>
                                            </div>
                                            <div class="mahasiswa-details">
                                                <h4><?= htmlspecialchars($log['nama_mahasiswa']) ?></h4>
                                                <p><?= htmlspecialchars($log['nim']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-display">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d M Y', strtotime($log['tanggal'])) ?>
                                        </div>
                                        <small style="color: var(--gray-500); margin-left: 24px;">
                                            <?= date('H:i', strtotime($log['jam_absensi'])) ?> WIB
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-count"><?= $log['jumlah_kegiatan'] ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;"><?= htmlspecialchars($log['nama_mitra'] ?? '-') ?></div>
                                        <small style="color: var(--gray-500);"><?= htmlspecialchars($log['nama_kelompok'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge pending">
                                            <i class="fas fa-clock"></i>
                                            Pending
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" 
                                                    onclick="lihatDetailLogbook(<?= $log['id_logbook'] ?>)">
                                                <i class="fas fa-eye"></i>
                                                Detail
                                            </button>
                                            <button class="btn-action btn-approve" 
                                                    onclick="validasiLogbook(<?= $log['id_logbook'] ?>, 'disetujui')">
                                                <i class="fas fa-check"></i>
                                                Setujui
                                            </button>
                                            <button class="btn-action btn-reject" 
                                                    onclick="validasiLogbook(<?= $log['id_logbook'] ?>, 'ditolak')">
                                                <i class="fas fa-times"></i>
                                                Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-content" id="tab-riwayat">
        <div class="card">
            <div class="card-header">
                <h2>Riwayat Logbook</h2>
                <p class="card-subtitle">
                    <i class="fas fa-filter"></i>
                    Gunakan filter untuk mempermudah pencarian
                </p>
            </div>

            <div class="filters-bar">
                <div class="filter-group">
                    <label>Mahasiswa</label>
                    <select id="filterMahasiswa">
                        <option value="">Semua Mahasiswa</option>
                        <?php
                        // Get unique mahasiswa
                        $mahasiswa_list = [];
                        foreach ($logbook_riwayat as $log) {
                            $key = $log['id_mahasiswa'];
                            if (!isset($mahasiswa_list[$key])) {
                                $mahasiswa_list[$key] = $log['nama_mahasiswa'];
                            }
                        }
                        foreach ($mahasiswa_list as $id => $nama):
                        ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Dari Tanggal</label>
                    <input type="date" id="filterTanggalMulai">
                </div>

                <div class="filter-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="filterTanggalSelesai">
                </div>

                <button class="btn-filter" onclick="applyFilter()">
                    <i class="fas fa-search"></i>
                    Filter
                </button>
            </div>

            <?php if (empty($logbook_riwayat)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Belum Ada Riwayat Logbook</h3>
                    <p>Riwayat logbook akan muncul setelah mahasiswa mengirim logbook</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table id="tableRiwayat">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Tanggal</th>
                                <th class="text-center">Kegiatan</th>
                                <th>Tempat Magang</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logbook_riwayat as $index => $log): ?>
                                <tr data-mahasiswa="<?= $log['id_mahasiswa'] ?>" 
                                    data-status="<?= $log['status_validasi'] ?>"
                                    data-tanggal="<?= $log['tanggal'] ?>">
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="mahasiswa-info">
                                            <div class="mahasiswa-avatar">
                                                <?= strtoupper(substr($log['nama_mahasiswa'], 0, 2)) ?>
                                            </div>
                                            <div class="mahasiswa-details">
                                                <h4><?= htmlspecialchars($log['nama_mahasiswa']) ?></h4>
                                                <p><?= htmlspecialchars($log['nim']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-display">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d M Y', strtotime($log['tanggal'])) ?>
                                        </div>
                                        <small style="color: var(--gray-500); margin-left: 24px;">
                                            <?= date('H:i', strtotime($log['jam_absensi'])) ?> WIB
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-count"><?= $log['jumlah_kegiatan'] ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;"><?= htmlspecialchars($log['nama_mitra'] ?? '-') ?></div>
                                        <small style="color: var(--gray-500);"><?= htmlspecialchars($log['nama_kelompok'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $status_class = $log['status_validasi'];
                                        $status_icon = [
                                            'pending' => 'fa-clock',
                                            'disetujui' => 'fa-check-circle',
                                            'ditolak' => 'fa-times-circle'
                                        ];
                                        $status_text = [
                                            'pending' => 'Pending',
                                            'disetujui' => 'Disetujui',
                                            'ditolak' => 'Ditolak'
                                        ];
                                        ?>
                                        <span class="status-badge <?= $status_class ?>">
                                            <i class="fas <?= $status_icon[$status_class] ?>"></i>
                                            <?= $status_text[$status_class] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" 
                                                    onclick="lihatDetailLogbook(<?= $log['id_logbook'] ?>)">
                                                <i class="fas fa-eye"></i>
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDetailLogbook">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div>
                <h3>Detail Logbook</h3>
                <p class="modal-subtitle" id="detailSubtitle"></p>
            </div>
            <button class="close-modal" onclick="closeModal('modalDetailLogbook')">&times;</button>
        </div>
        <div class="modal-body" id="detailLogbookContent">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Memuat data...</p>
            </div>
        </div>
        <div class="modal-footer" id="detailLogbookFooter">
            <button class="btn btn-secondary" onclick="closeModal('modalDetailLogbook')">
                Tutup
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalValidasi">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h3 id="validasiTitle">Validasi Logbook</h3>
                <p class="modal-subtitle">Berikan catatan untuk mahasiswa</p>
            </div>
            <button class="close-modal" onclick="closeModal('modalValidasi')">&times;</button>
        </div>
        <form id="formValidasi">
            <div class="modal-body">
                <input type="hidden" id="validasiIdLogbook" name="id_logbook">
                <input type="hidden" id="validasiStatus" name="status">
                
                <div class="form-group">
                    <label for="validasiCatatan">
                        Catatan <span style="color: var(--gray-500);">(Opsional)</span>
                    </label>
                    <textarea id="validasiCatatan" 
                              name="catatan" 
                              rows="5"
                              placeholder="Berikan feedback atau catatan untuk mahasiswa..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalValidasi')">
                    Batal
                </button>
                <button type="submit" class="btn" id="btnSubmitValidasi">
                    <i class="fas fa-check"></i>
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalImage">
    <div class="modal-image-content">
        <span class="close-modal" onclick="closeModal('modalImage')">&times;</span>
        <img id="modalImageContent" src="" alt="Full Image">
    </div>
</div>

<script src="scripts/monitoring.js?v=<?= time(); ?>"></script>