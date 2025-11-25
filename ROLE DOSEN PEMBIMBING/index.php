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
        <a href="index.php?page=akun" class="nav-item <?= $page=='akun'?'active':'' ?>">
            <i class="fas fa-user-circle"></i> <span>Akun</span>
        </a>
        <a href="index.php?page=daftar_Bimbingan" class="nav-item <?= $page=='daftar_Bimbingan'?'active':'' ?>">
            <i class="fas fa-user-graduate"></i> <span>Daftar Bimbingan</span>
        </a>
        <a href="index.php?page=monitoring" class="nav-item <?= $page=='monitoring'?'active':'' ?>">
            <i class="fas fa-clipboard-list"></i> <span>Monitoring</span>
        </a>
        <a href="index.php?page=validasi_Absensi" class="nav-item <?= $page=='validasi_Absensi'?'active':'' ?>">
            <i class="fas fa-calendar-check"></i> <span>Validasi Absensi</span>
        </a>
        <a href="index.php?page=evaluasi_Nilai" class="nav-item <?= $page=='evaluasi_Nilai'?'active':'' ?>">
            <i class="fas fa-star-half-alt"></i> <span>Evaluasi & Nilai</span>
        </a>
        <a href="index.php?page=notifikasi" class="nav-item <?= $page=='notifikasi'?'active':'' ?>">
            <i class="fas fa-bell"></i> <span>Notifikasi</span>
        </a>
      </div>

      <div class="premium-box">
        <h3>Buku Panduan</h3>
        <button class="premium-btn">Selengkapnya</button>
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

          <div class="user-profile">
            <img src="images/tyakk.png" alt="Foto Profil" class="profile-pic" />
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

    <script>
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
        
        // Check if any field is filled
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
          window.location.href = '/WSI/SIMAGANGG/';
        }
      }
    </script>

  </body>
</html>
