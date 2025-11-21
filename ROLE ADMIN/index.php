<?php
session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = "pages/$page.php";
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
        <a href="index.php?page=manajemen_User" class="nav-item <?= $page=='manajemen_User'?'active':'' ?>">
            <i class="fas fa-users-gear"></i> <span>Manajemen User</span>
        </a>
        <a href="index.php?page=manajemen_Mitra" class="nav-item <?= $page=='manajemen_Mitra'?'active':'' ?>">
            <i class="fas fa-building"></i> <span>Manajemen Mitra</span>
        </a>
        <a href="index.php?page=laporan_Sistem" class="nav-item <?= $page=='laporan_Sistem'?'active':'' ?>">
            <i class="fas fa-chart-line"></i> <span>Laporan Sistem</span>
        </a>
        <a href="index.php?page=notifikasi" class="nav-item <?= $page=='notifikasi'?'active':'' ?>">
            <i class="fas fa-bell"></i> <span>Notifikasi</span>
        </a>
        <a href="index.php?page=help_Center" class="nav-item <?= $page=='help_Center'?'active':'' ?>">
            <i class="fas fa-life-ring"></i> <span>Help Center</span>
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
  </body>
</html>
