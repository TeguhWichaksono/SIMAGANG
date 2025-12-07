<?php
session_start();
include '../Koneksi/koneksi.php';
require_once '../config.php';

// Ambil halaman saat ini
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = "pages/$page.php";

// Cek Role (Jika fungsi cekRole belum aktif, kode ini aman dilewati sementara)
// cekRole('Koordinator Bidang Magang'); 

$foto_profil_path = 'images/husin.jpg'; // Default

// Logika pengambilan foto profil sederhana
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

// ==========================================
// LOGIKA ACTIVE STATE & DROPDOWN
// ==========================================
// Daftar halaman yang ada di dalam Master Data
$masterDataPages = ['data_Mahasiswa', 'data_Kelompok', 'data_Dospem', 'data_Mitra'];

// Cek apakah halaman yang dibuka ada di dalam Master Data
// Jika YA, tambahkan class 'active' ke tombol parent dan 'show' ke submenu
$isMasterDataActive = in_array($page, $masterDataPages) ? 'active' : '';
$submenuClass = in_array($page, $masterDataPages) ? 'show' : ''; 
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= ucfirst(str_replace('_', ' ', $page)) ?> | SI MAGANG</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="styles/styles.css" />

  <style>
    /* === CSS KHUSUS UNTUK ANIMASI DROPDOWN === */
    
    /* Submenu default (Tersembunyi) */
    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, opacity 0.3s ease;
        opacity: 0;
        background-color: rgba(0, 0, 0, 0.02);
        margin-left: 10px;
        border-left: 2px solid rgba(66, 112, 244, 0.2);
    }

    /* Submenu saat aktif (Muncul) */
    .submenu.show {
        max-height: 500px; /* Nilai cukup besar agar konten muat */
        opacity: 1;
        transition: max-height 0.4s ease-in, opacity 0.3s ease;
    }

    /* Item di dalam submenu */
    .submenu .nav-item {
        font-size: 13px;
        padding-left: 45px !important;
        background-color: transparent;
        border-left: none;
    }

    .submenu .nav-item:hover {
        background-color: rgba(66, 112, 244, 0.08);
    }
    
    /* Style untuk tombol dropdown parent */
      .dropdown-btn {
        display: flex;
        align-items: center;
        justify-content: space-between; /* INI KUNCINYA: Mendorong konten ke ujung kanan-kiri */
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
        font-size: 12px; /* UKURAN DIPERKECIL (seperti Koordinator) */
        color: #828795; /* Opsional: warna abu agar tidak terlalu mencolok */
      }
      
      .dropdown-btn.active .arrow {
        transform: rotate(180deg);
        color: #4270F4; /* Warna biru saat aktif */
      }

    .dropdown-btn.active .arrow {
        transform: rotate(180deg);
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
      <a href="index.php?page=dashboard" class="nav-item <?= $page == 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-home"></i> <span>Dashboard</span>
      </a>

      <div class="nav-item dropdown-btn <?= $isMasterDataActive ?>" onclick="toggleDropdown('masterData')">
        <div style="display:flex; align-items:center;">
            <i class="fas fa-database"></i> <span>Master Data</span>
        </div>
        <i class="fas fa-chevron-down arrow"></i>
      </div>
      
      <div class="submenu <?= $submenuClass ?>" id="masterData">
          <a href="index.php?page=data_Mahasiswa" class="nav-item <?= $page == 'data_Mahasiswa' ? 'active' : '' ?>">
            <i class="fas fa-user-graduate"></i> <span>Data Mahasiswa</span>
          </a>
          <a href="index.php?page=data_Kelompok" class="nav-item <?= $page == 'data_Kelompok' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>Data Kelompok</span>
          </a>
          <a href="index.php?page=data_Dospem" class="nav-item <?= $page == 'data_Dospem' ? 'active' : '' ?>">
            <i class="fas fa-chalkboard-teacher"></i> <span>Data Dospem</span>
          </a>
          <a href="index.php?page=data_Mitra" class="nav-item <?= $page == 'data_Mitra' ? 'active' : '' ?>">
            <i class="fas fa-building"></i> <span>Data Mitra</span>
          </a>
      </div>

      <a href="index.php?page=persetujuan_magang_korbid" class="nav-item <?= $page == 'persetujuan_magang_korbid' ? 'active' : '' ?>">
        <i class="fas fa-check-circle"></i> <span>Persetujuan Magang</span>
      </a>
      
      <a href="index.php?page=persetujuan_mitra_korbid" class="nav-item <?= $page == 'persetujuan_mitra_korbid' ? 'active' : '' ?>">
        <i class="fas fa-handshake"></i> <span>Persetujuan Mitra</span>
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
          Selamat Datang, Koordinator Bidang Magang
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
              <span>Profil Saya</span>
            </a>
            <div class="profile-menu-item" onclick="openChangePasswordModal()">
              <i class="fas fa-key"></i>
              <span>Ganti Password</span>
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
        echo "<div style='display:flex; justify-content:center; align-items:center; height:60vh; flex-direction:column;'>
                <img src='https://cdn-icons-png.flaticon.com/512/7486/7486754.png' width='100' style='margin-bottom:20px; opacity:0.5;'>
                <h3 style='color:#6c7380;'>Halaman belum tersedia</h3>
                <p style='color:#828795;'>File 'pages/$page.php' tidak ditemukan.</p>
              </div>";
      }
      ?>
    </div>
  </div>

  <script>
    // FUNGSI ANIMASI DROPDOWN
    function toggleDropdown(id) {
        var submenu = document.getElementById(id);
        var btn = submenu.previousElementSibling; // Tombol pemicu (dropdown-btn)
        
        // Toggle class 'show' untuk memicu transisi CSS height & opacity
        submenu.classList.toggle('show');
        
        // Toggle class 'active' pada tombol untuk memutar panah
        btn.classList.toggle('active');
    }

    // Toggle profile dropdown
    function toggleProfileMenu(event) {
      event.stopPropagation();
      const dropdown = document.getElementById('profileDropdown');
      dropdown.classList.toggle('show');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
      // Profile Dropdown
      const profileDropdown = document.getElementById('profileDropdown');
      const userProfile = document.querySelector('.user-profile');
      if (profileDropdown && !userProfile.contains(event.target)) {
        profileDropdown.classList.remove('show');
      }
    });

    // (Opsional) Placeholder function
    function openChangePasswordModal() {
        alert("Fitur ganti password akan muncul di sini.");
    }
  </script>
</body>
</html>