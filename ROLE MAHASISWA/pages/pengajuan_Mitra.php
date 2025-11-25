<?php
// pengajuan_Mitra.php
// Letakkan di: ROLE MAHASISWA/pages/pengajuan_Mitra.php
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
      <input type="text" id="namaMitra" name="namaMitra" placeholder="Pilih mitra dari daftar rekomendasi" required readonly>
    </div>

    <div class="form-group full-width">
      <label for="alamatMitra">Alamat Instansi</label>
      <textarea id="alamatMitra" name="alamatMitra" placeholder="Alamat akan terisi otomatis" required readonly></textarea>
    </div>

    <div class="form-group full-width">
      <label for="bidangMitra">Bidang Usaha</label>
      <input type="text" id="bidangMitra" name="bidangMitra" placeholder="Bidang akan terisi otomatis" required readonly>
    </div>

    <div class="form-group full-width">
      <label for="kontakMitra">Kontak (No HP / Email)</label>
      <input type="text" id="kontakMitra" name="kontakMitra" placeholder="Kontak akan terisi otomatis" required readonly>
    </div>

    <!-- BUTTON ACTIONS -->
    <div class="form-actions">
      <button type="button" id="btnRekomendasi">
        <i class="fas fa-list"></i> Lihat Rekomendasi Mitra
      </button>
      <button type="button" id="btnTambahBaru">
        <i class="fas fa-plus"></i> Tambahkan Mitra Baru
      </button>
      <button type="button" id="btnSimpanMitra" style="display:none;">
        <i class="fas fa-save"></i> Simpan Mitra
      </button>
      <button type="button" id="btnBatalTambah" style="display:none;">
        <i class="fas fa-times"></i> Batal
      </button>
    </div>

    <!-- Tombol Kembali diletakkan di kiri -->
    <div class="form-actions" style="margin-top:20px;">
      <button type="button" id="btnKembali" class="btn btn-secondary" onclick="window.history.back();">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>
    </div>
    
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
      <tbody id="bodyMitra">
        <tr>
          <td colspan="4" style="text-align:center;">Loading data...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>


<script>
// ==========================================
// VARIABLE GLOBAL
// ==========================================
let mitraData = [];

// ==========================================
// FUNGSI LOAD DATA DARI DATABASE
// ==========================================
function loadMitraFromDatabase() {
  fetch('pages/getMitra.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        mitraData = data.data;
        loadDropdown();
        loadTable();
      } else {
        alert('Gagal memuat data mitra: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat memuat data mitra');
    });
}

// Load data saat halaman pertama kali dibuka
loadMitraFromDatabase();

// ==========================================
// FUNGSI LOAD DROPDOWN FILTER BIDANG
// ==========================================
function loadDropdown() {
  let bidangList = [...new Set(mitraData.map(m => m.bidang))];
  
  const selectBidang = document.getElementById("filterBidang");
  selectBidang.innerHTML = '<option value="">Semua Bidang</option>';
  
  bidangList.forEach(bid => {
    let opt = document.createElement("option");
    opt.value = bid;
    opt.textContent = bid;
    selectBidang.appendChild(opt);
  });
}

// ==========================================
// FUNGSI LOAD TABLE MITRA
// ==========================================
function loadTable(filterText = "", filterBidang = "") {
  const tbody = document.getElementById("bodyMitra");
  tbody.innerHTML = "";

  const filteredData = mitraData
    .filter(m => m.nama.toLowerCase().includes(filterText.toLowerCase()))
    .filter(m => filterBidang === "" ? true : m.bidang === filterBidang);

  if (filteredData.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Tidak ada data ditemukan</td></tr>';
    return;
  }

  filteredData.forEach(m => {
    tbody.innerHTML += `
      <tr>
        <td>${m.nama}</td>
        <td>${m.alamat}</td>
        <td>${m.bidang}</td>
        <td><button class="btnPilih" onclick="pilihMitra('${escapeHtml(m.nama)}','${escapeHtml(m.alamat)}','${escapeHtml(m.bidang)}','${escapeHtml(m.kontak)}')">Pilih</button></td>
      </tr>
    `;
  });
}

// Fungsi untuk escape HTML (mencegah XSS)
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// ==========================================
// EVENT LISTENER SEARCH & FILTER
// ==========================================
document.getElementById("searchMitra").addEventListener("keyup", e => {
  loadTable(e.target.value, document.getElementById("filterBidang").value);
});

document.getElementById("filterBidang").addEventListener("change", e => {
  loadTable(document.getElementById("searchMitra").value, e.target.value);
});

// ==========================================
// OPEN & CLOSE POPUP
// ==========================================
document.getElementById("btnRekomendasi").onclick = () => {
  document.getElementById("popupRekom").style.display = "block";
  loadMitraFromDatabase();
};

document.getElementById("closePopup").onclick = () => {
  document.getElementById("popupRekom").style.display = "none";
};

// Close popup saat klik di luar popup
window.onclick = (event) => {
  const popup = document.getElementById("popupRekom");
  if (event.target == popup) {
    popup.style.display = "none";
  }
};

// ==========================================
// PILIH MITRA DARI POPUP
// ==========================================
function pilihMitra(nama, alamat, bidang, kontak) {
  document.getElementById("namaMitra").value = nama;
  document.getElementById("alamatMitra").value = alamat;
  document.getElementById("bidangMitra").value = bidang;
  document.getElementById("kontakMitra").value = kontak;
  document.getElementById("popupRekom").style.display = "none";
  
  setFormReadonly(true);
  alert("Mitra berhasil dipilih: " + nama);
}

// ==========================================
// FUNGSI ENABLE/DISABLE FORM
// ==========================================
function setFormReadonly(readonly) {
  document.getElementById("namaMitra").readOnly = readonly;
  document.getElementById("alamatMitra").readOnly = readonly;
  document.getElementById("bidangMitra").readOnly = readonly;
  document.getElementById("kontakMitra").readOnly = readonly;
  
  if (readonly) {
    document.getElementById("btnRekomendasi").style.display = "inline-block";
    document.getElementById("btnTambahBaru").style.display = "inline-block";
    document.getElementById("btnSimpanMitra").style.display = "none";
    document.getElementById("btnBatalTambah").style.display = "none";
  } else {
    document.getElementById("btnRekomendasi").style.display = "none";
    document.getElementById("btnTambahBaru").style.display = "none";
    document.getElementById("btnSimpanMitra").style.display = "inline-block";
    document.getElementById("btnBatalTambah").style.display = "inline-block";
  }
}

// ==========================================
// TOMBOL TAMBAH MITRA BARU
// ==========================================
document.getElementById("btnTambahBaru").onclick = () => {
  document.getElementById("formMitra").reset();
  setFormReadonly(false);
  
  document.getElementById("namaMitra").placeholder = "Masukkan nama mitra";
  document.getElementById("alamatMitra").placeholder = "Masukkan alamat instansi";
  document.getElementById("bidangMitra").placeholder = "Masukkan bidang usaha";
  document.getElementById("kontakMitra").placeholder = "Masukkan kontak";
  
  document.getElementById("namaMitra").focus();
};

// ==========================================
// TOMBOL BATAL
// ==========================================
document.getElementById("btnBatalTambah").onclick = () => {
  document.getElementById("formMitra").reset();
  setFormReadonly(true);
  
  document.getElementById("namaMitra").placeholder = "Pilih mitra dari daftar rekomendasi";
  document.getElementById("alamatMitra").placeholder = "Alamat akan terisi otomatis";
  document.getElementById("bidangMitra").placeholder = "Bidang akan terisi otomatis";
  document.getElementById("kontakMitra").placeholder = "Kontak akan terisi otomatis";
};

// ==========================================
// TOMBOL SIMPAN MITRA BARU
// ==========================================
document.getElementById("btnSimpanMitra").onclick = () => {
  const nama = document.getElementById("namaMitra").value.trim();
  const alamat = document.getElementById("alamatMitra").value.trim();
  const bidang = document.getElementById("bidangMitra").value.trim();
  const kontak = document.getElementById("kontakMitra").value.trim();
  
  if (!nama || !alamat || !bidang || !kontak) {
    alert("Semua field harus diisi!");
    return;
  }
  
  // Kirim data ke server
  fetch('pages/simpanMitra.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      nama: nama,
      alamat: alamat,
      bidang: bidang,
      kontak: kontak
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert("Mitra baru berhasil disimpan!");
      setFormReadonly(true);
      loadMitraFromDatabase();
      
      document.getElementById("namaMitra").placeholder = "Pilih mitra dari daftar rekomendasi";
      document.getElementById("alamatMitra").placeholder = "Alamat akan terisi otomatis";
      document.getElementById("bidangMitra").placeholder = "Bidang akan terisi otomatis";
      document.getElementById("kontakMitra").placeholder = "Kontak akan terisi otomatis";
    } else {
      alert("Gagal menyimpan mitra: " + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert("Terjadi kesalahan saat menyimpan mitra");
  });
};

// ==========================================
// HANDLE SUBMIT FORM
// ==========================================
document.getElementById("formMitra").addEventListener("submit", function(e) {
  e.preventDefault();
  
  if (!document.getElementById("namaMitra").value) {
    alert("Silakan pilih mitra terlebih dahulu!");
    return;
  }
  
  window.location.href = "index.php?page=berkas_Magang";
});
</script>