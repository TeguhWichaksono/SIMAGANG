<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../Koneksi/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$id_user = intval($_SESSION['id']);

// Fungsi helper untuk menampilkan error
function show_error_box($title, $msg) {
    echo "<div style=\"background:#f8d7da;color:#721c24;padding:12px;border:1px solid #f5c6cb;border-radius:6px;margin:16px 0;\">"
       . "<strong>{$title}</strong><div style='margin-top:8px;'>".nl2br(htmlspecialchars($msg))."</div></div>";
}

// Ambil id_mahasiswa dari user yang login
$id_mahasiswa = null;
$qMhs = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ? LIMIT 1";
$stmtMhs = mysqli_prepare($conn, $qMhs);
if ($stmtMhs) {
    mysqli_stmt_bind_param($stmtMhs, 'i', $id_user);
    if (!mysqli_stmt_execute($stmtMhs)) {
        show_error_box('Database error (ambil mahasiswa)', mysqli_error($conn));
        exit;
    }
    $resMhs = mysqli_stmt_get_result($stmtMhs);
    $mhs = mysqli_fetch_assoc($resMhs);
    if ($mhs) $id_mahasiswa = $mhs['id_mahasiswa'];
    mysqli_stmt_close($stmtMhs);
} else {
    show_error_box('Prepare failed (ambil mahasiswa)', mysqli_error($conn));
    exit;
}

if (!$id_mahasiswa) {
    echo "<p style='padding:20px;'>Data mahasiswa tidak ditemukan!</p>";
    exit;
}

// ============================================
// PERBAIKAN UTAMA: Deteksi kolom status otomatis
// ============================================
$status_column = 'status_pengajuan'; // default

// Cek kolom mana yang ada di tabel
$check_columns = "SHOW COLUMNS FROM pengajuan_mitra LIKE 'status%'";
$result_check = mysqli_query($conn, $check_columns);
$columns_found = [];
while ($col = mysqli_fetch_assoc($result_check)) {
    $columns_found[] = $col['Field'];
}

// Prioritas: status_pengajuan > status
if (in_array('status_pengajuan', $columns_found)) {
    $status_column = 'status_pengajuan';
} elseif (in_array('status', $columns_found)) {
    $status_column = 'status';
}

// Query dengan kolom status yang terdeteksi
$query = "
    SELECT 
      pm.*,
      u.nama AS nama_pengaju,
      pm.{$status_column} as status_aktual
    FROM pengajuan_mitra pm
    JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    WHERE pm.id_mahasiswa = ?
    ORDER BY pm.tanggal_pengajuan DESC
";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    show_error_box('Prepare failed (query pengajuan mitra)', mysqli_error($conn));
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id_mahasiswa);
if (!mysqli_stmt_execute($stmt)) {
    show_error_box('Execute failed (query pengajuan mitra)', mysqli_error($conn));
    mysqli_stmt_close($stmt);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}
mysqli_stmt_close($stmt);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<style>
  .status-container { 
    background: #fff; 
    padding: 25px; 
    border-radius: 12px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    max-width:1100px; 
    margin:20px auto; 
  }
  
  .status-card { 
    border:1px solid #e0e0e0; 
    border-radius:10px; 
    padding:20px; 
    margin-bottom:20px;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  
  .status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  }
  
  .status-header { 
    display:flex; 
    justify-content:space-between; 
    align-items:center; 
    margin-bottom:15px; 
    padding-bottom:15px; 
    border-bottom:2px solid #f0f0f0; 
  }
  
  .status-badge { 
    padding:6px 16px; 
    border-radius:20px; 
    font-size:13px; 
    font-weight:600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  
  .status-menunggu { 
    background:#fff3cd; 
    color:#856404; 
  }
  
  .status-diterima { 
    background:#d4edda; 
    color:#155724; 
  }
  
  .status-ditolak { 
    background:#f8d7da; 
    color:#721c24; 
  }

  .info-grid { 
    display:grid; 
    grid-template-columns:repeat(2,1fr); 
    gap:15px; 
    margin-top:10px; 
  }
  
  .info-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
  }
  
  .info-item b {
    display: block;
    margin-bottom: 5px;
    color: #495057;
    font-size: 13px;
  }
  
  .catatan-box { 
    background:#fff8e1; 
    padding:15px; 
    border-radius:8px; 
    margin-top:15px; 
    border-left:4px solid #ffc107; 
  }
  
  .catatan-box.ditolak {
    background:#f8d9da;
    border-left-color:#dc3545;
  }
  
  .catatan-box.diterima {
    background:#d4edda;
    border-left-color:#28a745;
  }
  
  .btn-ajukan-lagi { 
    margin-top:15px; 
    display:inline-block; 
    padding:10px 18px; 
    background:#0d6efd; 
    color:#fff; 
    border-radius:6px; 
    text-decoration:none; 
    font-weight:600;
    transition: background 0.3s;
  }
  
  .btn-ajukan-lagi:hover {
    background:#0b5ed7;
  }
  
  .empty-state { 
    text-align:center; 
    padding:60px 20px; 
    color:#999; 
  }
  
  .page-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    color: #262A39;
  }
  
  .page-title i {
    color: #4270F4;
  }
  
  .debug-info {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-size: 12px;
    color: #004085;
  }
</style>

<div class="status-container">
  <h2 class="page-title">
    <i class="fas fa-building"></i> 
    Status Pengajuan Mitra Baru
  </h2>

  <!-- DEBUG INFO (Hapus setelah konfirmasi berhasil) -->
  <div class="debug-info">
    <strong>🔍 Debug Info:</strong> 
    Kolom status yang digunakan: <code><?= $status_column ?></code> | 
    Total pengajuan: <strong><?= count($rows) ?></strong>
  </div>

  <?php if (count($rows) === 0): ?>
    <div class="empty-state">
      <i class="fas fa-inbox" style="font-size:64px; opacity:0.3;"></i>
      <h3>Belum Ada Pengajuan Mitra</h3>
      <p>Anda belum pernah mengajukan mitra baru.</p>
      <a href="index.php?page=pengajuan_Mitra" class="btn-ajukan-lagi">
        <i class="fas fa-plus"></i> Ajukan Mitra Baru
      </a>
    </div>

  <?php else: ?>

    <?php foreach ($rows as $index => $row): 
        // PERBAIKAN: Gunakan status_aktual dari query
        $status_raw = strtolower(trim($row['status_aktual'] ?? 'menunggu'));
        
        $status_class = "status-menunggu";
        $status_text  = "Menunggu Persetujuan";
        $icon = "fa-clock";
        
        // Deteksi status dengan lebih fleksibel
        if ($status_raw === 'diterima' || $status_raw === 'disetujui' || $status_raw === 'approved') {
            $status_class = "status-diterima";
            $status_text  = "Disetujui";
            $icon = "fa-check-circle";
        } elseif ($status_raw === 'ditolak' || $status_raw === 'rejected') {
            $status_class = "status-ditolak";
            $status_text  = "Ditolak";
            $icon = "fa-times-circle";
        }
    ?>
      <div class="status-card">
        <div class="status-header">
          <div>
            <h3 style="margin:0 0 5px 0;">
              <?= htmlspecialchars($row['nama_perusahaan'] ?? 'Mitra') ?>
            </h3>
            <p style="margin:0; color:#666;">
              <i class="far fa-calendar"></i>
              Diajukan: <?= isset($row['tanggal_pengajuan']) ? date('d F Y', strtotime($row['tanggal_pengajuan'])) : '-' ?>
            </p>
          </div>
          <span class="status-badge <?= $status_class ?>">
            <i class="fas <?= $icon ?>"></i> 
            <?= $status_text ?>
            <small style="opacity:0.7;">(<?= $status_raw ?>)</small>
          </span>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <b><i class="fas fa-user"></i> Pengaju:</b>
            <?= htmlspecialchars($row['nama_pengaju'] ?? '-') ?>
          </div>
          
          <div class="info-item">
            <b><i class="fas fa-building"></i> Nama Perusahaan:</b>
            <?= htmlspecialchars($row['nama_perusahaan'] ?? '-') ?>
          </div>
          
          <div class="info-item">
            <b><i class="fas fa-map-marker-alt"></i> Alamat:</b>
            <?= htmlspecialchars($row['alamat'] ?? '-') ?>
          </div>
          
          <div class="info-item">
            <b><i class="fas fa-briefcase"></i> Bidang:</b>
            <?= htmlspecialchars($row['bidang'] ?? '-') ?>
          </div>
          
          <div class="info-item">
            <b><i class="fas fa-phone"></i> Kontak:</b>
            <?= htmlspecialchars($row['kontak'] ?? '-') ?>
          </div>
          
          <div class="info-item">
            <b><i class="fas fa-clock"></i> Tanggal Pengajuan:</b>
            <?= isset($row['tanggal_pengajuan']) ? date('d M Y H:i', strtotime($row['tanggal_pengajuan'])) : '-' ?>
          </div>
        </div>

        <?php if ($status_raw === 'menunggu'): ?>
          <div class="catatan-box">
            <strong><i class="fas fa-info-circle"></i> Informasi:</strong>
            <p>Pengajuan mitra Anda sedang diproses oleh Koordinator Bidang Magang. Mohon menunggu persetujuan.</p>
          </div>
        <?php endif; ?>

        <?php if ($status_raw === 'diterima' || $status_raw === 'disetujui' || $status_raw === 'approved'): ?>
          <div class="catatan-box diterima">
            <strong><i class="fas fa-check-circle"></i> Pengajuan Disetujui:</strong>
            <p>Selamat! Mitra "<strong><?= htmlspecialchars($row['nama_perusahaan']) ?></strong>" telah disetujui dan dapat digunakan untuk pengajuan magang.</p>
            <?php if (!empty($row['catatan'])): ?>
              <p style="margin-top:10px;"><b>Catatan Korbid:</b><br><?= nl2br(htmlspecialchars($row['catatan'])) ?></p>
            <?php endif; ?>
          </div>
          
          <?php if ($index === 0): ?>
            <a href="index.php?page=pengajuan_Mitra" class="btn-ajukan-lagi">
              <i class="fas fa-arrow-right"></i> Lanjut ke Pengajuan Magang
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($status_raw === 'ditolak' || $status_raw === 'rejected'): ?>
          <div class="catatan-box ditolak">
            <strong><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan:</strong>
            <p><?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tidak ada catatan')) ?></p>
          </div>

          <?php if ($index === 0): ?>
            <a href="index.php?page=pengajuan_Mitra" class="btn-ajukan-lagi">
              <i class="fas fa-redo"></i> Ajukan Mitra Lagi
            </a>
          <?php endif; ?>
        <?php endif; ?>

      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</div>