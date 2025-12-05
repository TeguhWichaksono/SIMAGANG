<?php
include 'C:/xampp/htdocs/SIMAGANG/Koneksi/koneksi.php';

// Hitung jumlah mahasiswa
$queryMahasiswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa");
$dataMahasiswa = mysqli_fetch_assoc($queryMahasiswa);
$totalMahasiswa = $dataMahasiswa['total'];

// Hitung jumlah dosen pembimbing
$queryDosen = mysqli_query($conn, "SELECT COUNT(*) as total FROM dosen");
$dataDosen = mysqli_fetch_assoc($queryDosen);
$totalDosen = $dataDosen['total'];

// Hitung jumlah mitra/perusahaan
$queryMitra = mysqli_query($conn, "SELECT COUNT(*) as total FROM mitra_perusahaan");
$dataMitra = mysqli_fetch_assoc($queryMitra);
$totalMitra = $dataMitra['total'];

// Hitung mahasiswa aktif (yang sedang magang)
$queryMahasiswaAktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa WHERE status_magang = 'magang_aktif'");
$dataMahasiswaAktif = mysqli_fetch_assoc($queryMahasiswaAktif);
$totalMahasiswaAktif = $dataMahasiswaAktif['total'];

// Hitung mahasiswa yang sudah ditempatkan
$queryDitempatkan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengajuan_mitra WHERE status_pengajuan = 'disetujui'");
$dataDitempatkan = mysqli_fetch_assoc($queryDitempatkan);
$totalDitempatkan = $dataDitempatkan['total'];

// Hitung persentase mahasiswa ditempatkan
$persenDitempatkan = $totalMahasiswa > 0 ? round(($totalDitempatkan / $totalMahasiswa) * 100) : 0;

// Ambil aktivitas terbaru dari log_aktivitas
$queryAktivitas = mysqli_query($conn, "SELECT * FROM log_aktivitas ORDER BY waktu DESC LIMIT 5");

// Ambil data mitra untuk pemetaan (dengan koordinat)
$queryMitraPeta = mysqli_query($conn, "SELECT nama_mitra, alamat, bidang FROM mitra_perusahaan WHERE status = 'aktif' LIMIT 20");
?>

<link rel="stylesheet" href="styles/dashboard.css" />
<!-- Dashboard Container -->
<div class="dashboard-container">
  <!-- Main Dashboard Content -->
  <div class="dashboard-main">
    <!-- Transfer Cards -->
    <div class="transfer-cards">
      <!-- Card Mahasiswa - Klik ke halaman data mahasiswa -->
      <div class="transfer-card clickable" onclick="window.location.href='index.php?page=data_Mahasiswa'">
        <div class="card-icon">
          <i class="fas fa-users"></i>
        </div>
        <p class="card-title">Jumlah Mahasiswa Magang</p>
        <h2 class="card-amount"><?= $totalMahasiswa ?> Mahasiswa</h2>
      </div>

      <!-- Card Dosen - Klik ke halaman data dosen -->
      <div class="transfer-card clickable" onclick="window.location.href='index.php?page=data_dospem'">
        <div class="card-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <p class="card-title">Jumlah Dosen Pembimbing</p>
        <h2 class="card-amount"><?= $totalDosen ?> Dosen</h2>
      </div>

      <!-- Card Mitra - Klik ke halaman data mitra -->
      <div class="transfer-card clickable" onclick="window.location.href='index.php?page=data_Mitra'">
        <div class="card-icon">
          <i class="fas fa-building"></i>
        </div>
        <p class="card-title">Jumlah Instansi/Perusahaan Mitra</p>
        <h2 class="card-amount"><?= $totalMitra ?> Mitra</h2>
      </div>
    </div>

    <!-- Pemetaan Mitra (Jember) -->
    <div class="transaction-section">
      <div class="transaction-card">
        <h3 class="section-title">Pemetaan Mitra - Jember</h3>
        <div id="map"></div>
      </div>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="transaction-section">
      <div class="transaction-card">
        <h3 class="section-title">Aktivitas Terbaru</h3>
        
        <?php if (mysqli_num_rows($queryAktivitas) > 0) : ?>
          <?php while ($aktivitas = mysqli_fetch_assoc($queryAktivitas)) : ?>
            <div class="transaction-item">
              <div class="transaction-icon">
                <i class="fas fa-file-upload"></i>
              </div>
              <div class="transaction-content">
                <div class="transaction-title"><?= htmlspecialchars($aktivitas['aktivitas']) ?></div>
                <div class="transaction-time">
                  <i class="far fa-clock"></i> <?= date('d M Y, H:i', strtotime($aktivitas['waktu'])) ?>
                </div>
              </div>
              <div class="transaction-amount positive">Selesai</div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <div class="transaction-item">
            <div class="transaction-content">
              <div class="transaction-title">Belum ada aktivitas terbaru</div>
            </div>
          </div>
        <?php endif; ?>
        
      </div>
    </div>
  </div>

  <!-- Dashboard Sidebar -->
  <div class="dashboard-sidebar">
    <div class="savings-card">
      <h3 class="savings-title">Jumlah Mahasiswa Aktif</h3>
      <div class="savings-amount"><?= $totalMahasiswaAktif ?> Mahasiswa</div>
      <div class="time-filter">
        <button class="time-option">Harian</button>
        <button class="time-option">Mingguan</button>
        <button class="time-option active">Bulanan</button>
        <button class="time-option">Lainnya</button>
      </div>

      <div class="chart-container">
        <svg class="chart" viewBox="0 0 300 100" preserveAspectRatio="none">
          <defs>
            <linearGradient id="gradientFill" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="#4270F4" stop-opacity="0.7" />
              <stop offset="100%" stop-color="#4270F4" stop-opacity="0.1" />
            </linearGradient>
          </defs>
          <path
            class="chart-line-path"
            d="M0,80 C20,70 40,30 60,60 C80,90 100,40 120,30 C140,20 160,50 180,20 C200,30 220,60 240,80 C260,60 280,40 300,60"></path>
          <path
            class="chart-area"
            d="M0,80 C20,70 40,30 60,60 C80,90 100,40 120,30 C140,20 160,50 180,20 C200,30 220,60 240,80 C260,60 280,40 300,60 L300,100 L0,100 Z"></path>
          <circle cx="180" cy="20" r="6" fill="#4270F4" stroke="#ffffff" stroke-width="3" />
        </svg>
      </div>

      <div class="timeline">
        <div class="month">Okt</div>
        <div class="month">Nov</div>
        <div class="month active">Des</div>
        <div class="month">Jan</div>
        <div class="month">Feb</div>
        <div class="month">Mar</div>
      </div>
    </div>

    <div class="plan-card">
      <div class="plan-info">
        <div class="plan-title">Mahasiswa Sudah Ditempatkan</div>
        <div class="plan-status">Proses</div>
      </div>
      <div class="plan-progress">
<<<<<<< HEAD
        <div class="plan-percentage">68%</div>
=======
        <div class="plan-percentage"><?= $persenDitempatkan ?>%</div>
>>>>>>> origin/arilmun
      </div>
    </div>
  </div>
</div>

<!-- Leaflet JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

<script>
  var map = L.map('map').setView([-8.1731, 113.7035], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  var markersGroup = L.markerClusterGroup();

  // Data mitra dari database
  var mitra = [
    <?php 
    $mitraArray = [];
    while ($mitraPeta = mysqli_fetch_assoc($queryMitraPeta)) {
      // Generate koordinat random di sekitar Jember untuk demo
      $lat = -8.1731 + (rand(-100, 100) / 1000);
      $lng = 113.7035 + (rand(-100, 100) / 1000);
      
      echo "{
        name: '" . addslashes($mitraPeta['nama_mitra']) . "',
        coords: [" . $lat . ", " . $lng . "],
        address: '" . addslashes($mitraPeta['alamat']) . "',
        bidang: '" . addslashes($mitraPeta['bidang']) . "'
      },";
    }
    ?>
  ];

  mitra.forEach(m => {
    var marker = L.marker(m.coords).bindPopup(`
      <b>${m.name}</b><br>
      <i class="fas fa-map-marker-alt"></i> ${m.address}<br>
      <i class="fas fa-briefcase"></i> ${m.bidang}
    `);
    markersGroup.addLayer(marker);
  });

  map.addLayer(markersGroup);

  if (markersGroup.getLayers().length > 0) {
    map.fitBounds(markersGroup.getBounds(), { padding: [40, 40] });
  }
</script>
