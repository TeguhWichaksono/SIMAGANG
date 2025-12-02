<?php
include '../Koneksi/koneksi.php';

$query = "
    SELECT 
        dosen.nidn,
        dosen.prodi,
        dosen.kontak,
        users.nama
    FROM dosen
    JOIN users ON users.id = dosen.id_user
    WHERE users.role = 'dosen_pembimbing'
    ORDER BY users.nama ASC
";

$result = mysqli_query($conn, $query);
?>

<link rel="stylesheet" href="styles/data_dospem.css">

<div class="content-section">
  <h3><i class="fas fa-user-graduate"></i> Data Dosen Pembimbing</h3>

  <div class="search-bar-data">
    <input type="text" id="searchMahasiswa" placeholder="Cari Dosen..." />
  </div>

  <table id="tabelMahasiswa">
    <thead>
      <tr>
        <th>No</th>
        <th>NIDN</th>
        <th>Nama Dosen</th>
        <th>Kontak</th>
        <th>Program Studi</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      while ($row = mysqli_fetch_assoc($result)) {
      ?>
      <tr>
        <td><?= $no++; ?></td>
        <td><?= $row['nidn']; ?></td>
        <td><?= $row['nama']; ?></td>
        <td><?= $row['kontak']; ?></td>
        <td><?= $row['prodi']; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
  document.getElementById("searchMahasiswa").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#tabelMahasiswa tbody tr").forEach((row) => {
      row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
  });
</script>
