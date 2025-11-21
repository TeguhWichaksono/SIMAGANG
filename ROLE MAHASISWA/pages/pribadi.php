<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../Koneksi/koneksi.php';

// Cegah akses tanpa login
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$id_user = $_SESSION['id'];

/* =========================================
   1. AMBIL DATA USERS (nama, email, nim)
========================================= */
$query_user = "SELECT nama, email, nim FROM users WHERE id = ?";
$stmt1 = mysqli_prepare($conn, $query_user);

if (!$stmt1) {
    die("Prepare gagal (users): " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt1, 'i', $id_user);
mysqli_stmt_execute($stmt1);
$res1 = mysqli_stmt_get_result($stmt1);
$user = mysqli_fetch_assoc($res1);

$nama  = htmlspecialchars($user['nama'] ?? '');
$email = htmlspecialchars($user['email'] ?? '');
$nim   = htmlspecialchars($user['nim'] ?? '');

mysqli_stmt_close($stmt1);


/* =========================================
   2. AMBIL DATA MAHASISWA (prodi, angkatan, kontak)
========================================= */
$query_mhs = "SELECT prodi, angkatan, kontak FROM mahasiswa WHERE id_user = ?";
$stmt2 = mysqli_prepare($conn, $query_mhs);

if (!$stmt2) {
    die("Prepare gagal SELECT mahasiswa: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt2, 'i', $id_user);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
$mhs = mysqli_fetch_assoc($res2);
mysqli_stmt_close($stmt2);

// Variabel aman untuk FORM
$prodi    = htmlspecialchars($mhs['prodi'] ?? '');
$angkatan = htmlspecialchars($mhs['angkatan'] ?? '');
$kontak   = htmlspecialchars($mhs['kontak'] ?? '');

?>

<link rel="stylesheet" href="styles/pribadi.css" />

<div class="form-container">
  <h2>Profil Mahasiswa</h2>
  <p>
    Silakan lengkapi data profil Anda. Nama, email, dan NIM 
    otomatis diambil dari sistem dan tidak dapat diubah.<br>
    Jika terdapat kesalahan dalam penulisan nama, silakan hubungi admin untuk perbaikan.
  </p>

  <!-- Alert di LUAR form -->
  <div class="alert-sukses" style="display: none;">
    Data berhasil diperbarui!
  </div>

  <form id="formProfil" method="POST" action="update_pribadi.php">
    <input type="hidden" name="id_user" value="<?= $id_user ?>">

    <!-- Baris 1 -->
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" value="<?= $nama ?>" readonly />
    </div>

    <div class="form-group">
      <label>NIM</label>
      <input type="text" value="<?= $nim ?>" readonly />
    </div>

    <!-- Baris 2 -->
    <div class="form-group">
      <label>Email</label>
      <input type="text" value="<?= $email ?>" readonly />
    </div>

    <div class="form-group">
      <label>Program Studi</label>
      <input type="text" name="prodi" value="<?= $prodi ?>" placeholder="Masukkan Program Studi" />
    </div>

    <!-- Baris 3 -->
    <div class="form-group">
      <label>Kontak</label>
      <input type="text" name="kontak" value="<?= $kontak ?>" placeholder="Masukkan Nomor Telepon / Whatsapp Anda" />
    </div>

    <div class="form-group">
      <label>Tahun Angkatan</label>
      <input type="number" name="angkatan" value="<?= $angkatan ?>" placeholder="Contoh: 2022" />
    </div>

    <!-- Tombol -->
    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
    </div>
  </form>
</div>