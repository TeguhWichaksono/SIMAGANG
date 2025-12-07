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
  <title>Login | SI MAGANG</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
    }

    body {
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }

    .login-container {
      background: #fff;
      border-radius: 30px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 900px;
      display: flex;
      overflow: hidden;
      min-height: 550px;
    }

    /* Form Side */
    .form-container {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 50px 40px;
    }

    .logo-section {
      margin-bottom: 30px;
      text-align: center;
    }

    .logo-section h2 {
      color: #512da8;
      font-size: 28px;
      margin-bottom: 8px;
    }

    .logo-section p {
      color: #666;
      font-size: 14px;
    }

    form {
      width: 100%;
      max-width: 350px;
      display: flex;
      flex-direction: column;
    }

    form h1 {
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 10px;
      color: #333;
      text-align: center;
    }

    .welcome-text {
      text-align: center;
      color: #666;
      font-size: 14px;
      margin-bottom: 30px;
    }

    .error-message {
      background: #ffe6e6;
      border-left: 4px solid #dc3545;
      color: #dc3545;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      text-align: left;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
    }

    .input-group i.input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      font-size: 16px;
      pointer-events: none;
    }

    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      font-size: 16px;
      cursor: pointer;
      transition: color 0.3s ease;
      z-index: 10;
      padding: 10px;
      pointer-events: all;
    }

    .toggle-password:hover {
      color: #512da8;
    }

    form input {
      width: 100%;
      padding: 14px 45px 14px 45px;
      border: 1px solid #ddd;
      border-radius: 10px;
      background: #f9f9f9;
      font-size: 14px;
      outline: none;
      transition: all 0.3s ease;
    }

    form input:focus {
      border-color: #512da8;
      background: #fff;
      box-shadow: 0 0 8px rgba(81, 45, 168, 0.2);
    }

    .forgot-password {
      text-align: right;
      margin: -10px 0 20px 0;
    }

    .forgot-password a {
      color: #512da8;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .forgot-password a:hover {
      color: #2575fc;
      text-decoration: underline;
    }

    form button {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 25px;
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      color: white;
      font-weight: bold;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    form button:hover {
      transform: translateY(-2px);
      background: linear-gradient(135deg, #2575fc, #6a11cb);
      box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
    }

    .divider {
      display: flex;
      align-items: center;
      margin: 25px 0;
      color: #999;
      font-size: 13px;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #ddd;
    }

    .divider::before {
      margin-right: 10px;
    }

    .divider::after {
      margin-left: 10px;
    }

    .social-login {
      display: flex;
      gap: 15px;
      justify-content: center;
    }

    .social-btn {
      width: 45px;
      height: 45px;
      border: 1px solid #ddd;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #666;
      text-decoration: none;
      transition: all 0.3s ease;
      background: #fff;
    }

    .social-btn:hover {
      border-color: #512da8;
      color: #512da8;
      transform: translateY(-3px);
      box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    }

    /* Purple Panel */
    .panel {
      flex: 1;
      background: linear-gradient(135deg, #5c6bc0, #512da8);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      text-align: center;
      padding: 50px 40px;
      position: relative;
      overflow: hidden;
    }

    .panel::before {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      top: -150px;
      right: -150px;
    }

    .panel::after {
      content: '';
      position: absolute;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      bottom: -100px;
      left: -100px;
    }

    .panel-content {
      position: relative;
      z-index: 1;
    }

    .panel h1 {
      font-size: 32px;
      margin-bottom: 20px;
      font-weight: 700;
    }

    .panel p {
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 25px;
      opacity: 0.95;
    }

    .panel-illustration {
      width: 280px;
      height: 280px;
      margin: 30px auto 0;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 120px;
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-20px);
      }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }

      .panel {
        order: -1;
        padding: 40px 30px;
      }

      .panel h1 {
        font-size: 24px;
      }

      .panel-illustration {
        width: 200px;
        height: 200px;
        font-size: 80px;
        margin-top: 20px;
      }

      .form-container {
        padding: 40px 30px;
      }

      form {
        max-width: 100%;
      }
    }

    @media (max-width: 480px) {
      body {
        padding: 10px;
      }

      .login-container {
        border-radius: 20px;
      }

      .form-container {
        padding: 30px 20px;
      }

      .logo-section h2 {
        font-size: 24px;
      }

      form h1 {
        font-size: 22px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Form Container -->
    <div class="form-container">
      <div class="logo-section">
        <h2>SI MAGANG</h2>
        <p>Manajemen Informatika</p>
      </div>

      <form method="POST">
        <h1>Selamat Datang</h1>
        <p class="welcome-text">Silakan masuk dengan akun Anda</p>

        <?php if ($message != ""): ?>
          <div class="error-message">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <div class="input-group">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" id="password" placeholder="Kata Sandi" required>
          <i class="fas fa-eye toggle-password" id="togglePassword"></i>
        </div>

        <div class="forgot-password">
          <a href="kirimReset.php">
            <i class="fas fa-question-circle"></i> Lupa sandi?
          </a>
        </div>

        <button type="submit" name="login">
          <i class="fas fa-sign-in-alt"></i> Masuk
        </button>

        <!-- <div class="divider">atau masuk dengan</div>

        <div class="social-login">
          <a href="#" class="social-btn" title="Google">
            <i class="fab fa-google"></i>
          </a>
          <a href="#" class="social-btn" title="Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="social-btn" title="GitHub">
            <i class="fab fa-github"></i>
          </a>
          <a href="#" class="social-btn" title="LinkedIn">
            <i class="fab fa-linkedin-in"></i>
          </a>
        </div> -->
      </form>
    </div>

    <!-- Purple Panel -->
    <div class="panel">
      <div class="panel-content">
        <h1>Sistem Informasi Magang</h1>
        <p>
          Kelola aktivitas magang Anda dengan mudah dan efisien. 
          Akses semua fitur yang Anda butuhkan dalam satu platform.
        </p>
        <div class="panel-illustration">
          <i class="fas fa-briefcase"></i>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Toggle the type attribute
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      
      // Toggle the icon
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });

    // Prevent toggle icon from interfering with input
    password.addEventListener('click', function(e) {
      e.stopPropagation();
      this.focus();
    });
  </script>
</body>
</html>