<?php
/**
 * File: status_pengajuan.php
 * Role: Mahasiswa
 * Fungsi: Melihat status, info dosen, upload surat penerimaan, download surat pelaksanaan.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../Koneksi/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$id_user = intval($_SESSION['id']);
$message_success = '';
$message_error = '';

// -------------------------------------------------
// A. HANDLE UPLOAD SURAT PENERIMAAN (Action)
// -------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ditolak_mitra') {
    $id_pengajuan_post = intval($_POST['id_pengajuan']);
    
    mysqli_begin_transaction($conn);
    
    try {
        // Update status pengajuan menjadi 'ditolak_mitra'
        $qUpdate = "UPDATE pengajuan_magang 
                    SET status_pengajuan = 'ditolak_mitra',
                        catatan_korbid = CONCAT(IFNULL(catatan_korbid, ''), '\n[DITOLAK MITRA] Kelompok ditolak oleh mitra perusahaan.')
                    WHERE id_pengajuan = ?";
        $stmtUpdate = mysqli_prepare($conn, $qUpdate);
        mysqli_stmt_bind_param($stmtUpdate, 'i', $id_pengajuan_post);
        mysqli_stmt_execute($stmtUpdate);
        
        // Ambil ID Kelompok untuk update status mahasiswa
        $qKelompok = "SELECT id_kelompok FROM pengajuan_magang WHERE id_pengajuan = ?";
        $stmtKel = mysqli_prepare($conn, $qKelompok);
        mysqli_stmt_bind_param($stmtKel, 'i', $id_pengajuan_post);
        mysqli_stmt_execute($stmtKel);
        $resKel = mysqli_stmt_get_result($stmtKel);
        $dataKel = mysqli_fetch_assoc($resKel);
        
        if ($dataKel) {
            $qUpdateMhs = "UPDATE mahasiswa m
                          JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
                          SET m.status_magang = 'pra-magang'
                          WHERE ak.id_kelompok = ?";
            $stmtMhs = mysqli_prepare($conn, $qUpdateMhs);
            mysqli_stmt_bind_param($stmtMhs, 'i', $dataKel['id_kelompok']);
            mysqli_stmt_execute($stmtMhs);
        }
        
        // Kirim notifikasi ke Korbid
        $qKorbid = "SELECT id FROM users WHERE role = 'Koordinator Bidang Magang'";
        $resKorbid = mysqli_query($conn, $qKorbid);
        
        while ($korbid = mysqli_fetch_assoc($resKorbid)) {
            $pesan = "Pengajuan ID:$id_pengajuan_post DITOLAK oleh Mitra. Kelompok akan mengajukan ulang.";
            $qNotif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal)
                       VALUES (?, ?, 'baru', NOW())";
            $stmtNotif = mysqli_prepare($conn, $qNotif);
            mysqli_stmt_bind_param($stmtNotif, 'is', $korbid['id'], $pesan);
            mysqli_stmt_execute($stmtNotif);
        }
        
        mysqli_commit($conn);
        $message_success = "Status pengajuan diperbarui: Ditolak oleh Mitra. Silakan ajukan ulang dengan mitra lain.";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message_error = "Gagal memproses: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_penerimaan') {
    $id_pengajuan_post = intval($_POST['id_pengajuan']);
    
    if (isset($_FILES['file_penerimaan']) && $_FILES['file_penerimaan']['error'] === 0) {
        $allowed = ['pdf'];
        $filename = $_FILES['file_penerimaan']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['file_penerimaan']['size'];

        if (!in_array($ext, $allowed)) {
            $message_error = "Format file harus PDF.";
        } elseif ($filesize > 5 * 1024 * 1024) {
            $message_error = "Ukuran file maksimal 5MB.";
        } else {
            $target_dir = "../uploads/dokumen_magang/"; 
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $qInfo = "SELECT k.nama_kelompok, m.prodi 
                      FROM pengajuan_magang pm 
                      JOIN kelompok k ON pm.id_kelompok = k.id_kelompok 
                      JOIN mahasiswa m ON pm.id_mahasiswa_ketua = m.id_mahasiswa
                      WHERE pm.id_pengajuan = ?";
            
            $stmt_info = mysqli_prepare($conn, $qInfo);
            mysqli_stmt_bind_param($stmt_info, 'i', $id_pengajuan_post);
            mysqli_stmt_execute($stmt_info);
            $res_info = mysqli_stmt_get_result($stmt_info);
            $data_info = mysqli_fetch_assoc($res_info);

            $prodi_raw = strtolower(trim($data_info['prodi'] ?? '')); 
            $alias_prodi = 'UMUM'; 

            if (strpos($prodi_raw, 'manajemen informatika') !== false) {
                $alias_prodi = 'MIF';
            } elseif (strpos($prodi_raw, 'teknik komputer') !== false) {
                $alias_prodi = 'TKK';
            } elseif (strpos($prodi_raw, 'teknik informatika') !== false) {
                $alias_prodi = 'TIF';
            }
            
            $clean_nama = preg_replace('/[^A-Za-z0-9]/', '', $data_info['nama_kelompok']);
            $timestamp = date('YmdHis');
            
            $new_name = "SuratPenerimaan_{$alias_prodi}_{$clean_nama}_{$timestamp}.pdf";
            
            $upload_dir = "../uploads/dokumen_magang/"; 
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $target_file = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['file_penerimaan']['tmp_name'], $target_file)) {
              $jenis = 'surat_penerimaan';
              $today = date('Y-m-d');
              
              $cekDoc = mysqli_query($conn, "SELECT id_dokumen FROM dokumen_magang WHERE id_pengajuan = $id_pengajuan_post AND jenis = '$jenis'");
              
              if (mysqli_num_rows($cekDoc) > 0) {
                  $qUpd = "UPDATE dokumen_magang SET file_path = ?, tanggal_upload = ? WHERE id_pengajuan = ? AND jenis = ?";
                  $stmtUpd = mysqli_prepare($conn, $qUpd);
                  mysqli_stmt_bind_param($stmtUpd, 'ssis', $new_name, $today, $id_pengajuan_post, $jenis);
                  mysqli_stmt_execute($stmtUpd);
              } else {
                  $qIns = "INSERT INTO dokumen_magang (id_pengajuan, jenis, file_path, tanggal_upload) VALUES (?, ?, ?, ?)";
                  $stmtIns = mysqli_prepare($conn, $qIns);
                  mysqli_stmt_bind_param($stmtIns, 'isss', $id_pengajuan_post, $jenis, $new_name, $today);
                  mysqli_stmt_execute($stmtIns);
              }

              $qUpdateStatus = "UPDATE mahasiswa m
                                JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
                                SET m.status_magang = 'magang_aktif'
                                WHERE ak.id_kelompok = (
                                    SELECT id_kelompok FROM pengajuan_magang WHERE id_pengajuan = ?
                                )";
              $stmtStatus = mysqli_prepare($conn, $qUpdateStatus);
              mysqli_stmt_bind_param($stmtStatus, 'i', $id_pengajuan_post);
              
              if (mysqli_stmt_execute($stmtStatus)) {
                  $message_success = "Surat Penerimaan berhasil diupload! Status Anda sekarang MAGANG AKTIF. Harap tunggu Korbid mengirimkan Surat Pelaksanaan.";
              } else {
                  $message_success = "Surat Penerimaan berhasil diupload, namun gagal mengubah status mahasiswa. Harap hubungi admin.";
              }
              
              mysqli_stmt_close($stmtStatus);
          } else {
              $message_error = "Gagal mengupload file.";
          }
        }
    } else {
        $message_error = "File belum dipilih atau terjadi error upload.";
    }
}

// -------------------------------------------------
// B. AMBIL DATA KELOMPOK USER
// -------------------------------------------------
$id_kelompok = null;
$qKel = "SELECT ak.id_kelompok, ak.peran
         FROM anggota_kelompok ak
         JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
         WHERE m.id_user = ? LIMIT 1";
$stmtKel = mysqli_prepare($conn, $qKel);
mysqli_stmt_bind_param($stmtKel, 'i', $id_user);
mysqli_stmt_execute($stmtKel);
$resKel = mysqli_stmt_get_result($stmtKel);
$dataKelompok = mysqli_fetch_assoc($resKel);

if ($dataKelompok) {
    $id_kelompok = $dataKelompok['id_kelompok'];
    $is_ketua = ($dataKelompok['peran'] === 'ketua');
} else {
    echo "<div style='padding:20px; text-align:center;'><h3>Anda belum tergabung dalam kelompok!</h3></div>";
    exit;
}

// -------------------------------------------------
// C. AMBIL DATA PENGAJUAN (+ INFO DOSEN & SURAT)
// -------------------------------------------------
/* Query ini join ke tabel:
   1. pengajuan_magang (pm)
   2. kelompok (k) -> Untuk ambil id_dosen_pembimbing
   3. dosen (d) -> Data dosen
   4. users (u_dosen) -> Nama & Email dosen
   5. mitra_perusahaan (mp) -> Info mitra
   6. dokumen_magang (doc_terima, doc_laksana) -> Cek ketersediaan dokumen
*/

$query = "
    SELECT 
      pm.*,
      k.nama_kelompok,
      mp.nama_mitra,
      mp.alamat,
      mp.bidang,
      u_ketua.nama AS nama_ketua,
      
      -- Info Dosen Pembimbing (dari tabel kelompok)
      u_dosen.nama AS nama_dosen,
      u_dosen.email AS email_dosen,
      d.kontak AS kontak_dosen,
      
      -- Cek Dokumen (Subqueries untuk efisiensi di list view)
      (SELECT file_path FROM dokumen_magang WHERE id_pengajuan = pm.id_pengajuan AND jenis = 'surat_penerimaan' LIMIT 1) as file_penerimaan,
      (SELECT file_path FROM dokumen_magang WHERE id_pengajuan = pm.id_pengajuan AND jenis = 'surat_pelaksanaan' LIMIT 1) as file_pelaksanaan

    FROM pengajuan_magang pm
    JOIN kelompok k ON pm.id_kelompok = k.id_kelompok
    JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    JOIN mahasiswa m_ketua ON pm.id_mahasiswa_ketua = m_ketua.id_mahasiswa
    JOIN users u_ketua ON m_ketua.id_user = u_ketua.id
    
    -- Join Dosen (Left join karena mungkin belum disetujui/belum ada dosen)
    LEFT JOIN dosen d ON k.id_dosen_pembimbing = d.id_dosen 
    LEFT JOIN users u_dosen ON d.id_user = u_dosen.id
    
    WHERE pm.id_kelompok = ?
    ORDER BY pm.tanggal_pengajuan DESC, pm.id_pengajuan DESC
    ";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_kelompok);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<style>
  .status-container { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width:1100px; margin:20px auto; font-family: 'Segoe UI', sans-serif; }
  .status-card { border:1px solid #eef2f7; border-radius:12px; padding:25px; margin-bottom:25px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: transform 0.2s; }
  .status-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
  
  .status-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f8f9fa; }
  .status-badge { padding:8px 16px; border-radius:20px; font-size:13px; font-weight:600; display: inline-flex; align-items: center; gap: 6px; }
  .status-menunggu { background:#fff8e1; color:#b78900; }
  .status-diterima { background:#e8f5e9; color:#2e7d32; }
  .status-ditolak { background:#ffebee; color:#c62828; }

  .info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-top:10px; }
  .info-item label { display:block; font-size:12px; color:#888; margin-bottom:4px; font-weight:600; text-transform:uppercase; }
  .info-item span { font-size:15px; color:#333; font-weight:500; }

  /* Dosen Box */
  .dosen-box { background: #f0f7ff; border: 1px solid #cce5ff; border-radius: 8px; padding: 15px; margin-top: 20px; }
  .dosen-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; color: #004085; font-weight: 700; }
  .dosen-detail { display: flex; gap: 20px; flex-wrap: wrap; }
  .dosen-contact { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #0056b3; background: #fff; padding: 5px 12px; border-radius: 20px; }

  /* Flow Steps */
  .flow-section { margin-top: 25px; border-top: 2px dashed #e0e0e0; padding-top: 20px; }
  .flow-title { font-size: 16px; font-weight: 700; color: #444; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
  
  .action-buttons { display: flex; gap: 10px; margin-top: 10px; }
  .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; transition: all 0.3s; }
  .btn-primary { background: #007bff; color: white; }
  .btn-success { background: #28a745; color: white; }
  .btn-danger { background: #dc3545; color: white; }
  .btn-secondary { background: #6c757d; color: white; }
  .btn:hover { opacity: 0.9; transform: translateY(-1px); }
  .btn-surat { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
  .btn-surat:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
  .btn-surat-penerimaan { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
  .btn-surat-penerimaan:hover { background: linear-gradient(135deg, #218838 0%, #1aa179 100%); }
  .btn-surat-upload { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000; }
  .btn-surat-upload:hover { background: linear-gradient(135deg, #e0a800 0%, #fb8c00 100%); }
  .btn-surat-pelaksanaan { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; }
  .btn-surat-pelaksanaan:hover { background: linear-gradient(135deg, #0056b3 0%, #004085 100%); }
  .link-file { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; font-size: 13px; font-weight: 600; transition: all 0.3s; }
  .link-file:hover { background: #138496; transform: translateY(-1px); }
  .link-file i { font-size: 12px; }


  .alert-box { padding: 15px; border-radius: 8px; margin-top: 15px; }
  .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
  .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
  .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
  .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

  .upload-area { background: #f8f9fa; border: 2px dashed #ced4da; padding: 20px; text-align: center; border-radius: 8px; margin-top: 15px; }
  
  .hidden { display: none; }
</style>

<div class="status-container">
  <h2><i class="fas fa-clipboard-list"></i> Status Pengajuan Magang</h2>

  <?php if ($message_success): ?>
    <div class="alert-box alert-success"><i class="fas fa-check-circle"></i> <?= $message_success ?></div>
  <?php endif; ?>
  <?php if ($message_error): ?>
    <div class="alert-box alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $message_error ?></div>
  <?php endif; ?>

  <?php if (count($rows) === 0): ?>
    <div style="text-align:center; padding:50px; color:#999;">
      <i class="fas fa-inbox" style="font-size:64px; opacity:0.3; margin-bottom:20px;"></i>
      <h3>Belum Ada Pengajuan</h3>
      <p>Kelompok Anda belum mengajukan magang.</p>
      <?php if ($is_ketua): ?>
        <a href="index.php?page=pengajuan_Mitra" class="btn btn-primary" style="margin-top:10px;">
          <i class="fas fa-plus"></i> Mulai Pengajuan
        </a>
      <?php endif; ?>
    </div>
  <?php else: ?>

  <?php foreach ($rows as $index => $row): 
      // Logic Status Badge
      $status_class = "status-menunggu";
      $status_text  = "Menunggu Persetujuan Korbid";
      $icon = "fa-clock";
      
      if ($row['status_pengajuan'] === 'diterima') {
          $status_class = "status-diterima";
          $status_text  = "Disetujui Korbid";
          $icon = "fa-check-circle";
      } elseif ($row['status_pengajuan'] === 'ditolak') {
          $status_class = "status-ditolak";
          $status_text  = "Ditolak Korbid";
          $icon = "fa-times-circle";
      } elseif ($row['status_pengajuan'] === 'ditolak_mitra') {
          $status_class = "status-ditolak";
          $status_text  = "Ditolak Mitra";
          $icon = "fa-times-circle";
      }
  ?>
      <div class="status-card">
        <div class="status-header">
          <div>
            <h3 style="margin:0 0 5px 0; color:#333;"><?= htmlspecialchars($row['nama_kelompok']) ?></h3>
            <div style="font-size:13px; color:#666;">
              <i class="far fa-calendar-alt"></i> Diajukan: <?= date('d F Y', strtotime($row['tanggal_pengajuan'])) ?>
            </div>
          </div>
          <span class="status-badge <?= $status_class ?>">
            <i class="fas <?= $icon ?>"></i> <?= $status_text ?>
          </span>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <label>Mitra Perusahaan</label>
            <span><?= htmlspecialchars($row['nama_mitra']) ?></span>
          </div>
          <div class="info-item">
            <label>Bidang</label>
            <span><?= htmlspecialchars($row['bidang']) ?></span>
          </div>
          <div class="info-item">
            <label>Alamat Mitra</label>
            <span><?= htmlspecialchars($row['alamat']) ?></span>
          </div>
          <div class="info-item">
            <label>Ketua Kelompok</label>
            <span><?= htmlspecialchars($row['nama_ketua']) ?></span>
          </div>
        </div>

        <?php if ($row['status_pengajuan'] === 'ditolak'): ?>
          <div class="alert-box alert-danger" style="margin-top:20px;">
            <strong><i class="fas fa-heart-broken"></i> Ditolak oleh Mitra Perusahaan</strong>
            <p style="margin-top:5px;">Mohon maaf, lamaran magang kelompok Anda tidak diterima oleh <strong><?= htmlspecialchars($row['nama_mitra']) ?></strong>.</p>
          </div>
          
          <div class="alert-box alert-info">
            <strong><i class="fas fa-info-circle"></i> Langkah Selanjutnya:</strong>
            <ul style="margin-left: 15px; margin-top: 5px;">
               <li>Anda dapat mengajukan ulang ke mitra lain.</li>
               <li>Data kelompok dan dosen pembimbing tetap tersimpan.</li>
               <li>Silakan pilih mitra baru melalui menu Pengajuan Mitra.</li>
            </ul>
          </div>
          
          <?php if ($index === 0 && $is_ketua): ?>
             <a href="index.php?page=pengajuan_Mitra" class="btn btn-primary" style="margin-top:15px;">
               <i class="fas fa-redo"></i> Ajukan Ulang ke Mitra Lain
             </a>
          <?php endif; ?>

        <?php elseif ($row['status_pengajuan'] === 'diterima'): ?>

          <div class="dosen-box">
            <div class="dosen-header"><i class="fas fa-chalkboard-teacher"></i> Dosen Pembimbing Ditugaskan</div>
            <?php if ($row['nama_dosen']): ?>
              <div class="dosen-detail">
                <div class="dosen-contact"><i class="fas fa-user"></i> <?= htmlspecialchars($row['nama_dosen']) ?></div>
                <?php if($row['kontak_dosen']): ?>
                  <div class="dosen-contact"><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($row['kontak_dosen']) ?></div>
                <?php endif; ?>
                <?php if($row['email_dosen']): ?>
                  <div class="dosen-contact"><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email_dosen']) ?></div>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <small style="color:#666;"><em>Data dosen belum diperbarui.</em></small>
            <?php endif; ?>
          </div>

          <div class="flow-section">
            <div class="flow-title"><i class="fas fa-paper-plane"></i> Status Lamaran ke Mitra</div>
            
            <?php if (empty($row['file_penerimaan'])): ?>
              <div id="konfirmasi-mitra-<?= $index ?>">
                <p>Silakan kirimkan Proposal Magang ke <strong><?= htmlspecialchars($row['nama_mitra']) ?></strong>. Setelah mendapatkan balasan, konfirmasi hasilnya di bawah ini:</p>
                
                <div class="action-buttons">
                  <button type="button" class="btn btn-success" onclick="showUploadForm(<?= $index ?>)">
                    <i class="fas fa-check"></i> Lamaran Diterima Mitra
                  </button>
                  
                  <!-- FORM Submit Ditolak Mitra -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="ditolak_mitra">
                    <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin lamaran ditolak oleh mitra? Status akan diubah dan Anda perlu mengajukan ulang.')">
                      <i class="fas fa-times"></i> Lamaran Ditolak Mitra
                    </button>
                  </form>
                </div>
              </div>

              <div id="form-upload-<?= $index ?>" class="upload-area hidden">
                <h4 style="margin-bottom:10px; color:#28a745;">Selamat! Langkah Selanjutnya:</h4>
                <p style="margin-bottom:15px; font-size:14px; color:#555;">
                  Upload bukti <strong>Surat Penerimaan</strong> dari Mitra (PDF). File ini akan dikirim ke Koordinator Bidang untuk diproses menjadi Surat Pelaksanaan.
                </p>
                
                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="upload_penerimaan">
                  <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                  
                  <input type="file" name="file_penerimaan" accept=".pdf" required style="margin-bottom:10px;">
                  <br>
                  <button type="button" class="btn btn-secondary" onclick="hideUploadForm(<?= $index ?>)">Batal</button>
                  <button type="submit" class="btn btn-success">Upload & Kirim ke Korbid</button>
                </form>
              </div>

            <?php else: ?>
              <div class="alert-box alert-success">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                   <span><i class="fas fa-check-circle"></i> <strong>Surat Penerimaan Mitra telah diupload.</strong></span>
                   <a href="../uploads/dokumen_magang/<?= $row['file_penerimaan'] ?>" target="_blank" class="link-file">
                     <i class="fas fa-eye"></i> Lihat File
                   </a>
                </div>
                
                <?php if (empty($row['file_pelaksanaan'])): ?>
                  <p style="margin-top:10px; font-size:14px; color:#666;">
                    <i class="fas fa-clock"></i> <em>Status: Menunggu Surat Pelaksanaan dari Koordinator Bidang.</em>
                  </p>
                <?php endif; ?>
              </div>

              <?php if (!empty($row['file_pelaksanaan'])): ?>
                <div class="alert-box alert-info" style="margin-top:15px; border-left: 5px solid #007bff;">
                  <h4 style="margin-bottom:10px; color:#0c5460;"><i class="fas fa-file-signature"></i> Surat Pelaksanaan Siap!</h4>
                  <p style="margin-bottom:15px;">Koordinator Bidang telah menerbitkan Surat Pelaksanaan. Silakan download dan serahkan ke Mitra.</p>
                  <a href="../uploads/dokumen_magang/<?= $row['file_pelaksanaan'] ?>" target="_blank" class="btn-surat btn-surat-pelaksanaan">
                    <i class="fas fa-download"></i> Download Surat Pelaksanaan
                  </a>
                </div>
              <?php endif; ?>

            <?php endif; ?>
          </div>

        <?php endif; ?>

      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</div>

<script>
function showUploadForm(index) {
  document.getElementById('konfirmasi-mitra-' + index).classList.add('hidden');
  document.getElementById('form-upload-' + index).classList.remove('hidden');
  document.getElementById('info-reject-' + index).classList.add('hidden');
}

function hideUploadForm(index) {
  document.getElementById('konfirmasi-mitra-' + index).classList.remove('hidden');
  document.getElementById('form-upload-' + index).classList.add('hidden');
}

function showUploadForm(index) {
  document.getElementById('konfirmasi-mitra-' + index).classList.add('hidden');
  document.getElementById('form-upload-' + index).classList.remove('hidden');
}

function hideUploadForm(index) {
  document.getElementById('konfirmasi-mitra-' + index).classList.remove('hidden');
  document.getElementById('form-upload-' + index).classList.add('hidden');
}

</script>