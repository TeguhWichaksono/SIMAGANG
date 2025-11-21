<link rel="stylesheet" href="styles/laporanMagang.css" />
<div class="form-container" id="step4DokumenPendukung">
  <h2>Upload Dokumen Pendukung </h2>
  <p class="form-desc">
    Silakan unduh template CV dan Proposal, lalu unggah dokumen sesuai format yang ditentukan.
  </p>

  <!-- Tombol Download Template -->
  <div class="template-buttons">
    <a href="templates/template_cv.pdf" class="btn-template" download>
      <i class="fas fa-download"></i> Download Template CV
    </a>
    <a href="templates/template_proposal.pdf" class="btn-template" download>
      <i class="fas fa-download"></i> Download Template Proposal
    </a>
  </div>

  <!-- Form Upload -->
  <form id="formDokumenPendukung" enctype="multipart/form-data">
    <div class="dokumen-grid">
      <div class="form-group">
        <label for="cv">Upload Berkas Magang (PDF)</label>
        <input type="file" id="cv" name="cv" accept=".pdf" required />
        <div id="preview-cv" class="preview-box"></div>
      </div>

      
    <div class="form-actions">
      <button type="submit" class="btn-submit">
        <i class="fas fa-arrow-left""></i> Kembali
      </button>
    </div>
  </form>
</div>

    <div class="form-actions">
      <button type="submit" class="btn-submit">
        <i class="fas fa-paper-plane"></i> Kirim Pengajuan
      </button>
    </div>
  </form>
</div>

<!-- POPUP FULLSCREEN PDF VIEW -->
<div id="pdfPopup" class="popup-overlay">
  <div class="popup-content">
    <button class="btn-close" id="closePopup">&times;</button>
    <iframe id="pdfViewer" src="" frameborder="0"></iframe>
  </div>
</div>

<script>
  const fileInputs = ['cv', 'proposal'];

  fileInputs.forEach(id => {
    const input = document.getElementById(id);
    const preview = document.getElementById(`preview-${id}`);

    input.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const fileURL = URL.createObjectURL(file);
        const previewItem = document.createElement('div');
        previewItem.classList.add('pdf-preview-item');
        previewItem.innerHTML = `
          <iframe src="${fileURL}" class="pdf-thumbnail"></iframe>
          <span>${file.name}</span>
        `;
        preview.innerHTML = '';
        preview.appendChild(previewItem);

        // Double click untuk buka popup penuh
        previewItem.addEventListener('dblclick', () => {
          document.getElementById('pdfViewer').src = fileURL;
          document.getElementById('pdfPopup').classList.add('show');
        });
      } else {
        preview.innerHTML = '';
      }
    });
  });

  // Tutup popup
  document.getElementById('closePopup').addEventListener('click', () => {
    document.getElementById('pdfPopup').classList.remove('show');
    document.getElementById('pdfViewer').src = '';
  });

  document.getElementById('formDokumenPendukung').addEventListener('submit', (e) => {
    e.preventDefault();
    alert('✅ Dokumen berhasil diunggah dan pengajuan dikirim!');
  });
</script>
