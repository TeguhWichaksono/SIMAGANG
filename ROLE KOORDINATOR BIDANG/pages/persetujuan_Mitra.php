<?php
// persetujuan_Mitra.php
?>

<link rel="stylesheet" href="styles/persetujuan_Mitra.css">

<div class="content-section">
  <h3><i class="fas fa-check-circle"></i> Daftar Mitra yang Diajukan Mahasiswa</h3>
  <div class="search-bar-data">
    <input type="text" id="searchMitraInput" placeholder="Cari Mitra atau Mahasiswa..." />
  </div>

  <table id="tabelMitra">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Mitra / Instansi</th>
        <th>Diajukan oleh Mahasiswa</th>
        <th>Anggota Kelompok</th>
        <th>Kontak Mitra</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>PT Jember Abadi</td>
        <td>Septiya Qorrata Ayun</td>
        <td>
          <ul class="anggota-list">
            <li>Septiya Qorrata Ayun (E31241242)</li>
            <li>Rizky Ramadhan (E31241243)</li>
            <li>Putri Alifia (E31241250)</li>
          </ul>
        </td>
        <td>0812-3456-7890</td>
        <td class="status-pending">Pending</td>
        <td>
          <button class="btn-action btn-acc">ACC</button>
          <button class="btn-action btn-tolak">Tolak</button>
        </td>
      </tr>

      <tr>
        <td>2</td>
        <td>CV Sentosa</td>
        <td>Naila Fadhilah</td>
        <td>
          <ul class="anggota-list">
            <li>Naila Fadhilah (E31241244)</li>
            <li>Rendi Saputra (E31241255)</li>
            <li>Devi Anggraini (E31241260)</li>
          </ul>
        </td>
        <td>0896-1234-5678</td>
        <td class="status-pending">Pending</td>
        <td>
          <button class="btn-action btn-acc">ACC</button>
          <button class="btn-action btn-tolak">Tolak</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  // Fungsi ACC dan Tolak Mitra
  document.querySelectorAll("#tabelMitra tbody tr").forEach((row) => {
    const btnAcc = row.querySelector(".btn-acc");
    const btnTolak = row.querySelector(".btn-tolak");
    const statusCell = row.querySelector("td:nth-child(6)");

    btnAcc.addEventListener("click", () => {
      statusCell.textContent = "Disetujui";
      statusCell.className = "status-disetujui";
      alert(`Mitra "${row.cells[1].textContent}" telah disetujui!`);
    });

    btnTolak.addEventListener("click", () => {
      statusCell.textContent = "Ditolak";
      statusCell.className = "status-ditolak";
      alert(`Mitra "${row.cells[1].textContent}" telah ditolak!`);
    });
  });

  // Fungsi pencarian
  document.getElementById("searchMitraInput").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#tabelMitra tbody tr").forEach((row) => {
      row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
  });
</script>
