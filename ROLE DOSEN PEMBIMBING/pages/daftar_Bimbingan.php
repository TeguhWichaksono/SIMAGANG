<?php
// daftar_Bimbingan.php
// Daftar Mahasiswa Bimbingan untuk Dosen Pembimbing

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
// QUERY DAFTAR MAHASISWA BIMBINGAN
// ========================================
$query_mahasiswa = "
    SELECT 
        m.id_mahasiswa,
        u.id AS id_user,
        u.nama AS nama_mahasiswa,
        u.email,
        u.foto_profil,
        u.nim,
        m.prodi,
        m.kontak,
        m.angkatan,
        m.status_magang,
        k.id_kelompok,
        k.nama_kelompok,
        mp.nama_mitra,
        mp.alamat AS alamat_mitra,
        mp.bidang AS bidang_mitra,
        ak.peran AS peran_kelompok,
        -- Count logbook
        (SELECT COUNT(*) FROM logbook_harian lh WHERE lh.id_mahasiswa = m.id_mahasiswa) AS total_logbook,
        (SELECT COUNT(*) FROM logbook_harian lh WHERE lh.id_mahasiswa = m.id_mahasiswa AND lh.status_validasi = 'pending') AS logbook_pending,
        (SELECT COUNT(*) FROM logbook_harian lh WHERE lh.id_mahasiswa = m.id_mahasiswa AND lh.status_validasi = 'disetujui') AS logbook_disetujui,
        (SELECT COUNT(*) FROM logbook_harian lh WHERE lh.id_mahasiswa = m.id_mahasiswa AND lh.status_validasi = 'ditolak') AS logbook_ditolak
    FROM mahasiswa m
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN pengajuan_magang pm ON k.id_kelompok = pm.id_kelompok AND pm.status_pengajuan = 'diterima'
    LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    WHERE k.id_dosen_pembimbing = ?
    ORDER BY k.nama_kelompok ASC, u.nim ASC
";

$stmt_mahasiswa = mysqli_prepare($conn, $query_mahasiswa);

if (!$stmt_mahasiswa) {
    die("Error Query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_mahasiswa, 'i', $id_dosen);
mysqli_stmt_execute($stmt_mahasiswa);
$result_mahasiswa = mysqli_stmt_get_result($stmt_mahasiswa);

$mahasiswa_list = [];
$kelompok_list = [];
$mitra_list = [];

while ($row = mysqli_fetch_assoc($result_mahasiswa)) {
    $mahasiswa_list[] = $row;
    
    // Collect unique kelompok
    if ($row['nama_kelompok'] && !in_array($row['nama_kelompok'], $kelompok_list)) {
        $kelompok_list[] = $row['nama_kelompok'];
    }
    
    // Collect unique mitra
    if ($row['nama_mitra'] && !in_array($row['nama_mitra'], $mitra_list)) {
        $mitra_list[] = $row['nama_mitra'];
    }
}

// Stats
$total_mahasiswa = count($mahasiswa_list);
$total_kelompok = count($kelompok_list);
$total_magang_aktif = count(array_filter($mahasiswa_list, fn($m) => $m['status_magang'] === 'magang_aktif'));
$total_selesai = count(array_filter($mahasiswa_list, fn($m) => $m['status_magang'] === 'selesai'));
?>

<link rel="stylesheet" href="styles/daftar_Bimbingan.css?v=<?= time(); ?>">

<div class="bimbingan-container">
    <!-- ========================================
         PAGE HEADER
    ======================================== -->
    <!-- <div class="page-header">
        <div class="header-content">
            <div class="header-icon">👥</div>
            <div>
                <h1 class="header-title">Daftar Mahasiswa Bimbingan</h1>
                <p class="header-subtitle">Kelola dan pantau mahasiswa yang Anda bimbing</p>
            </div>
        </div>
    </div> -->

    <!-- ========================================
         MAIN CARD
    ======================================== -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Daftar Mahasiswa</h2>
                <p class="card-subtitle">
                    <i class="fas fa-info-circle"></i>
                    Data mahasiswa yang sedang Anda bimbing
                </p>
            </div>
            <button class="btn-export" onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i>
                Export Excel
            </button>
        </div>

        <!-- ========================================
             FILTER BAR
        ======================================== -->
        <div class="filters-bar">
            <div class="filter-group">
                <label>
                    <i class="fas fa-search"></i>
                    Cari Mahasiswa
                </label>
                <input type="text" 
                       id="searchMahasiswa" 
                       placeholder="Nama Mahasiswa"
                       onkeyup="applyFilters()">
            </div>

            <div class="filter-group">
                <label>Kelompok</label>
                <select id="filterKelompok" onchange="applyFilters()">
                    <option value="">Semua Kelompok</option>
                    <?php foreach ($kelompok_list as $kelompok): ?>
                        <option value="<?= htmlspecialchars($kelompok) ?>"><?= htmlspecialchars($kelompok) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- <div class="filter-group">
                <label>Status Magang</label>
                <select id="filterStatus" onchange="applyFilters()">
                    <option value="">Semua Status</option>
                    <option value="pra-magang">Pra-Magang</option>
                    <option value="magang_aktif">Magang Aktif</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div> -->

            <div class="filter-group">
                <label>Tempat Magang</label>
                <select id="filterMitra" onchange="applyFilters()">
                    <option value="">Semua Tempat</option>
                    <?php foreach ($mitra_list as $mitra): ?>
                        <option value="<?= htmlspecialchars($mitra) ?>"><?= htmlspecialchars($mitra) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn-reset" onclick="resetFilters()">
                <i class="fas fa-redo"></i>
                Reset
            </button>
        </div>

        <!-- ========================================
             TABLE
        ======================================== -->
        <?php if (empty($mahasiswa_list)): ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <h3>Belum Ada Mahasiswa Bimbingan</h3>
                <p>Anda belum memiliki mahasiswa bimbingan saat ini</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="tableMahasiswa">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mahasiswa</th>
                            <th>Kelompok</th>
                            <th>Tempat Magang</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Progress Logbook</th>
                            <th class="text-center">Kontak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa_list as $index => $mhs): ?>
                            <tr data-kelompok="<?= htmlspecialchars($mhs['nama_kelompok'] ?? '') ?>"
                                data-status="<?= $mhs['status_magang'] ?>"
                                data-mitra="<?= htmlspecialchars($mhs['nama_mitra'] ?? '') ?>"
                                data-nama="<?= strtolower($mhs['nama_mahasiswa']) ?>"
                                data-nim="<?= $mhs['nim'] ?>">
                                
                                <td class="text-center"><?= $index + 1 ?></td>
                                
                                <!-- Mahasiswa Info -->
                                <td>
                                    <div class="mahasiswa-info">
                                        <div class="mahasiswa-avatar-wrapper">
                                            <?php if ($mhs['foto_profil']): ?>
                                                <img src="../ROLE Mahasiswa/uploads/<?= htmlspecialchars($mhs['foto_profil']) ?>" 
                                                     alt="<?= htmlspecialchars($mhs['nama_mahasiswa']) ?>"
                                                     class="mahasiswa-avatar-img">
                                            <?php else: ?>
                                                <div class="mahasiswa-avatar-placeholder">
                                                    <?= strtoupper(substr($mhs['nama_mahasiswa'], 0, 2)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($mhs['peran_kelompok'] === 'ketua'): ?>
                                                <div class="badge-ketua" title="Ketua Kelompok">
                                                    <i class="fas fa-crown"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mahasiswa-details">
                                            <h4><?= htmlspecialchars($mhs['nama_mahasiswa']) ?></h4>
                                            <p><?= htmlspecialchars($mhs['nim']) ?></p>
                                            <small><?= htmlspecialchars($mhs['prodi']) ?></small>
                                        </div>
                                    </div>
                                    <?php if ($mhs['status_magang'] === 'magang_aktif'): ?>
                                        <a href="actions/set_selesai.php?id=<?= $mhs['id_mahasiswa'] ?>" 
                                        class="btn-action btn-finish" 
                                        title="Nyatakan Selesai Magang"
                                        onclick="return confirm('Apakah Anda yakin ingin menyatakan mahasiswa ini TELAH SELESAI magang? Status tidak dapat dikembalikan.')">
                                            <i class="fas fa-check-double"></i>
                                            Selesai
                                        </a>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Kelompok -->
                                <td>
                                    <div class="kelompok-badge">
                                        <i class="fas fa-users"></i>
                                        <?= htmlspecialchars($mhs['nama_kelompok'] ?? '-') ?>
                                    </div>
                                </td>
                                
                                <!-- Tempat Magang -->
                                <td>
                                    <?php if ($mhs['nama_mitra']): ?>
                                        <div class="mitra-info">
                                            <strong><?= htmlspecialchars($mhs['nama_mitra']) ?></strong>
                                            <small><?= htmlspecialchars($mhs['bidang_mitra'] ?? '') ?></small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ada</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Status Magang -->
                                <td class="text-center">
                                    <?php
                                    $status_class = [
                                        'pra-magang' => 'warning',
                                        'magang_aktif' => 'success',
                                        'selesai' => 'info'
                                    ];
                                    $status_text = [
                                        'pra-magang' => 'Pra-Magang',
                                        'magang_aktif' => 'Magang Aktif',
                                        'selesai' => 'Selesai'
                                    ];
                                    $status_icon = [
                                        'pra-magang' => 'fa-hourglass-start',
                                        'magang_aktif' => 'fa-briefcase',
                                        'selesai' => 'fa-check-circle'
                                    ];
                                    ?>
                                    <span class="status-badge <?= $status_class[$mhs['status_magang']] ?>">
                                        <i class="fas <?= $status_icon[$mhs['status_magang']] ?>"></i>
                                        <?= $status_text[$mhs['status_magang']] ?>
                                    </span>
                                </td>
                                
                                <!-- Progress Logbook -->
                                <td>
                                    <div class="progress-logbook">
                                        <div class="progress-item">
                                            <i class="fas fa-clock text-warning"></i>
                                            <span><?= $mhs['logbook_pending'] ?> Pending</span>
                                        </div>
                                        <div class="progress-item">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span><?= $mhs['logbook_disetujui'] ?> Disetujui</span>
                                        </div>
                                        <div class="progress-item">
                                            <i class="fas fa-times-circle text-danger"></i>
                                            <span><?= $mhs['logbook_ditolak'] ?> Ditolak</span>
                                        </div>
                                        <div class="progress-summary">
                                            Total: <strong><?= $mhs['total_logbook'] ?> logbook</strong>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Kontak -->
                                <td class="text-center">
                                    <div class="contact-buttons">
                                        <?php if ($mhs['kontak']): ?>
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $mhs['kontak']) ?>" 
                                               target="_blank"
                                               class="btn-contact whatsapp"
                                               title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($mhs['email']): ?>
                                            <a href="mailto:<?= htmlspecialchars($mhs['email']) ?>"
                                               class="btn-contact email"
                                               title="Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <!-- Aksi -->
                                <td class="text-center">
                                    <button class="btn-action btn-detail" 
                                            onclick="lihatDetailMahasiswa(<?= $mhs['id_mahasiswa'] ?>)">
                                        <i class="fas fa-eye"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="pagination-info">
                <p>Menampilkan <strong id="showingCount"><?= count($mahasiswa_list) ?></strong> dari <strong><?= $total_mahasiswa ?></strong> mahasiswa</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================
     MODAL: DETAIL MAHASISWA
======================================== -->
<div class="modal-overlay" id="modalDetailMahasiswa">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div>
                <h3>Detail Mahasiswa</h3>
                <p class="modal-subtitle" id="detailMahasiswaSubtitle"></p>
            </div>
            <button class="close-modal" onclick="closeModal('modalDetailMahasiswa')">&times;</button>
        </div>
        <div class="modal-body" id="detailMahasiswaContent">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Memuat data...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modalDetailMahasiswa')">
                Tutup
            </button>
        </div>
    </div>
</div>

<script src="scripts/daftar_Bimbingan.js?v=<?= time(); ?>"></script>