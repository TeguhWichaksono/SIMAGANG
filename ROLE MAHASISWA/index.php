<?php
session_start();
include '../Koneksi/koneksi.php';
require_once '../config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = "pages/$page.php";

cekRole('Mahasiswa');

if($_SESSION['role'] !== 'Mahasiswa'){
  echo "Anda bukan Mahasiswa";
}

// ==========================================
// 1. LOGIKA STATUS MAGANG (ORIGINAL RESTORED)
// ==========================================
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

// Set akses berdasarkan status (Sesuai kode asli Anda)
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


// ==========================================
// 2. LOGIKA ACTIVE STATE UNTUK DROPDOWN
// ==========================================

// Group Profil
$profilPages = ['pribadi', 'kelompok'];
$isProfilActive = in_array($page, $profilPages) ? 'active' : '';
$profilSubmenuClass = in_array($page, $profilPages) ? 'show' : '';

// Group Pengajuan
$pengajuanPages = ['pengajuan_Mitra', 'berkas_Magang', 'status_pengajuan', 'status_pengajuan_mitra'];
$isPengajuanActive = in_array($page, $pengajuanPages) ? 'active' : '';
$pengajuanSubmenuClass = in_array($page, $pengajuanPages) ? 'show' : '';


// ==========================================
// 3. FOTO PROFIL
// ==========================================
$foto_profil_path = ''; 
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
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
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= ucfirst(str_replace('_', ' ', $page)) ?> | SI MAGANG</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
    <link rel="stylesheet" href="styles/styles.css" />

    <style>
      /* OVERRIDE CSS EXTERNAL UNTUK ANIMASI */
      
      .submenu {
        display: block !important; /* Timpa display:none dari styles.css */
        
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        
        transition: max-height 0.4s ease-out, opacity 0.3s ease;
        
        background-color: rgba(0, 0, 0, 0.02);
        border-left: 2px solid rgba(66, 112, 244, 0.2);
        margin-left: 10px;
      }

      /* Saat kelas .show ditambahkan via JS */
      .submenu.show {
        max-height: 500px;
        opacity: 1;
        transition: max-height 0.4s ease-in, opacity 0.3s ease;
      }

      /* Style Item Submenu */
      .submenu .nav-item {
        padding-left: 45px !important;
        font-weight: 400;
        font-size: 14px;
        border-left: none;
        background-color: transparent;
      }

      .submenu .nav-item:hover {
        background-color: rgba(66, 112, 244, 0.08);
      }

      .submenu .nav-item.active {
        background-color: rgba(66, 112, 244, 0.12);
        color: #4270F4;
        font-weight: 500;
        border-left: 3px solid #4270F4;
      }

      /* Style untuk tombol dropdown parent */
      .dropdown-btn {
        display: flex;
        align-items: center;
        justify-content: space-between; /* Arrow di kanan */
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

      /* Rotasi & Ukuran Panah Dropdown */
      .dropdown-btn .arrow {
        transition: transform 0.3s ease;
        font-size: 12px; /* Ukuran kecil sesuai request */
        color: #828795;
      }
      
      .dropdown-btn.active .arrow {
        transform: rotate(180deg);
        color: #4270F4;
      }

      /* Disabled State Styles */
      .nav-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
        position: relative;
      }
      
      /* Tooltip logic moved to inline style if needed or rely on external CSS */
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

        <div class="nav-item dropdown-btn <?= $isProfilActive ?>" onclick="toggleDropdown('submenuProfil', this)">
          <div style="display: flex; align-items: center;">
            <i class="fas fa-user-circle"></i> <span style="margin-left: 10px;">Profil</span>
          </div>
          <i class="fas fa-chevron-down arrow"></i>
        </div>

        <div class="submenu <?= $profilSubmenuClass ?>" id="submenuProfil">
          <a href="index.php?page=pribadi" class="nav-item <?= $page=='pribadi'?'active':'' ?>">
            <i class="fas fa-user"></i> Pribadi
          </a>
          <a href="index.php?page=kelompok" class="nav-item <?= $page=='kelompok'?'active':'' ?>">
            <i class="fas fa-users"></i> Kelompok
          </a>
        </div>

        <div class="nav-item dropdown-btn <?= $isPengajuanActive ?>" onclick="toggleDropdown('submenuMagang', this)">
          <div style="display: flex; align-items: center;">
            <i class="fas fa-file-alt"></i> <span style="margin-left: 10px;">Pengajuan</span>
          </div>
          <i class="fas fa-chevron-down arrow"></i>
        </div>

        <div class="submenu <?= $pengajuanSubmenuClass ?>" id="submenuMagang">
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

        <a href="<?= $can_access_magang ? 'index.php?page=logbook' : '#' ?>" 
           class="nav-item <?= $page=='logbook'?'active':'' ?> 
                  <?= !$can_access_magang ? 'disabled' : '' ?>
                  <?= ($status_magang === 'selesai') ? 'readonly' : '' ?>"
           title="<?= !$can_access_magang ? 'Selesaikan proses pengajuan dulu' : '' ?>">
          <i class="fas fa-list"></i> <span>Logbook Harian</span>
        </a>

        

      </div>

      <div class="premium-box">
        <h3>Buku Panduan</h3>
        <a href="PEDOMAN_MAGANG_MAHASISWA_POLIJE_2025.pdf" class="premium-btn" target="_blank">
          Selengkapnya
        </a>
      </div>
    </div>

    <div class="main-content">
      <div class="header">
        <div class="welcome-section">
          <p class="greeting">
            Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Mahasiswa') ?>
          </p>
          <h1 class="welcome-title">
            <?= ucfirst(str_replace('_', ' ', $page)) ?>
          </h1>
        </div>

        <div class="header-right">
          <a href="index.php?page=notifikasi" style="text-decoration:none;">
            <div class="notification-bell">
              <i class="fas fa-bell"></i>
              <div class="notification-indicator"></div>
            </div>
          </a>

          <div class="user-profile" onclick="toggleProfileMenu(event)">
            <img src="<?= $foto_profil_path ?>" alt="Foto Profil" class="profile-pic" />
            
            <div class="profile-dropdown" id="profileDropdown">
              <a href="Akun.php" class="profile-menu-item">
                <i class="fas fa-user"></i>
                <span>Profil</span>
              </a>
              <div class="profile-menu-item" onclick="openChangePasswordModal()">
                <i class="fas fa-key"></i>
                <span>Ganti Kata Sandi</span>
              </div>
              <a href="../Login/logout.php" class="profile-menu-item logout" onclick="return confirm('Yakin ingin keluar?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </a>
            </div>
          </div>

        </div>
      </div>

      <div class="page-container">
        <?php
          if (file_exists($pagePath)) {
              include $pagePath;
          } else {
              echo "<div style='text-align:center; margin-top:50px; color:#888;'>
                      <h3>Halaman tidak ditemukan</h3>
                    </div>";
          }
        ?>
      </div>
    </div>

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

    <script>
      // Toggle Dropdown dengan Animasi Smooth
      function toggleDropdown(submenuId, btnElement) {
        const submenu = document.getElementById(submenuId);
        
        // Toggle class 'show' (memicu transisi CSS max-height & opacity)
        submenu.classList.toggle('show');
        
        btnElement.classList.toggle('active');
      }

      // Toggle Profile Dropdown
      function toggleProfileMenu(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
      }

      // Close dropdown click outside
      document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const userProfile = document.querySelector('.user-profile');
        if (dropdown && !userProfile.contains(event.target)) {
          dropdown.classList.remove('show');
        }
      });

      // Modal Password Logic
      function openChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.add('show');
        const profileDropdown = document.getElementById('profileDropdown');
        if(profileDropdown) profileDropdown.classList.remove('show');
      }

      function closePasswordModal() {
        document.getElementById('changePasswordModal').classList.remove('show');
        document.getElementById('changePasswordForm').reset();
      }

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

      // Handle Change Password Submit
      document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;

        if (newPass !== confirmPass) {
          alert('Konfirmasi kata sandi tidak cocok!');
          return;
        }

        // Simulasi sukses (atau ganti dengan fetch ke endpoint PHP update password)
        alert('Kata sandi berhasil diubah!');
        closePasswordModal();
      });
    </script>
  </body>
</html>