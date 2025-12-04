// ========================================
// LOGBOOK.JS - Main JavaScript Handler
// ========================================

// Global variables
let video, canvas, stream, currentLocation, capturedData;
let cameraActive = false;

// ========================================
// TAB SWITCHING
// ========================================
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.add('active');
    
    // Add active class to selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Stop camera if switching away from absensi
    if (tabName !== 'absensi' && cameraActive) {
        stopCamera();
    }
}

// ========================================
// MODAL FUNCTIONS
// ========================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        
        // Special handling for camera modal
        if (modalId === 'modalCamera') {
            stopCamera();
            resetCameraUI();
        }
        
        // Special handling for kegiatan modal
        if (modalId === 'modalKegiatan') {
            resetKegiatanForm();
        }
    }
}

// Close modal when clicking overlay
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        closeModal(e.target.id);
    }
});

// ========================================
// CAMERA FUNCTIONS
// ========================================
async function startCamera() {
    try {
        video = document.getElementById('video');
        canvas = document.getElementById('canvas');
        
        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        });
        
        video.srcObject = stream;
        cameraActive = true;
        
        // Start getting location
        updateLocation();
        
    } catch (err) {
        alert('Tidak dapat mengakses kamera: ' + err.message);
        console.error('Camera error:', err);
    }
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
        cameraActive = false;
    }
}

function updateLocation() {
    const liveLocation = document.getElementById('liveLocation');
    
    if (!navigator.geolocation) {
        liveLocation.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Geolocation tidak didukung';
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`
                );
                const data = await response.json();
                const address = data.display_name || 'Lokasi tidak diketahui';
                
                currentLocation = {
                    lat: lat.toFixed(6),
                    lon: lon.toFixed(6),
                    address: address
                };
                
                liveLocation.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${currentLocation.lat}, ${currentLocation.lon}`;
            } catch (err) {
                currentLocation = {
                    lat: lat.toFixed(6),
                    lon: lon.toFixed(6),
                    address: 'Tidak dapat mendapatkan alamat'
                };
                
                liveLocation.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${currentLocation.lat}, ${currentLocation.lon}`;
            }
        },
        (error) => {
            liveLocation.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Lokasi tidak tersedia';
            console.error('Location error:', error);
        }
    );
}

function capturePhoto() {
    if (!currentLocation) {
        alert('Menunggu lokasi terdeteksi...');
        return;
    }
    
    video = document.getElementById('video');
    canvas = document.getElementById('canvas');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    const imageData = canvas.toDataURL('image/jpeg', 0.9);
    const capturedImage = document.getElementById('capturedImage');
    capturedImage.src = imageData;
    
    const now = new Date();
    const dateStr = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('id-ID');
    
    const photoTimestamp = document.getElementById('photoTimestamp');
    photoTimestamp.innerHTML = `
        <div class="timestamp-date">${dateStr}</div>
        <div class="timestamp-time">${timeStr}</div>
        <div class="timestamp-location">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <div>${currentLocation.address}</div>
                <div class="timestamp-coords">${currentLocation.lat}, ${currentLocation.lon}</div>
            </div>
        </div>
    `;
    
    capturedData = {
        image: imageData,
        timestamp: now.toISOString(),
        dateFormatted: dateStr,
        timeFormatted: timeStr,
        location: currentLocation
    };
    
    // Update UI
    document.getElementById('cameraSection').style.display = 'none';
    document.getElementById('previewSection').style.display = 'block';
    document.getElementById('captureBtn').style.display = 'none';
    document.getElementById('saveAbsenBtn').style.display = 'inline-flex';
    document.getElementById('retakeBtn').style.display = 'inline-flex';
    
    stopCamera();
}

function retakePhoto() {
    resetCameraUI();
    startCamera();
}

function resetCameraUI() {
    document.getElementById('cameraSection').style.display = 'block';
    document.getElementById('previewSection').style.display = 'none';
    document.getElementById('captureBtn').style.display = 'inline-flex';
    document.getElementById('saveAbsenBtn').style.display = 'none';
    document.getElementById('retakeBtn').style.display = 'none';
    capturedData = null;
}

async function saveAbsensi() {
    if (!capturedData) {
        alert('Tidak ada foto yang diambil.');
        return;
    }
    
    const saveBtn = document.getElementById('saveAbsenBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    const formData = new FormData();
    formData.append('foto', capturedData.image);
    formData.append('lokasi', capturedData.location.address);
    formData.append('latitude', capturedData.location.lat);
    formData.append('longitude', capturedData.location.lon);
    formData.append('timestamp', capturedData.timestamp);
    
    try {
        const response = await fetch('pages/save_absensi_logbook.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Gagal: ' + result.message);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    } catch (error) {
        alert('Terjadi kesalahan saat menyimpan.');
        console.error('Save error:', error);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

// ========================================
// KEGIATAN FUNCTIONS
// ========================================
function openTambahKegiatanModal() {
    document.getElementById('modalKegiatanTitle').textContent = 'Tambah Kegiatan';
    document.getElementById('editIdDetail').value = '';
    resetKegiatanForm();
    openModal('modalKegiatan');
}

function resetKegiatanForm() {
    const form = document.getElementById('formKegiatan');
    if (form) {
        form.reset();
        document.getElementById('editIdDetail').value = '';
        document.getElementById('charCount').textContent = '0';
        removeFotoPreview();
    }
}

function previewFotoKegiatan(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (max 5MB)
        if (file.size > 5242880) {
            alert('Ukuran file maksimal 5MB');
            input.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file harus JPG, JPEG, atau PNG');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFotoKegiatanImg').src = e.target.result;
            document.getElementById('previewFotoKegiatan').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function removeFotoPreview() {
    document.getElementById('fotoKegiatan').value = '';
    document.getElementById('previewFotoKegiatan').style.display = 'none';
    document.getElementById('previewFotoKegiatanImg').src = '';
}

async function editKegiatan(idDetail) {
    try {
        const response = await fetch(`pages/get_detail_kegiatan.php?id_detail=${idDetail}`);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Fill form
            document.getElementById('modalKegiatanTitle').textContent = 'Edit Kegiatan';
            document.getElementById('editIdDetail').value = data.id_detail;
            document.getElementById('jamMulai').value = data.jam_mulai;
            document.getElementById('jamSelesai').value = data.jam_selesai;
            document.getElementById('deskripsiKegiatan').value = data.deskripsi_kegiatan;
            document.getElementById('charCount').textContent = data.deskripsi_kegiatan.length;
            
            // Show existing photo if available
            if (data.foto_kegiatan) {
                document.getElementById('previewFotoKegiatanImg').src = 'uploads/' + data.foto_kegiatan;
                document.getElementById('previewFotoKegiatan').style.display = 'block';
            }
            
            openModal('modalKegiatan');
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat mengambil data.');
        console.error('Edit error:', error);
    }
}

async function deleteKegiatan(idDetail) {
    if (!confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id_detail', idDetail);
    
    try {
        const response = await fetch('pages/delete_kegiatan_logbook.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat menghapus.');
        console.error('Delete error:', error);
    }
}

// ========================================
// RIWAYAT FUNCTIONS
// ========================================
async function lihatDetailRiwayat(idLogbook) {
    openModal('modalDetailRiwayat');
    
    const contentDiv = document.getElementById('detailRiwayatContent');
    contentDiv.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`pages/get_detail_riwayat.php?id_logbook=${idLogbook}`);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            const logbook = data.logbook;
            const kegiatan = data.kegiatan;
            
            // Update modal title
            document.getElementById('detailRiwayatTanggal').textContent = 
                '📅 ' + logbook.tanggal_formatted;
            
            // Build HTML content
            let html = `
                <!-- Absensi Section -->
                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-camera"></i>
                        Data Absensi
                    </h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Waktu Absensi</label>
                            <div class="detail-value">
                                <i class="fas fa-clock"></i>
                                ${logbook.jam_absensi_formatted} WIB
                            </div>
                        </div>
                        <div class="detail-item">
                            <label>Lokasi</label>
                            <div class="detail-value">
                                <i class="fas fa-map-marker-alt"></i>
                                ${logbook.lokasi_absensi}
                            </div>
                        </div>
                        <div class="detail-item full-width">
                            <label>Foto Absensi</label>
                            <img src="uploads/${logbook.foto_absensi}" 
                                 alt="Foto Absensi" 
                                 class="detail-photo"
                                 onclick="openImageModal(this.src)">
                        </div>
                    </div>
                </div>
                
                <!-- Kegiatan Section -->
                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-clipboard-list"></i>
                        Detail Kegiatan (${data.jumlah_kegiatan})
                    </h4>
            `;
            
            if (kegiatan.length > 0) {
                html += '<div class="kegiatan-list">';
                kegiatan.forEach((k, idx) => {
                    html += `
                        <div class="kegiatan-item">
                            <div class="kegiatan-number">${idx + 1}</div>
                            <div class="kegiatan-content">
                                <div class="kegiatan-time">
                                    <i class="fas fa-clock"></i>
                                    ${k.jam_mulai.substring(0, 5)} - ${k.jam_selesai.substring(0, 5)}
                                </div>
                                <div class="kegiatan-text">
                                    ${k.deskripsi_kegiatan.replace(/\n/g, '<br>')}
                                </div>
                                ${k.foto_kegiatan ? `
                                    <img src="uploads/${k.foto_kegiatan}" 
                                         alt="Foto Kegiatan" 
                                         class="kegiatan-photo"
                                         onclick="openImageModal(this.src)">
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html += `
                    <div class="empty-kegiatan">
                        <i class="fas fa-inbox"></i>
                        <p>Tidak ada kegiatan yang dicatat</p>
                    </div>
                `;
            }
            
            html += '</div>';
            
            // Status Validasi Section
            html += `
                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-check-circle"></i>
                        Status Validasi
                    </h4>
                    <div class="validation-status">
            `;
            
            if (logbook.status_validasi === 'pending') {
                html += `
                    <div class="status-box status-pending">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Menunggu Validasi</strong>
                            <p>Logbook ini sedang menunggu validasi dari dosen pembimbing.</p>
                        </div>
                    </div>
                `;
            } else if (logbook.status_validasi === 'disetujui') {
                html += `
                    <div class="status-box status-approved">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Disetujui</strong>
                            <p>Divalidasi oleh: ${logbook.nama_dosen_validator || 'Dosen Pembimbing'}</p>
                            <p>Tanggal: ${logbook.tanggal_validasi_formatted || '-'}</p>
                        </div>
                    </div>
                `;
            } else if (logbook.status_validasi === 'ditolak') {
                html += `
                    <div class="status-box status-rejected">
                        <i class="fas fa-times-circle"></i>
                        <div>
                            <strong>Ditolak</strong>
                            <p>Divalidasi oleh: ${logbook.nama_dosen_validator || 'Dosen Pembimbing'}</p>
                            <p>Tanggal: ${logbook.tanggal_validasi_formatted || '-'}</p>
                            ${logbook.catatan_dosen ? `<p class="catatan"><strong>Catatan:</strong> ${logbook.catatan_dosen}</p>` : ''}
                        </div>
                    </div>
                `;
            }
            
            html += `
                    </div>
                </div>
            `;
            
            contentDiv.innerHTML = html;
            
        } else {
            contentDiv.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${result.message}</p>
                </div>
            `;
        }
    } catch (error) {
        contentDiv.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Terjadi kesalahan saat memuat data.</p>
            </div>
        `;
        console.error('Fetch error:', error);
    }
}

// ========================================
// IMAGE MODAL
// ========================================
function openImageModal(imageSrc) {
    document.getElementById('modalImage').classList.add('show');
    document.getElementById('modalImageContent').src = imageSrc;
    document.body.style.overflow = 'hidden';
}

// ========================================
// EVENT LISTENERS
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Open Camera Button
    const btnOpenCamera = document.getElementById('btnOpenCamera');
    if (btnOpenCamera) {
        btnOpenCamera.addEventListener('click', function() {
            openModal('modalCamera');
            setTimeout(() => startCamera(), 300);
        });
    }
    
    // Capture Button
    const captureBtn = document.getElementById('captureBtn');
    if (captureBtn) {
        captureBtn.addEventListener('click', capturePhoto);
    }
    
    // Retake Button
    const retakeBtn = document.getElementById('retakeBtn');
    if (retakeBtn) {
        retakeBtn.addEventListener('click', retakePhoto);
    }
    
    // Save Absensi Button
    const saveAbsenBtn = document.getElementById('saveAbsenBtn');
    if (saveAbsenBtn) {
        saveAbsenBtn.addEventListener('click', saveAbsensi);
    }
    
    // Tambah Kegiatan Button
    const btnTambahKegiatan = document.getElementById('btnTambahKegiatan');
    if (btnTambahKegiatan) {
        btnTambahKegiatan.addEventListener('click', openTambahKegiatanModal);
    }
    
    // Form Kegiatan Submit
    const formKegiatan = document.getElementById('formKegiatan');
    if (formKegiatan) {
        formKegiatan.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('pages/save_kegiatan_logbook.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + result.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                alert('Terjadi kesalahan saat menyimpan.');
                console.error('Submit error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    // Character Counter for Deskripsi
    const deskripsiKegiatan = document.getElementById('deskripsiKegiatan');
    if (deskripsiKegiatan) {
        deskripsiKegiatan.addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });
    }
});

// Make functions globally available
window.switchTab = switchTab;
window.openModal = openModal;
window.closeModal = closeModal;
window.editKegiatan = editKegiatan;
window.deleteKegiatan = deleteKegiatan;
window.lihatDetailRiwayat = lihatDetailRiwayat;
window.openImageModal = openImageModal;
window.previewFotoKegiatan = previewFotoKegiatan;
window.removeFotoPreview = removeFotoPreview;