<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../Koneksi/koneksi.php';
$activeTab = $_SESSION['active_tab'] ?? 'profil';
unset($_SESSION['active_tab']);

cekRole(role: 'Mahasiswa');

// 1. Cek Login
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}
$id_user = $_SESSION['id'];

// 2. Dapatkan ID Mahasiswa
$stmt_mhs = $conn->prepare("SELECT m.id_mahasiswa, u.nama, u.nim, m.status_magang FROM mahasiswa m JOIN users u ON m.id_user = u.id WHERE u.id = ?");
$stmt_mhs->bind_param("i", $id_user);
$stmt_mhs->execute();
$curr_mahasiswa = $stmt_mhs->get_result()->fetch_assoc();
$id_mahasiswa_login = $curr_mahasiswa['id_mahasiswa'];
$status_magang_saya = $curr_mahasiswa['status_magang']; // Status ketua

// 3. Cek Kelompok
$stmt_cek = $conn->prepare("
    SELECT k.id_kelompok, k.nama_kelompok, ak.peran 
    FROM anggota_kelompok ak 
    JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
    WHERE ak.id_mahasiswa = ?
");
$stmt_cek->bind_param("i", $id_mahasiswa_login);
$stmt_cek->execute();
$data_kelompok = $stmt_cek->get_result()->fetch_assoc();

$id_kelompok = $data_kelompok['id_kelompok'] ?? null;
$nama_kelompok = $data_kelompok['nama_kelompok'] ?? '';
$peran_saya = $data_kelompok['peran'] ?? '';

// 4. Hitung Anggota
$jumlah_anggota = 0;
if ($id_kelompok) {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM anggota_kelompok WHERE id_kelompok = ?");
    $stmt_count->bind_param("i", $id_kelompok);
    $stmt_count->execute();
    $jumlah_anggota = $stmt_count->get_result()->fetch_assoc()['total'];
}

// 5. Ambil Daftar Anggota (Updated: Fetch status_magang)
$anggota = [];
if ($id_kelompok) {
    $stmt_ang = $conn->prepare("
        SELECT ak.id_anggota, u.nama, u.nim, m.kontak, ak.peran, m.id_mahasiswa, m.status_magang
        FROM anggota_kelompok ak
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
        JOIN users u ON m.id_user = u.id
        WHERE ak.id_kelompok = ?
        ORDER BY 
            CASE WHEN ak.peran = 'ketua' THEN 0 ELSE 1 END,
            ak.id_anggota ASC 
    ");
    $stmt_ang->bind_param("i", $id_kelompok);
    $stmt_ang->execute();
    $anggota = $stmt_ang->get_result();
}
?>

<link rel="stylesheet" href="styles/kelompok.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Tombol Bubarkan yang lebih Stylish */
    .btn-danger-custom {
        background-color: #fff;
        color: #d32f2f;
        border: 1px solid #d32f2f;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(211, 47, 47, 0.1);
    }

    .btn-danger-custom:hover {
        background-color: #d32f2f;
        color: white;
        box-shadow: 0 4px 8px rgba(211, 47, 47, 0.3);
        transform: translateY(-1px);
    }

    .btn-danger-custom i {
        font-size: 1.1em;
    }

    /* Tombol Disabled (untuk status Magang Aktif) */
    .btn-disabled {
        background-color: #e0e0e0;
        color: #9e9e9e;
        border: none;
        cursor: not-allowed;
        padding: 6px 12px;
        border-radius: 6px;
    }
    
    .badge-status {
        font-size: 0.75rem;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 5px;
    }
    .status-aktif { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
</style>

<div class="kelompok-container">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card-header-wrapper">
        <div class="page-title">
            <h2>Kelompok Magang</h2>
        </div>
        <div class="tab-navigation">
            <button class="tab-btn" onclick="openTab(event, 'tab-profil')" id="defaultOpen">
                <i class="fa fa-file-alt"></i> Profil Kelompok
            </button>
            <button class="tab-btn" onclick="openTab(event, 'tab-anggota')">
                <i class="fa fa-users"></i> Anggota Kelompok
            </button>
        </div>
    </div>

    <div id="tab-profil" class="tab-content">
        <div class="content-box">
            <h3>Profil Kelompok</h3>
            
            <form action="pages/crud_kelompok/handler_kelompok.php" method="POST">
                <input type="hidden" name="action" value="simpan_profil">
                <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
                <input type="hidden" name="id_mahasiswa" value="<?= $id_mahasiswa_login ?>">

                <div class="form-group">
                    <label>Nama Kelompok</label>
                    <input type="text" class="form-control" name="nama_kelompok" 
                           placeholder="Masukkan Nama Kelompok" 
                           value="<?= htmlspecialchars($nama_kelompok) ?>" required />
                    <?php if(!$id_kelompok): ?>
                        <small style="color: #666;">*Anda belum memiliki kelompok. Simpan untuk membuat kelompok baru.</small>
                    <?php endif; ?>
                </div>

                <div class="action-right" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <?php if ($id_kelompok && $peran_saya == 'ketua' && $jumlah_anggota == 1): ?>
                        <button type="button" class="btn-danger-custom" onclick="showModal('modalBubarkan')">
                            <i class="fa fa-exclamation-triangle"></i> Bubarkan Kelompok
                        </button>
                    <?php else: ?>
                        <div></div> <?php endif; ?>

                    <button type="submit" class="btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-anggota" class="tab-content">
        <div class="content-box">
            <div class="box-header">
                <h3>Daftar Anggota</h3>
                <?php if ($id_kelompok && $peran_saya == 'ketua'): ?>
                    <?php if ($jumlah_anggota < 4): ?>
                        <button class="btn-add" onclick="showModal('modalTambah')">
                            <i class="fa fa-plus"></i> Tambah Anggota
                        </button>
                    <?php else: ?>
                        <span style="background-color: #ffebee; color: #c62828; padding: 8px 12px; border-radius: 20px; font-size: 0.85rem; border: 1px solid #ef9a9a;">
                            <i class="fa fa-ban"></i> Kelompok Penuh (Max 4)
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ($id_kelompok): ?>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Status</th>
                        <th>Peran</th>
                        <?php if ($peran_saya == 'ketua'): ?>
                        <th width="15%">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    while($row = $anggota->fetch_assoc()): 
                        $is_magang_aktif = ($row['status_magang'] == 'magang_aktif');
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td>
                            <?php if($is_magang_aktif): ?>
                                <span class="badge-status status-aktif">Magang Aktif</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['peran'] == 'ketua'): ?>
                                <span class="role-ketua">Ketua</span>
                            <?php else: ?>
                                Anggota
                            <?php endif; ?>
                        </td>
                        <?php if ($peran_saya == 'ketua'): ?>
                        <td>
                            <?php if(!$is_magang_aktif): ?>
                                <button class="btn-icon btn-edit" 
                                        onclick="openEditModal('<?= $row['id_anggota'] ?>', '<?= addslashes($row['nama']) ?>', '<?= $row['peran'] ?>', '<?= $row['id_mahasiswa'] ?>')">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-icon btn-disabled" title="Sedang Magang Aktif (Locked)">
                                    <i class="fa fa-lock"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($row['id_mahasiswa'] != $id_mahasiswa_login): ?>
                                <?php if($is_magang_aktif): ?>
                                    <button class="btn-icon btn-disabled" onclick="alert('Tidak dapat menghapus anggota yang sedang berstatus Magang Aktif demi integritas data!')">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                <?php else: ?>
                                    <form action="pages/crud_kelompok/handler_anggota.php" method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                        <input type="hidden" name="action" value="hapus_anggota">
                                        <input type="hidden" name="id_anggota" value="<?= $row['id_anggota'] ?>">
                                        <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
                                        <button type="submit" class="btn-icon btn-delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn-icon btn-delete" disabled style="opacity:0.3">
                                    <i class="fa fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalTambah')">&times;</span>
        <h3>Tambah Anggota</h3>
        <form action="pages/crud_kelompok/handler_anggota.php" method="POST">
            <input type="hidden" name="action" value="tambah_anggota">
            <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
            <div class="form-group">
                <label>Masukkan NIM Mahasiswa</label>
                <input type="text" name="nim" class="form-control" placeholder="Contoh: E41231..." required>
            </div>
            <div class="action-right">
                <button type="submit" class="btn-primary">Tambahkan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalEdit')">&times;</span>
        <h3>Edit Anggota</h3>
        <form method="POST" action="pages/crud_kelompok/handler_anggota.php">
            <input type="hidden" name="action" value="edit_peran">
            <input type="hidden" name="id_anggota" id="edit_id_anggota">
            <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
            <input type="hidden" name="id_mahasiswa_target" id="edit_id_mahasiswa">
            <input type="hidden" name="id_mahasiswa_ketua" value="<?= $id_mahasiswa_login ?>">
            <div class="form-group">
                <label>Nama Anggota</label>
                <input type="text" class="form-control" id="edit_nama_anggota" readonly>
            </div>
            <div class="form-group">
                <label>Peran</label>
                <select name="peran" class="form-control" id="edit_peran_select">
                    <option value="anggota">Anggota</option>
                    <option value="ketua">Ketua</option>
                </select>
            </div>
            <div class="action-right">
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalBubarkan" class="modal">
    <div class="modal-content" style="border-top: 5px solid #d32f2f;">
        <span class="close" onclick="closeModal('modalBubarkan')">&times;</span>
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fa fa-exclamation-circle" style="font-size: 3rem; color: #d32f2f;"></i>
        </div>
        <h3 style="text-align: center; color: #d32f2f;">Bubarkan Kelompok?</h3>
        
        <p style="text-align: center;">
            Anda yakin ingin membubarkan kelompok <strong><?= htmlspecialchars($nama_kelompok) ?></strong>?
        </p>
        <p style="text-align: center; color: #666; font-size: 0.9rem;">
            Tindakan ini bersifat <strong>permanen</strong> dan tidak dapat dibatalkan.
        </p>
        
        <form action="pages/crud_kelompok/handler_kelompok.php" method="POST" style="margin-top: 25px;">
            <input type="hidden" name="action" value="bubarkan_kelompok">
            <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
            <input type="hidden" name="id_mahasiswa" value="<?= $id_mahasiswa_login ?>">
            
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn-secondary" onclick="closeModal('modalBubarkan')" style="flex: 1;">Batal</button>
                <button type="submit" class="btn-danger-custom" style="flex: 1; justify-content: center; background: #d32f2f; color: white;">
                    Ya, Bubarkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab Logic (Sama)
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    if(evt) evt.currentTarget.className += " active";
}

document.addEventListener("DOMContentLoaded", () => {
    let active = "<?= $activeTab ?>";
    if (active === "anggota") {
        openTab(null, 'tab-anggota');
        if(document.querySelectorAll(".tab-btn")[1]) document.querySelectorAll(".tab-btn")[1].classList.add("active");
    } else {
        openTab(null, 'tab-profil');
        if(document.querySelectorAll(".tab-btn")[0]) document.querySelectorAll(".tab-btn")[0].classList.add("active");
    }
});

function showModal(modalId) { document.getElementById(modalId).style.display = "block"; }
function closeModal(modalId) { document.getElementById(modalId).style.display = "none"; }
window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = "none"; } }

function openEditModal(id, nama, peran, id_mhs) {
    document.getElementById('edit_id_anggota').value = id;
    document.getElementById('edit_nama_anggota').value = nama;
    document.getElementById('edit_peran_select').value = peran;
    document.getElementById('edit_id_mahasiswa').value = id_mhs;
    showModal('modalEdit');
}
</script>