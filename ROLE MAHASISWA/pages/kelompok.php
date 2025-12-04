<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../Koneksi/koneksi.php';
$activeTab = $_SESSION['active_tab'] ?? 'profil';
unset($_SESSION['active_tab']);

cekRole(role:'Mahasiswa');

// 1. Cek Login
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}
$id_user = $_SESSION['id'];

// 2. Dapatkan ID Mahasiswa dari tabel mahasiswa berdasarkan User ID
$stmt_mhs = $conn->prepare("SELECT m.id_mahasiswa, u.nama, u.nim FROM mahasiswa m JOIN users u ON m.id_user = u.id WHERE u.id = ?");

if (!$stmt_mhs) {
    die("Error prepare statement mahasiswa: " . $conn->error);
}

$stmt_mhs->bind_param("i", $id_user);
$stmt_mhs->execute();
$res_mhs = $stmt_mhs->get_result();
$curr_mahasiswa = $res_mhs->fetch_assoc();
$id_mahasiswa_login = $curr_mahasiswa['id_mahasiswa'];

// 3. Cek apakah mahasiswa ini sudah punya kelompok?
$stmt_cek = $conn->prepare("
    SELECT k.id_kelompok, k.nama_kelompok, ak.peran 
    FROM anggota_kelompok ak 
    JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
    WHERE ak.id_mahasiswa = ?
");

if (!$stmt_cek) {
    die("Error prepare statement cek kelompok: " . $conn->error);
}

$stmt_cek->bind_param("i", $id_mahasiswa_login);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$data_kelompok = $result_cek->fetch_assoc();

$id_kelompok = $data_kelompok['id_kelompok'] ?? null;
$nama_kelompok = $data_kelompok['nama_kelompok'] ?? '';
$peran_saya = $data_kelompok['peran'] ?? '';

// 4. Cek jumlah anggota kelompok (untuk tombol bubarkan)
$jumlah_anggota = 0;
if ($id_kelompok) {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM anggota_kelompok WHERE id_kelompok = ?");
    
    if (!$stmt_count) {
        die("Error prepare statement count anggota: " . $conn->error);
    }
    
    $stmt_count->bind_param("i", $id_kelompok);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $jumlah_anggota = $result_count->fetch_assoc()['total'];
}

// 5. Ambil Daftar Anggota (Hanya jika sudah punya kelompok)
$anggota = [];
if ($id_kelompok) {
    $stmt_ang = $conn->prepare("
        SELECT ak.id_anggota, u.nama, u.nim, m.kontak, ak.peran, m.id_mahasiswa
        FROM anggota_kelompok ak
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
        JOIN users u ON m.id_user = u.id
        WHERE ak.id_kelompok = ?
        ORDER BY 
            CASE WHEN ak.peran = 'ketua' THEN 0 ELSE 1 END,  -- Ketua = 0 (prioritas tinggi)
            ak.id_anggota ASC 
    ");
    
    if (!$stmt_ang) {
        die("Error prepare statement: " . $conn->error);
    }
    
    $stmt_ang->bind_param("i", $id_kelompok);
    $stmt_ang->execute();
    $anggota = $stmt_ang->get_result();
}
?>

<link rel="stylesheet" href="styles/kelompok.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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

    <!-- TAB 1: PROFIL KELOMPOK -->
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
                        <small style="color: #666;">*Anda belum memiliki kelompok. Simpan untuk membuat kelompok baru dan Anda otomatis menjadi Ketua.</small>
                    <?php endif; ?>
                </div>

                <div class="action-right">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    
                    <?php if ($id_kelompok && $peran_saya == 'ketua' && $jumlah_anggota == 1): ?>
                        <button type="button" class="btn-delete" onclick="showModal('modalBubarkan')" style="margin-left: 10px;">
                            <i class="fa fa-times-circle"></i> Bubarkan Kelompok
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: ANGGOTA KELOMPOK -->
    <div id="tab-anggota" class="tab-content">
        <div class="content-box">
            
            <div class="box-header">
                <h3>Daftar Anggota</h3>
                <?php if ($id_kelompok && $peran_saya == 'ketua'): ?>
                    <button class="btn-add" onclick="showModal('modalTambah')">
                        <i class="fa fa-plus"></i> Tambah Anggota
                    </button>
                <?php elseif (!$id_kelompok): ?>
                    <span style="color:red; font-size:0.9rem;">Buat kelompok lebih dulu atau minta seorang ketua kelompok memasukkan anda kedalam kelompok mereka.</span>
                <?php endif; ?>
            </div>

            <?php if ($id_kelompok): ?>

            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Kontak</th>
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
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['kontak'] ?? '-') ?></td>
                        <td>
                            <?php if($row['peran'] == 'ketua'): ?>
                                <span class="role-ketua">Ketua</span>
                            <?php else: ?>
                                Anggota
                            <?php endif; ?>
                        </td>
                        <?php if ($peran_saya == 'ketua'): ?>
                        <td>
                            <button class="btn-icon btn-edit" 
                                    onclick="openEditModal('<?= $row['id_anggota'] ?>', '<?= addslashes($row['nama']) ?>', '<?= $row['peran'] ?>', '<?= $row['id_mahasiswa'] ?>')">
                                <i class="fa fa-pencil"></i>
                            </button>
                            
                            <?php if ($row['id_mahasiswa'] != $id_mahasiswa_login): ?>
                            <form action="pages/crud_kelompok/handler_anggota.php" method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                <input type="hidden" name="action" value="hapus_anggota">
                                <input type="hidden" name="id_anggota" value="<?= $row['id_anggota'] ?>">
                                <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
                                <button type="submit" class="btn-icon btn-delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn-icon btn-delete" disabled title="Tidak dapat menghapus diri sendiri">
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

<!-- Modal Tambah Anggota -->
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
                <small>Pastikan mahasiswa tersebut terdaftar dan belum memiliki kelompok.</small>
            </div>
            
            <div class="action-right">
                <button type="submit" class="btn-primary">Tambahkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Peran -->
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
                <small style="color:orange">⚠️ Perhatian: Jika mengubah menjadi Ketua, ketua lama (Anda) akan menjadi anggota.</small>
            </div>
            
            <div class="action-right">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Bubarkan Kelompok -->
<div id="modalBubarkan" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalBubarkan')">&times;</span>
        <h3>⚠️ Bubarkan Kelompok</h3>
        <p>Apakah Anda yakin ingin membubarkan kelompok <strong><?= htmlspecialchars($nama_kelompok) ?></strong>?</p>
        <p style="color:red; font-size:0.9rem;">Tindakan ini tidak dapat dibatalkan!</p>
        
        <form action="pages/crud_kelompok/handler_kelompok.php" method="POST">
            <input type="hidden" name="action" value="bubarkan_kelompok">
            <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
            <input type="hidden" name="id_mahasiswa" value="<?= $id_mahasiswa_login ?>">
            
            <div class="action-right">
                <button type="button" class="btn-secondary" onclick="closeModal('modalBubarkan')">Batal</button>
                <button type="submit" class="btn-delete">Ya, Bubarkan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab Logic
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

// Default Open Tab
document.addEventListener("DOMContentLoaded", () => {
    let active = "<?= $activeTab ?>";
    if (active === "anggota") {
        openTab(null, 'tab-anggota');
        document.querySelectorAll(".tab-btn")[1].classList.add("active");
    } else {
        openTab(null, 'tab-profil');
        document.querySelectorAll(".tab-btn")[0].classList.add("active");
    }
});

// Modal Logic
function showModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Tutup modal jika klik di luar konten
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
}

// Helper untuk Modal Edit (isi data ke form)
function openEditModal(id_anggota, nama, peran, id_mahasiswa) {
    document.getElementById('edit_id_anggota').value = id_anggota;
    document.getElementById('edit_nama_anggota').value = nama;
    document.getElementById('edit_peran_select').value = peran;
    document.getElementById('edit_id_mahasiswa').value = id_mahasiswa;
    showModal('modalEdit');
}
</script>
