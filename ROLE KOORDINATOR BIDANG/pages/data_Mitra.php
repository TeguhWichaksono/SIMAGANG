<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/SIMAGANG/Koneksi/koneksi.php';

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM mitra_perusahaan ORDER BY id_mitra DESC");
?>

<link rel="stylesheet" href="styles/data_Mitra.css?v=<?= time() ?>">

<div class="content-section">
  <h3><i class="fas fa-building"></i> Daftar Mitra Perusahaan</h3>

  <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
      <i class="fas fa-times-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="toolbar">
    <div class="toolbar-left">
      <a href="pages/Create_Mitra.php" class="btn btn-add">
        <i class="fas fa-plus"></i> Tambah
      </a>
      
      <button onclick="openImportModal()" class="btn btn-import">
        <i class="fas fa-file-import"></i> Import Excel
      </button>
      
      <a href="pages/Export_Mitra_Excel.php" class="btn btn-export">
        <i class="fas fa-file-excel"></i> Export
      </a>
    </div>

    <div class="search-bar-data">
      <input type="text" id="searchMitra" placeholder="Cari nama, bidang, atau alamat..." />
    </div>
  </div>

  <div class="table-responsive">
    <table id="tabelMitra">
      <thead>
        <tr>
          <th width="5%">No</th>
          <th>Nama Mitra</th>
          <th>Bidang</th>
          <th>Alamat</th>
          <th>Kontak</th>
          <th>Status</th>
          <th width="10%">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)) : ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($row['nama_mitra']); ?></strong></td>
            <td><?= htmlspecialchars($row['bidang']); ?></td>
            <td><?= htmlspecialchars($row['alamat']); ?></td>
            <td>
                <?php if($row['kontak']): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['kontak']) ?>" target="_blank" style="text-decoration:none; color:#28a745; font-weight:600;">
                        <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($row['kontak']); ?>
                    </a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>

            <td>
                <span class="badge <?= ($row['status'] == 'aktif') ? 'status-aktif' : 'status-nonaktif' ?>">
                    <?= ucfirst($row['status']); ?>
                </span>
            </td>
            <td>
              <div style="display:flex; gap:5px;">
                  <button class="action-btn edit-btn" onclick="window.location.href='pages/Edit_Mitra.php?id=<?= $row['id_mitra']; ?>'" title="Edit">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button class="action-btn delete-btn" onclick="confirmDelete(<?= $row['id_mitra']; ?>)" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="importModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">Import Data Mitra</div>
        <form action="pages/Import_Mitra.php" method="POST" enctype="multipart/form-data">
            <p style="margin-bottom:15px; font-size:13px; color:#666; line-height:1.5;">
                Gunakan file format <strong>.CSV</strong> (Excel -> Save As -> CSV).<br>
                Pastikan urutan kolom: <br>
                <code>Nama, Bidang, Alamat, Kontak, Status</code>
            </p>
            
            <input type="file" name="file_mitra" class="file-input" accept=".csv" required style="margin-bottom:20px;">
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Batal</button>
                <button type="submit" class="btn btn-import" name="import">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<style>
.alert {
  padding: 12px 20px;
  margin-bottom: 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: slideDown 0.3s ease;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border-left: 4px solid #28a745;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border-left: 4px solid #dc3545;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<script>
  // Search Function
  document.getElementById("searchMitra").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#tabelMitra tbody tr").forEach((row) => {
      row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
  });

  // Delete Confirmation
  function confirmDelete(id) {
    if (confirm("Apakah Anda yakin ingin menghapus mitra ini?")) {
      window.location.href = "pages/Delete_Mitra.php?id=" + id;
    }
  }

  // Modal Logic
  function openImportModal() {
    document.getElementById('importModal').style.display = 'flex';
  }
  function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
  }
  
  // Close modal when clicking outside
  window.onclick = function(event) {
    let modal = document.getElementById('importModal');
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

  // Auto-hide alert after 5 seconds
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    });
  }, 5000);
</script>