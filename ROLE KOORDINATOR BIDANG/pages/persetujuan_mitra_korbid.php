<?php
/**
 * File: persetujuan_mitra_korbid.php
 * Role: Koordinator Bidang Magang
 * Fungsi: Menampilkan dan memproses pengajuan mitra baru dari mahasiswa
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../Koneksi/koneksi.php';

// Cek login dan role
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Koordinator Bidang Magang') {
    header("Location: ../Login/login.php");
    exit;
}

$id_korbid = $_SESSION['id'];

// Ambil data pengajuan mitra
$query = "SELECT 
            pm.id_pengajuan,
            pm.nama_perusahaan,
            pm.alamat,
            pm.bidang,
            pm.kontak,
            pm.tanggal_pengajuan,
            pm.status_pengajuan,
            pm.catatan,
            u.nama as nama_mahasiswa,
            u.nim as nim_mahasiswa,
            mhs.prodi,
            mhs.kontak as kontak_mahasiswa
          FROM pengajuan_mitra pm
          LEFT JOIN mahasiswa mhs ON pm.id_mahasiswa = mhs.id_mahasiswa
          LEFT JOIN users u ON mhs.id_user = u.id
          ORDER BY 
            CASE pm.status_pengajuan
              WHEN 'menunggu' THEN 1
              WHEN 'disetujui' THEN 2
              WHEN 'ditolak' THEN 3
            END,
            pm.tanggal_pengajuan DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Persetujuan Mitra Baru</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f6fa;
      padding: 20px;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    h2 {
      margin-bottom: 25px;
      color: #2c3e50;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .info-banner {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .info-banner i {
      font-size: 24px;
    }

    .filter-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
      border-bottom: 2px solid #e0e0e0;
      padding-bottom: 10px;
    }

    .tab-btn {
      padding: 10px 20px;
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 15px;
      font-weight: 500;
      color: #666;
      border-radius: 6px 6px 0 0;
      transition: all 0.3s;
    }

    .tab-btn.active {
      background: #007bff;
      color: white;
    }

    .tab-btn:hover:not(.active) {
      background: #f0f0f0;
    }

    .search-box {
      margin-bottom: 20px;
    }

    .search-box input {
      width: 100%;
      padding: 12px 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background: #f8f9fa;
      font-weight: 600;
      color: #495057;
      position: sticky;
      top: 0;
    }

    tr:hover {
      background: #f8f9fa;
    }

    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.3s;
      margin-right: 5px;
    }

    .btn-view {
      background: #007bff;
      color: white;
    }

    .btn-view:hover {
      background: #0056b3;
    }

    .btn-approve {
      background: #28a745;
      color: white;
    }

    .btn-approve:hover {
      background: #218838;
    }

    .btn-reject {
      background: #dc3545;
      color: white;
    }

    .btn-reject:hover {
      background: #c82333;
    }

    .status-badge {
      padding: 5px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }

    .status-menunggu {
      background: #fff3cd;
      color: #856404;
    }

    .status-disetujui {
      background: #d4edda;
      color: #155724;
    }

    .status-ditolak {
      background: #f8d7da;
      color: #721c24;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background: white;
      margin: 3% auto;
      padding: 30px;
      width: 90%;
      max-width: 800px;
      max-height: 85vh;
      overflow-y: auto;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.3);
      animation: slideDown 0.3s;
    }

    @keyframes slideDown {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .close-btn {
      float: right;
      font-size: 28px;
      font-weight: bold;
      color: #aaa;
      cursor: pointer;
      line-height: 20px;
    }

    .close-btn:hover {
      color: #000;
    }

    .modal h3 {
      margin-bottom: 20px;
      color: #2c3e50;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }

    .info-section {
      margin-bottom: 25px;
    }

    .info-section h4 {
      margin-bottom: 12px;
      color: #495057;
      font-size: 16px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .info-label {
      font-size: 13px;
      color: #6c757d;
      font-weight: 600;
    }

    .info-value {
      font-size: 14px;
      color: #212529;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #495057;
    }

    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: inherit;
      font-size: 14px;
      resize: vertical;
      min-height: 100px;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid #e0e0e0;
    }

    .btn-submit {
      padding: 12px 30px;
      font-size: 15px;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #999;
    }

    .empty-state i {
      font-size: 64px;
      margin-bottom: 20px;
      opacity: 0.3;
    }

    .anggota-list {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-top: 10px;
    }

    .anggota-item {
      padding: 10px;
      border-bottom: 1px solid #e0e0e0;
    }

    .anggota-item:last-child {
      border-bottom: none;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>
    <i class="fas fa-handshake"></i>
    Persetujuan Mitra Baru
  </h2>

  <div class="info-banner">
    <i class="fas fa-info-circle"></i>
    <div>
      <strong>Informasi:</strong> Berikut adalah daftar mitra baru yang diajukan oleh mahasiswa dan memerlukan persetujuan Anda.
    </div>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
  <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
    <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
  </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
  <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
  </div>
  <?php endif; ?>

  <!-- Filter Tabs -->
  <div class="filter-tabs">
    <button class="tab-btn active" onclick="filterStatus('all')">
      Semua (<span id="count-all">0</span>)
    </button>
    <button class="tab-btn" onclick="filterStatus('menunggu')">
      Menunggu (<span id="count-menunggu">0</span>)
    </button>
    <button class="tab-btn" onclick="filterStatus('disetujui')">
      Disetujui (<span id="count-disetujui">0</span>)
    </button>
    <button class="tab-btn" onclick="filterStatus('ditolak')">
      Ditolak (<span id="count-ditolak">0</span>)
    </button>
  </div>

  <!-- Search Box -->
  <div class="search-box">
    <input type="text" id="searchInput" placeholder="Cari nama mitra atau mahasiswa..." onkeyup="searchTable()">
  </div>

  <?php if (mysqli_num_rows($result) === 0): ?>
  
  <div class="empty-state">
    <i class="fas fa-inbox"></i>
    <h3>Belum Ada Pengajuan Mitra</h3>
    <p>Tidak ada pengajuan mitra baru yang perlu diproses saat ini.</p>
  </div>

  <?php else: ?>

  <table id="pengajuanTable">
    <thead>
      <tr>
        <th width="5%">No</th>
        <th width="20%">Nama Mitra</th>
        <th width="18%">Diajukan Oleh</th>
        <th width="18%">Bidang</th>
        <th width="12%">Tanggal Ajukan</th>
        <th width="10%">Status</th>
        <th width="27%">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      $count_all = 0;
      $count_menunggu = 0;
      $count_disetujui = 0;
      $count_ditolak = 0;
      
      mysqli_data_seek($result, 0); // Reset pointer
      
      while ($row = mysqli_fetch_assoc($result)): 
        $count_all++;
        if ($row['status_pengajuan'] === 'menunggu') $count_menunggu++;
        if ($row['status_pengajuan'] === 'disetujui') $count_disetujui++;
        if ($row['status_pengajuan'] === 'ditolak') $count_ditolak++;
        
        $status_class = 'status-menunggu';
        $status_text = 'Menunggu';
        if ($row['status_pengajuan'] === 'disetujui') {
          $status_class = 'status-disetujui';
          $status_text = 'Disetujui';
        } elseif ($row['status_pengajuan'] === 'ditolak') {
          $status_class = 'status-ditolak';
          $status_text = 'Ditolak';
        }
      ?>
      <tr class="pengajuan-row" data-status="<?= $row['status_pengajuan'] ?>">
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
        <td><?= htmlspecialchars($row['bidang']) ?></td>
        <td><?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])) ?></td>
        <td><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
        <td>
          <button class="btn btn-view" onclick="viewDetail(<?= $row['id_pengajuan'] ?>)">
            <i class="fas fa-eye"></i> Detail
          </button>
          <?php if ($row['status_pengajuan'] === 'menunggu'): ?>
          <button class="btn btn-approve" onclick="openApproveModal(<?= $row['id_pengajuan'] ?>)">
            <i class="fas fa-check"></i> Setujui
          </button>
          <button class="btn btn-reject" onclick="openRejectModal(<?= $row['id_pengajuan'] ?>)">
            <i class="fas fa-times"></i> Tolak
          </button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <?php endif; ?>
</div>

<!-- Modal Detail -->
<div id="modalDetail" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('modalDetail')">&times;</span>
    <div id="detailContent"></div>
  </div>
</div>

<!-- Modal Approve -->
<div id="modalApprove" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('modalApprove')">&times;</span>
    <h3><i class="fas fa-check-circle" style="color: #28a745;"></i> Setujui Pengajuan Mitra</h3>
    
    <form id="formApprove" method="POST" action="pages/proses_persetujuan_mitra.php">
      <input type="hidden" name="action" value="approve">
      <input type="hidden" name="id_pengajuan" id="approve_id_pengajuan">
      
      <div class="form-group">
        <label><i class="fas fa-comment"></i> Catatan untuk Mahasiswa (Opsional)</label>
        <textarea name="catatan" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
      </div>
      
      <div class="modal-actions">
        <button type="button" class="btn btn-reject" onclick="closeModal('modalApprove')">
          Batal
        </button>
        <button type="submit" class="btn btn-approve btn-submit">
          <i class="fas fa-check"></i> Setujui Mitra
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Reject -->
<div id="modalReject" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('modalReject')">&times;</span>
    <h3><i class="fas fa-times-circle" style="color: #dc3545;"></i> Tolak Pengajuan Mitra</h3>
    
    <form id="formReject" method="POST" action="pages/proses_persetujuan_mitra.php">
      <input type="hidden" name="action" value="reject">
      <input type="hidden" name="id_pengajuan" id="reject_id_pengajuan">
      
      <div class="form-group">
        <label><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan <span style="color: red;">*</span></label>
        <textarea name="alasan" placeholder="Jelaskan alasan penolakan..." required></textarea>
      </div>
      
      <div class="modal-actions">
        <button type="button" class="btn btn-view" onclick="closeModal('modalReject')">
          Batal
        </button>
        <button type="submit" class="btn btn-reject btn-submit">
          <i class="fas fa-times"></i> Tolak Mitra
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Data pengajuan untuk JavaScript
const pengajuanData = <?= json_encode(mysqli_fetch_all(mysqli_query($conn, $query), MYSQLI_ASSOC)) ?>;

// Update counts
document.getElementById('count-all').textContent = <?= $count_all ?>;
document.getElementById('count-menunggu').textContent = <?= $count_menunggu ?>;
document.getElementById('count-disetujui').textContent = <?= $count_disetujui ?>;
document.getElementById('count-ditolak').textContent = <?= $count_ditolak ?>;

// Filter by status
function filterStatus(status) {
  const rows = document.querySelectorAll('.pengajuan-row');
  const tabs = document.querySelectorAll('.tab-btn');
  
  // Update active tab
  tabs.forEach(tab => tab.classList.remove('active'));
  event.target.classList.add('active');
  
  // Filter rows
  rows.forEach(row => {
    if (status === 'all' || row.dataset.status === status) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

// Search function
function searchTable() {
  const input = document.getElementById('searchInput');
  const filter = input.value.toUpperCase();
  const table = document.getElementById('pengajuanTable');
  const tr = table.getElementsByTagName('tr');
  
  for (let i = 1; i < tr.length; i++) {
    const tdMitra = tr[i].getElementsByTagName('td')[1];
    const tdMhs = tr[i].getElementsByTagName('td')[2];
    
    if (tdMitra || tdMhs) {
      const txtMitra = tdMitra.textContent || tdMitra.innerText;
      const txtMhs = tdMhs.textContent || tdMhs.innerText;
      
      if (txtMitra.toUpperCase().indexOf(filter) > -1 || 
          txtMhs.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = '';
      } else {
        tr[i].style.display = 'none';
      }
    }
  }
}

// View detail
function viewDetail(id) {
  const data = pengajuanData.find(p => p.id_pengajuan == id);
  if (!data) return;
  
  let html = `
    <h3>Detail Pengajuan Mitra</h3>
    
    <div class="info-section">
      <h4><i class="fas fa-building"></i> Informasi Mitra</h4>
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Nama Perusahaan</span>
          <span class="info-value">${data.nama_perusahaan}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Bidang Usaha</span>
          <span class="info-value">${data.bidang}</span>
        </div>
        <div class="info-item" style="grid-column: 1 / -1;">
          <span class="info-label">Alamat</span>
          <span class="info-value">${data.alamat}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Kontak</span>
          <span class="info-value">${data.kontak}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Tanggal Pengajuan</span>
          <span class="info-value">${new Date(data.tanggal_pengajuan).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</span>
        </div>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fas fa-user"></i> Informasi Pengaju</h4>
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Nama Mahasiswa</span>
          <span class="info-value">${data.nama_mahasiswa}</span>
        </div>
        <div class="info-item">
          <span class="info-label">NIM</span>
          <span class="info-value">${data.nim_mahasiswa}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Program Studi</span>
          <span class="info-value">${data.prodi}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Kontak</span>
          <span class="info-value">${data.kontak_mahasiswa}</span>
        </div>
      </div>
    </div>
    
    ${data.catatan ? `
    <div class="info-section">
      <h4><i class="fas fa-sticky-note"></i> Catatan Tambahan</h4>
      <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
        <p>${data.catatan}</p>
      </div>
    </div>
    ` : ''}
  `;
  
  document.getElementById('detailContent').innerHTML = html;
  document.getElementById('modalDetail').style.display = 'block';
}

// Open approve modal
function openApproveModal(id) {
  document.getElementById('approve_id_pengajuan').value = id;
  document.getElementById('modalApprove').style.display = 'block';
}

// Open reject modal
function openRejectModal(id) {
  document.getElementById('reject_id_pengajuan').value = id;
  document.getElementById('modalReject').style.display = 'block';
}

// Close modal
function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
  if (event.target.classList.contains('modal')) {
    event.target.style.display = 'none';
  }
}

// Form validation
document.getElementById('formApprove').addEventListener('submit', function(e) {
  if (!confirm('Apakah Anda yakin ingin menyetujui mitra ini? Mitra akan ditambahkan ke database dan dapat dipilih oleh mahasiswa lain.')) {
    e.preventDefault();
  }
});

document.getElementById('formReject').addEventListener('submit', function(e) {
  if (!confirm('Apakah Anda yakin ingin menolak pengajuan mitra ini?')) {
    e.preventDefault();
  }
});
</script>

</body>
</html>