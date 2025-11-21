<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../Koneksi/koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}
$id_user = $_SESSION['id'];

// 2. Dapatkan ID Mahasiswa dari tabel mahasiswa berdasarkan User ID
$stmt_mhs = $conn->prepare("SELECT id_mahasiswa, nama, nim FROM mahasiswa JOIN users ON mahasiswa.id_user = users.id WHERE users.id = ?");
$stmt_mhs->bind_param("i", $id_user);
$stmt_mhs->execute();
$res_mhs = $stmt_mhs->get_result();
$curr_mahasiswa = $res_mhs->fetch_assoc();
$id_mahasiswa_login = $curr_mahasiswa['id_mahasiswa'];

// 3. Cek apakah mahasiswa ini sudah punya kelompok?
// Kita join dari anggota_kelompok ke kelompok
$stmt_cek = $conn->prepare("
    SELECT k.id_kelompok, k.nama_kelompok, ak.peran 
    FROM anggota_kelompok ak 
    JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
    WHERE ak.id_mahasiswa = ?
");
$stmt_cek->bind_param("i", $id_mahasiswa_login);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$data_kelompok = $result_cek->fetch_assoc();

$id_kelompok = $data_kelompok['id_kelompok'] ?? null;
$nama_kelompok = $data_kelompok['nama_kelompok'] ?? '';
$peran_saya = $data_kelompok['peran'] ?? '';

// 4. Ambil Daftar Anggota (Hanya jika sudah punya kelompok)
$anggota = [];
if ($id_kelompok) {
    $stmt_ang = $conn->prepare("
        SELECT ak.id_anggota, u.nama, u.nim, m.kontak, ak.peran, m.id_mahasiswa
        FROM anggota_kelompok ak
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
        JOIN users u ON m.id_user = u.id
        WHERE ak.id_kelompok = ?
        ORDER BY ak.peran DESC, u.nama ASC 
    "); 
    // Order by peran DESC asumsi 'ketua' > 'anggota' secara alphabet, atau sesuaikan enum
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
                </div>
            </form>
        </div>
    </div>

    <div id="tab-anggota" class="tab-content">
        <div class="content-box">
            
            <div class="box-header">
                <h3>Daftar Anggota</h3>
                <?php if ($id_kelompok): ?>
                    <button class="btn-add" onclick="showModal('modalAdd')">
                        <i class="fa fa-plus"></i> Tambah Anggota
                    </button>
                <?php else: ?>
                    <span style="color:red; font-size:0.9rem;">Buat kelompok di tab Profil terlebih dahulu.</span>
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
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    while($row = $anggota->fetch_assoc()): 
                    ?>
                    <tr>\
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['kontak']) ?></td>
                        <td>
                            <?php if($row['peran'] == 'ketua'): ?>
                                <span class="role-ketua">Ketua</span>
                            <?php else: ?>
                                Anggota
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-icon btn-edit" 
                                    onclick="openEditModal('<?= $row['id_anggota'] ?>', '<?= $row['nama'] ?>', '<?= $row['peran'] ?>')">
                                <i class="fa fa-pencil"></i>
                            </button>
                            
                            <form action="pages/crud_kelompok/handler_kelompok.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                <input type="hidden" name="action" value="hapus_anggota">
                                <input type="hidden" name="id_anggota" value="<?= $row['id_anggota'] ?>">
                                <button type="submit" class="btn-icon btn-delete"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalAdd" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalAdd')">&times;</span>
        <h3>Tambah Anggota</h3>
        <form action="pages/crud_kelompok/handler_kelompok.php-" method="POST">
            <input type="hidden" name="action" value="tambah_anggota">
            <input type="hidden" name="id_kelompok" value="<?= $id_kelompok ?>">
            
            <div class="form-group">
                <label>Masukkan NIM Mahasiswa</label>
                <input type="text" name="nim_anggota" class="form-control" placeholder="Contoh: E41231..." required>
                <small>Pastikan mahasiswa tersebut terdaftar dan belum memiliki kelompok.</small>
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
        <form action="pages/crud_kelompok/handler_kelompok.php" method="POST">
            <input type="hidden" name="action" value="edit_peran">
            <input type="hidden" name="id_anggota" id="edit_id_anggota">
            
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
                <small style="color:orange">Perhatian: Jika mengubah menjadi Ketua, ketua lama akan menjadi anggota.</small>
            </div>
            
            <div class="action-right">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
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
// Default Open
document.getElementById("defaultOpen").click();

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
function openEditModal(id, nama, peran) {
    document.getElementById('edit_id_anggota').value = id;
    document.getElementById('edit_nama_anggota').value = nama;
    document.getElementById('edit_peran_select').value = peran;
    showModal('modalEdit');
}
</script> 