/**
 * scripts/dashboard.js
 * Dashboard Koordinator Logic (Chart & Map)
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JS Loaded!');
    initMap();
});

// LEAFLET MAP INITIALIZATION
function initMap() {
    const mapElement = document.getElementById('map');
    
    // Safety Check: Pastikan elemen map ada
    if(!mapElement) {
        console.error('❌ Element #map tidak ditemukan!');
        return;
    }

    console.log('✅ Element #map ditemukan');

    // Cek apakah Leaflet sudah dimuat
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet library belum dimuat!');
        mapElement.innerHTML = '<div style="padding:20px; text-align:center; color:#ef4444;">Error: Library peta belum dimuat</div>';
        return;
    }

    console.log('✅ Leaflet library tersedia');

    try {
        // 1. Inisialisasi Peta (Default Center: Jember)
        const map = L.map('map').setView([-8.1731, 113.7035], 11);
        console.log('✅ Peta diinisialisasi');

        // 2. Tambahkan Tile Layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);
        console.log('✅ Tile layer ditambahkan');

        // 3. Cek apakah ada data
        if (typeof mapData === 'undefined' || !Array.isArray(mapData)) {
            console.error('❌ Variable mapData tidak ditemukan atau bukan array!');
            return;
        }

        console.log('✅ Data mitra ditemukan:', mapData.length, 'items');

        if (mapData.length === 0) {
            console.warn('⚠️ Tidak ada data mitra untuk ditampilkan');
            return;
        }

        // 4. Tambahkan Marker dari Data
        const markersGroup = L.featureGroup();
        let markerCount = 0;

        mapData.forEach((p, index) => {
            
            console.log(`Marker ${index + 1}:`, p.name, `(${p.lat}, ${p.lng})`);
            
            // Validasi koordinat
            if (!p.lat || !p.lng || isNaN(p.lat) || isNaN(p.lng)) {
                console.warn(`⚠️ Koordinat tidak valid untuk ${p.name}`);
                return;
            }

            // Tentukan status validitas lokasi
            const statusLokasi = p.is_real 
                ? '<span style="color:green; font-weight:bold;">✓ Terverifikasi</span>' 
                : '<span style="color:orange; font-weight:bold;">⚠ Estimasi (Belum Diset)</span>';

            // Popup content
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
                        <i class="fas fa-map-marker-alt"></i> Status: ${statusLokasi}
                    </p>
                    <p style="margin: 5px 0; font-size: 12px;">
                        <i class="fas fa-users"></i> <b>${p.jumlah_mhs}</b> Mahasiswa Aktif
                    </p>
                    <p style="margin: 5px 0 0; font-size: 11px; color: #94a3b8; font-style: italic;">
                        ${p.alamat}
                    </p>
                    <div style="margin-top:10px; text-align:right;">
                        <a href="index.php?page=data_Mitra" style="font-size:11px; color:#4270f4; text-decoration:none;">
                            Edit Lokasi &rarr;
                        </a>
                    </div>
                </div>
            `;

            // Create marker dengan icon custom berdasarkan status
            const iconColor = p.is_real ? 'blue' : 'gray';
            
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
            
            // Buka popup untuk marker pertama (yang real)
            if(p.is_real && markerCount === 0) {
                marker.openPopup();
            }

            markersGroup.addLayer(marker);
            markerCount++;
        });

        console.log(`✅ ${markerCount} marker ditambahkan ke peta`);

        // 5. Auto Zoom agar semua marker terlihat
        if (markerCount > 0) {
            map.fitBounds(markersGroup.getBounds().pad(0.1));
            console.log('✅ Peta di-zoom ke semua marker');
        }

    } catch (error) {
        console.error('❌ Error saat inisialisasi peta:', error);
        mapElement.innerHTML = '<div style="padding:20px; text-align:center; color:#ef4444;">Error: Gagal memuat peta. Cek console untuk detail.</div>';
    }
}