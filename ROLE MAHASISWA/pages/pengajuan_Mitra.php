<link rel="stylesheet" href="styles/pengajuanMitra.css" />

<div class="form-container">
  <h2>Formulir Pengajuan Mitra Baru</h2>
  <form>
    <div class="form-group">
      <label for="namaInstansi">Nama Instansi / Perusahaan</label>
      <input type="text" id="namaInstansi" placeholder="Contoh: PT Maju Jaya Abadi" required />
    </div>

    <div class="form-group">
      <label for="alamat">Alamat Lengkap</label>
      <input type="text" id="alamat" placeholder="Masukkan alamat instansi" required />
    </div>

    <div class="form-group">
      <label for="bidang">Bidang Usaha</label>
      <select id="bidang" required>
        <option value="">Pilih Bidang</option>
        <option value="Teknologi Informasi">Programmer</option>
        <option value="Data Analyst">Data Analyst</option>
        <option value="Manufaktur">Manufaktur</option>
        <option value="Pendidikan">Pendidikan</option>
        <option value="Kesehatan">Kesehatan</option>
        <option value="Lainnya">Lainnya</option>
      </select>
    </div>

    <div class="form-group">
      <label for="kontak">Kontak Person</label>
      <input type="text" id="kontak" placeholder="Nama penanggung jawab" required />
    </div>

    <div class="form-group">
      <label for="noTelp">No. Telepon / WhatsApp</label>
      <input type="text" id="noTelp" placeholder="Contoh: 081234567890" required />
    </div>

    <div class="form-group">
      <label for="email">Alamat Email</label>
      <input type="email" id="email" placeholder="email@perusahaan.com" required />
    </div>

    <div class="form-group full-width">
      <label for="deskripsi">Deskripsi / Keterangan Tambahan</label>
      <textarea
        id="deskripsi"
        placeholder="Tuliskan deskripsi singkat tentang perusahaan, bidang kerja, atau alasan pengajuan mitra..."
      ></textarea>
    </div>

    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-paper-plane"></i> Kirim Pengajuan
      </button>
    </div>
  </form>
</div>
