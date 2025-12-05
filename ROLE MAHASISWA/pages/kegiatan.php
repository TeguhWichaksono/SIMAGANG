<?php
// Cek session dan status magang
if (!isset($_SESSION['id'])) {
    header('Location: ../Login/login.php');
    exit;
}

// Ambil data mahasiswa
$id_user = $_SESSION['id'];
$query_mahasiswa = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query_mahasiswa);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mahasiswa = mysqli_fetch_assoc($result);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];
date_default_timezone_set('Asia/Jakarta');


// Cek apakah sudah isi kegiatan hari ini
$today = date('Y-m-d');
$query_check_kegiatan = "SELECT COUNT(*) as count FROM kegiatan_harian WHERE id_mahasiswa = ? AND tanggal = ?";
$stmt_check_kegiatan = mysqli_prepare($conn, $query_check_kegiatan);
mysqli_stmt_bind_param($stmt_check_kegiatan, 'is', $id_mahasiswa, $today);
mysqli_stmt_execute($stmt_check_kegiatan);
$result_check_kegiatan = mysqli_stmt_get_result($stmt_check_kegiatan);
$check_kegiatan = mysqli_fetch_assoc($result_check_kegiatan);
$sudah_kegiatan = $check_kegiatan['count'] > 0;

// Cek apakah sudah absen hari ini
$query_check_absen = "SELECT COUNT(*) as count FROM absensi WHERE id_mahasiswa = ? AND tanggal = ?";
$stmt_check_absen = mysqli_prepare($conn, $query_check_absen);
mysqli_stmt_bind_param($stmt_check_absen, 'is', $id_mahasiswa, $today);
mysqli_stmt_execute($stmt_check_absen);
$result_check_absen = mysqli_stmt_get_result($stmt_check_absen);
$check_absen = mysqli_fetch_assoc($result_check_absen);
$sudah_absen = $check_absen['count'] > 0;

// Ambil riwayat kegiatan
$query_kegiatan = "SELECT * FROM kegiatan_harian WHERE id_mahasiswa = ? ORDER BY tanggal DESC, id_kegiatan DESC LIMIT 50";
$stmt_kegiatan = mysqli_prepare($conn, $query_kegiatan);
mysqli_stmt_bind_param($stmt_kegiatan, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_kegiatan);
$result_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);

$can_crud = isset($_SESSION['can_crud_magang']) ? $_SESSION['can_crud_magang'] : false;
?>

<link rel="stylesheet" href="styles/kegiatan.css?v=<?= time(); ?>">

<div class="kegiatan-container">
    <!-- Alert Notification -->
    <?php if ($sudah_kegiatan && !$sudah_absen && $can_crud): ?>
    <div class="alert-warning" id="alertAbsensi">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>⚠️ Perhatian!</strong>
            <p>Anda sudah mengisi kegiatan hari ini, tapi <strong>belum absensi</strong>. Jangan lupa untuk melengkapinya!</p>
        </div>
        <a href="index.php?page=absensi" class="alert-link">
            <i class="fas fa-camera"></i> Absen Sekarang
        </a>
    </div>
    <?php elseif ($sudah_kegiatan && $sudah_absen && $can_crud): ?>
    <div class="alert-success" id="alertSuccess">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>✅ Sempurna!</strong>
            <p>Absensi & Kegiatan hari ini sudah lengkap! Tetap semangat! 🎉</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="content-grid">
        <!-- Left: Camera Section -->
        <div class="card camera-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-camera"></i>
                    Ambil Foto Kegiatan
                </h2>
            </div>

            <?php if ($can_crud): ?>
                <!-- Camera Active Section -->
                <div class="camera-section" id="cameraSection">
                    <div class="camera-wrapper">
                        <video id="video" autoplay playsinline></video>
                        <div class="camera-overlay">
                            <div class="camera-frame"></div>
                        </div>
                        <div class="camera-info" id="liveLocation">
                            <i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="preview-section" id="previewSection" style="display: none;">
                    <div class="captured-photo">
                        <img id="capturedImage" src="" alt="Captured Photo">
                        <div class="photo-overlay" id="photoTimestamp"></div>
                    </div>
                </div>

                <canvas id="canvas" style="display: none;"></canvas>

                <!-- Camera Controls -->
                <div class="camera-controls">
                    <button class="btn btn-primary" id="captureBtn">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <button class="btn btn-success" id="fillKegiatanBtn" style="display: none;">
                        <i class="fas fa-edit"></i> Isi Kegiatan
                    </button>
                    <button class="btn btn-danger" id="retakeBtn" style="display: none;">
                        <i class="fas fa-redo-alt"></i> Foto Ulang
                    </button>
                </div>
            <?php else: ?>
                <!-- Read-only Mode -->
                <div class="readonly-notice">
                    <i class="fas fa-eye"></i>
                    <h3>Mode Read-Only</h3>
                    <p>Magang Anda sudah selesai. Anda hanya dapat melihat riwayat kegiatan.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: History Table -->
        <div class="card history-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-history"></i>
                    Riwayat Kegiatan
                </h2>
            </div>

            <div class="table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <?php if (mysqli_num_rows($result_kegiatan) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_kegiatan)): 
                                $is_today = ($row['tanggal'] == $today);
                                $is_validated = ($row['status_validasi'] == 'disetujui' || $row['status_validasi'] == 'ditolak');
                                $can_delete = $is_today && !$is_validated && $can_crud;
                                
                                // Potong deskripsi untuk preview
                                $deskripsi_preview = strlen($row['deskripsi']) > 50 
                                    ? substr($row['deskripsi'], 0, 50) . '...' 
                                    : $row['deskripsi'];
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                    <td>
                                        <div class="deskripsi-preview" 
                                             onclick="openDeskripsiModal('<?= htmlspecialchars($row['deskripsi'], ENT_QUOTES) ?>', '<?= date('l, d F Y', strtotime($row['tanggal'])) ?>')">
                                            <i class="fas fa-file-alt"></i>
                                            <span><?= htmlspecialchars($deskripsi_preview) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <img src="uploads/<?= $row['lokasi_kegiatan'] ?>" 
                                             alt="Foto Kegiatan" 
                                             class="table-photo" 
                                             onclick="openImageModal(this.src)">
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($row['status_validasi']) ?>">
                                            <?php if ($row['status_validasi'] == 'disetujui'): ?>
                                                <i class="fas fa-check-circle"></i> Disetujui
                                            <?php elseif ($row['status_validasi'] == 'ditolak'): ?>
                                                <i class="fas fa-times-circle"></i> Ditolak
                                            <?php else: ?>
                                                <i class="fas fa-clock"></i> Pending
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($can_delete): ?>
                                            <button class="btn-delete" 
                                                    onclick="deleteKegiatan(<?= $row['id_kegiatan'] ?>)"
                                                    title="Hapus/Retake Foto">
                                                <i class="fas fa-trash-alt"></i> Hapus/Retake
                                            </button>
                                        <?php elseif ($is_validated): ?>
                                            <button class="btn-delete disabled" 
                                                    onclick="alert('Kegiatan sudah dicek oleh Dosen Validator')"
                                                    title="Sudah divalidasi">
                                                <i class="fas fa-lock"></i> Hapus/Retake
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-delete disabled" 
                                                    onclick="alert('Anda tidak bisa menghapus/retake pada hari lain selain hari ini')"
                                                    title="Hanya bisa hapus hari ini">
                                                <i class="fas fa-ban"></i> Hapus/Retake
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Belum ada riwayat kegiatan</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Deskripsi -->
<div class="modal-overlay" id="deskripsiInputModal">
    <div class="modal-content modal-deskripsi">
        <div class="modal-header">
            <h3>Deskripsikan Kegiatan Hari Ini</h3>
            <button class="close-modal" onclick="closeDeskripsiInputModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="textarea-wrapper">
                <textarea id="deskripsiInput" 
                          placeholder="Jelaskan kegiatan hari ini secara detail (minimal 150 karakter)..."></textarea>
                <div class="char-counter" id="charCounter">
                    <span id="currentChars">0</span> / 150
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-save-full" id="saveDeskripsiBtn" disabled>
                <i class="fas fa-check-circle"></i> Simpan Foto & Deskripsi
            </button>
        </div>
    </div>
</div>

<!-- Modal View Deskripsi Lengkap -->
<div class="modal-overlay" id="deskripsiViewModal">
    <div class="modal-content modal-view-deskripsi">
        <div class="modal-header">
            <div>
                <h3>Deskripsi Kegiatan</h3>
                <p class="modal-date" id="viewDeskripsiDate"></p>
            </div>
            <button class="close-modal" onclick="closeDeskripsiViewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="deskripsi-full" id="viewDeskripsiContent"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeskripsiViewModal()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal for Full Image -->
<div class="modal-overlay" id="imageModal">
    <div class="modal-image-content">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" src="" alt="Full Image">
    </div>
</div>

<script>
const canCrud = <?= $can_crud ? 'true' : 'false' ?>;
const idMahasiswa = <?= $id_mahasiswa ?>;

if (canCrud) {
    let video = document.getElementById('video');
    let canvas = document.getElementById('canvas');
    let captureBtn = document.getElementById('captureBtn');
    let fillKegiatanBtn = document.getElementById('fillKegiatanBtn');
    let retakeBtn = document.getElementById('retakeBtn');
    let cameraSection = document.getElementById('cameraSection');
    let previewSection = document.getElementById('previewSection');
    let capturedImage = document.getElementById('capturedImage');
    let photoTimestamp = document.getElementById('photoTimestamp');
    let liveLocation = document.getElementById('liveLocation');
    
    let deskripsiInput = document.getElementById('deskripsiInput');
    let charCounter = document.getElementById('charCounter');
    let currentChars = document.getElementById('currentChars');
    let saveDeskripsiBtn = document.getElementById('saveDeskripsiBtn');
    
    let stream = null;
    let currentLocation = null;
    let capturedData = null;

    // Start camera
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            });
            video.srcObject = stream;
        } catch (err) {
            alert('Tidak dapat mengakses kamera: ' + err.message);
        }
    }

    // Get location
    function updateLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
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
                }
            );
        }
    }

    // Capture photo
    captureBtn.addEventListener('click', () => {
        if (!currentLocation) {
            alert('Menunggu lokasi terdeteksi...');
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        const imageData = canvas.toDataURL('image/jpeg', 0.9);
        capturedImage.src = imageData;
        
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        const timeStr = now.toLocaleTimeString('id-ID');
        
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
        
        cameraSection.style.display = 'none';
        previewSection.style.display = 'block';
        captureBtn.style.display = 'none';
        fillKegiatanBtn.style.display = 'inline-flex';
        retakeBtn.style.display = 'inline-flex';
        
        stopCamera();
    });

    // Open deskripsi input modal
    fillKegiatanBtn.addEventListener('click', () => {
        document.getElementById('deskripsiInputModal').classList.add('show');
        document.body.style.overflow = 'hidden';
        deskripsiInput.focus();
    });

    // Character counter with validation
    deskripsiInput.addEventListener('input', () => {
        const length = deskripsiInput.value.length;
        currentChars.textContent = length;
        
        if (length >= 150) {
            charCounter.classList.add('valid');
            charCounter.classList.remove('invalid');
            saveDeskripsiBtn.disabled = false;
        } else {
            charCounter.classList.add('invalid');
            charCounter.classList.remove('valid');
            saveDeskripsiBtn.disabled = true;
        }
    });

    // Save photo and deskripsi
    saveDeskripsiBtn.addEventListener('click', async () => {
        if (!capturedData) return;
        
        const deskripsi = deskripsiInput.value.trim();
        
        if (deskripsi.length < 150) {
            alert('Deskripsi minimal 150 karakter!');
            return;
        }
        
        const formData = new FormData();
        formData.append('id_mahasiswa', idMahasiswa);
        formData.append('foto', capturedData.image);
        formData.append('lokasi', capturedData.location.address);
        formData.append('latitude', capturedData.location.lat);
        formData.append('longitude', capturedData.location.lon);
        formData.append('timestamp', capturedData.timestamp);
        formData.append('deskripsi', deskripsi);
        
        try {
            const response = await fetch('pages/save_kegiatan.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Kegiatan berhasil disimpan!');
                location.reload();
            } else {
                alert('Gagal menyimpan kegiatan: ' + result.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat menyimpan.');
            console.error(error);
        }
    });

    // Retake photo
    retakeBtn.addEventListener('click', () => {
        resetCamera();
    });

    function resetCamera() {
        cameraSection.style.display = 'block';
        previewSection.style.display = 'none';
        captureBtn.style.display = 'inline-flex';
        fillKegiatanBtn.style.display = 'none';
        retakeBtn.style.display = 'none';
        capturedData = null;
        
        // Reset deskripsi input
        deskripsiInput.value = '';
        currentChars.textContent = '0';
        charCounter.classList.remove('valid', 'invalid');
        saveDeskripsiBtn.disabled = true;
        
        startCamera();
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    // Initialize
    startCamera();
    updateLocation();
    setInterval(updateLocation, 30000);
}

// Close deskripsi input modal
function closeDeskripsiInputModal() {
    const deskripsi = document.getElementById('deskripsiInput').value;
    
    if (deskripsi.length > 0) {
        if (confirm('Deskripsi belum tersimpan. Yakin ingin menutup?')) {
            document.getElementById('deskripsiInputModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    } else {
        document.getElementById('deskripsiInputModal').classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Open deskripsi view modal
function openDeskripsiModal(deskripsi, tanggal) {
    document.getElementById('viewDeskripsiContent').textContent = deskripsi;
    document.getElementById('viewDeskripsiDate').textContent = '📅 ' + tanggal;
    document.getElementById('deskripsiViewModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close deskripsi view modal
function closeDeskripsiViewModal() {
    document.getElementById('deskripsiViewModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Modal image functions
function openImageModal(imageSrc) {
    document.getElementById('imageModal').classList.add('show');
    document.getElementById('modalImage').src = imageSrc;
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}

document.getElementById('imageModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'imageModal') {
        closeImageModal();
    }
});

// Delete kegiatan function
function deleteKegiatan(idKegiatan) {
    if (!confirm('Apakah Anda yakin ingin menghapus kegiatan ini? Anda dapat mengambil foto ulang setelah dihapus.')) {
        return;
    }

    const formData = new FormData();
    formData.append('id_kegiatan', idKegiatan);
    formData.append('id_mahasiswa', idMahasiswa);

    fetch('pages/delete_kegiatan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Gagal menghapus: ' + result.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat menghapus.');
        console.error(error);
    });
}
</script>