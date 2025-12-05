<?php
<<<<<<< HEAD
// manajemen_User.php
// Halaman utama Manajemen User (tanpa header dan sidebar)
?>

<link rel="stylesheet" href="styles/manajemen_User.css" />

<div class="user-container">
  <div class="header-action">
    <button class="add-btn">
      <i class="fas fa-plus"></i> Tambah User
    </button>
  </div>

  <table class="user-table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama User</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Septiya Qorrata Ayun</td>
        <td>septiya@mail.com</td>
        <td>Admin</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Diva Hafizdatul Albin</td>
        <td>diva@mail.com</td>
        <td>Korbid</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>3</td>
        <td>Khoiril Nisrullah</td>
        <td>aril@mail.com</td>
        <td>Dosen Pembimbing</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>4</td>
        <td>Saskia Aurelia</td>
        <td>saskia@mail.com</td>
        <td>Dosen Pembimbing</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>5</td>
        <td>Teguh Wichaksono</td>
        <td>teguh@mail.com</td>
        <td>Mahasiswa</td>
        <td>Nonaktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
=======
include '../Koneksi/koneksi.php';

$query_pengurus = mysqli_query($conn, "SELECT * FROM users WHERE role <> 'mahasiswa' ORDER BY id ASC");

$query_mahasiswa = mysqli_query($conn, "SELECT * FROM users WHERE role = 'mahasiswa' ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manajemen User</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="stylesheet" href="styles/manajemen_user.css?v=3">

</head>
<body>

<div class="user-container">
  <h2 style="margin-bottom:15px;">Manajemen User</h2>

  <div class="header-action" style="gap:8px;">
    <button id="btnPengurus" class="add-btn tab-btn" onclick="showPengurus()">Data Pengurus</button>
    <button id="btnMahasiswa" class="add-btn tab-btn" onclick="showMahasiswa()">Data Mahasiswa</button>
  </div>

  <!-- ===================== PENGURUS ========================= -->
  <div id="pengurusTable">
    <div class="header-action">
      <button class="add-btn" data-bs-toggle="modal" data-bs-target="#modalPengurus">+ Tambah Pengurus</button>
    </div>

    <table class="user-table">
      <thead>
        <tr><th>No</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($query_pengurus)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <?php 
              // Format tampilan role
              $roleDisplay = $row['role'];
              if ($row['role'] == 'koordinator_bidang') {
                  $roleDisplay = 'Koordinator Bidang';
              } elseif ($row['role'] == 'dosen_pembimbing') {
                  $roleDisplay = 'Dosen Pembimbing';
              }
              echo htmlspecialchars($roleDisplay);
            ?>
          </td>
          <td>
            <button class="action-btn edit-btn" onclick="editPengurus(<?= $row['id'] ?>, '<?= addslashes($row['nama']) ?>', '<?= addslashes($row['email']) ?>', '<?= addslashes($row['role']) ?>')">
              <i class="fa fa-pen"></i>
            </button>
            <a href="pages/crud_user/delete_pengurus.php?id=<?= $row['id'] ?>" class="delete-link">
              <button class="action-btn delete-btn"><i class="fa fa-trash"></i></button>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- ===================== MAHASISWA ========================= -->
  <div id="mahasiswaTable" style="display:none;">
    <div class="header-action">
      <button class="add-btn" data-bs-toggle="modal" data-bs-target="#modalMahasiswa">+ Tambah Mahasiswa</button>
    </div>

    <!-- Upload Excel Section -->
    <div class="upload-container">
      <form action="pages/crud_user/upload_excel_mahasiswa.php" method="POST" enctype="multipart/form-data" id="formUploadExcel">
        <div class="upload-row">
          <input type="file" name="excel_file" class="file-input" accept=".csv,.xlsx,.xls" required>
          <button type="submit" class="upload-btn">
            <i class="fa fa-upload"></i> Upload Excel
          </button>
        </div>
        <small class="upload-note small-note">
          Format: <strong>Kolom A = Nama</strong>, <strong>Kolom B = NIM</strong> | Support sheets (File .csv)
        </small>
      </form>
    </div>

    <table class="user-table">
      <thead>
        <tr><th>No</th><th>NIM</th><th>Nama</th><th>Email</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php $no=1; while($m = mysqli_fetch_assoc($query_mahasiswa)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($m['nim']) ?></td>
          <td><?= htmlspecialchars($m['nama']) ?></td>
          <td><?= htmlspecialchars($m['email']) ?></td>
          <td>
            <button class="action-btn edit-btn" onclick="editMahasiswa(<?= $m['id'] ?>, '<?= addslashes($m['nama']) ?>', '<?= addslashes($m['nim']) ?>', '<?= addslashes($m['email']) ?>')">
              <i class="fa fa-pen"></i>
            </button>
            <a href="pages/crud_user/delete_mahasiswa.php?id=<?= $m['id'] ?>" class="delete-link">
              <button class="action-btn delete-btn"><i class="fa fa-trash"></i></button>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- =================== MODAL EDIT PENGURUS ==================== -->
<div class="modal fade" id="modalEditPengurus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Pengurus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="pages/crud_user/update_pengurus.php" method="POST" id="formEditPengurus">
        <input type="hidden" name="id" id="edit_pengurus_id">
        <input type="hidden" name="is_modal" value="1">
        <div class="modal-body">
          <label class="form-label">Nama</label>
          <input type="text" name="nama" id="edit_pengurus_nama" class="form-control mb-3" required>

          <label class="form-label">Email</label>
          <input type="email" name="email" id="edit_pengurus_email" class="form-control mb-3" required>

          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Kosongkan jika tidak ingin mengubah">
          <small class="text-muted">*Isi hanya jika ingin mengubah password</small>

          <label class="form-label">Role</label>
          <select name="role" id="edit_pengurus_role" class="form-select mb-3" required>
            <option value="Admin">Admin</option>
            <option value="koordinator_bidang">Koordinator Bidang</option>
            <option value="dosen_pembimbing">Dosen Pembimbing</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =================== MODAL EDIT MAHASISWA ==================== -->
<div class="modal fade" id="modalEditMahasiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Mahasiswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="pages/crud_user/update_mahasiswa.php" method="POST" id="formEditMahasiswa">
        <input type="hidden" name="id" id="edit_mahasiswa_id">
        <input type="hidden" name="is_modal" value="1">
        <div class="modal-body">
          <label class="form-label">NIM</label>
          <input type="text" name="nim" id="edit_mahasiswa_nim" class="form-control mb-3" required>

          <label class="form-label">Nama</label>
          <input type="text" name="nama" id="edit_mahasiswa_nama" class="form-control mb-3" required>

          <label class="form-label">Email</label>
          <input type="email" name="email" id="edit_mahasiswa_email" class="form-control mb-3" required>

          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Kosongkan jika tidak ingin mengubah">
          <small class="text-muted">*Isi hanya jika ingin mengubah password</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =================== MODAL TAMBAH PENGURUS ==================== -->
<div class="modal fade" id="modalPengurus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Pengurus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="pages/crud_user/create_pengurus.php" method="POST">
        <div class="modal-body">
          
          <label class="form-label">Nama</label>
          <input type="text" name="nama" class="form-control mb-3" placeholder="Masukkan Nama" required>

          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="Masukkan Email" required>

          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Masukkan Password" required>

          <label class="form-label">Role</label>
          <select name="role" class="form-select mb-3" required>
            <option value="" disabled selected>Pilih Role</option>
            <option value="Admin">Admin</option>
            <option value="koordinator_bidang">Koordinator Bidang</option>
            <option value="dosen_pembimbing">Dosen Pembimbing</option>
          </select>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- =================== MODAL TAMBAH MAHASISWA ==================== -->
<div class="modal fade" id="modalMahasiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Mahasiswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="pages/crud_user/create_mahasiswa.php" method="POST">
        <div class="modal-body">
          
          <label class="form-label">NIM</label>
          <input type="text" name="nim" class="form-control mb-3" placeholder="Masukkan NIM" required>

          <label class="form-label">Nama</label>
          <input type="text" name="nama" class="form-control mb-3" placeholder="Masukkan Nama" required>

          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="Masukkan Email" required>

          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Masukkan Password" required>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>


<script>
function showPengurus(){
  document.getElementById('pengurusTable').style.display='block';
  document.getElementById('mahasiswaTable').style.display='none';
}
function showMahasiswa(){
  document.getElementById('pengurusTable').style.display='none';
  document.getElementById('mahasiswaTable').style.display='block';
}

// Cek parameter tab dari URL
const urlParams = new URLSearchParams(window.location.search);
const activeTab = urlParams.get('tab');

if (activeTab === 'mahasiswa') {
    showMahasiswa();
} else {
    showPengurus();
}

// Function untuk edit pengurus
function editPengurus(id, nama, email, role) {
    document.getElementById('edit_pengurus_id').value = id;
    document.getElementById('edit_pengurus_nama').value = nama;
    document.getElementById('edit_pengurus_email').value = email;
    document.getElementById('edit_pengurus_role').value = role;
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditPengurus'));
    modal.show();
}

// Function untuk edit mahasiswa
function editMahasiswa(id, nama, nim, email) {
    document.getElementById('edit_mahasiswa_id').value = id;
    document.getElementById('edit_mahasiswa_nim').value = nim;
    document.getElementById('edit_mahasiswa_nama').value = nama;
    document.getElementById('edit_mahasiswa_email').value = email;
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditMahasiswa'));
    modal.show();
}

// Validasi upload Excel
document.getElementById('formUploadExcel')?.addEventListener('submit', function(e) {
    const fileInput = this.querySelector('input[type="file"]');
    if (!fileInput.files.length) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Pilih File!',
            text: 'Silakan pilih file Excel/CSV terlebih dahulu',
        });
        return;
    }
    
    const fileName = fileInput.files[0].name;
    const fileExt = fileName.split('.').pop().toLowerCase();
    
    if (!['csv', 'xlsx', 'xls'].includes(fileExt)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Format Salah!',
            text: 'Hanya file .csv, .xlsx, atau .xls yang diperbolehkan',
        });
        return;
    }
});

// Konfirmasi hapus dengan SweetAlert
document.querySelectorAll('.delete-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.href;
        
        Swal.fire({
            title: 'Hapus Data?',
            text: 'Apakah Anda yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e63946',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});

// Notifikasi
<?php if (isset($_GET['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Akun baru berhasil ditambahkan!',
    showConfirmButton: false,
    timer: 1500
});
<?php endif; ?>

<?php if (isset($_GET['success_upload'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Upload Berhasil!',
    html: 'Berhasil: <strong><?= $_GET['success_upload'] ?></strong> data<br>Gagal: <strong><?= $_GET['failed_upload'] ?? 0 ?></strong> data (duplikat)',
    confirmButtonText: 'OK'
});
<?php endif; ?>

<?php if (isset($_GET['success_delete'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil Dihapus!',
    text: 'Data berhasil dihapus dari sistem',
    showConfirmButton: false,
    timer: 1500
});
<?php endif; ?>

<?php if (isset($_GET['success_update'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil Diupdate!',
    text: 'Data berhasil diperbarui',
    showConfirmButton: false,
    timer: 1500
});
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<?php 
    $errorMsg = 'Terjadi kesalahan!';
    switch($_GET['error']) {
        case 'invalid_file':
            $errorMsg = 'Format file tidak valid! Gunakan .csv, .xlsx, atau .xls';
            break;
        case 'upload_failed':
            $errorMsg = 'Upload file gagal!';
            break;
        case 'read_failed':
            $errorMsg = 'Gagal membaca file Excel!';
            break;
        case 'no_data':
            $errorMsg = 'Tidak ada data yang berhasil diimport!';
            break;
        case 'duplicate_nim':
            $errorMsg = 'NIM sudah terdaftar!';
            break;
        case 'library_missing':
            $errorMsg = 'Library PhpSpreadsheet belum terinstall! Jalankan: composer require phpoffice/phpspreadsheet';
            break;
    }
?>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= $errorMsg ?>',
    confirmButtonText: 'OK'
});
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
>>>>>>> origin/arilmun
