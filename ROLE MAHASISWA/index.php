<?php
session_start();
include '../Koneksi/koneksi.php'; 
require_once '../config.php';

$status_magang = 'pra-magang'; // Default
$can_access_magang = false;
$can_crud_magang = false;

if (isset($_SESSION['id'])) {
    $id_user = $_SESSION['id'];
    $query_status = "SELECT status_magang FROM mahasiswa WHERE id_user = ?";
    $stmt_status = mysqli_prepare($conn, $query_status);
    
    if ($stmt_status) {
        mysqli_stmt_bind_param($stmt_status, 'i', $id_user);
        mysqli_stmt_execute($stmt_status);
        $result_status = mysqli_stmt_get_result($stmt_status);
        
        if ($row_status = mysqli_fetch_assoc($result_status)) {
            $status_magang = $row_status['status_magang'];
        }
        mysqli_stmt_close($stmt_status);
    }
}

// Set akses berdasarkan status
if ($status_magang === 'magang_aktif') {
    $can_access_magang = true;
    $can_crud_magang = true; // Bisa CRUD
} elseif ($status_magang === 'selesai') {
    $can_access_magang = true;
    $can_crud_magang = false; // Read-only
} else {
    $can_access_magang = false;
    $can_crud_magang = false;
}

// Save to session untuk akses di halaman lain
$_SESSION['status_magang'] = $status_magang;
$_SESSION['can_access_magang'] = $can_access_magang;
$_SESSION['can_crud_magang'] = $can_crud_magang;

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = "pages/$page.php";

// cekRole('Mahasiswa');

// if($_SESSION['role'] !== 'Mahasiswa'){
//   echo "Anda bukan Mahasiswa";
// }

$foto_profil_path = ''; 

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
      .dropdown-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-weight: 600;
        border-left: 3px solid transparent;
      }
      .dropdown-btn:hover {
        background-color: rgba(66, 112, 244, 0.1);
        border-left-color: #4270F4;
      }
      .dropdown-btn.active {
        background-color: rgba(66, 112, 244, 0.15);
        border-left-color: #4270F4;
      }
      .dropdown-btn .arrow {
        transition: transform 0.3s ease;
        font-size: 12px;
      }
      .dropdown-btn.active .arrow {
        transform: rotate(180deg);
      }
      .submenu {
        display: none;
        background-color: rgba(0, 0, 0, 0.02);
        border-left: 2px solid rgba(66, 112, 244, 0.2);
        margin-left: 10px;
      }
      .submenu .nav-item {
        padding-left: 45px !important;
        font-weight: 400;
        font-size: 14px;
        border-left: none;
        background-color: transparent;
      }
      .submenu .nav-item:hover {
        background-color: rgba(66, 112, 244, 0.08);
        padding-left: 48px !important;
      }
      .submenu .nav-item.active {
        background-color: rgba(66, 112, 244, 0.12);
        border-left: 3px solid #4270F4;
        color: #4270F4;
        font-weight: 500;
      }
      .submenu .nav-item i {
        font-size: 13px;
        opacity: 0.8;
      }
      .nav-item.disabled,
      .dropdown-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed !important;
        pointer-events: none; /* Mencegah klik */
        position: relative;
      }

      .nav-item.disabled::after,
      .dropdown-btn.disabled::after {
        content: '🔒';
        position: absolute;
        right: 15px;
        font-size: 14px;
      }

      /* Tooltip untuk disabled item */
      .nav-item.disabled:hover::before,
      .dropdown-btn.disabled:hover::before {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        margin-left: 10px;
        pointer-events: all; /* Override parent */
      }

      /* Untuk yang selesai magang (read-only) */
      .nav-item.readonly::after {
        content: '👁️'; /* Icon mata untuk read-only */
      }
          </style>
  </head>

  <body>
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

        <!-- DROPDOWN PROFIL -->
        <div class="nav-item dropdown-btn <?= ($page=='pribadi' || $page=='kelompok') ? 'active':'' ?>" 
             onclick="toggleDropdown('submenuProfil', this)">
          <div style="display: flex; align-items: center;">
            <i class="fas fa-user-circle"></i> <span style="margin-left: 10px;">Profil</span>
          </div>
          <i class="fas fa-chevron-down arrow"></i>
        </div>

        <!-- Submenu Profil -->
        <div class="submenu" id="submenuProfil">
          <a href="index.php?page=pribadi" class="nav-item <?= $page=='pribadi'?'active':'' ?>">
            <i class="fas fa-user"></i> Pribadi
          </a>
          <a href="index.php?page=kelompok" class="nav-item <?= $page=='kelompok'?'active':'' ?>">
            <i class="fas fa-users"></i> Kelompok
          </a>
        </div>

        <!-- DROPDOWN PENGAJUAN -->
        <div class="nav-item dropdown-btn <?= ($page=='pengajuan_Mitra' || $page=='berkas_Magang' || $page=='status_pengajuan' || $page=='status_pengajuan_mitra') ? 'active':'' ?>" 
             onclick="toggleDropdown('submenuMagang', this)">
          <div style="display: flex; align-items: center;">
            <i class="fas fa-file-alt"></i> <span style="margin-left: 10px;">Pengajuan</span>
          </div>
          <i class="fas fa-chevron-down arrow"></i>
        </div>

        <!-- Submenu Pengajuan Magang -->
        <div class="submenu" id="submenuMagang">
          <a href="index.php?page=pengajuan_Mitra" class="nav-item <?= $page=='pengajuan_Mitra'?'active':'' ?>">
            <i class="fas fa-building"></i> Mitra
          </a>
          <a href="index.php?page=berkas_Magang" class="nav-item <?= $page=='berkas_Magang'?'active':'' ?>">
            <i class="fas fa-file-upload"></i> Berkas
          </a>
          <a href="index.php?page=status_pengajuan" class="nav-item <?= $page=='status_pengajuan'?'active':'' ?>">
            <i class="fas fa-clipboard-list"></i> Status Pengajuan
          </a>
          <a href="index.php?page=status_pengajuan_mitra" class="nav-item <?= $page=='status_pengajuan_mitra'?'active':'' ?>">
            <i class="fas fa-building-circle-check"></i> Status Mitra
          </a>
        </div>

          <!-- ABSENSI & KEGIATAN - Conditional Access -->
          <a href="<?= $can_access_magang ? 'index.php?page=absensi' : 'javascript:void(0)' ?>" 
            class="nav-item <?= $page=='absensi'?'active':'' ?> 
                    <?= !$can_access_magang ? 'disabled' : '' ?> 
                    <?= ($status_magang === 'selesai') ? 'readonly' : '' ?>"
            data-tooltip="<?= !$can_access_magang ? 'Upload Surat Penerimaan dulu untuk akses fitur ini' : ($status_magang === 'selesai' ? 'Magang sudah selesai (Read-only)' : '') ?>"
            <?= !$can_access_magang ? 'onclick="return false;"' : '' ?>>
            <i class="fas fa-tasks"></i> <span>Absensi Harian</span>
          </a>

          <a href="<?= $can_access_magang ? 'index.php?page=kegiatan' : 'javascript:void(0)' ?>" 
            class="nav-item <?= $page=='kegiatan'?'active':'' ?> 
                    <?= !$can_access_magang ? 'disabled' : '' ?> 
                    <?= ($status_magang === 'selesai') ? 'readonly' : '' ?>"
            data-tooltip="<?= !$can_access_magang ? 'Upload Surat Penerimaan dulu untuk akses fitur ini' : ($status_magang === 'selesai' ? 'Magang sudah selesai (Read-only)' : '') ?>"
            <?= !$can_access_magang ? 'onclick="return false;"' : '' ?>>
            <i class="fas fa-tasks"></i> <span>Kegiatan Harian</span>
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
            Selamat Datang, <?= $_SESSION['nama'] ?? 'Mahasiswa' ?>
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
              <a href="index.php?page=pribadi" class="profile-menu-item" onclick="event.stopPropagation();">
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

    <!-- JS Toggle -->
    <script>
      // Fungsi toggle dropdown sidebar
      function toggleDropdown(submenuId, parentElement) {
        const submenu = document.getElementById(submenuId);
        const isOpen = submenu.style.display === "block";
        
        // Tutup semua dropdown terlebih dahulu
        const allSubmenus = document.querySelectorAll('.submenu');
        const allDropdownBtns = document.querySelectorAll('.dropdown-btn');
        
        allSubmenus.forEach(function(menu) {
          menu.style.display = "none";
        });
        
        allDropdownBtns.forEach(function(btn) {
          btn.classList.remove("active");
        });
        
        // Jika dropdown yang diklik sebelumnya tertutup, buka dropdown tersebut
        if (!isOpen) {
          submenu.style.display = "block";
          parentElement.classList.add("active");
        }
      }

      // Auto open dropdown jika halaman child aktif
      window.addEventListener('DOMContentLoaded', function() {
        <?php if ($page=='pribadi' || $page=='kelompok'): ?>
          document.getElementById('submenuProfil').style.display = 'block';
          document.querySelector('[onclick*="submenuProfil"]').classList.add('active');
        <?php endif; ?>

        <?php if ($page=='pengajuan_Mitra' || $page=='berkas_Magang' || $page=='status_pengajuan' || $page=='status_pengajuan_mitra'): ?>
          document.getElementById('submenuMagang').style.display = 'block';
          document.querySelector('[onclick*="submenuMagang"]').classList.add('active');
        <?php endif; ?>
      });

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
          window.location.href = '/WSI/SIMAGANGG/Login/login.php';
        }
      }
    </script>

  </body>
</html>
