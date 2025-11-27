<?php
// data_Mitra.php
?>

<link rel="stylesheet" href="styles/data_Mitra.css?v=<?=time()?>">

<div class="content-section">
  <h3><i class="fas fa-building"></i> Daftar Mitra / Perusahaan</h3>
  <div class="search-bar-data">
    <input type="text" id="searchMitra" placeholder="Cari Mitra..." />
  </div>
<button class="add-btn">
        <i class="fas fa-plus"></i> Tambah Mitra
      </button>
  <table id="tabelMitra">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Mitra</th>
        <th>Bidang</th>
        <th>Alamat</th>
        <th>Kontak (WA)</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>PT Jember Abadi</td>
        <td>IT dan Software</td>
        <td>Jl. Letjen Panjaitan No.1</td>
        <td>0812-3456-7890</td>
        <td>Aktif</td>
        <td><button class="btn-view">Lihat</button>
        <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>CV Sentosa</td>
        <td>Desain Grafis</td>
        <td>Jl. Raya Jember No.8</td>
        <td>0896-1234-5678</td>
        <td>Aktif</td>
        <td><button class="btn-view">Lihat</button>
        <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
        </td>
      </tr>
      <tr>
        <td>3</td>
        <td>Koperasi Sejahtera</td>
        <td>IT dan Software</td>
        <td>Jl. Sudirman No.5</td>
        <td>0812-9876-5432</td>
        <td>Aktif</td>
        <td><button class="btn-view">Lihat</button>
        <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  // Fungsi pencarian Mitra
  document.getElementById("searchMitra").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#tabelMitra tbody tr").forEach((row) => {
      row.style.display = row.textContent.toLowerCase().includes(value)
        ? ""
        : "none";
    });
  });

  // Fungsi tombol "Lihat"
  document.querySelectorAll(".btn-view").forEach((btn) => {
    btn.addEventListener("click", () => {
      const mitraName = btn.closest("tr").cells[1].textContent;
      alert(`Detail Mitra: ${mitraName}\n(Ini bisa dikembangkan untuk modal detail lebih lengkap)`);
    });
  });
</script>
