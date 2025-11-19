<?php
session_start();
// Sertakan koneksi database
include '../Koneksi/koneksi.php'; 

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = "pages/$page.php";

$foto_profil_path = 'images/tyakk.png'; 

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

    <!-- Tambahan CSS dropdown -->
   
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

        <!-- DROPDOWN MAGANG -->
        <div class="nav-item dropdown-btn 
            <?= ($page=='pengajuan_Magang' || $page=='pengajuan_Kelompok' || $page=='pengajuan_Mitra' || $page=='berkas_Magang') ? 'active':'' ?>"
            id="dropdownMagang">
          <i class="fas fa-file-alt"></i> <span>Pengajuan Magang</span>
          <i class="fas fa-chevron-down arrow" id="arrowIcon"></i>
        </div>

        <!-- Submenu -->
       <div class="submenu" id="submenuPanel">
    <a href="index.php?page=pengajuan_Magang" class="nav-item <?= $page=='pengajuan_Magang'?'active':'' ?>">
        <i class="fas fa-user"></i> Pengajuan Ketua
    </a>
    <a href="index.php?page=pengajuan_Kelompok" class="nav-item <?= $page=='pengajuan_Kelompok'?'active':'' ?>">
        <i class="fas fa-users"></i> Pengajuan Kelompok
    </a>
    <a href="index.php?page=pengajuan_Mitra" class="nav-item <?= $page=='pengajuan_Mitra'?'active':'' ?>">
        <i class="fas fa-building"></i> Pengajuan Mitra
    </a>
    <a href="index.php?page=berkas_Magang" class="nav-item <?= $page=='berkas_Magang'?'active':'' ?>">
        <i class="fas fa-file-upload"></i> Berkas Magang
    </a>
</div>


        <a href="index.php?page=absensi_Kegiatan" class="nav-item <?= $page=='absensi_Kegiatan'?'active':'' ?>">
          <i class="fas fa-tasks"></i> <span>Absensi & Kegiatan</span>
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

          <div class="user-profile">
            <a href="Akun.php">
              <img src="<?= $foto_profil_path ?>" alt="Foto Profil" class="profile-pic" style="cursor:pointer;" />
            </a>
          </div>

          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Mencari" />
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

    <!-- JS Toggle -->
<script>
  const dropdown = document.getElementById("dropdownMagang");
  const submenu = document.getElementById("submenuPanel");
  const arrow = document.getElementById("arrowIcon");

  dropdown.addEventListener("click", () => {
      submenu.style.display = submenu.style.display === "block" ? "none" : "block";
      arrow.classList.toggle("rotate");
  });

  // Auto open if child page active
  <?php if ($page=='pengajuan_Magang' || $page=='pengajuan_Kelompok' || $page=='pengajuan_Mitra' || $page=='berkas_Magang'): ?>
      submenu.style.display = "block";
      arrow.classList.add("rotate");
  <?php endif; ?>
</script>

  </body>
</html>
