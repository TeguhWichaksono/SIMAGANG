<?php
include "../../../Koneksi/koneksi.php";

$isModal = isset($_POST['is_modal']) && $_POST['is_modal'] == '1';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id' AND role <> 'mahasiswa'");
    $data = mysqli_fetch_assoc($query);
    
    if (!$data) {
        header("Location: ../../index.php?page=manajemen_User&error=not_found");
        exit();
    }
} else {
    if (!$isModal) {
        header("Location: ../../index.php?page=manajemen_User");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = mysqli_query($conn, "UPDATE users SET nama = '$nama', email = '$email', password = '$password', role = '$role' WHERE id = '$id' AND role <> 'mahasiswa'");
    } else {
        $query = mysqli_query($conn, "UPDATE users SET nama = '$nama', email = '$email', role = '$role' WHERE id = '$id' AND role <> 'mahasiswa'");
    }
    
    if ($query) {
        header("Location: ../../index.php?page=manajemen_User&success_update=1");
    } else {
        header("Location: ../../index.php?page=manajemen_User&error=update_failed");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Pengurus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Pengurus</h4>
    </div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
          <small class="text-muted">*Isi hanya jika ingin mengubah password</small>
        </div>

        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="Admin" <?= $data['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="koordinator_bidang" <?= $data['role'] == 'koordinator_bidang' ? 'selected' : '' ?>>Koordinator Bidang</option>
            <option value="dosen_pembimbing" <?= $data['role'] == 'dosen_pembimbing' ? 'selected' : '' ?>>Dosen Pembimbing</option>
          </select>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="../../index.php?page=manajemen_User" class="btn btn-secondary">Batal</a>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>