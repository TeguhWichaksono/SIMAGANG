<?php
include '../../Koneksi/koneksi.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama = $_POST["nama"];
  $nim = $_POST["nim"];
  $email = $_POST["email"];
  $password = $_POST["password"];

  if ($nama == "" || $nim == "" || $email == "" || $password == "") {
    $message = "❌ Semua field harus diisi!";
  } else {
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
      $message = "❌ Email sudah terdaftar!";
    } else {

      // ✅ Simpan password apa adanya tanpa hash
      $query = "INSERT INTO users (nama, nim, email, password, role) 
                VALUES ('$nama', '$nim', '$email', '$password', 'mahasiswa')";
      
      if (mysqli_query($conn, $query)) {
        echo "<script>alert('✅ Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        exit();
      } else {
        $message = "❌ Gagal menyimpan data: " . mysqli_error($conn);
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - SIMAGANG</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
  <div class="container">
    <div class="form-container">
      <form method="POST">
        <h1>Daftar Akun Mahasiswa</h1>
        <div class="social-icons">
          <a href="#"><i class="fa-brands fa-google-plus-g"></i></a>
          <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#"><i class="fa-brands fa-github"></i></a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
        <span>Masukkan data Anda untuk mendaftar</span>

        <?php if ($message != ""): ?>
          <p style="color:red;"><?= $message ?></p>
        <?php endif; ?>

        <input type="text" name="nama" placeholder="Nama" required />
        <input type="text" name="nim" placeholder="NIM" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Kata Sandi" required>
        <button type="submit">Daftar</button>
      </form>
    </div>

    <div class="panel">
      <h1>Sudah Punya Akun?</h1>
      <p>Masuk ke akun Anda untuk melanjutkan kegiatan magang</p>
      <button onclick="window.location.href='login.php'">Masuk</button>
    </div>
  </div>
</body>
</html>