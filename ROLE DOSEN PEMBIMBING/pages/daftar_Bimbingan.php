<link rel="stylesheet" href="styles/daftar_Bimbingan.css">

<div class="form-container">
  <h2>Data Mahasiswa Bimbingan</h2>
  <p style="text-align:center; color:#555;">
    Berikut daftar mahasiswa yang ditetapkan oleh Koordinator Bidang untuk Anda bimbing.
  </p>

  <table id="tabelBimbingan">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Mahasiswa</th>
        <th>NIM</th>
        <th>Program Studi</th>
        <th>Tempat Magang</th>
        <th>Status Bimbingan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Septiya Qorrata Ayun</td>
        <td>E31241242</td>
        <td>Manajemen Informatika</td>
        <td>Diskominfo Probolinggo</td>
        <td>Aktif</td>
        <td>
          <button class="btn-detail"><i class="fas fa-eye"></i> Lihat Detail</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Rizky Saputra</td>
        <td>E31241243</td>
        <td>Teknik Komputer</td>
        <td>PT Telkom Indonesia</td>
        <td>Belum Dimulai</td>
        <td>
          <button class="btn-detail"><i class="fas fa-eye"></i> Lihat Detail</button>
        </td>
      </tr>
      <tr>
        <td>3</td>
        <td>Anisa Rahmawati</td>
        <td>E31241244</td>
        <td>Sistem Informasi</td>
        <td>Bank Jatim Cab. Jember</td>
        <td>Selesai</td>
        <td>
          <button class="btn-detail"><i class="fas fa-eye"></i> Lihat Detail</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  // Simulasi aksi tombol lihat detail
  document.querySelectorAll(".btn-detail").forEach((btn) => {
    btn.addEventListener("click", () => {
      alert("Fitur detail bimbingan akan diarahkan ke halaman detail mahasiswa.");
    });
  });
</script>
