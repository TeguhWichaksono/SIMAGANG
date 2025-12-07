<?php
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
  <link rel="stylesheet" href="styles/manajemen_user.css?v=<?= time(); ?>">

</head>
<body>

<div class="user-container">
  
  <div class="page-header">
    <h2><i class="fas fa-users-cog text-primary"></i> Manajemen User</h2>
    
    <div class="tab-navigation">
      <button id="btnPengurus" class="tab-btn active" onclick="showPengurus()">
        <i class="fas fa-user-tie"></i> Pengurus
      </button>
      <button id="btnMahasiswa" class="tab-btn" onclick="showMahasiswa()">
        <i class="fas fa-user-graduate"></i> Mahasiswa
      </button>
    </div>
  </div>

  <div class="content-wrapper">
    
    <div id="pengurusTable">
      <div class="action-bar">
        <button class="btn-add-new" data-bs-toggle="modal" data-bs-target="#modalPengurus">
          <i class="fas fa-plus"></i> Tambah Pengurus
        </button>
      </div>

      <div class="table-responsive">
        <table class="user-table">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th width="25%">Nama</th>
              <th width="25%">Email</th>
              <th width="20%">Role</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no=1; while($row = mysqli_fetch_assoc($query_pengurus)): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <div class="fw-bold"><?= htmlspecialchars($row['nama']) ?></div>
              </td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td>
                <?php 
                  $roleRaw = $row['role'];
                  $badgeClass = 'role-admin'; // default
                  $roleLabel = $roleRaw;

                  if ($roleRaw == 'koordinator_bidang') {
                      $roleLabel = 'Koordinator Bidang';
                      $badgeClass = 'role-korbid';
                  } elseif ($roleRaw == 'dosen_pembimbing') {
                      $roleLabel = 'Dosen Pembimbing';
                      $badgeClass = 'role-dosen';
                  } elseif ($roleRaw == 'Admin') {
                      $badgeClass = 'role-admin';
                  }
                ?>
                <span class="badge-role <?= $badgeClass ?>"><?= htmlspecialchars($roleLabel) ?></span>
              </td>
              <td>
                <div class="action-buttons">
                  <button class="btn-icon btn-edit" onclick="editPengurus(<?= $row['id'] ?>, '<?= addslashes($row['nama']) ?>', '<?= addslashes($row['email']) ?>', '<?= addslashes($row['role']) ?>')">
                    <i class="fa fa-pen"></i>
                  </button>
                  <a href="pages/crud_user/delete_pengurus.php?id=<?= $row['id'] ?>" class="delete-link">
                    <button class="btn-icon btn-delete"><i class="fa fa-trash"></i></button>
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div id="mahasiswaTable" style="display:none;">
      
      <div class="upload-box">
        <div class="upload-info">
          <h5><i class="fas fa-file-excel text-success"></i> Import Data Mahasiswa</h5>
          <p>Format: Kolom A (Nama), Kolom B (NIM). Mendukung .csv, .xlsx</p>
        </div>
        <form action="pages/crud_user/upload_excel_mahasiswa.php" method="POST" enctype="multipart/form-data" id="formUploadExcel" class="upload-form-group">
          <input type="file" name="excel_file" class="file-input-custom" accept=".csv,.xlsx,.xls" required>
          <button type="submit" class="btn-upload">
            <i class="fa fa-upload"></i> Upload
          </button>
        </form>
      </div>

      <div class="action-bar">
        <button class="btn-add-new" data-bs-toggle="modal" data-bs-target="#modalMahasiswa">
          <i class="fas fa-plus"></i> Tambah Mahasiswa
        </button>
      </div>

      <div class="table-responsive">
        <table class="user-table">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th width="20%">NIM</th>
              <th width="30%">Nama</th>
              <th width="30%">Email</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no=1; while($m = mysqli_fetch_assoc($query_mahasiswa)): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><span class="fw-bold text-primary"><?= htmlspecialchars($m['nim']) ?></span></td>
              <td><?= htmlspecialchars($m['nama']) ?></td>
              <td><?= htmlspecialchars($m['email']) ?></td>
              <td>
                <div class="action-buttons">
                  <button class="btn-icon btn-edit" onclick="editMahasiswa(<?= $m['id'] ?>, '<?= addslashes($m['nama']) ?>', '<?= addslashes($m['nim']) ?>', '<?= addslashes($m['email']) ?>')">
                    <i class="fa fa-pen"></i>
                  </button>
                  <a href="pages/crud_user/delete_mahasiswa.php?id=<?= $m['id'] ?>" class="delete-link">
                    <button class="btn-icon btn-delete"><i class="fa fa-trash"></i></button>
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div> </div> <div class="modal fade" id="modalEditPengurus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Data Pengurus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="pages/crud_user/update_pengurus.php" method="POST" id="formEditPengurus">
        <input type="hidden" name="id" id="edit_pengurus_id">
        <input type="hidden" name="is_modal" value="1">
        <div class="modal-body p-4">
          <label class="form-label fw-bold">Nama</label>
          <input type="text" name="nama" id="edit_pengurus_nama" class="form-control mb-3" required>

          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" id="edit_pengurus_email" class="form-control mb-3" required>

          <label class="form-label fw-bold">Password</label>
          <input type="password" name="password" class="form-control mb-1" placeholder="Kosongkan jika tidak ingin mengubah">
          <small class="text-muted d-block mb-3">*Isi hanya jika ingin mengubah password</small>

          <label class="form-label fw-bold">Role</label>
          <select name="role" id="edit_pengurus_role" class="form-select mb-3" required>
            <option value="Admin">Admin</option>
            <option value="koordinator_bidang">Koordinator Bidang</option>
            <option value="dosen_pembimbing">Dosen Pembimbing</option>
          </select>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditMahasiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Data Mahasiswa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="pages/crud_user/update_mahasiswa.php" method="POST" id="formEditMahasiswa">
        <input type="hidden" name="id" id="edit_mahasiswa_id">
        <input type="hidden" name="is_modal" value="1">
        <div class="modal-body p-4">
          <label class="form-label fw-bold">NIM</label>
          <input type="text" name="nim" id="edit_mahasiswa_nim" class="form-control mb-3" required>

          <label class="form-label fw-bold">Nama</label>
          <input type="text" name="nama" id="edit_mahasiswa_nama" class="form-control mb-3" required>

          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" id="edit_mahasiswa_email" class="form-control mb-3" required>

          <label class="form-label fw-bold">Password</label>
          <input type="password" name="password" class="form-control mb-1" placeholder="Kosongkan jika tidak ingin mengubah">
          <small class="text-muted d-block mb-3">*Isi hanya jika ingin mengubah password</small>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPengurus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Tambah Pengurus Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="pages/crud_user/create_pengurus.php" method="POST">
        <div class="modal-body p-4">
          <label class="form-label fw-bold">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control mb-3" placeholder="Contoh: Budi Santoso" required>

          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="email@polije.ac.id" required>

          <label class="form-label fw-bold">Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Masukkan Password" required>

          <label class="form-label fw-bold">Role</label>
          <select name="role" class="form-select mb-3" required>
            <option value="" disabled selected>Pilih Role</option>
            <option value="Admin">Admin</option>
            <option value="koordinator_bidang">Koordinator Bidang</option>
            <option value="dosen_pembimbing">Dosen Pembimbing</option>
          </select>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalMahasiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Tambah Mahasiswa Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="pages/crud_user/create_mahasiswa.php" method="POST">
        <div class="modal-body p-4">
          <label class="form-label fw-bold">NIM</label>
          <input type="text" name="nim" class="form-control mb-3" placeholder="Contoh: E41211001" required>

          <label class="form-label fw-bold">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control mb-3" placeholder="Masukkan Nama" required>

          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="email@student.polije.ac.id" required>

          <label class="form-label fw-bold">Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Masukkan Password" required>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
// Logic Switch Tab & Active State
function showPengurus(){
  document.getElementById('pengurusTable').style.display='block';
  document.getElementById('mahasiswaTable').style.display='none';
  
  // Update tombol active
  document.getElementById('btnPengurus').classList.add('active');
  document.getElementById('btnMahasiswa').classList.remove('active');
}

function showMahasiswa(){
  document.getElementById('pengurusTable').style.display='none';
  document.getElementById('mahasiswaTable').style.display='block';
  
  // Update tombol active
  document.getElementById('btnMahasiswa').classList.add('active');
  document.getElementById('btnPengurus').classList.remove('active');
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