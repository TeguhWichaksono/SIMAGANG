<link rel="stylesheet" href="styles/pengajuanMagang.css" />
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<div class="form-container">
  <h2>Formulir Tambah Anggota Kelompok</h2>
  <form id="formAnggota">
    <p>Tambahkan hingga 3 anggota kelompok (selain ketua). Inputkan nama anggota di bawah ini:</p>

    <div id="anggotaContainer"></div>

    <!-- Tombol Kembali diletakkan di kiri -->
    <div class="form-actions" style="margin-top:20px;">
      <button type="button" id="btnKembali" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>
    </div>

    <!-- Tombol lainnya di kanan -->
    <div class="form-actions" style="display:flex; gap:10px; justify-content:flex-end; margin-top:10px;">
      <button type="button" id="tambahAnggotaBtn" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Anggota
      </button>
      <form action="index.php?page=pengajuan_Mitra" method="POST">
      <button type="submit" id="lanjutMitra" class="btn btn-success">
        <i class="fas fa-arrow-right"></i> Lanjut ke Mitra Magang
      </button>
    </div>

  </form>
</div>

<script>
  const anggotaContainer = document.getElementById("anggotaContainer");
  const tambahAnggotaBtn = document.getElementById("tambahAnggotaBtn");
  let jumlahAnggota = 0;
  const maxAnggota = 3;
  const prodiKetua = "Manajemen Informatika";

  tambahAnggotaBtn.addEventListener("click", () => {
    if (jumlahAnggota >= maxAnggota) {
      alert("Maksimal 3 anggota tambahan.");
      return;
    }

    jumlahAnggota++;

    const anggotaHTML = `
      <div class="anggota-box" style="border:1px solid #ccc; border-radius:10px; padding:15px; margin-bottom:15px;">
        <h4>Anggota ${jumlahAnggota}</h4>
        
        <div class="form-group">
          <label for="nim${jumlahAnggota}">NIM</label>
          <input type="text" id="nim${jumlahAnggota}" name="nim[]" placeholder="Masukkan NIM anggota" required />
        </div>

        <div class="form-group">
          <label for="nama${jumlahAnggota}">Nama</label>
          <input type="text" id="nama${jumlahAnggota}" name="nama[]" placeholder="Masukkan nama anggota" required />
        </div>

        <div class="form-group">
          <label for="prodi${jumlahAnggota}">Program Studi</label>
          <input type="text" id="prodi${jumlahAnggota}" name="prodi[]" value="${prodiKetua}" readonly />
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-danger hapusAnggota" data-id="${jumlahAnggota}">
            <i class="fas fa-trash"></i> Hapus Anggota
          </button>
        </div>
      </div>
    `;
    anggotaContainer.insertAdjacentHTML("beforeend", anggotaHTML);
  });

  document.addEventListener("click", (e) => {
    if (e.target.closest(".hapusAnggota")) {
      e.target.closest(".anggota-box").remove();

      jumlahAnggota--;

      const boxes = document.querySelectorAll(".anggota-box h4");
      boxes.forEach((h4, index) => {
        h4.textContent = `Anggota ${index + 1}`;
      });
    }
  });

  document.getElementById("formAnggota").addEventListener("submit", (e) => {
    e.preventDefault();
    if (jumlahAnggota < 1) {
      alert("Minimal tambahkan 1 anggota sebelum lanjut!");
      return;
    }
    window.location.href = "index.php?page=pengajuan_Mitra";
  });

  // Tombol kembali
  document.getElementById("btnKembali").addEventListener("click", () => {
    history.back();
  });
</script>
