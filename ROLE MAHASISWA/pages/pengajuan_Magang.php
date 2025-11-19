<link rel="stylesheet" href="styles/pengajuanMagang.css" />
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<div class="form-container">
  <h2>Informasi Kelompok & Ketua</h2>
  <form id="formKelompokKetua">

    <!-- Informasi Kelompok -->
    <div class="form-section">
      <h3>Data Kelompok</h3>

      <div class="form-group">
        <label for="namaKelompok">Nama Kelompok</label>
        <input type="text" id="namaKelompok" name="namaKelompok" placeholder="Contoh: Kelompok 7 - Manajemen Informatika" required />
      </div>

      <div class="form-group">
        <label for="tahunAkademik">Tahun Akademik</label>
        <input type="text" id="tahunAkademik" name="tahunAkademik" placeholder="2025/2026" required />
      </div>
    </div>

    <!-- Informasi Ketua -->
    <div class="form-section">
      <h3>Data Ketua Kelompok</h3>

      <div class="form-group">
        <label for="namaKetua">Nama Ketua</label>
        <input type="text" id="namaKetua" name="namaKetua" value="Septiya Qorrata Ayun" readonly />
      </div>

      <div class="form-group">
        <label for="nimKetua">NIM</label>
        <input type="text" id="nimKetua" name="nimKetua" value="E31241242" readonly />
      </div>

      <div class="form-group">
        <label for="prodiKetua">Program Studi</label>
        <input type="text" id="prodiKetua" name="prodiKetua" value="Manajemen Informatika" readonly />
      </div>

      <div class="form-group">
        <label for="noHpKetua">No. HP Aktif</label>
        <input type="text" id="noHpKetua" name="noHpKetua" placeholder="Masukkan nomor HP aktif" required />
      </div>

      <div class="form-group">
        <label for="emailKetua">Email Aktif</label>
        <input type="email" id="emailKetua" name="emailKetua" placeholder="Masukkan email aktif" required />
      </div>
    </div>

    <form action="index.php?page=pengajuan_Kelompok" method="POST">
      <div class="form-actions">
        <button type="submit" id="lanjutAnggota">
          <i class="fas fa-arrow-right"></i> Selanjutnya
        </button>
      </div>
    </form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const namaKetua = "Septiya Qorrata Ayun";
    const nimKetua = "E31241242";
    const prodiKetua = "Manajemen Informatika";

    document.getElementById("namaKetua").value = namaKetua;
    document.getElementById("nimKetua").value = nimKetua;
    document.getElementById("prodiKetua").value = prodiKetua;
  });

  document.getElementById("formKelompokKetua").addEventListener("submit", (e) => {
    e.preventDefault();
    window.location.href = "index.php?page=pengajuan_Kelompok";
  });
</script>
