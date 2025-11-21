<?php
// pengajuanMitra.php
?>

<link rel="stylesheet" href="styles/pengajuanmitra.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<div class="form-container">
  <h2>Pemilihan Mitra Magang</h2>
  
  <form id="formMitra">
    <p>Pilih mitra dari daftar rekomendasi kampus atau tambahkan mitra baru jika belum tersedia.</p>

    <!-- FIELD FULL WIDTH -->
    <div class="form-group full-width">
      <label for="namaMitra">Nama Mitra</label>
      <input type="text" id="namaMitra" name="namaMitra" placeholder="Masukkan atau pilih mitra" required>
    </div>

    <div class="form-group full-width">
      <label for="alamatMitra">Alamat Instansi</label>
      <textarea id="alamatMitra" name="alamatMitra" placeholder="Masukkan alamat instansi" required></textarea>
    </div>

    <div class="form-group full-width">
      <label for="bidangMitra">Bidang Usaha</label>
      <input type="text" id="bidangMitra" name="bidangMitra" placeholder="Contoh: Teknologi Informasi, Pendidikan, dll" required>
    </div>

    <div class="form-group full-width">
      <label for="kontakMitra">Kontak (No HP / Email)</label>
      <input type="text" id="kontakMitra" name="kontakMitra" placeholder="Masukkan kontak mitra" required>
    </div>

    <!-- BUTTON ACTIONS -->
    <div class="form-actions">
      <button type="button" id="btnRekomendasi">
        <i class="fas fa-list"></i> Lihat Rekomendasi Mitra
      </button>
      <button type="button" id="btnTambahBaru">
        <i class="fas fa-plus"></i> Tambahkan Mitra Baru
      </button>
    </div>

    <!-- Tombol Kembali diletakkan di kiri -->
    <div class="form-actions" style="margin-top:20px;">
      <button type="button" id="btnKembali" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>
    </div>
    <form action="index.php?page=berkas_Magang" method="POST">
    <div class="form-actions">
      <button type="submit" id="lanjutDokumen">
        <i class="fas fa-arrow-right"></i> Lanjut ke Upload Dokumen
      </button>
    </div>
  </form>
</div>

<!-- ================= POP-UP REKOMENDASI ================= -->
<div class="popup" id="popupRekom">
  <div class="popup-content">
    <span class="close" id="closePopup">&times;</span>
    <h3>Daftar Rekomendasi Mitra Kampus</h3>

    <!-- SEARCH & FILTER WRAPPER -->
    <div class="filter-container">
      <select id="filterBidang">
        <option value="">Semua Bidang</option>
      </select>

      <input type="text" id="searchMitra" placeholder="Cari nama mitra...">
    </div>

    <table id="tabelRekomMitra">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Bidang</th>
          <th>Pilih</th>
        </tr>
      </thead>
      <tbody id="bodyMitra"></tbody>
    </table>
  </div>
</div>


<script>
// ==== DUMMY DATA (BISA DIGANTI BACKEND) ====
const mitraData = [
  { nama: "PT Maju Sejahtera", alamat: "Surabaya", bidang: "Teknologi", kontak: "08212345" },
  { nama: "CV Digital Creative", alamat: "Malang", bidang: "Software House", kontak: "083898989" },
  { nama: "Bank ABC Indonesia", alamat: "Jakarta", bidang: "Perbankan", kontak: "081998877" },
  { nama: "Edu Center Mandiri", alamat: "Bandung", bidang: "Pendidikan", kontak: "081233221" },
  { nama: "PT Visioner Mandiri", alamat: "Solo", bidang: "Konsultan", kontak: "087712345678" },
  { nama: "SMK Informatika Utama", alamat: "Semarang", bidang: "Pendidikan", kontak: "089912345678" },
  { nama: "PT Giga Teknologi Nusantara", alamat: "Sidoarjo", bidang: "Teknologi", kontak: "081212345555" }
];

function loadDropdown() {
  let bidangList = [...new Set(mitraData.map(m => m.bidang))];
  bidangList.forEach(bid => {
    let opt = document.createElement("option");
    opt.value = bid;
    opt.textContent = bid;
    document.getElementById("filterBidang").appendChild(opt);
  });
}
loadDropdown();

function loadTable(filterText = "", filterBidang = "") {
  const tbody = document.getElementById("bodyMitra");
  tbody.innerHTML = "";

  mitraData
  .filter(m => m.nama.toLowerCase().includes(filterText.toLowerCase()))
  .filter(m => filterBidang === "" ? true : m.bidang === filterBidang)
  .forEach(m => {
    tbody.innerHTML += `
      <tr>
        <td>${m.nama}</td>
        <td>${m.alamat}</td>
        <td>${m.bidang}</td>
        <td><button class="btnPilih" onclick="pilihMitra('${m.nama}','${m.alamat}','${m.bidang}','${m.kontak}')">Pilih</button></td>
      </tr>
    `;
  });
}
loadTable();

// Search dan Filter
document.getElementById("searchMitra").addEventListener("keyup", e => {
  loadTable(e.target.value, document.getElementById("filterBidang").value);
});
document.getElementById("filterBidang").addEventListener("change", e => {
  loadTable(document.getElementById("searchMitra").value, e.target.value);
});

// Open & Close Popup
document.getElementById("btnRekomendasi").onclick = () => document.getElementById("popupRekom").style.display = "block";
document.getElementById("closePopup").onclick = () => document.getElementById("popupRekom").style.display = "none";

// Insert Data dari Popup
function pilihMitra(nama, alamat, bidang, kontak) {
  document.getElementById("namaMitra").value = nama;
  document.getElementById("alamatMitra").value = alamat;
  document.getElementById("bidangMitra").value = bidang;
  document.getElementById("kontakMitra").value = kontak;
  document.getElementById("popupRekom").style.display = "none";
  alert("Mitra berhasil dipilih: " + nama);
}

// Reset Form
document.getElementById("btnTambahBaru").onclick = () => document.getElementById("formMitra").reset();
</script>
