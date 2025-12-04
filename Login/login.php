<?php
include '../Koneksi/koneksi.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $query = "SELECT * FROM users WHERE email='$email'";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

    if (password_verify($password, $data['password'])) {

      $_SESSION['id'] = $data['id'];
      // $_SESSION['nama'] = $data['nama'];
      $_SESSION['nim'] = $data['nim'];
      $_SESSION['role'] = $data['role'];

      switch ($data['role']) {
        case 'Admin':
          header("Location: ../ROLE ADMIN/index.php");
          break;
        case 'Dosen Pembimbing':
          header("Location: ../ROLE DOSEN PEMBIMBING/index.php");
          break;
        case 'Koordinator Bidang Magang':
          header("Location: ../ROLE KOORDINATOR BIDANG/index.php");
          break;
        case 'Mahasiswa':
          header("Location: ../ROLE MAHASISWA/index.php");
          break;
          break;
        default:
          $message = "❌ Role tidak dikenali!";
          break;
      }

      exit;
    } else {
      $message = "❌ Kata sandi salah!";
    }
  } else {
    $message = "❌ Email tidak ditemukan!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SIMAGANG</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
  <div class="container">
    <div class="form-container">
      <form method="POST">
        <h1>Masuk</h1>
        <div class="social-icons">
          <a href="#"><i class="fa-brands fa-google-plus-g"></i></a>
          <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#"><i class="fa-brands fa-github"></i></a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
        <span>Atau gunakan akun Anda</span>

        <?php if ($message != ""): ?>
          <p style="color:red;"><?= $message ?></p>
        <?php endif; ?>

        <!-- <input type="text" name="nama" placeholder="Nama" required /> -->
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Kata Sandi" required>
        <button type="submit" name="login">Masuk</button>
      </form>
    </div>

    <div class="panel">
      <h1>Si Magang</h1>
      <p>Buat akun baru untuk mengelola kegiatan magang Anda</p>
      <button onclick="window.location.href='register.php'">Daftar</button>
    </div>
  </div>
</body>

</html>