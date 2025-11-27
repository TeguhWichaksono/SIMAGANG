<link rel="stylesheet" href="styles/kelompok.css" />

<div class="form-container">
  <h2>Form Tambah Anggota Kelompok</h2>
  <form id="kelompokForm">
    <div class="form-group">
      <label for="nama">Nama Anggota</label>
      <input type="text" id="nama" placeholder="Masukkan nama lengkap" required />
    </div>

    <div class="form-group">
      <label for="nim">NIM</label>
      <input type="text" id="nim" placeholder="Masukkan NIM anggota" required />
    </div>

    <div class="form-group">
      <label for="prodi">Program Studi</label>
      <input type="text" id="prodi" placeholder="Masukkan program studi" required />
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" placeholder="Masukkan email aktif" required />
    </div>

    <div class="form-group">
      <label for="telepon">Nomor Telepon / WA</label>
      <input type="text" id="telepon" placeholder="Contoh: 081234567890" required />
    </div>

    <div class="form-group">
      <label for="mitra">Nama Mitra / Tempat Magang</label>
      <input type="text" id="mitra" placeholder="Contoh: PT. Maju Jaya" required />
    </div>

    <div class="form-group">
      <label for="foto">Upload CV</label>
      <input type="file" id="foto" accept="image/*" required />
    </div>

    <div class="preview" id="preview"></div>

    <div class="form-group full-width">
      <label for="tugas">Tugas dalam Kelompok</label>
      <input type="text" id="tugas" placeholder="Contoh: Ketua / Dokumentasi / Desain" required />
    </div>

    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-user-plus"></i> Tambah Anggota
      </button>
    </div>
  </form>

  <!-- Tabel Anggota -->
  <h2 style="margin-top: 40px;">Daftar Anggota Kelompok</h2>
  <table id="tabelKelompok">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Prodi</th>
        <th>Email</th>
        <th>No. WA</th>
        <th>Mitra</th>
        <th>Foto</th>
        <th>Tugas</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  const form = document.getElementById("kelompokForm");
  const tabel = document.querySelector("#tabelKelompok tbody");
  const fotoInput = document.getElementById("foto");
  const preview = document.getElementById("preview");
  let fotoData = "";
  let no = 1;

  fotoInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        fotoData = event.target.result;
        preview.innerHTML = `<img src="${fotoData}" alt="Preview Foto" />`;
      };
      reader.readAsDataURL(file);
    }
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const nama = document.getElementById("nama").value;
    const nim = document.getElementById("nim").value;
    const prodi = document.getElementById("prodi").value;
    const email = document.getElementById("email").value;
    const telepon = document.getElementById("telepon").value;
    const mitra = document.getElementById("mitra").value;
    const tugas = document.getElementById("tugas").value;

    if (!nama || !nim || !prodi || !email || !telepon || !mitra || !tugas || !fotoData) return;

    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${no++}</td>
      <td>${nama}</td>
      <td>${nim}</td>
      <td>${prodi}</td>
      <td>${email}</td>
      <td>${telepon}</td>
      <td>${mitra}</td>
      <td><img src="${fotoData}" width="50" height="50" style="border-radius:6px;object-fit:cover;" /></td>
      <td>${tugas}</td>
      <td><button class="btn-delete">Hapus</button></td>
    `;

    tabel.appendChild(row);
    form.reset();
    preview.innerHTML = "";
    fotoData = "";

    row.querySelector(".btn-delete").addEventListener("click", () => row.remove());
  });
</script>
