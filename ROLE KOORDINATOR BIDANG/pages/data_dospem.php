<?php
include '../Koneksi/koneksi.php';

// Query untuk mengambil data dosen pembimbing
$query = "
    SELECT 
        dosen.id_dosen,
        dosen.nidn,
        dosen.prodi,
        dosen.kontak,
        users.nama,
        users.email
    FROM dosen
    INNER JOIN users ON users.id = dosen.id_user
    WHERE users.role = 'Dosen Pembimbing'
    ORDER BY users.nama ASC
";

$result = mysqli_query($conn, $query);

// Debug: Cek apakah query berhasil dan ada data
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$totalData = mysqli_num_rows($result);

// Ambil data untuk filter prodi
$prodiQuery = "SELECT DISTINCT prodi FROM dosen WHERE prodi IS NOT NULL ORDER BY prodi ASC";
$prodiResult = mysqli_query($conn, $prodiQuery);
?>

<link rel="stylesheet" href="styles/data_dospem.css">

<div class="content-section">
  <h3><i class="fas fa-chalkboard-teacher"></i> Data Dosen Pembimbing</h3>

  <!-- Filter Section -->
  <div class="filter-wrapper">
    <div class="search-bar-data">
      <input type="text" id="searchDosen" placeholder="Cari berdasarkan NIDN, Nama, atau Kontak..." />
    </div>

    <div class="filter-controls">
      <select id="filterProdi" class="filter-select">
        <option value="">Semua Program Studi</option>
        <?php while($prodi = mysqli_fetch_assoc($prodiResult)) { ?>
          <option value="<?= htmlspecialchars($prodi['prodi']); ?>">
            <?= htmlspecialchars($prodi['prodi']); ?>
          </option>
        <?php } ?>
      </select>

      <button class="btn-reset" onclick="resetFilter()">
        <i class="fas fa-redo-alt"></i> Reset
      </button>
    </div>
  </div>

  <!-- Info jumlah data -->
  <!-- <div class="info-badge">
    <div class="badge-icon">
      <i class="fas fa-users"></i>
    </div>
    <div class="badge-content">
      <span class="badge-label">Total Dosen Pembimbing</span>
      <span class="badge-count" id="infoCount"><?= $totalData; ?> Dosen</span>
    </div>
  </div> -->

  <table id="tabelDosen">
    <thead>
      <tr>
        <th>No</th>
        <th>NIDN</th>
        <th>Nama Dosen</th>
        <th>Email</th>
        <th>Kontak</th>
        <th>Program Studi</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      mysqli_data_seek($result, 0);
      while ($row = mysqli_fetch_assoc($result)) {
      ?>
      <tr data-prodi="<?= htmlspecialchars($row['prodi']); ?>">
        <td><?= $no++; ?></td>
        <td><?= htmlspecialchars($row['nidn']); ?></td>
        <td><?= htmlspecialchars($row['nama']); ?></td>
        <td><?= htmlspecialchars($row['email']); ?></td>
        <td><?= htmlspecialchars($row['kontak']); ?></td>
        <td><?= htmlspecialchars($row['prodi']); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <!-- Pesan jika tidak ada data -->
  <div id="noData" style="display: none; text-align: center; padding: 30px; color: #999;">
    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px;"></i>
    <p style="margin: 0;">Tidak ada data yang ditemukan</p>
  </div>
</div>

<script>
// Fungsi untuk search dan filter
function filterTable() {
  const searchValue = document.getElementById("searchDosen").value.toLowerCase();
  const prodiValue = document.getElementById("filterProdi").value.toLowerCase();
  const rows = document.querySelectorAll("#tabelDosen tbody tr");
  let visibleCount = 0;

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase();
    const prodi = row.getAttribute('data-prodi').toLowerCase();
    
    const matchSearch = text.includes(searchValue);
    const matchProdi = prodiValue === "" || prodi === prodiValue;
    
    if (matchSearch && matchProdi) {
      row.style.display = "";
      visibleCount++;
      row.querySelector('td:first-child').textContent = visibleCount;
    } else {
      row.style.display = "none";
    }
  });

  // Update info count
  const totalRows = rows.length;
  const infoCount = document.getElementById("infoCount");
  
  if (visibleCount === totalRows) {
    infoCount.innerHTML = `${totalRows} Dosen`;
  } else {
    infoCount.innerHTML = `${visibleCount} dari ${totalRows} Dosen`;
  }

  // Tampilkan pesan jika tidak ada data
  const noData = document.getElementById("noData");
  const table = document.getElementById("tabelDosen");
  if (visibleCount === 0) {
    table.style.display = "none";
    noData.style.display = "block";
  } else {
    table.style.display = "table";
    noData.style.display = "none";
  }
}

// Fungsi reset filter
function resetFilter() {
  document.getElementById("searchDosen").value = "";
  document.getElementById("filterProdi").value = "";
  filterTable();
}

// Event listeners
document.getElementById("searchDosen").addEventListener("keyup", filterTable);
document.getElementById("filterProdi").addEventListener("change", filterTable);
</script>