<link rel="stylesheet" href="styles/data_Mahasiswa_Kelompok.css" />

<link rel="stylesheet" href="styles/data_Mahasiswa_Kelompok.css">

<div class="content-section">
  <h3><i class="fas fa-user-graduate"></i> Data Mahasiswa</h3>
  <div class="search-bar-data">
    <input type="text" id="searchMahasiswa" placeholder="Cari Mahasiswa..." />
  </div>

  <table id="tabelMahasiswa">
    <thead>
      <tr>
        <th>No</th>
        <th>NIM</th>
        <th>Nama Mahasiswa</th>
        <th>Program Studi</th>
        <th>Kelompok</th>
        <th>Dosen Pembimbing</th>
        <th>Mitra / Tempat Magang</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>E31241242</td>
        <td>Septiya Qorrata Ayun</td>
        <td>Manajemen Informatika</td>
        <td>Kelompok 3</td>
        <td>Ibu Fitri, M.Kom</td>
        <td>PT Jember Abadi</td>
        <td><span style="color: green;">Aktif</span></td>
      </tr>
      <tr>
        <td>2</td>
        <td>E31241243</td>
        <td>Rizky Ramadhan</td>
        <td>Manajemen Informatika</td>
        <td>Kelompok 3</td>
        <td>Ibu Fitri, M.Kom</td>
        <td>PT Jember Abadi</td>
        <td><span style="color: green;">Aktif</span></td>
      </tr>
      <tr>
        <td>3</td>
        <td>E31241244</td>
        <td>Naila Fadhilah</td>
        <td>Manajemen Informatika</td>
        <td>Kelompok 4</td>
        <td>Pak Budi, M.Kom</td>
        <td>CV Sentosa</td>
        <td><span style="color: orange;">Proses</span></td>
      </tr>
    </tbody>
  </table>
</div>

<div class="content-section">
  <h3><i class="fas fa-users"></i> Data Kelompok Magang</h3>
  <div class="search-bar-data">
    <input type="text" id="searchKelompok" placeholder="Cari Kelompok..." />
  </div>

  <table id="tabelKelompok">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Kelompok</th>
        <th>Anggota</th>
        <th>Dosen Pembimbing</th>
        <th>Mitra / Tempat Magang</th>
        <th>Kontak (WA)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Kelompok 3</td>
        <td>
          <ul class="anggota-list">
            <li>Septiya Qorrata Ayun (E31241242)</li>
            <li>Rizky Ramadhan (E31241243)</li>
            <li>Putri Alifia (E31241250)</li>
          </ul>
        </td>
        <td>Ibu Fitri, M.Kom</td>
        <td>PT Jember Abadi</td>
        <td>0812-3456-7890</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Kelompok 4</td>
        <td>
          <ul class="anggota-list">
            <li>Naila Fadhilah (E31241244)</li>
            <li>Rendi Saputra (E31241255)</li>
            <li>Devi Anggraini (E31241260)</li>
          </ul>
        </td>
        <td>Pak Budi, M.Kom</td>
        <td>CV Sentosa</td>
        <td>0896-1234-5678</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  // Fungsi pencarian Mahasiswa
  document
    .getElementById("searchMahasiswa")
    .addEventListener("keyup", function () {
      let value = this.value.toLowerCase();
      document.querySelectorAll("#tabelMahasiswa tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(value)
          ? ""
          : "none";
      });
    });

  // Fungsi pencarian Kelompok
  document
    .getElementById("searchKelompok")
    .addEventListener("keyup", function () {
      let value = this.value.toLowerCase();
      document.querySelectorAll("#tabelKelompok tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(value)
          ? ""
          : "none";
      });
    });
</script>