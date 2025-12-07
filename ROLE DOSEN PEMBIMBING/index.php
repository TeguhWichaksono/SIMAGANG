<?php
session_start();
include '../Koneksi/koneksi.php'; 
require_once '../config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = "pages/$page.php";

cekRole('Dosen Pembimbing');

if($_SESSION['role'] !== 'Dosen Pembimbing'){
  echo "Anda bukan Dosen Pembimbing";
}

$foto_profil_path = 'images/faisal.png'; 

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    
    // Ambil foto_profil dari database
    $stmt = mysqli_prepare($conn, "SELECT foto_profil FROM users WHERE id = ?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            $db_foto = $row['foto_profil'];
            
            if (!empty($db_foto) && file_exists("uploads/" . $db_foto)) {
                $foto_profil_path = "uploads/" . $db_foto;
            } 
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= ucfirst($page) ?> | SI MAGANG</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
    <link rel="stylesheet" href="styles/styles.css" />

    <style>
      /* Profile Dropdown Styles */
      .profile-dropdown {
        position: absolute;
        top: 60px;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        display: none;
        z-index: 1000;
        overflow: hidden;
        animation: fadeInDown 0.3s ease;
      }

      .profile-dropdown.show {
        display: block;
      }

      @keyframes fadeInDown {
        from {
          opacity: 0;
          transform: translateY(-10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .profile-menu-item {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        color: #262A39;
        font-size: 14px;
        text-decoration: none;
      }

      .profile-menu-item:hover {
        background-color: rgba(66, 112, 244, 0.08);
      }

      .profile-menu-item.logout {
        color: #dc3545;
        border-top: 1px solid #f0f0f0;
      }

      .profile-menu-item.logout:hover {
        background-color: rgba(220, 53, 69, 0.08);
      }

      .profile-menu-item i {
        font-size: 16px;
        width: 20px;
      }

      /* Modal Styles */
      .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
      }

      .modal-overlay.show {
        display: flex;
      }

      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }

      .modal-content {
        background: white;
        border-radius: 12px;
        padding: 0;
        width: 90%;
        max-width: 450px;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      }

      .modal-content.modal-small {
        max-width: 400px;
      }

      @keyframes slideUp {
        from {
          transform: translateY(50px);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .modal-header h3 {
        margin: 0;
        font-size: 20px;
        color: #262A39;
        font-weight: 600;
      }

      .close-modal {
        background: none;
        border: none;
        font-size: 28px;
        color: #6c757d;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background-color 0.2s ease;
      }

      .close-modal:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }

      .modal-body {
        padding: 24px;
      }

      .modal-body p {
        margin: 0;
        color: #495057;
        line-height: 1.6;
        font-size: 15px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group:first-of-type {
        margin-top: 24px;
      }

      .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #262A39;
        font-size: 14px;
      }

      .password-input-wrapper {
        position: relative;
      }

      .form-group input {
        width: 100%;
        padding: 12px 40px 12px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
      }

      .form-group input:focus {
        outline: none;
        border-color: #4270F4;
        box-shadow: 0 0 0 3px rgba(66, 112, 244, 0.1);
      }

      .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        font-size: 16px;
        transition: color 0.2s ease;
      }

      .toggle-password:hover {
        color: #4270F4;
      }

      .modal-footer {
        padding: 16px 24px 24px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
      }

      .btn-primary, .btn-secondary, .btn-danger {
        padding: 10px 24px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .btn-primary {
        background-color: #4270F4;
        color: white;
      }

      .btn-primary:hover {
        background-color: #3461e8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(66, 112, 244, 0.3);
      }

      .btn-secondary {
        background-color: #6c757d;
        color: white;
      }

      .btn-secondary:hover {
        background-color: #5a6268;
      }

      .btn-danger {
        background-color: #dc3545;
        color: white;
      }

      .btn-danger:hover {
        background-color: #c82333;
      }
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo">
        <svg width="100" height="40" viewBox="0 0 100 40">
          <path d="M12,15 L30,15 L24,27 L12,27 Z" fill="#4270F4" />
          <text x="38" y="27" fill="#262A39" font-size="20" font-weight="bold">
            SMG
          </text>
        </svg>
      </div>

      <div class="nav-menu">
        <a href="index.php?page=dashboard" class="nav-item <?= $page=='dashboard'?'active':'' ?>">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
        <a href="index.php?page=daftar_Bimbingan" class="nav-item <?= $page=='daftar_Bimbingan'?'active':'' ?>">
            <i class="fas fa-user-graduate"></i> <span>Daftar Bimbingan</span>
        </a>
        <a href="index.php?page=monitoring" class="nav-item <?= $page=='monitoring'?'active':'' ?>">
            <i class="fas fa-clipboard-list"></i> <span>Monitoring</span>
        </a>
        
        
      </div>

      <div class="premium-box">
        <h3>Buku Panduan</h3>
        <a href="PEDOMAN_MAGANG_MAHASISWA_POLIJE_2025.pdf" class="premium-btn" target="_blank">
          Selengkapnya
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="header">
        <div class="welcome-section">
          <p class="greeting">
            Selamat Datang, <?= $_SESSION['nama'] ?? 'Dosen Pembimbing' ?>
          </p>
          <h1 class="welcome-title">
            <?= ucfirst(str_replace('_', ' ', $page)) ?>
          </h1>
        </div>

        <div class="header-right">
          <div class="notification-bell">
            <i class="fas fa-bell"></i>
            <div class="notification-indicator"></div>
          </div>

          <div class="user-profile" onclick="toggleProfileMenu(event)">
            <img src="<?= $foto_profil_path ?>" alt="Foto Profil" class="profile-pic" />
            
            <!-- Dropdown Menu -->
            <div class="profile-dropdown" id="profileDropdown">
              <a href="Akun.php" class="profile-menu-item" onclick="event.stopPropagation();">
                <i class="fas fa-user"></i>
                <span>Profil</span>
              </a>
              <div class="profile-menu-item" onclick="event.stopPropagation(); openChangePasswordModal()">
                <i class="fas fa-key"></i>
                <span>Ganti Kata Sandi</span>
              </div>
              <div class="profile-menu-item logout" onclick="event.stopPropagation(); confirmLogout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Konten Halaman Dinamis -->
      <div class="page-container">
        <?php
          if (file_exists($pagePath)) {
              include $pagePath;
          } else {
              echo "<h2 style='text-align:center;margin-top:50px;'>Halaman tidak ditemukan</h2>";
          }
        ?>
      </div>
    </div>

    <!-- Modal Ganti Kata Sandi -->
    <div class="modal-overlay" id="changePasswordModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Ganti Kata Sandi</h3>
          <button class="close-modal" onclick="closePasswordModal()">&times;</button>
        </div>
        <form id="changePasswordForm">
          <div class="form-group">
            <label for="newPassword">Kata Sandi Baru</label>
            <div class="password-input-wrapper">
              <input type="password" id="newPassword" name="newPassword" placeholder="Masukkan kata sandi baru" required>
              <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('newPassword', this)"></i>
            </div>
          </div>
          <div class="form-group">
            <label for="confirmPassword">Konfirmasi Kata Sandi</label>
            <div class="password-input-wrapper">
              <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi kata sandi baru" required>
              <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirmPassword', this)"></i>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePasswordModal()">Tutup</button>
            <button type="submit" class="btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Konfirmasi Penutupan -->
    <div class="modal-overlay" id="confirmCloseModal">
      <div class="modal-content modal-small">
        <div class="modal-header">
          <h3>Peringatan</h3>
        </div>
        <div class="modal-body">
          <p>Kata sandi belum tersimpan. Yakin ingin menutup formulir ini?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="cancelClose()">Batal</button>
          <button type="button" class="btn-danger" onclick="forceClose()">Ya, Tutup</button>
        </div>
      </div>
    </div>

    <!-- JavaScript -->
    <script>
      // Toggle profile dropdown
      function toggleProfileMenu(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
      }

      // Close dropdown when clicking outside
      document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const userProfile = document.querySelector('.user-profile');
        
        if (!userProfile.contains(event.target)) {
          dropdown.classList.remove('show');
        }
      });

      // Open change password modal
      function openChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.add('show');
        document.getElementById('profileDropdown').classList.remove('show');
        document.body.style.overflow = 'hidden';
      }

      // Close password modal with check
      function closePasswordModal() {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword || confirmPassword) {
          document.getElementById('confirmCloseModal').classList.add('show');
        } else {
          forceClose();
        }
      }

      // Cancel close (back to form)
      function cancelClose() {
        document.getElementById('confirmCloseModal').classList.remove('show');
      }

      // Force close all modals
      function forceClose() {
        document.getElementById('changePasswordModal').classList.remove('show');
        document.getElementById('confirmCloseModal').classList.remove('show');
        document.getElementById('changePasswordForm').reset();
        document.body.style.overflow = 'auto';
      }

      // Toggle password visibility
      function togglePasswordVisibility(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      }

      // Handle form submission
      document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
          alert('Kata sandi dan konfirmasi kata sandi tidak sama!');
          return;
        }
        
        if (newPassword.length < 6) {
          alert('Kata sandi harus minimal 6 karakter!');
          return;
        }
        
        // AJAX request to update password
        fetch('update_password.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'new_password=' + encodeURIComponent(newPassword)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Kata sandi berhasil diubah!');
            forceClose();
          } else {
            alert('Gagal mengubah kata sandi: ' + data.message);
          }
        })
        .catch(error => {
          alert('Terjadi kesalahan. Silakan coba lagi.');
          console.error('Error:', error);
        });
      });

      // Confirm logout
      function confirmLogout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
          window.location.href = '/SIMAGANG/Login/logout.php';
        }
      }
    </script>
  </body>
</html>