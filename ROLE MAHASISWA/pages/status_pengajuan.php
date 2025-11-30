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

// -----------------------------
// Debug helper (tampilkan error aman)
// -----------------------------
function show_error_box($title, $msg) {
    echo "<div style=\"background:#f8d7da;color:#721c24;padding:12px;border:1px solid #f5c6cb;border-radius:6px;margin:16px 0;\">"
       . "<strong>{$title}</strong><div style='margin-top:8px;'>".nl2br(htmlspecialchars($msg))."</div></div>";
}

// -------------------------------------------------
// 1) Ambil id_kelompok dari anggota_kelompok
// -------------------------------------------------
$id_kelompok = null;
$qKel = "SELECT ak.id_kelompok
         FROM anggota_kelompok ak
         JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
         WHERE m.id_user = ? LIMIT 1";
$stmtKel = mysqli_prepare($conn, $qKel);
if ($stmtKel) {
    mysqli_stmt_bind_param($stmtKel, 'i', $id_user);
    if (!mysqli_stmt_execute($stmtKel)) {
        show_error_box('Database error (ambil kelompok)', mysqli_error($conn));
        exit;
    }
    $resKel = mysqli_stmt_get_result($stmtKel);
    $kel = mysqli_fetch_assoc($resKel);
    if ($kel) $id_kelompok = $kel['id_kelompok'];
    mysqli_stmt_close($stmtKel);
} else {
    show_error_box('Prepare failed (ambil kelompok)', mysqli_error($conn));
    exit;
}

if (!$id_kelompok) {
    echo "<p style='padding:20px;'>Anda belum tergabung dalam kelompok!</p>";
    exit;
}

// -------------------------------------------------
// 2) DETEKSI KOLOM ID PENGAJUAN YANG ADA DI TABEL
//    -> prioritas: id_pengajuan, id_pengajuan_magang
//    -> bila tidak ada, fallback ORDER BY tanggal_pengajuan DESC
// -------------------------------------------------
$id_col = null;
$schema = null;
$tbl = 'pengajuan_magang';

$col_check_q = "
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
      AND COLUMN_NAME IN ('id_pengajuan', 'id_pengajuan_magang')
    ORDER BY FIELD(COLUMN_NAME, 'id_pengajuan', 'id_pengajuan_magang')
    LIMIT 1
";
$stmtCol = mysqli_prepare($conn, $col_check_q);
if ($stmtCol) {
    mysqli_stmt_bind_param($stmtCol, 's', $tbl);
    if (!mysqli_stmt_execute($stmtCol)) {
        // jika gagal (mis. hak akses INFORMATION_SCHEMA), jangan crash — pakai fallback
        mysqli_stmt_close($stmtCol);
        $id_col = null;
    } else {
        $resCol = mysqli_stmt_get_result($stmtCol);
        $c = mysqli_fetch_assoc($resCol);
        if ($c && !empty($c['COLUMN_NAME'])) {
            $id_col = $c['COLUMN_NAME']; // salah satu dari dua nama
        }
        mysqli_stmt_close($stmtCol);
    }
} else {
    // prepare gagal (beberapa environment tidak izinkan INFORMATION_SCHEMA via prepared), fallback
    $id_col = null;
}

// -------------------------------------------------
// 3) Susun query utama, urut berdasarkan id_col kalau ada
// -------------------------------------------------
$order_clause = "";
if ($id_col) {
    // amankan nama kolom (hanya dua opsi jadi aman)
    if ($id_col === 'id_pengajuan' || $id_col === 'id_pengajuan_magang') {
        $order_clause = "ORDER BY pm.`{$id_col}` DESC";
    } else {
        $order_clause = "ORDER BY pm.tanggal_pengajuan DESC";
    }
} else {
    $order_clause = "ORDER BY pm.tanggal_pengajuan DESC";
}

$query = "
    SELECT 
      pm.*,
      k.nama_kelompok,
      mp.nama_mitra,
      mp.alamat,
      mp.bidang,
      u.nama AS nama_ketua
    FROM pengajuan_magang pm
    JOIN kelompok k ON pm.id_kelompok = k.id_kelompok
    JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    JOIN mahasiswa m ON pm.id_mahasiswa_ketua = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    WHERE pm.id_kelompok = ?
    {$order_clause}
";

// prepare & execute
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    show_error_box('Prepare failed (query pengajuan)', mysqli_error($conn) . "\n\nQuery: " . $query);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id_kelompok);
if (!mysqli_stmt_execute($stmt)) {
    show_error_box('Execute failed (query pengajuan)', mysqli_error($conn));
    mysqli_stmt_close($stmt);
    exit;
}
$result = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}
mysqli_stmt_close($stmt);

// -------------------------------------------------
// 4) Ambil info apakah user adalah ketua (untuk menampilkan tombol ajukan ulang hanya jika ketua)
// -------------------------------------------------
$is_ketua = false;
$qKetua = "SELECT ak.peran
           FROM anggota_kelompok ak
           JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
           WHERE ak.id_kelompok = ? AND m.id_user = ? LIMIT 1";
$stmtK = mysqli_prepare($conn, $qKetua);
if ($stmtK) {
    mysqli_stmt_bind_param($stmtK, 'ii', $id_kelompok, $id_user);
    if (mysqli_stmt_execute($stmtK)) {
        $resK = mysqli_stmt_get_result($stmtK);
        $kk = mysqli_fetch_assoc($resK);
        if ($kk && isset($kk['peran']) && $kk['peran'] === 'ketua') $is_ketua = true;
    }
    mysqli_stmt_close($stmtK);
}

// -------------------------------------------------
// 5) Render HTML (tampilan) — tombol Ajukan Ulang hanya muncul di index 0 dan bila status = 'ditolak'
// -------------------------------------------------
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<style>
  .status-container { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width:1100px; margin:20px auto; }
  .status-card { border:1px solid #e0e0e0; border-radius:10px; padding:20px; margin-bottom:20px; }
  .status-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:15px; border-bottom:2px solid #f0f0f0; }
  .status-badge { padding:6px 16px; border-radius:20px; font-size:13px; font-weight:600; }
  .status-menunggu { background:#fff3cd; color:#856404; }
  .status-diterima { background:#d4edda; color:#155724; }
  .status-ditolak { background:#f8d7da; color:#721c24; }

  .info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:15px; margin-top:10px; }
  .catatan-box { background:#f8d9da; padding:15px; border-radius:8px; margin-top:15px; border-left:4px solid #dc3545; }
  .btn-ajukan-ulang { margin-top:15px; display:inline-block; padding:10px 18px; background:#0d6efd; color:#fff; border-radius:6px; text-decoration:none; font-weight:600; }
  .empty-state { text-align:center; padding:60px 20px; color:#999; }
</style>

<div class="status-container">
  <h2><i class="fas fa-clipboard-list"></i> Status Pengajuan Magang</h2>

  <?php if (count($rows) === 0): ?>
    <div class="empty-state">
      <i class="fas fa-inbox" style="font-size:64px; opacity:0.3;"></i>
      <h3>Belum Ada Pengajuan</h3>
      <p>Kelompok Anda belum mengajukan magang.</p>
      <a href="index.php?page=pengajuan_Mitra" class="btn-ajukan-ulang"><i class="fas fa-plus"></i> Mulai Pengajuan</a>
    </div>

  <?php else: ?>

    <?php foreach ($rows as $index => $row): 
        $status_class = "status-menunggu";
        $status_text  = "Menunggu Persetujuan";
        $icon = "fa-clock";
        if (isset($row['status_pengajuan'])) {
            if ($row['status_pengajuan'] === 'diterima') {
                $status_class = "status-diterima";
                $status_text  = "Disetujui";
                $icon = "fa-check-circle";
            } elseif ($row['status_pengajuan'] === 'ditolak') {
                $status_class = "status-ditolak";
                $status_text  = "Ditolak";
                $icon = "fa-times-circle";
            }
        }
    ?>
      <div class="status-card">
        <div class="status-header">
          <div>
            <h3 style="margin:0 0 5px 0;"><?= htmlspecialchars($row['nama_kelompok'] ?? 'Kelompok') ?></h3>
            <p style="margin:0; color:#666;">
              <i class="far fa-calendar"></i>
              Diajukan: <?= isset($row['tanggal_pengajuan']) ? date('d F Y', strtotime($row['tanggal_pengajuan'])) : '-' ?>
            </p>
          </div>
          <span class="status-badge <?= $status_class ?>">
            <i class="fas <?= $icon ?>"></i> <?= $status_text ?>
          </span>
        </div>

        <div class="info-grid">
          <div><b>Ketua:</b> <?= htmlspecialchars($row['nama_ketua'] ?? '-') ?></div>
          <div><b>Mitra:</b> <?= htmlspecialchars($row['nama_mitra'] ?? '-') ?></div>
          <div><b>Alamat:</b> <?= htmlspecialchars($row['alamat'] ?? '-') ?></div>
          <div><b>Bidang:</b> <?= htmlspecialchars($row['bidang'] ?? '-') ?></div>
        </div>

        <?php if (isset($row['status_pengajuan']) && $row['status_pengajuan'] === 'ditolak'): ?>
          <div class="catatan-box">
            <strong><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan:</strong>
            <p><?= nl2br(htmlspecialchars($row['catatan_korbid'] ?? 'Tidak ada catatan')) ?></p>
          </div>

          <?php if ($index === 0 && $is_ketua): ?>
            <a href="index.php?page=pengajuan_Mitra" class="btn-ajukan-ulang"><i class="fas fa-redo"></i> Ajukan Ulang Magang</a>
          <?php elseif ($index === 0): ?>
            <p style="margin-top:10px; color:#856404;"><i class="fas fa-info-circle"></i> Hanya ketua kelompok yang dapat mengajukan ulang.</p>
          <?php endif; ?>

        <?php endif; ?>

      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</div>
