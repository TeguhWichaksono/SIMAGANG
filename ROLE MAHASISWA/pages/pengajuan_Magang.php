<link rel="stylesheet" href="styles/pengajuanMagang.css" />

<div class="form-container">
  <h2>Formulir Pengajuan Magang</h2>
  <form>
    <div class="form-group">
      <label for="nama">Nama Mahasiswa</label>
      <input type="text" id="nama" placeholder="Contoh: Septiya Qorrata Ayun" required />
    </div>

    <div class="form-group">
      <label for="nim">NIM</label>
      <input type="text" id="nim" placeholder="E31241242" required />
    </div>

    <div class="form-group">
      <label for="prodi">Program Studi</label>
      <select id="prodi" required>
        <option value="">Pilih Program Studi</option>
        <option value="Manajemen Informatika">Manajemen Informatika</option>
        <option value="Teknik Informatika">Teknik Informatika</option>
        <option value="Sistem Informasi">Sistem Informasi</option>
        <option value="Bisnis Digital">Bisnis Digital</option>
        <option value="Teknik Rekayasa Komputer">Teknik Rekayasa Komputer</option>
      </select>
    </div>

    <div class="form-group">
      <label for="semester">Semester</label>
      <input type="number" id="semester" min="1" max="8" placeholder="Masukkan semester" required />
    </div>

    <div class="form-group">
      <label for="mitra">Nama Mitra / Perusahaan Tujuan</label>
      <input type="text" id="mitra" placeholder="Contoh: PT Jember Abadi" required />
    </div>

    <div class="form-group">
      <label for="alamatMitra">Alamat Mitra</label>
      <input type="text" id="alamatMitra" placeholder="Alamat lengkap mitra" required />
    </div>

    <div class="form-group">
      <label for="posisi">Posisi / Bidang Magang</label>
      <input type="text" id="posisi" placeholder="Contoh: Web Developer" required />
    </div>

    <div class="form-group">
      <label for="tanggalMulai">Tanggal Mulai</label>
      <input type="date" id="tanggalMulai" required />
    </div>

    <div class="form-group">
      <label for="tanggalSelesai">Tanggal Selesai</label>
      <input type="date" id="tanggalSelesai" required />
    </div>

    <div class="form-group full-width">
      <label for="deskripsi">Deskripsi / Alasan Pengajuan</label>
      <textarea
        id="deskripsi"
        placeholder="Tuliskan alasan memilih mitra tersebut, bidang yang diminati, dan harapan selama magang..."
      ></textarea>
    </div>

    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-paper-plane"></i> Kirim Pengajuan
      </button>
    </div>
  </form>
</div>
