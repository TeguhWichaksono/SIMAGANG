<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include '../Koneksi/koneksi.php';

// Cek login
if (!isset($_SESSION['id'])) {
  echo '<div style="text-align:center;padding:50px;">';
  echo '<h2>Session Expired</h2>';
  echo '<p>Silakan <a href="../Login/login.php">login kembali</a></p>';
  echo '</div>';
  return;
}

$id_user = $_SESSION['id'];

// Ambil data user
$query_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $query_user);
mysqli_stmt_bind_param($stmt_user, 'i', $id_user);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);

if (!$user_data) {
  echo '<div style="text-align:center;padding:50px;"><h2>Session Invalid</h2><p>Silakan <a href="../Login/login.php">login kembali</a>.</p></div>';
  return;
}

// Ambil data mahasiswa
$query_mhs = "SELECT * FROM mahasiswa WHERE id_user = ?";
$stmt_mhs = mysqli_prepare($conn, $query_mhs);
mysqli_stmt_bind_param($stmt_mhs, 'i', $id_user);
mysqli_stmt_execute($stmt_mhs);
$result_mhs = mysqli_stmt_get_result($stmt_mhs);
$mahasiswa_data = mysqli_fetch_assoc($result_mhs);

// Gabungkan data
$mahasiswa = array_merge(
  [
    'nama' => $user_data['nama'] ?? '',
    'nim' => $user_data['nim'] ?? '',
    'email' => $user_data['email'] ?? '',
    'role' => $user_data['role'] ?? ''
  ],
  $mahasiswa_data ? [
    'id_mahasiswa' => $mahasiswa_data['id_mahasiswa'],
    'kontak' => $mahasiswa_data['kontak'] ?? '',
    'angkatan' => $mahasiswa_data['angkatan'] ?? '',
    'prodi' => $mahasiswa_data['prodi'] ?? '',
    'status_magang' => $mahasiswa_data['status_magang'] ?? 'pra-magang'
  ] : []
);

if (!$mahasiswa_data) {
  $id_mahasiswa = null;
} else {
  $id_mahasiswa = $mahasiswa_data['id_mahasiswa'];
}

// Cek kelompok
$kelompok = null;
if ($id_mahasiswa) {
  $query_kelompok = "SELECT k.*, ak.peran 
                     FROM anggota_kelompok ak 
                     JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
                     WHERE ak.id_mahasiswa = ?";
  $stmt2 = mysqli_prepare($conn, $query_kelompok);
  mysqli_stmt_bind_param($stmt2, 'i', $id_mahasiswa);
  mysqli_stmt_execute($stmt2);
  $result2 = mysqli_stmt_get_result($stmt2);
  $kelompok = mysqli_fetch_assoc($result2);
}

$sudah_mengajukan = false;
$pengajuan_ditolak = false;
$id_kelompok = $kelompok['id_kelompok'] ?? null;

if ($id_kelompok) {
  // Cek apakah ada pengajuan aktif (menunggu atau diterima)
  $query_cek = "SELECT id_pengajuan, status_pengajuan 
                FROM pengajuan_magang 
                WHERE id_kelompok = ? 
                AND status_pengajuan IN ('menunggu', 'diterima', 'menunggu_mitra')
                ORDER BY tanggal_pengajuan DESC LIMIT 1";
  $stmt3 = mysqli_prepare($conn, $query_cek);
  mysqli_stmt_bind_param($stmt3, 'i', $id_kelompok);
  mysqli_stmt_execute($stmt3);
  $result3 = mysqli_stmt_get_result($stmt3);
  
  if (mysqli_num_rows($result3) > 0) {
    $sudah_mengajukan = true;
  } else {
    // Cek apakah pengajuan terakhir ditolak
    $query_cek_tolak = "SELECT id_pengajuan FROM pengajuan_magang 
                        WHERE id_kelompok = ? 
                        AND status_pengajuan = 'ditolak'
                        ORDER BY tanggal_pengajuan DESC LIMIT 1";
    $stmt4 = mysqli_prepare($conn, $query_cek_tolak);
    mysqli_stmt_bind_param($stmt4, 'i', $id_kelompok);
    mysqli_stmt_execute($stmt4);
    $result4 = mysqli_stmt_get_result($stmt4);
    
    if (mysqli_num_rows($result4) > 0) {
      $pengajuan_ditolak = true;
    }
  }
}

// ============ REVISI: LOGIKA MITRA & STATUS MAGANG ============
$mitra_valid = false;
$id_mitra = null;
$nama_mitra = '';
$alamat_mitra = '';
$bidang_mitra = '';
$mitra_status = '';

// Cek status mahasiswa dulu
$status_mhs = $mahasiswa['status_magang'];
$is_magang_jalan = ($status_mhs == 'magang_aktif' || $status_mhs == 'selesai');

if ($is_magang_jalan && $id_kelompok) {
    // Jika sudah aktif/selesai, ambil mitra dari pengajuan yang SUDAH DITERIMA
    $q_mitra_fix = "SELECT pm.id_mitra, mp.nama_mitra, mp.alamat, mp.bidang 
                    FROM pengajuan_magang pm
                    JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
                    WHERE pm.id_kelompok = ? AND pm.status_pengajuan = 'diterima'
                    LIMIT 1";
    $stmt_fix = mysqli_prepare($conn, $q_mitra_fix);
    mysqli_stmt_bind_param($stmt_fix, 'i', $id_kelompok);
    mysqli_stmt_execute($stmt_fix);
    $res_fix = mysqli_stmt_get_result($stmt_fix);
    
    if ($row_fix = mysqli_fetch_assoc($res_fix)) {
        $mitra_valid = true;
        $id_mitra = $row_fix['id_mitra'];
        $nama_mitra = $row_fix['nama_mitra'];
        $alamat_mitra = $row_fix['alamat'];
        $bidang_mitra = $row_fix['bidang'];
        $mitra_status = 'approved'; // Pasti approved karena sudah aktif
    }
} else {
    // Jika masih pra-magang, cek session seperti biasa
    $mitra_data = $_SESSION['selected_mitra'] ?? null;
    
    if ($mitra_data && is_array($mitra_data)) {
        $mitra_status = $mitra_data['status'] ?? '';
        
        if ($mitra_status === 'pending') {
            $id_pengajuan = intval($mitra_data['id_pengajuan_mitra'] ?? 0);
            if ($id_pengajuan > 0) {
                // ... (Kode cek pengajuan pending tetap sama) ...
                $query_check = "SELECT status_pengajuan FROM pengajuan_mitra WHERE id_pengajuan = ?";
                $stmt_check = mysqli_prepare($conn, $query_check);
                mysqli_stmt_bind_param($stmt_check, 'i', $id_pengajuan);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                $pengajuan = mysqli_fetch_assoc($result_check);
                
                if ($pengajuan && $pengajuan['status_pengajuan'] === 'menunggu') {
                    $nama_mitra = $mitra_data['nama'] ?? '';
                    $alamat_mitra = $mitra_data['alamat'] ?? '';
                    $bidang_mitra = $mitra_data['bidang'] ?? '';
                    $mitra_valid = true;
                }
            }
        } else if ($mitra_status === 'approved') {
            // ... (Kode cek mitra approved tetap sama) ...
            $id_mitra = intval($mitra_data['id_mitra'] ?? 0);
            if ($id_mitra > 0) {
                $query_verify = "SELECT nama_mitra, alamat, bidang FROM mitra_perusahaan WHERE id_mitra = ?";
                $stmt_verify = mysqli_prepare($conn, $query_verify);
                mysqli_stmt_bind_param($stmt_verify, 'i', $id_mitra);
                mysqli_stmt_execute($stmt_verify);
                $res_verify = mysqli_stmt_get_result($stmt_verify);
                if ($mitra = mysqli_fetch_assoc($res_verify)) {
                    $nama_mitra = $mitra['nama_mitra'];
                    $alamat_mitra = $mitra['alamat'];
                    $bidang_mitra = $mitra['bidang'];
                    $mitra_valid = true;
                }
            }
        }
    }
}

// Validasi data lengkap (Updated)
$warnings = [];
// Jika sudah aktif/selesai, abaikan warning data
if (!$is_magang_jalan) {
    if (empty($mahasiswa['prodi'])) $warnings[] = "Data Program Studi belum lengkap";
    if (empty($mahasiswa['kontak'])) $warnings[] = "Data Kontak belum lengkap";
    if (empty($mahasiswa['angkatan'])) $warnings[] = "Data Angkatan belum lengkap";
    if (!$id_mahasiswa) $warnings[] = "Data mahasiswa belum terdaftar";
    if (!$kelompok) $warnings[] = "Anda belum terdaftar dalam kelompok";
    if ($kelompok && $kelompok['peran'] !== 'ketua') $warnings[] = "Hanya ketua kelompok yang dapat mengajukan";
    if (!$mitra_valid) $warnings[] = "Mitra magang belum dipilih";
}

// Validasi data lengkap
$warnings = [];
if (empty($mahasiswa['prodi'])) $warnings[] = "Data Program Studi belum lengkap";
if (empty($mahasiswa['kontak'])) $warnings[] = "Data Kontak belum lengkap";
if (empty($mahasiswa['angkatan'])) $warnings[] = "Data Angkatan belum lengkap";
if (!$id_mahasiswa) $warnings[] = "Data mahasiswa belum terdaftar di sistem";
if (!$kelompok) $warnings[] = "Anda belum terdaftar dalam kelompok";
if ($kelompok && $kelompok['peran'] !== 'ketua') $warnings[] = "Hanya ketua kelompok yang dapat mengajukan";
if (!$mitra_valid) $warnings[] = "Mitra magang belum dipilih atau tidak valid";
?>

<link rel="stylesheet" href="styles/laporanMagang.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<div class="form-container">
  <h2>Upload Dokumen Pendukung Magang</h2>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert-success" style="padding: 12px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert-error" style="padding: 12px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 15px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (!empty($warnings)): ?>
    <div class="alert-warning" style="padding: 12px; background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; border-radius: 5px; margin-bottom: 15px;">
      <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian:</strong>
      <ul style="margin: 8px 0 0 20px;">
        <?php foreach ($warnings as $warning): ?>
          <li><?= $warning ?></li>
        <?php endforeach; ?>
      </ul>
      <p style="margin-top: 8px;">Silakan lengkapi data terlebih dahulu sebelum mengajukan magang.</p>
    </div>
  <?php endif; ?>

  <?php if ($sudah_mengajukan): ?>
    <div class="alert-info" style="padding: 12px; background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; border-radius: 5px; margin-bottom: 15px;">
      <i class="fas fa-info-circle"></i> Kelompok Anda sudah mengajukan magang. Silakan cek status di menu <a href="index.php?page=status_pengajuan">Status Pengajuan</a>.
    </div>
  <?php endif; ?>

  <?php if ($pengajuan_ditolak && !$sudah_mengajukan): ?>
    <div class="alert-warning" style="padding: 12px; background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; border-radius: 5px; margin-bottom: 15px;">
      <i class="fas fa-redo"></i> <strong>Pengajuan Ulang:</strong> Pengajuan sebelumnya <strong>DITOLAK</strong>. Anda dapat mengajukan ulang dengan memperbaiki data dan dokumen.
    </div>
  <?php endif; ?>

  <!-- Ringkasan Data -->
  <div class="data-summary" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <h3 style="margin-top: 0;">Ringkasan Data Pengajuan</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
      <div><strong>Nama:</strong> <?= htmlspecialchars($mahasiswa['nama']) ?></div>
      <div><strong>NIM:</strong> <?= htmlspecialchars($mahasiswa['nim']) ?></div>
      <div><strong>Program Studi:</strong> <?= htmlspecialchars($mahasiswa['prodi'] ?: '-') ?></div>
      <div><strong>Angkatan:</strong> <?= htmlspecialchars($mahasiswa['angkatan'] ?: '-') ?></div>
      <div><strong>Kelompok:</strong> <?= $kelompok ? htmlspecialchars($kelompok['nama_kelompok']) : '-' ?></div>
      <div><strong>Peran:</strong> <?= $kelompok ? ucfirst($kelompok['peran']) : '-' ?></div>
      <div style="grid-column: 1 / -1;">
        <strong>Mitra:</strong>
        <?php if ($mitra_valid && $nama_mitra): ?>
          <span style="color: #28a745; font-weight: bold;">✓ <?= htmlspecialchars($nama_mitra) ?></span>
          
          <?php if ($mitra_status === 'pending'): ?>
            <span style="background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 8px;">
              <i class="fas fa-clock"></i> PENDING APPROVAL
            </span>
            <br><small style="color: #856404; margin-left: 10px;">
              ⚠️ Mitra masih menunggu persetujuan Koordinator Bidang Magang
            </small>
          <?php else: ?>
            <small style="color: #666;">(ID: <?= $id_mitra ?>)</small>
          <?php endif; ?>
          
          <?php if ($bidang_mitra || $alamat_mitra): ?>
            <br><small style="color: #666; margin-left: 10px;">
              <?php if ($bidang_mitra): ?>
                <i class="fas fa-briefcase"></i> Bidang: <?= htmlspecialchars($bidang_mitra) ?>
              <?php endif; ?>
              <?php if ($alamat_mitra): ?>
                <br><i class="fas fa-map-marker-alt"></i> Alamat: <?= htmlspecialchars($alamat_mitra) ?>
              <?php endif; ?>
            </small>
          <?php endif; ?>
        <?php else: ?>
          <span style="color: red; font-weight: bold;">✗ Belum dipilih atau tidak valid</span>
          <a href="index.php?page=pengajuan_Mitra" style="color: #007bff; text-decoration: underline; margin-left: 10px;">
            Pilih Mitra Sekarang
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <p class="form-desc">
    Silakan unduh template CV dan Proposal, lalu unggah dokumen sesuai format yang ditentukan (PDF, maksimal 5MB per file).
  </p>

  <!-- Tombol Download Template -->
  <div class="template-buttons" style="display: flex; gap: 10px; margin-bottom: 20px;">
    <a href="templates/Format CV New.docx" class="btn-template" download style="flex: 1; text-align: center; padding: 12px; background: #17a2b8; color: white; text-decoration: none; border-radius: 6px;">
      <i class="fas fa-download"></i> Download Template CV
    </a>
    <a href="templates/Format Proposal PKL.docx" class="btn-template" download style="flex: 1; text-align: center; padding: 12px; background: #17a2b8; color: white; text-decoration: none; border-radius: 6px;">
      <i class="fas fa-download"></i> Download Template Proposal
    </a>
  </div>

  <!-- Form Upload -->
  <form id="formDokumenPendukung" method="POST" action="pages/handler_pengajuan_magang.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="submit_pengajuan">

    <div class="dokumen-grid" style="max-width: 100%;"> <div class="form-group">
        <label for="file_proposal_cv">Upload Proposal & CV (PDF, Max 5MB) <span style="color: red;">*</span></label>
        
        <?php if ($is_magang_jalan): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Dokumen Proposal & CV sudah diverifikasi.
            </div>
        <?php else: ?>
            <input type="file" id="file_proposal_cv" name="file_proposal_cv" accept=".pdf" required 
                   class="file-input-custom" />
            
            <div id="preview-container" class="preview-container" style="display:none;">
                <p style="margin-bottom:10px; font-weight:bold; color:#555;">Preview Dokumen:</p>
                <iframe id="pdf-preview-frame" src="" width="100%" height="500px" style="border: 1px solid #ddd; border-radius: 8px;"></iframe>
            </div>
        <?php endif; ?>
      </div>

    <div class="form-actions" style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
      <button type="button" class="btn-secondary" onclick="window.location.href='index.php?page=pengajuan_Mitra'">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>

      <?php if (!$is_magang_jalan && !$sudah_mengajukan): ?>
          <button type="submit" class="btn-submit"
            <?= (!empty($warnings)) ? 'disabled' : '' ?>
            style="background: <?= (!empty($warnings)) ? '#ccc' : '#28a745' ?>;">
            <i class="fas fa-paper-plane"></i> Kirim Pengajuan
          </button>
      <?php endif; ?>
    </div>
  </form>

<!-- POPUP PDF VIEW -->
<div id="pdfPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
  <div class="popup-content" style="width: 90%; height: 90%; background: white; border-radius: 10px; position: relative; padding: 20px;">
    <button class="btn-close" id="closePopup" style="position: absolute; top: 10px; right: 10px; background: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 18px;">&times;</button>
    <iframe id="pdfViewer" src="" frameborder="0" style="width: 100%; height: 100%; border-radius: 5px;"></iframe>
  </div>
</div>

<script>
  // Ambil elemen input dan container preview
  const fileInput = document.getElementById('file_proposal_cv');
  const previewContainer = document.getElementById('preview-container');
  const previewFrame = document.getElementById('pdf-preview-frame');

  // Cek apakah elemen ada (untuk menghindari error)
  if (fileInput) {
      fileInput.addEventListener('change', function(e) {
          const file = e.target.files[0];
          
          if (file) {
              // Validasi Ukuran (Maks 5MB)
              if (file.size > 5 * 1024 * 1024) {
                  alert('Ukuran file maksimal 5MB!');
                  this.value = ''; // Reset input agar user bisa upload ulang
                  previewContainer.style.display = 'none';
                  return;
              }

              // Validasi Tipe File (Wajib PDF)
              if (file.type !== 'application/pdf') {
                  alert('File harus berformat PDF!');
                  this.value = ''; 
                  previewContainer.style.display = 'none';
                  return;
              }

              // Tampilkan Preview PDF Full
              const fileURL = URL.createObjectURL(file);
              previewFrame.src = fileURL;
              previewContainer.style.display = 'block'; // Tampilkan container
          } else {
              // Jika user cancel upload
              previewContainer.style.display = 'none';
          }
      });
  }

  // Handle Submit (Konfirmasi Pengiriman)
  const formDokumen = document.getElementById('formDokumenPendukung');
  if (formDokumen) {
      formDokumen.addEventListener('submit', (e) => {
        if (!confirm('Apakah Anda yakin ingin mengirim pengajuan magang? Pastikan dokumen sudah benar.')) {
          e.preventDefault();
        }
      });
  }
</script>

<style>
  .pdf-preview-item:hover { background: #f8f9fa; }
  .btn-view-pdf:hover { background: #0056b3; }
  .alert-success, .alert-error, .alert-warning, .alert-info { animation: slideDown 0.3s ease; }
  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>