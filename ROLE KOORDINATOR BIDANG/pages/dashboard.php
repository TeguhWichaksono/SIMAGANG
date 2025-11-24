<link rel="stylesheet" href="styles/dashboard.css" />
<!-- Dashboard Container -->
<div class="dashboard-container">
  <!-- Main Dashboard Content -->
  <div class="dashboard-main">
    <!-- Transfer Cards -->
    <div class="transfer-cards">
      <div class="transfer-card">
        <div class="card-icon">
          <i class="fas fa-users"></i>
        </div>
        <p class="card-title">Jumlah Mahasiswa Magang</p>
        <h2 class="card-amount">94 Mahasiswa</h2>
      </div>

      <div class="transfer-card">
        <div class="card-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <p class="card-title">Jumlah Dosen Pembimbing</p>
        <h2 class="card-amount">45 Dosen</h2>
      </div>

      <div class="transfer-card">
        <div class="card-icon">
          <i class="fas fa-building"></i>
        </div>
        <!-- ✅ Revisi kecil pada teks -->
        <p class="card-title">Jumlah Instansi/perusahaan Mitra</p>
        <h2 class="card-amount">165 Mitra</h2>
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
        <div class="transaction-item">
          <div class="transaction-icon">
            <i class="fas fa-file-upload"></i>
          </div>
          <div class="transaction-content">
            <div class="transaction-title">Budi Santoso mengunggah Laporan Bab II</div>
            <div class="transaction-time">
              <i class="far fa-clock"></i> Today, 14:45
            </div>
          </div>
          <div class="transaction-amount positive">Selesai</div>
        </div>

        <div class="transaction-item">
          <div class="transaction-icon">
            <i class="fas fa-user-graduate"></i>
          </div>
          <div class="transaction-content">
            <div class="transaction-title">Siti Aminah ditempatkan di PT Telkom</div>
            <div class="transaction-time">
              <i class="far fa-clock"></i> Yesterday, 16:08
            </div>
          </div>
          <div class="transaction-amount positive">Selesai</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard Sidebar -->
  <div class="dashboard-sidebar">
    <div class="savings-card">
      <h3 class="savings-title">Jumlah Mahasiswa Aktif</h3>
      <div class="savings-amount">65 Mahasiswa</div>
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
        <div class="plan-percentage">68%</div>
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

  var mitra = [{
      name: "PT Jember Abadi",
      coords: [-8.1681, 113.7040],
      address: "Jl. Letjen Panjaitan No.1"
    },
    {
      name: "CV Jember Maju",
      coords: [-8.1800, 113.6950],
      address: "Jl. Gajah Mada No.12"
    },
    {
      name: "Koperasi Sejahtera",
      coords: [-8.1750, 113.7100],
      address: "Jl. Sudirman No.5"
    },
    {
      name: "PT Nusantara Jember",
      coords: [-8.1700, 113.7200],
      address: "Jl. Ahmad Yani No.25"
    },
    {
      name: "CV Sentosa",
      coords: [-8.1650, 113.6900],
      address: "Jl. Raya Jember No.8"
    }
  ];

  mitra.forEach(m => {
    var marker = L.marker(m.coords).bindPopup(`<b>${m.name}</b><br>${m.address}`);
    markersGroup.addLayer(marker);
  });

  map.addLayer(markersGroup);

  if (markersGroup.getLayers().length > 0) {
    map.fitBounds(markersGroup.getBounds(), {
      padding: [40, 40]
    });
  }
</script>