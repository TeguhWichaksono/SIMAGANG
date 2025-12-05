<?php
// ========================================
// SECURITY & SESSION CHECK
// ========================================
if (!isset($_SESSION['id'])) {
    header('Location: ../Login/login.php');
    exit;
}

function tanggal_indo($tanggal) {
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $hari = array (
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    );
    
    $timestamp = strtotime($tanggal);
    $nama_hari = $hari[date('l', $timestamp)];
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    
    return $nama_hari . ', ' . $tgl . ' ' . $bln . ' ' . $thn;
}

// ========================================
// GET MAHASISWA DATA
// ========================================
$id_user = $_SESSION['id'];
$query_mahasiswa = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query_mahasiswa);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mahasiswa = mysqli_fetch_assoc($result);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$can_crud = isset($_SESSION['can_crud_magang']) ? $_SESSION['can_crud_magang'] : false;

// ========================================
// CEK STATUS ABSENSI HARI INI
// ========================================
$query_check_absen = "SELECT id_logbook, tanggal, jam_absensi, foto_absensi, lokasi_absensi, status_validasi 
                      FROM logbook_harian 
                      WHERE id_mahasiswa = ? AND tanggal = ?";
$stmt_check = mysqli_prepare($conn, $query_check_absen);
if (!$stmt_check) {
    die("Query Error: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt_check, 'is', $id_mahasiswa, $today);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$absen_hari_ini = mysqli_fetch_assoc($result_check);
$sudah_absen = ($absen_hari_ini !== null);
$id_logbook_today = $sudah_absen ? $absen_hari_ini['id_logbook'] : null;

// ========================================
// GET DETAIL KEGIATAN HARI INI
// ========================================
$kegiatan_hari_ini = [];
if ($sudah_absen) {
    $query_kegiatan = "SELECT * FROM detail_kegiatan 
                       WHERE id_logbook = ? 
                       ORDER BY jam_mulai ASC, urutan ASC";
    $stmt_kegiatan = mysqli_prepare($conn, $query_kegiatan);
    mysqli_stmt_bind_param($stmt_kegiatan, 'i', $id_logbook_today);
    mysqli_stmt_execute($stmt_kegiatan);
    $result_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);
    while ($row = mysqli_fetch_assoc($result_kegiatan)) {
        $kegiatan_hari_ini[] = $row;
    }
}

// ========================================
// GET RIWAYAT LOGBOOK
// ========================================
$query_riwayat = "SELECT 
                    lh.id_logbook,
                    lh.tanggal,
                    lh.status_validasi,
                    lh.catatan_dosen,
                    COUNT(dk.id_detail) as jumlah_kegiatan
                  FROM logbook_harian lh
                  LEFT JOIN detail_kegiatan dk ON lh.id_logbook = dk.id_logbook
                  WHERE lh.id_mahasiswa = ?
                  GROUP BY lh.id_logbook
                  ORDER BY lh.tanggal DESC
                  LIMIT 50";
$stmt_riwayat = mysqli_prepare($conn, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
$riwayat_logbook = [];
while ($row = mysqli_fetch_assoc($result_riwayat)) {
    $riwayat_logbook[] = $row;
}

// ========================================
// DETERMINE DEFAULT TAB
// ========================================
$default_tab = 'absensi';
if ($sudah_absen) {
    $default_tab = 'kegiatan';
}
?>

<link rel="stylesheet" href="styles/logbook.css?v=<?= time(); ?>">

<div class="logbook-container">
    <!-- ========================================
         HEADER
    ======================================== -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">📓</div>
            <div>
                <h1 class="header-title">Logbook Magang</h1>
                <p class="header-subtitle">Catat absensi dan kegiatan harian magang Anda</p>
            </div>
        </div>
    </div>

    <!-- ========================================
         NAVIGATION TABS
    ======================================== -->
    <div class="nav-tabs-container">
        <div class="nav-tabs">
            <button class="nav-tab <?= $default_tab === 'absensi' ? 'active' : '' ?>" 
                    onclick="switchTab('absensi')" 
                    id="tab-absensi">
                <i class="fas fa-camera"></i>
                <span>Absensi Hari Ini</span>
            </button>
            <button class="nav-tab <?= $default_tab === 'kegiatan' ? 'active' : '' ?>" 
                    onclick="switchTab('kegiatan')" 
                    id="tab-kegiatan"
                    <?= !$sudah_absen && $can_crud ? 'disabled' : '' ?>>
                <i class="fas fa-edit"></i>
                <span>Kegiatan Hari Ini</span>
            </button>
            <button class="nav-tab <?= $default_tab === 'riwayat' ? 'active' : '' ?>" 
                    onclick="switchTab('riwayat')" 
                    id="tab-riwayat">
                <i class="fas fa-history"></i>
                <span>Riwayat Logbook</span>
            </button>
        </div>
    </div>

    <!-- ========================================
         TAB CONTENT: ABSENSI
    ======================================== -->
    <div class="tab-content <?= $default_tab === 'absensi' ? 'active' : '' ?>" id="content-absensi">
        <div class="card">
            <?php if ($can_crud): ?>
                <?php if (!$sudah_absen): ?>
                    <!-- Belum Absen -->
                    <div class="absensi-empty-state">
                        <div class="camera-placeholder">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3>Ambil Foto Absensi</h3>
                        <p>Lakukan absensi untuk hari ini sebelum mengisi kegiatan</p>
                        <button class="btn btn-primary btn-lg" id="btnOpenCamera">
                            <i class="fas fa-camera"></i>
                            Buka Kamera
                        </button>
                    </div>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span><strong>💡 Catatan:</strong> Absensi hanya dilakukan sekali per hari. Setelah absensi, Anda dapat menambahkan detail kegiatan sepanjang hari.</span>
                    </div>
                <?php else: ?>
                    <!-- Sudah Absen -->
                    <div class="absensi-success-state">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Absensi Berhasil!</h3>
                        <p class="absensi-time">
                            <?= date('l, d F Y', strtotime($absen_hari_ini['tanggal'])) ?> - 
                            <?= date('H:i', strtotime($absen_hari_ini['jam_absensi'])) ?> WIB
                        </p>
                        <p class="absensi-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($absen_hari_ini['lokasi_absensi']) ?>
                        </p>
                        <button class="btn btn-secondary" onclick="switchTab('kegiatan')">
                            Lanjut Isi Kegiatan <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                    <!-- Preview Foto Absensi -->
                    <div class="absensi-photo-preview">
                        <h4>Foto Absensi Hari Ini</h4>
                        <img src="uploads/<?= $absen_hari_ini['foto_absensi'] ?>" 
                             alt="Foto Absensi" 
                             onclick="openImageModal(this.src)">
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Read-only Mode -->
                <div class="readonly-notice">
                    <i class="fas fa-eye"></i>
                    <h3>Mode Read-Only</h3>
                    <p>Magang Anda sudah selesai. Anda hanya dapat melihat riwayat logbook.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================
         TAB CONTENT: KEGIATAN HARI INI
    ======================================== -->
    <div class="tab-content <?= $default_tab === 'kegiatan' ? 'active' : '' ?>" id="content-kegiatan">
        <div class="card">
            <?php if ($sudah_absen): ?>
                <div class="kegiatan-header">
                    <div>
                        <h2>Detail Kegiatan Hari Ini</h2>
                        <p class="kegiatan-date">
                            <i class="fas fa-calendar"></i>
                            <?= date('l, d F Y', strtotime($today)) ?>
                        </p>
                    </div>
                    <?php if ($can_crud && $absen_hari_ini['status_validasi'] === 'pending'): ?>
                        <button class="btn btn-primary" id="btnTambahKegiatan">
                            <i class="fas fa-plus"></i>
                            Tambah Kegiatan
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (empty($kegiatan_hari_ini)): ?>
                    <div class="kegiatan-empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Belum ada kegiatan yang dicatat hari ini</p>
                        <?php if ($can_crud && $absen_hari_ini['status_validasi'] === 'pending'): ?>
                            <button class="btn btn-secondary" onclick="document.getElementById('btnTambahKegiatan').click()">
                                + Tambah Kegiatan Pertama
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="kegiatan-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Kegiatan</th>
                                    <th>Foto</th>
                                    <?php if ($can_crud && $absen_hari_ini['status_validasi'] === 'pending'): ?>
                                        <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kegiatan_hari_ini as $idx => $kegiatan): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td>
                                            <div class="time-range">
                                                <i class="fas fa-clock"></i>
                                                <?= date('H:i', strtotime($kegiatan['jam_mulai'])) ?> - 
                                                <?= date('H:i', strtotime($kegiatan['jam_selesai'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="kegiatan-desc">
                                                <?= nl2br(htmlspecialchars($kegiatan['deskripsi_kegiatan'])) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($kegiatan['foto_kegiatan']): ?>
                                                <img src="uploads/<?= $kegiatan['foto_kegiatan'] ?>" 
                                                     alt="Foto Kegiatan" 
                                                     class="table-photo"
                                                     onclick="openImageModal(this.src)">
                                            <?php else: ?>
                                                <span class="badge badge-gray">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($can_crud && $absen_hari_ini['status_validasi'] === 'pending'): ?>
                                            <td class="text-center">
                                                <button class="btn-icon btn-edit" 
                                                        onclick="editKegiatan(<?= $kegiatan['id_detail'] ?>)"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-icon btn-delete" 
                                                        onclick="deleteKegiatan(<?= $kegiatan['id_detail'] ?>)"
                                                        title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($can_crud && $absen_hari_ini['status_validasi'] === 'pending'): ?>
                    <div class="info-box info-blue">
                        <i class="fas fa-info-circle"></i>
                        <span><strong>ℹ️ Info:</strong> Anda dapat menambah, edit, atau hapus kegiatan sampai dosen validator melakukan validasi.</span>
                    </div>
                <?php elseif ($absen_hari_ini['status_validasi'] === 'disetujui'): ?>
                    <div class="info-box info-green">
                        <i class="fas fa-check-circle"></i>
                        <span><strong>✅ Disetujui:</strong> Logbook hari ini telah disetujui oleh dosen validator.</span>
                    </div>
                <?php elseif ($absen_hari_ini['status_validasi'] === 'ditolak'): ?>
                    <div class="info-box info-red">
                        <i class="fas fa-times-circle"></i>
                        <span><strong>❌ Ditolak:</strong> <?= htmlspecialchars($absen_hari_ini['catatan_dosen'] ?? 'Logbook hari ini ditolak oleh dosen validator.') ?></span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="kegiatan-locked-state">
                    <i class="fas fa-lock"></i>
                    <h3>Absensi Diperlukan</h3>
                    <p>Silakan lakukan absensi terlebih dahulu sebelum mengisi kegiatan</p>
                    <button class="btn btn-primary" onclick="switchTab('absensi')">
                        <i class="fas fa-camera"></i>
                        Absen Sekarang
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================
         TAB CONTENT: RIWAYAT LOGBOOK
    ======================================== -->
    <div class="tab-content <?= $default_tab === 'riwayat' ? 'active' : '' ?>" id="content-riwayat">
        <div class="card">
            <h2 class="card-title">Riwayat Logbook</h2>

            <?php if (empty($riwayat_logbook)): ?>
                <div class="riwayat-empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada riwayat logbook</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="riwayat-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-center">Jumlah Kegiatan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_logbook as $riwayat): ?>
                                <tr>
                                    <td>
                                        <div class="date-display">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('l, d F Y', strtotime($riwayat['tanggal'])) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-blue">
                                            <?= $riwayat['jumlah_kegiatan'] ?> kegiatan
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($riwayat['status_validasi'] === 'pending'): ?>
                                            <span class="status-badge status-pending">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        <?php elseif ($riwayat['status_validasi'] === 'disetujui'): ?>
                                            <span class="status-badge status-disetujui">
                                                <i class="fas fa-check-circle"></i>
                                                Disetujui
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-ditolak">
                                                <i class="fas fa-times-circle"></i>
                                                Ditolak
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick="lihatDetailRiwayat(<?= $riwayat['id_logbook'] ?>)">
                                            <i class="fas fa-eye"></i>
                                            Lihat Detail
                                        </button>
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

<!-- ========================================
     MODAL: CAMERA (ABSENSI)
======================================== -->
<div class="modal-overlay" id="modalCamera">
    <div class="modal-content modal-camera">
        <div class="modal-header">
            <h3>Ambil Foto Absensi</h3>
            <button class="close-modal" onclick="closeModal('modalCamera')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="camera-section" id="cameraSection">
                <div class="camera-wrapper">
                    <video id="video" autoplay playsinline></video>
                    <div class="camera-overlay">
                        <div class="camera-frame"></div>
                    </div>
                    <div class="camera-info" id="liveLocation">
                        <i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...
                    </div>
                </div>
            </div>

            <div class="preview-section" id="previewSection" style="display: none;">
                <div class="captured-photo">
                    <img id="capturedImage" src="" alt="Captured Photo">
                    <div class="photo-overlay" id="photoTimestamp"></div>
                </div>
            </div>

            <canvas id="canvas" style="display: none;"></canvas>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" id="captureBtn">
                <i class="fas fa-camera"></i> Ambil Foto
            </button>
            <button class="btn btn-success" id="saveAbsenBtn" style="display: none;">
                <i class="fas fa-check-circle"></i> Simpan Absensi
            </button>
            <button class="btn btn-danger" id="retakeBtn" style="display: none;">
                <i class="fas fa-redo-alt"></i> Foto Ulang
            </button>
        </div>
    </div>
</div>

<!-- ========================================
     MODAL: TAMBAH/EDIT KEGIATAN
======================================== -->
<div class="modal-overlay" id="modalKegiatan">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalKegiatanTitle">Tambah Kegiatan</h3>
            <button class="close-modal" onclick="closeModal('modalKegiatan')">&times;</button>
        </div>
        <form id="formKegiatan" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" id="editIdDetail" name="id_detail">
                <input type="hidden" name="id_logbook" value="<?= $id_logbook_today ?>">
                
                <div class="form-group">
                    <label>Waktu Kegiatan <span class="required">*</span></label>
                    <div class="time-input-group">
                        <div class="time-input">
                            <label class="time-label">Mulai</label>
                            <input type="time" name="jam_mulai" id="jamMulai" required>
                        </div>
                        <div class="time-separator">—</div>
                        <div class="time-input">
                            <label class="time-label">Selesai</label>
                            <input type="time" name="jam_selesai" id="jamSelesai" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsiKegiatan">Deskripsi Kegiatan <span class="required">*</span></label>
                    <textarea id="deskripsiKegiatan" 
                              name="deskripsi_kegiatan" 
                              rows="5" 
                              placeholder="Jelaskan kegiatan yang dilakukan secara detail..."
                              required></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span> karakter
                    </div>
                </div>

                <div class="form-group">
                    <label for="fotoKegiatan">Foto Kegiatan (Opsional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" 
                               id="fotoKegiatan" 
                               name="foto_kegiatan" 
                               accept="image/*"
                               onchange="previewFotoKegiatan(this)">
                        <label for="fotoKegiatan" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Pilih foto atau ambil dari kamera</span>
                        </label>
                    </div>
                    <div id="previewFotoKegiatan" style="display: none;">
                        <img id="previewFotoKegiatanImg" src="" alt="Preview">
                        <button type="button" class="btn-remove-preview" onclick="removeFotoPreview()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalKegiatan')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Kegiatan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================
     MODAL: DETAIL RIWAYAT
======================================== -->
<div class="modal-overlay" id="modalDetailRiwayat">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div>
                <h3>Detail Logbook</h3>
                <p class="modal-subtitle" id="detailRiwayatTanggal"></p>
            </div>
            <button class="close-modal" onclick="closeModal('modalDetailRiwayat')">&times;</button>
        </div>
        <div class="modal-body" id="detailRiwayatContent">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<!-- ========================================
     MODAL: VIEW IMAGE
======================================== -->
<div class="modal-overlay" id="modalImage">
    <div class="modal-image-content">
        <span class="close-modal" onclick="closeModal('modalImage')">&times;</span>
        <img id="modalImageContent" src="" alt="Full Image">
    </div>
</div>

<script src="scripts/logbook.js?v=<?= time(); ?>"></script>