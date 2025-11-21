<?php
include '../Koneksi/koneksi.php';

$users = mysqli_query($conn, 
  "SELECT * FROM users 
   WHERE role IN ('admin','Koordinator Bidang Magang','Dosen Pembimbing') 
   ORDER BY id ASC"
);
if (!$users) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manajemen User - SI MAGANG</title>

  <link rel="stylesheet" href="styles/styles.css">
  <link rel="stylesheet" href="styles/manajemen_User.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>

<div class="user-container">

  <div class="header-action">
    <button class="add-btn" onclick="openAddModal()">
      <i class="fa fa-plus"></i> Tambah User
    </button>
  </div>

  <table class="user-table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>Aksi</th>
      </tr>
    </thead>

    <tbody>
      <?php $no = 1; while($row = mysqli_fetch_assoc($users)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['role'] ?></td>

        <td>
          <button class="action-btn edit-btn"
            onclick="openEditModal('<?= $row['id']?>','<?= $row['nama']?>','<?= $row['email']?>','<?= $row['role']?>')">
            <i class="fa fa-pen"></i>
          </button>

<a href="pages/crud_user/delete_user.php?id=<?= $row['id'] ?>"
   onclick="return confirm('Yakin ingin menghapus user ini?')">
    <button class="action-btn delete-btn">
        <i class="fa fa-trash"></i>
    </button>
</a>

        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<div class="modal-bg" id="addModal">
  <div class="modal-box">
    <h3>Tambah User</h3>

    <form action="pages/crud_user/create_user.php" method="POST">
      <input type="text" name="nama" placeholder="Nama" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>

      <select name="role" required>
        <option value="">Pilih Role</option>
        <option value="Admin">Admin</option>
        <option value="Koordinator Bidang Magang">Koordinator Bidang Magang</option>
        <option value="Dosen Pembimbing">Dosen Pembimbing</option>
      </select>

      <button class="save-btn">Simpan</button>
    </form>

    <button class="close-btn" onclick="closeAddModal()">Tutup</button>
  </div>
</div>

<div class="modal-bg" id="editModal">
  <div class="modal-box">
    <h3>Edit User</h3>

    <form action="pages/crud_user/update_user.php" method="POST">
      <input type="hidden" id="edit_id" name="id">

      <input type="text" id="edit_nama" name="nama" required>
      <input type="email" id="edit_email" name="email" required>

      <select id="edit_role" name="role" required>
        <option value="Admin">Admin</option>
        <option value="Koordinator Bidang Magang">Koordinator Bidang Magang</option>
        <option value="Dosen Pembimbing">Dosen Pembimbing</option>
      </select>

      <button class="save-btn">Update</button>
    </form>

    <button class="close-btn" onclick="closeEditModal()">Tutup</button>
  </div>
</div>

<script>
function openAddModal(){
  document.getElementById("addModal").style.display = "flex";
}
function closeAddModal(){
  document.getElementById("addModal").style.display = "none";
}

function openEditModal(id, nama, email, role){
  document.getElementById("editModal").style.display = "flex";

  document.getElementById("edit_id").value = id;
  document.getElementById("edit_nama").value = nama;
  document.getElementById("edit_email").value = email;
  document.getElementById("edit_role").value = role;
}

function closeEditModal(){
  document.getElementById("editModal").style.display = "none";
}
</script>

</body>
</html>