<?php
include '../Koneksi/koneksi.php';

// 1. Query untuk Data Pengurus (Selain Mahasiswa)
$query_pengurus = "SELECT * FROM users 
                   WHERE role != 'Mahasiswa' 
                   ORDER BY id ASC";
$result_pengurus = mysqli_query($conn, $query_pengurus);

// 2. Query untuk Data Mahasiswa (Khusus Mahasiswa)
$query_mahasiswa = "SELECT * FROM users 
                    WHERE role = 'Mahasiswa' 
                    ORDER BY id ASC";
$result_mahasiswa = mysqli_query($conn, $query_mahasiswa);

if (!$result_pengurus || !$result_mahasiswa) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manajemen User - SI MAGANG</title>
  <link rel="stylesheet" href="styles/manajemen_User.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <style>
    /* Reset & Basic Style mirip referensi */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        padding: 20px;
    }

    /* Card Container */
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        overflow: hidden;
        max-width: 1000px;
        margin: 0 auto;
    }

    .card-header-title {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        color: #333;
        padding: 20px 0 10px;
    }

    /* Tab Navigation */
    .tab-nav {
        display: flex;
        border-bottom: 2px solid #e0e0e0;
        margin-top: 10px;
    }

    .tab-btn {
        flex: 1;
        padding: 15px;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        color: #6c757d;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }

    .tab-btn:hover {
        background-color: #f8f9fa;
    }

    /* Active Tab Style (Biru seperti gambar) */
    .tab-btn.active {
        color: #4e73df; /* Warna biru */
        border-bottom-color: #4e73df;
    }

    .tab-btn i {
        margin-right: 8px;
    }

    /* Tab Content */
    .tab-content {
        display: none; /* Hidden by default */
        padding: 20px;
        animation: fadeIn 0.5s;
    }
    
    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Section Header (Judul & Tombol Tambah) */
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .content-title {
        font-size: 18px;
        font-weight: bold;
        color: #444;
    }

    /* Buttons */
    .btn {
        border: none;
        border-radius: 4px;
        cursor: pointer;
        padding: 8px 12px;
        font-size: 14px;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-success { background-color: #28a745; } /* Hijau */
    .btn-warning { background-color: #ffc107; color: #fff; } /* Kuning */
    .btn-danger { background-color: #dc3545; } /* Merah */

    .btn:hover { opacity: 0.9; }

    /* Table Styling */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .custom-table th, .custom-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .custom-table th {
        background-color: #f8f9fc;
        color: #555;
        font-weight: bold;
    }

    .custom-table tr:hover {
        background-color: #fafafa;
    }

    .role-badge {
        font-weight: bold;
        color: #4e73df;
    }

    /* Modal Styling (Sederhana) */
    .modal-bg {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    .modal-box {
        background: white;
        padding: 25px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .modal-box h3 { margin-top: 0; margin-bottom: 20px; }
    
    .modal-box input, .modal-box select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box; /* Agar padding tidak menambah lebar */
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-close { background-color: #6c757d; }
    .btn-save { background-color: #4e73df; }

  </style>
</head>

<body>

<div class="card">
  <div class="card-header-title">Manajemen User</div>

  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('pengurus')">
      <i class="fa fa-file-lines"></i> Data Pengurus
    </button>
    <button class="tab-btn" onclick="switchTab('mahasiswa')">
      <i class="fa fa-users"></i> Data Mahasiswa
    </button>
  </div>

  <div id="pengurus" class="tab-content active">
    <div class="content-header">
        <span class="content-title">Daftar Pengurus</span>
        <button class="btn btn-success" onclick="openAddModal('pengurus')">
            <i class="fa fa-plus"></i> Tambah Pengurus
        </button>
    </div>

    <table class="custom-table">
      <thead>
        <tr>
          <th width="5%">No</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th> <th width="15%">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = mysqli_fetch_assoc($result_pengurus)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $row['nama'] ?></td>
          <td><?= $row['email'] ?></td>
          <td><span class="role-badge"><?= $row['role'] ?></span></td>
          <td>
            <button class="btn btn-warning" onclick="openEditModal('<?= $row['id']?>', '<?= $row['nama']?>', '<?= $row['email']?>', '<?= $row['role']?>', '')">
                <i class="fa fa-pen"></i>
            </button>
            <a href="pages/crud_user/delete_pengurus.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus pengurus ini?')" class="btn btn-danger">
                <i class="fa fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div id="mahasiswa" class="tab-content">
    <div class="content-header">
        <span class="content-title">Daftar Mahasiswa</span>
        <button class="btn btn-success" onclick="openAddModal('mahasiswa')">
            <i class="fa fa-plus"></i> Tambah Mahasiswa
        </button>
    </div>

    <table class="custom-table">
      <thead>
        <tr>
          <th width="5%">No</th>
          <th>Nama</th>
          <th>NIM</th> <th>Email</th>
          <th width="15%">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = mysqli_fetch_assoc($result_mahasiswa)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $row['nama'] ?></td>
          <td><?= isset($row['nim']) ? $row['nim'] : '-' ?></td>
          <td><?= $row['email'] ?></td>
          <td>
            <button class="btn btn-warning" onclick="openEditModal('<?= $row['id']?>', '<?= $row['nama']?>', '<?= $row['email']?>', 'Mahasiswa', '<?= isset($row['nim']) ? $row['nim'] : '' ?>')">
                <i class="fa fa-pen"></i>
            </button>
            <a href="pages/crud_user/delete_pengurus.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus mahasiswa ini?')" class="btn btn-danger">
                <i class="fa fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-bg" id="addModal">
  <div class="modal-box">
    <h3 id="modalTitle">Tambah User</h3>
    <form action="pages/crud_user/create_pengurus.php" method="POST">
      
      <input type="text" name="nama" placeholder="Nama Lengkap" required>
      
      <div id="inputNimGroup" style="display:none;">
         <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa (NIM)">
         <input type="hidden" name="role_fixed" value="Mahasiswa">
      </div>

      <input type="email" name="email" placeholder="Email Institusional Polije" required>
      <input type="password" name="password" placeholder="Kata Sandi" required>

      <select name="role" id="inputRoleGroup" required>
        <option value="">Pilih Role</option>
        <option value="Admin">Admin</option>
        <option value="Koordinator Bidang Magang">Koordinator Bidang Magang</option>
        <option value="Dosen Pembimbing">Dosen Pembimbing</option>
      </select>

      <div class="modal-footer">
        <button type="button" class="btn btn-close" onclick="closeModal('addModal')">Tutup</button>
        <button type="submit" class="btn btn-save">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-bg" id="editModal">
  <div class="modal-box">
    <h3>Edit User</h3>
    <form action="pages/crud_user/update_pengurus.php" method="POST">
      <input type="hidden" id="edit_id" name="id">
      
      <label>Nama</label>
      <input type="text" id="edit_nama" name="nama" required>
      
      <div id="editNimGroup" style="display:none;">
        <label>NIM</label>
        <input type="text" id="edit_nim" name="nim">
      </div>

      <label>Email</label>
      <input type="email" id="edit_email" name="email" required>

      <div id="editRoleGroup">
          <label>Role</label>
          <select id="edit_role" name="role">
            <option value="Admin">Admin</option>
            <option value="Koordinator Bidang Magang">Koordinator Bidang Magang</option>
            <option value="Dosen Pembimbing">Dosen Pembimbing</option>
            <!-- <option value="Mahasiswa">Mahasiswa</option>   -->
          </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-close" onclick="closeModal('editModal')">Tutup</button>
        <button type="submit" class="btn btn-save">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function switchTab(tabName) {
    var contents = document.getElementsByClassName("tab-content");
    for (var i = 0; i < contents.length; i++) {
        contents[i].classList.remove("active");
    }
    
    var buttons = document.getElementsByClassName("tab-btn");
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove("active");
    }

    document.getElementById(tabName).classList.add("active");
    
    if(tabName === 'pengurus') {
        document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
    } else {
        document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
    }
}

// Logic Modal Tambah
function openAddModal(type){
  document.getElementById("addModal").style.display = "flex";
  
  if(type === 'mahasiswa'){
    document.getElementById("modalTitle").innerText = "Tambah Mahasiswa";
    document.getElementById("inputNimGroup").style.display = "block"; // Tampilkan NIM
    document.getElementById("inputRoleGroup").style.display = "none"; // Sembunyikan Select Role
    document.getElementById("inputRoleGroup").removeAttribute('required'); // Hapus required role
  } else {
    document.getElementById("modalTitle").innerText = "Tambah Pengurus";
    document.getElementById("inputNimGroup").style.display = "none"; // Sembunyikan NIM
    document.getElementById("inputRoleGroup").style.display = "block"; // Tampilkan Select Role
    document.getElementById("inputRoleGroup").setAttribute('required', 'true');
  }
}

// Logic Modal Edit
function openEditModal(id, nama, email, role, nim){
  document.getElementById("editModal").style.display = "flex";

  document.getElementById("edit_id").value = id;
  document.getElementById("edit_nama").value = nama;
  document.getElementById("edit_email").value = email;
  document.getElementById("edit_role").value = role;
  
  if(role === 'Mahasiswa'){
      document.getElementById("editNimGroup").style.display = "block";
      document.getElementById("edit_nim").value = nim;
      document.getElementById("editRoleGroup").style.display = "none"; 
  } else {
      document.getElementById("editNimGroup").style.display = "none";
      document.getElementById("editRoleGroup").style.display = "block"; 
  }
}

function closeModal(modalId){
  document.getElementById(modalId).style.display = "none";
}

// Tutup modal jika klik di luar box
window.onclick = function(event) {
  if (event.target.classList.contains('modal-bg')) {
    event.target.style.display = "none";
  }
}
</script>

  </body>
</html>