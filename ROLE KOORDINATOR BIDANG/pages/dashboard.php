<?php
// pages/dashboard.php
// Dashboard Koordinator Bidang Magang

include '../Koneksi/koneksi.php';

// --- 1. STATS: Pengajuan Magang Pending ---
$q_pending_magang = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengajuan_magang WHERE status_pengajuan IN ('menunggu', 'menunggu_mitra')");
$pending_magang = ($q_pending_magang) ? mysqli_fetch_assoc($q_pending_magang)['total'] : 0;

// --- 2. STATS: Mitra Baru Pending ---
$q_pending_mitra = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengajuan_mitra WHERE status_pengajuan = 'menunggu'");
$pending_mitra = ($q_pending_mitra) ? mysqli_fetch_assoc($q_pending_mitra)['total'] : 0;

// --- 3. STATS: Mahasiswa Magang Aktif ---
$q_mhs_aktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa WHERE status_magang = 'magang_aktif'");
$mhs_aktif = ($q_mhs_aktif) ? mysqli_fetch_assoc($q_mhs_aktif)['total'] : 0;

// --- 4. STATS: Total Mitra Kerjasama ---
$q_total_mitra = mysqli_query($conn, "SELECT COUNT(*) as total FROM mitra_perusahaan WHERE status = 'aktif'");
$total_mitra = ($q_total_mitra) ? mysqli_fetch_assoc($q_total_mitra)['total'] : 0;

// --- 5. CHART DATA: Status Mahasiswa ---
$q_chart_status = mysqli_query($conn, "SELECT status_magang, COUNT(*) as jumlah FROM mahasiswa GROUP BY status_magang");
$chart_status_data = [];
if ($q_chart_status) {
    while($row = mysqli_fetch_assoc($q_chart_status)) {
        $label = ucwords(str_replace('_', ' ', $row['status_magang']));
        $chart_status_data[] = ['label' => $label, 'value' => (int)$row['jumlah']];
    }
}

// --- 6. LIST PRIORITAS: 5 Pengajuan Terbaru ---
$q_list_pending = mysqli_query($conn, "
    SELECT pm.id_pengajuan, k.nama_kelompok, m.nama_mitra, pm.tanggal_pengajuan 
    FROM pengajuan_magang pm
    JOIN kelompok k ON pm.id_kelompok = k.id_kelompok
    JOIN mitra_perusahaan m ON pm.id_mitra = m.id_mitra
    WHERE pm.status_pengajuan = 'menunggu'
    ORDER BY pm.tanggal_pengajuan ASC 
    LIMIT 5
");

// --- 7. PETA SEBARAN: Ambil Data Mitra ---
$queryMitraPeta = mysqli_query($conn, "
    SELECT 
        mp.id_mitra,
        mp.nama_mitra, 
        mp.bidang, 
        mp.alamat,
        mp.latitude,
        mp.longitude,
        (SELECT COUNT(*) 
         FROM pengajuan_magang pm 
         WHERE pm.id_mitra = mp.id_mitra 
         AND pm.status_pengajuan = 'diterima') as jumlah_mhs
    FROM mitra_perusahaan mp 
    WHERE mp.status = 'aktif'
");

$map_data = [];
if ($queryMitraPeta) {
    while ($m = mysqli_fetch_assoc($queryMitraPeta)) {
        
        $lat = $m['latitude'];
        $lng = $m['longitude'];
        $is_real = true;

        // PERBAIKAN: Cek NULL atau 0 dengan lebih tepat
        if ($lat === null || $lng === null || $lat == 0 || $lng == 0) {
            $lat = -8.172 + (mt_rand(-50, 50) / 1000); 
            $lng = 113.700 + (mt_rand(-50, 50) / 1000);
            $is_real = false; 
        }

        $map_data[] = [
            'id' => $m['id_mitra'],
            'name' => htmlspecialchars($m['nama_mitra']),
            'bidang' => htmlspecialchars($m['bidang']),
            'alamat' => htmlspecialchars($m['alamat']),
            'lat' => (float)$lat,
            'lng' => (float)$lng,
            'jumlah_mhs' => (int)$m['jumlah_mhs'],
            'is_real' => $is_real
        ];
    }
}
?>

<!-- CSS: PERBAIKAN - Tanpa ../ karena load dari root via index.php -->
<link rel="stylesheet" href="styles/dashboard.css?v=<?= time(); ?>">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="dashboard-coord">
    <div class="stats-grid">
        <div class="stat-card urgent clickable" onclick="window.location.href='index.php?page=persetujuan_magang_korbid'">
            <div class="stat-icon-bg red"><i class="fas fa-file-contract"></i></div>
            <div class="stat-content">
                <p>Approval Magang</p>
                <h3><?= $pending_magang ?> <span class="unit">Pending</span></h3>
            </div>
            <?php if($pending_magang > 0): ?>
                <div class="notification-badge pulse">!</div>
            <?php endif; ?>
        </div>

        <div class="stat-card warning clickable" onclick="window.location.href='index.php?page=persetujuan_mitra_korbid'">
            <div class="stat-icon-bg orange"><i class="fas fa-handshake"></i></div>
            <div class="stat-content">
                <p>Approval Mitra</p>
                <h3><?= $pending_mitra ?> <span class="unit">Baru</span></h3>
            </div>
        </div>

        <div class="stat-card info clickable" onclick="window.location.href='index.php?page=data_Mahasiswa'">
            <div class="stat-icon-bg blue"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-content">
                <p>Mahasiswa Magang</p>
                <h3><?= $mhs_aktif ?> <span class="unit">Aktif</span></h3>
            </div>
        </div>

        <div class="stat-card success clickable" onclick="window.location.href='index.php?page=data_Mitra'">
            <div class="stat-icon-bg green"><i class="fas fa-building"></i></div>
            <div class="stat-content">
                <p>Total Mitra</p>
                <h3><?= $total_mitra ?> <span class="unit">Instansi</span></h3>
            </div>
        </div>
    </div>

    <div class="dashboard-layout">
        
        <div class="layout-left">
            <div class="content-card map-section">
                <div class="card-header">
                    <h4><i class="fas fa-map-marked-alt"></i> Sebaran Mitra Magang</h4>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 350px; width: 100%; border-radius: 8px; z-index: 1;"></div>
                    
                    <div style="margin-top: 10px; font-size: 11px; color: #64748b; display:flex; gap:15px;">
                        <span><i class="fas fa-map-marker-alt" style="color:#2563eb;"></i> Lokasi Terverifikasi</span>
                        <span><i class="fas fa-map-marker-alt" style="color:#94a3b8;"></i> Lokasi Belum Diset (Estimasi)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="layout-right">
            <div class="content-card todo-section">
                <div class="card-header">
                    <h4><i class="fas fa-list-ul"></i> Perlu Diproses</h4>
                    <a href="index.php?page=persetujuan_magang_korbid" class="see-all">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <?php if($q_list_pending && mysqli_num_rows($q_list_pending) > 0): ?>
                        <div class="todo-list">
                            <?php while($item = mysqli_fetch_assoc($q_list_pending)): ?>
                                <div class="todo-item" onclick="window.location.href='index.php?page=persetujuan_magang_korbid'">
                                    <div class="todo-icon"><i class="fas fa-clock"></i></div>
                                    <div class="todo-info">
                                        <span class="todo-title"><?= htmlspecialchars($item['nama_kelompok']) ?></span>
                                        <span class="todo-subtitle">Ke: <?= htmlspecialchars($item['nama_mitra']) ?></span>
                                    </div>
                                    <div class="todo-date"><?= date('d M', strtotime($item['tanggal_pengajuan'])) ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p>Tidak ada pengajuan pending.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-card quick-links">
                <div class="card-header">
                    <h4><i class="fas fa-bolt"></i> Aksi Cepat</h4>
                </div>
                <div class="card-body action-grid">
                    <button onclick="window.location.href='index.php?page=data_Mitra'" class="action-btn">
                        <i class="fas fa-building"></i> Data Mitra
                    </button>
                    <button onclick="window.location.href='index.php?page=data_Kelompok'" class="action-btn">
                        <i class="fas fa-layer-group"></i> Kelompok
                    </button>
                    <button onclick="window.location.href='index.php?page=data_Dospem'" class="action-btn">
                        <i class="fas fa-chalkboard-teacher"></i> Dospem
                    </button>
                    <button onclick="window.location.href='index.php?page=persetujuan_mitra_korbid'" class="action-btn">
                        <i class="fas fa-handshake"></i> ACC Mitra
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // PASSING DATA KE JS
    const mapData = <?= json_encode($map_data) ?>;
    const chartData = <?= json_encode($chart_status_data) ?>;
    
    // DEBUG
    console.log('=== DEBUG INFO ===');
    console.log('Total Mitra:', mapData.length);
    console.log('Map Data:', mapData);
    console.log('==================');
</script>

<!-- JS: PERBAIKAN - Tanpa ../ karena load dari root via index.php -->
<script>
/**
 * Dashboard Koordinator Logic (Chart & Map)
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JS Loaded!');
    initMap();
});

// LEAFLET MAP INITIALIZATION
function initMap() {
    const mapElement = document.getElementById('map');
    
    if(!mapElement) {
        console.error('❌ Element #map tidak ditemukan!');
        return;
    }

    console.log('✅ Element #map ditemukan');

    if (typeof L === 'undefined') {
        console.error('❌ Leaflet library belum dimuat!');
        return;
    }

    console.log('✅ Leaflet library tersedia');

    try {
        const map = L.map('map').setView([-8.1731, 113.7035], 11);
        console.log('✅ Peta diinisialisasi');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);
        console.log('✅ Tile layer ditambahkan');

        if (typeof mapData === 'undefined' || !Array.isArray(mapData)) {
            console.error('❌ Variable mapData tidak ditemukan!');
            return;
        }

        console.log('✅ Data mitra:', mapData.length, 'items');

        if (mapData.length === 0) {
            console.warn('⚠️ Tidak ada data mitra');
            return;
        }

        const markersGroup = L.featureGroup();
        let markerCount = 0;

        mapData.forEach((p, index) => {
            
            console.log(`Marker ${index + 1}:`, p.name, `(${p.lat}, ${p.lng})`);
            
            if (!p.lat || !p.lng || isNaN(p.lat) || isNaN(p.lng)) {
                console.warn(`⚠️ Koordinat tidak valid untuk ${p.name}`);
                return;
            }

            const statusLokasi = p.is_real 
                ? '<span style="color:green; font-weight:bold;">✓ Terverifikasi</span>' 
                : '<span style="color:orange; font-weight:bold;">⚠ Estimasi</span>';

            let popupContent = `
                <div style="min-width: 200px; font-family: sans-serif;">
                    <h4 style="margin: 0 0 5px; color: #262A39; border-bottom:1px solid #eee; padding-bottom:5px;">
                        ${p.name}
                    </h4>
                    <div style="font-size: 11px; margin-bottom: 8px;">
                        <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #64748b;">
                            ${p.bidang}
                        </span>
                    </div>
                    <p style="margin: 5px 0; font-size: 12px;">
                        <i class="fas fa-map-marker-alt"></i> ${statusLokasi}
                    </p>
                    <p style="margin: 5px 0; font-size: 12px;">
                        <i class="fas fa-users"></i> <b>${p.jumlah_mhs}</b> Mahasiswa
                    </p>
                    <p style="margin: 5px 0 0; font-size: 11px; color: #94a3b8; font-style: italic;">
                        ${p.alamat}
                    </p>
                </div>
            `;

            const iconColor = p.is_real ? 'blue' : 'grey';
            
            const marker = L.marker([p.lat, p.lng], {
                icon: L.icon({
                    iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${iconColor}.png`,
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            })
            .addTo(map)
            .bindPopup(popupContent);
            
            if(p.is_real && markerCount === 0) {
                marker.openPopup();
            }

            markersGroup.addLayer(marker);
            markerCount++;
        });

        console.log(`✅ ${markerCount} marker ditambahkan`);

        if (markerCount > 0) {
            map.fitBounds(markersGroup.getBounds().pad(0.1));
            console.log('✅ Auto-zoom ke semua marker');
        }

    } catch (error) {
        console.error('❌ Error:', error);
    }
}
</script>