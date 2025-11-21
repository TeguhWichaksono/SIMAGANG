<?php
// pemilihan_mitra.php
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pemilihan Mitra</title>

<style>
/* FORM */
.form-container {
  background: #fff;
  padding: 15px;
  border-radius: 10px;
  width: 100%;
  max-width: 450px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-container label {
  font-weight: bold;
  margin-top: 10px;
  display: block;
}

.form-container input,
.form-container select {
  width: 100%;
  padding: 10px;
  border: 1px solid #bbb;
  border-radius: 8px;
  margin-top: 5px;
}

.btn-primary {
  margin-top: 12px;
  padding: 10px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
.btn-primary:hover { background: #3e8e41; }

/* POPUP */
.popup {
  display: none;
  position: fixed;
  z-index: 99999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
}
.popup-content {
  background: #fff;
  width: 70%;
  max-height: 80%;
  overflow-y: auto;
  margin: 60px auto;
  padding: 25px;
  border-radius: 10px;
}
.close {
  float: right;
  font-size: 25px;
  cursor: pointer;
}

/* TABLE */
#tabelRekomMitra {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
#tabelRekomMitra th, #tabelRekomMitra td {
  padding: 10px;
  border: 1px solid #ddd;
}
#tabelRekomMitra th { background: #f1f1f1; }

.btnPilih {
  padding: 6px 12px;
  background: #2196F3;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.btnPilih:hover { background: #0b7dda; }

/* === NEW ADDED STYLE FOR FILTER LAYOUT === */
.filter-row {
  display: flex;
  gap: 10px;
  width: 100%;
  margin-top: 10px;
}

.filter-row select,
.filter-row input {
  flex: 1;
  border-radius: 8px;
  border: 1px solid #aaa;
  padding: 10px;
}
</style>

</head>
<body>

<div class="form-container">
  <h3>Pemilihan Mitra Magang</h3>

  <label for="namaMitra">Nama Mitra</label>
  <input type="text" id="namaMitra" name="namaMitra" placeholder="Masukkan atau pilih mitra">

  <label for="alamatMitra">Alamat Instansi</label>
  <input type="text" id="alamatMitra" name="alamatMitra" placeholder="Masukkan alamat">

  <label for="bidangMitra">Bidang Usaha</label>
  <input type="text" id="bidangMitra" name="bidangMitra" placeholder="Contoh: IT, Perbankan, Pendidikan">

  <label for="kontakMitra">Kontak</label>
  <input type="text" id="kontakMitra" name="kontakMitra" placeholder="Email / No HP">

  <button type="button" class="btn-primary" id="btnRekom">Lihat Rekomendasi Mitra</button>
  <button type="button" class="btn-primary" id="resetForm">Mitra Baru</button>
  <button type="submit" class="btn-primary">Lanjut</button>
</div>


<!-- ================= POPUP REKOM ================= -->
<div class="popup" id="popupRekom">
  <div class="popup-content">
    <span class="close" id="closePopup">&times;</span>
    <h3>Daftar Rekomendasi Mitra</h3>

    <!-- NEW FILTER ROW (sesuai gambar) -->
    <div class="filter-row">
      <select id="filterBidang">
          <option value="">Pilih Bidang</option>
          <!-- backend will fill -->
      </select>

      <input type="text" id="searchMitra" placeholder="Cari Mitra...">
    </div>

    <table id="tabelRekomMitra">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Bidang</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="bodyMitra">
        <tr>
          <td>PT Maju Sejahtera</td>
          <td>Surabaya</td>
          <td>Teknologi</td>
          <td><button class="btnPilih" onclick="pilihMitra('PT Maju Sejahtera','Surabaya','Teknologi','08212345')">Pilih</button></td>
        </tr>
        <tr>
          <td>CV Digital Creative</td>
          <td>Malang</td>
          <td>Software House</td>
          <td><button class="btnPilih" onclick="pilihMitra('CV Digital Creative','Malang','Software House','083898989')">Pilih</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>


<script>
// Popup open & close
document.getElementById("btnRekom").onclick = () => {
    document.getElementById("popupRekom").style.display = "block";
};
document.getElementById("closePopup").onclick = () => {
    document.getElementById("popupRekom").style.display = "none";
};
window.onclick = (e) => {
    if (e.target === document.getElementById("popupRekom")) {
        document.getElementById("popupRekom").style.display = "none";
    }
};

// Reset form (mitra baru)
document.getElementById("resetForm").onclick = () => {
    document.getElementById("namaMitra").value = "";
    document.getElementById("alamatMitra").value = "";
    document.getElementById("bidangMitra").value = "";
    document.getElementById("kontakMitra").value = "";
};

// Isi form ketika pilih data
function pilihMitra(nama, alamat, bidang, kontak) {
    document.getElementById("namaMitra").value = nama;
    document.getElementById("alamatMitra").value = alamat;
    document.getElementById("bidangMitra").value = bidang;
    document.getElementById("kontakMitra").value = kontak;

    document.getElementById("popupRekom").style.display = "none";
    alert("Mitra berhasil dipilih: " + nama);
}
</script>

</body>
</html>
