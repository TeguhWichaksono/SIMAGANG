<link rel="stylesheet" href="styles/absensiKegiatan.css" />


<div class="form-container">
  <h2>Form Absensi dan Kegiatan Harian</h2>
  <form id="absenForm">
    <div class="form-group">
      <label for="tanggal">Tanggal</label>
      <input type="date" id="tanggal" required />
    </div>

    <div class="form-group">
      <label for="status">Status Kehadiran</label>
      <select id="status" required>
        <option value="">Pilih Status</option>
        <option value="Hadir">Hadir</option>
        <option value="Izin">Izin</option>
        <option value="Sakit">Sakit</option>
      </select>
    </div>

    <div class="form-group">
      <label for="foto">Upload Foto Kehadiran</label>
      <input type="file" id="foto" accept="image/*" required />
    </div>

    <div class="preview" id="preview"></div>

    <div class="form-group full-width">
      <label for="kegiatan">Kegiatan Harian</label>
      <textarea
        id="kegiatan"
        placeholder="Tuliskan kegiatan yang kamu lakukan hari ini..."
        required
      ></textarea>
    </div>

    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-save"></i> Simpan Absensi
      </button>
    </div>
  </form>

  <!-- Riwayat Absensi -->
  <h2 style="margin-top: 40px;">Riwayat Absensi</h2>
  <table id="tabelAbsen">
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Status</th>
        <th>Kegiatan</th>
        <th>Foto</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  const form = document.getElementById("absenForm");
  const tabel = document.querySelector("#tabelAbsen tbody");
  const preview = document.getElementById("preview");
  const fotoInput = document.getElementById("foto");
  let no = 1;
  let fotoData = "";

  // Preview foto sebelum simpan
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

  // Simpan data ke tabel
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const tanggal = document.getElementById("tanggal").value;
    const status = document.getElementById("status").value;
    const kegiatan = document.getElementById("kegiatan").value;

    if (!tanggal || !status || !kegiatan || !fotoData) return;

    const row = document.createElement("tr");
    const statusClass =
      status === "Hadir"
        ? "status-hadir"
        : status === "Izin"
        ? "status-izin"
        : "status-alpha";

    row.innerHTML = `
      <td>${no++}</td>
      <td>${tanggal}</td>
      <td class="${statusClass}">${status}</td>
      <td>${kegiatan}</td>
      <td><img src="${fotoData}" width="60" height="60" style="border-radius:8px;object-fit:cover;" /></td>
    `;

    tabel.appendChild(row);
    form.reset();
    preview.innerHTML = "";
    fotoData = "";
  });
</script>
