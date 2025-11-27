<?php
// dokumen_Magang.php
?>

<link rel="stylesheet" href="styles/dokumen_Magang.css">

<div class="content-section">
  <h3><i class="fas fa-file-alt"></i> Daftar Dokumen Mahasiswa</h3>

  <div class="search-bar-data">
    <input type="text" id="searchDokumen" placeholder="Cari dokumen..." />
  </div>

  <table id="tabelDokumen">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Mahasiswa</th>
        <th>NIM</th>
        <th>Kelompok</th>
        <th>Jenis Dokumen</th>
        <th>Nama File</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Septiya Qorrata Ayun</td>
        <td>E31241242</td>
        <td>Kelompok 3</td>
        <td>Proposal</td>
        <td>Proposal_SMG.pdf</td>
        <td>
          <button class="btn-action btn-view" onclick="viewDoc('Proposal_SMG.pdf')">Lihat</button>
          <button class="btn-action btn-download" onclick="downloadDoc('Proposal_SMG.pdf')">Download</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Rizky Ramadhan</td>
        <td>E31241243</td>
        <td>Kelompok 3</td>
        <td>Laporan Bab I</td>
        <td>Laporan_Bab1.pdf</td>
        <td>
          <button class="btn-action btn-view" onclick="viewDoc('Laporan_Bab1.pdf')">Lihat</button>
          <button class="btn-action btn-download" onclick="downloadDoc('Laporan_Bab1.pdf')">Download</button>
        </td>
      </tr>
      <tr>
        <td>3</td>
        <td>Naila Fadhilah</td>
        <td>E31241244</td>
        <td>Kelompok 4</td>
        <td>Laporan Bab II</td>
        <td>Laporan_Bab2.pdf</td>
        <td>
          <button class="btn-action btn-view" onclick="viewDoc('Laporan_Bab2.pdf')">Lihat</button>
          <button class="btn-action btn-download" onclick="downloadDoc('Laporan_Bab2.pdf')">Download</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  // Fungsi pencarian dokumen
  document.getElementById("searchDokumen").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#tabelDokumen tbody tr").forEach((row) => {
      row.style.display = row.textContent.toLowerCase().includes(value)
        ? ""
        : "none";
    });
  });

  // Fungsi Lihat Dokumen
  function viewDoc(fileName) {
    alert("Preview dokumen: " + fileName);
    // bisa diganti dengan modal/iframe PDF
  }

  // Fungsi Download Dokumen
  function downloadDoc(fileName) {
    alert("Mengunduh dokumen: " + fileName);
    // bisa diganti window.location = "path/fileName"
  }
</script>
