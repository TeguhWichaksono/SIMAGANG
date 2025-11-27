<link rel="stylesheet" href="styles/laporanMagang.css" />

<div class="form-container">
  <h2>Unggah Laporan Magang</h2>
  <form id="laporanForm">
    <div class="form-group">
      <label for="judul">Judul Laporan</label>
      <input type="text" id="judul" placeholder="Contoh: Laporan Minggu Ke-1" required />
    </div>

    <div class="form-group">
      <label for="tanggal">Tanggal Upload</label>
      <input type="date" id="tanggal" required />
    </div>

    <div class="form-group full-width">
      <label for="deskripsi">Deskripsi Singkat</label>
      <textarea
        id="deskripsi"
        placeholder="Tuliskan deskripsi singkat laporan..."
        required
      ></textarea>
    </div>

    <div class="form-group full-width">
      <label for="fileLaporan">Upload File Laporan (PDF / DOCX)</label>
      <input type="file" id="fileLaporan" accept=".pdf,.doc,.docx" required />
    </div>

    <div class="preview" id="preview"></div>

    <div class="form-actions">
      <button type="submit">
        <i class="fas fa-upload"></i> Upload Laporan
      </button>
    </div>
  </form>

  <!-- Riwayat Laporan -->
  <h2 style="margin-top: 40px;">Riwayat Laporan Magang</h2>
  <table id="tabelLaporan">
    <thead>
      <tr>
        <th>No</th>
        <th>Judul</th>
        <th>Tanggal</th>
        <th>Deskripsi</th>
        <th>File</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  const form = document.getElementById("laporanForm");
  const tabel = document.querySelector("#tabelLaporan tbody");
  const preview = document.getElementById("preview");
  const fileInput = document.getElementById("fileLaporan");
  let no = 1;
  let fileData = "";

  // Preview file sebelum disimpan
  fileInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file && file.type === "application/pdf") {
      const reader = new FileReader();
      reader.onload = (event) => {
        fileData = event.target.result;
        preview.innerHTML = `<embed src="${fileData}" type="application/pdf" />`;
      };
      reader.readAsDataURL(file);
    } else if (file) {
      preview.innerHTML = `<p style="color:#666;">File siap diunggah: ${file.name}</p>`;
    }
  });

  // Tambahkan data ke tabel
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const judul = document.getElementById("judul").value;
    const tanggal = document.getElementById("tanggal").value;
    const deskripsi = document.getElementById("deskripsi").value;
    const file = fileInput.files[0];

    if (!judul || !tanggal || !deskripsi || !file) return;

    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${no++}</td>
      <td>${judul}</td>
      <td>${tanggal}</td>
      <td>${deskripsi}</td>
      <td><a href="#" class="file-link"><i class="fas fa-file-alt"></i> ${file.name}</a></td>
    `;

    tabel.appendChild(row);
    form.reset();
    preview.innerHTML = "";
  });
</script>
