<?php
include "../../../Koneksi/koneksi.php";

$isModal = isset($_POST['is_modal']) && $_POST['is_modal'] == '1';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id' AND role = 'mahasiswa'");
    $data = mysqli_fetch_assoc($query);
    
    if (!$data) {
        header("Location: ../../index.php?page=manajemen_User&error=not_found");
        exit();
    }
} else {
    // Redirect jika tidak ada ID
    if (!$isModal) {
        header("Location: ../../index.php?page=manajemen_User");
        exit();
    }
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek apakah password diubah
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = mysqli_query($conn, "UPDATE users SET nama = '$nama', nim = '$nim', email = '$email', password = '$password' WHERE id = '$id' AND role = 'mahasiswa'");
    } else {
        $query = mysqli_query($conn, "UPDATE users SET nama = '$nama', nim = '$nim', email = '$email' WHERE id = '$id' AND role = 'mahasiswa'");
    }
    
    if ($query) {
        header("Location: ../../index.php?page=manajemen_User&success_update=1&tab=mahasiswa");
    } else {
        header("Location: ../../index.php?page=manajemen_User&error=update_failed&tab=mahasiswa");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Mahasiswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Mahasiswa</h4>
    </div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">NIM</label>
          <input type="text" name="nim" class="form-control" value="<?= htmlspecialchars($data['nim']) ?>" required>
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