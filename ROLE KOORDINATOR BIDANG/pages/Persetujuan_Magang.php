<?php
// dokumen_Magang.php
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Pengajuan Magang</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f9fafc;
      margin: 0;
      padding: 20px;
    }

    .content-section {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      max-width: 1000px;
      margin: auto;
    }

    h3 {
      margin-bottom: 15px;
      color: #333;
    }

    .search-bar-data input {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 15px;
      outline: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    th, td {
      padding: 12px 10px;
      border-bottom: 1px solid #ddd;
    }

    th {
      background: #f0f0f0;
    }

    .btn-action {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      color: #fff;
      transition: 0.3s;
    }

    .btn-view { background: #007bff; }
    .btn-view:hover { background: #0056b3; }

    .btn-approve { background: #28a745; }
    .btn-approve:hover { background: #218838; }

    .btn-reject { background: #dc3545; }
    .btn-reject:hover { background: #b02a37; }

    /* Modal Style */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      position: relative;
      box-shadow: 0 3px 12px rgba(0,0,0,0.2);
      animation: fadeIn 0.3s ease;
      max-height: 90vh;
      overflow-y: auto;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: scale(0.9);}
      to {opacity: 1; transform: scale(1);}
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      cursor: pointer;
      color: #333;
    }

    .info-box {
      background: #f7f9fc;
      border-radius: 8px;
      padding: 10px 15px;
      margin-bottom: 10px;
      border-left: 4px solid #007bff;
    }

    .info-box p {
      margin: 5px 0;
    }

    .doc-links {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 10px;
    }

    .doc-links a {
      background: #28a745;
      color: #fff;
      padding: 10px 15px;
      border-radius: 8px;
      text-decoration: none;
      display: inline-block;
      transition: 0.3s;
    }

    .doc-links a:hover {
      background: #1e7e34;
    }

    .input-area {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    input[type="file"], textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-family: inherit;
      font-size: 14px;
    }

    .btn-submit {
      background: #007bff;
      color: #fff;
      padding: 10px 15px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-submit:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

<div class="content-section">
  <h3><i class="fas fa-file-alt"></i> Data Pengajuan Magang Mahasiswa</h3>

  <div class="search-bar-data">
    <input type="text" id="searchDokumen" placeholder="Cari nama kelompok..." />
  </div>

  <table id="tabelDokumen">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Kelompok</th>
        <th>Angkatan</th>
        <th>Data Pengajuan</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Kelompok 3</td>
        <td>2024</td>
        <td><button class="btn-action btn-view" onclick="showDetail('kelompok3')">Lihat Detail</button></td>
        <td>
          <button class="btn-action btn-approve" onclick="openApprove('kelompok3')">Setuju</button>
          <button class="btn-action btn-reject" onclick="openReject('kelompok3')">Tolak</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Kelompok 4</td>
        <td>2024</td>
        <td><button class="btn-action btn-view" onclick="showDetail('kelompok4')">Lihat Detail</button></td>
        <td>
          <button class="btn-action btn-approve" onclick="openApprove('kelompok4')">Setuju</button>
          <button class="btn-action btn-reject" onclick="openReject('kelompok4')">Tolak</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Modal Detail -->
<div id="modalDetail" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <div id="detailContainer"></div>
  </div>
</div>

<!-- Modal Approve -->
<div id="modalApprove" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h3>Upload Surat Pelaksanaan Magang</h3>
    <form class="input-area" id="formApprove">
      <input type="file" name="suratPelaksanaan" accept=".pdf" required />
      <button type="submit" class="btn-submit">Upload Surat</button>
    </form>
  </div>
</div>

<!-- Modal Reject -->
<div id="modalReject" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h3>Masukkan Alasan Penolakan</h3>
    <form class="input-area" id="formReject">
      <textarea name="alasan" rows="4" placeholder="Tuliskan alasan penolakan..." required></textarea>
      <button type="submit" class="btn-submit">Kirim</button>
    </form>
  </div>
</div>

<script>
  // Data kelompok
  const dataKelompok = {
    kelompok3: {
      namaKelompok: "Kelompok 3",
      angkatan: "2024",
      ketua: { nama: "Septiya Qorrata Ayun", nim: "E31241242", prodi: "Manajemen Informatika" },
      anggota: [
        { nama: "Rizky Ramadhan", nim: "E31241243", prodi: "Manajemen Informatika" },
        { nama: "Naila Fadhilah", nim: "E31241244", prodi: "Manajemen Informatika" },
        { nama: "Anisa Rahma", nim: "E31241245", prodi: "Manajemen Informatika" }
      ],
      mitra: "PT Teknologi Cerdas Nusantara",
      dokumen: {
        proposal: "uploads/proposal_kelompok3.pdf",
        cv: "uploads/cv_kelompok3.pdf"
      }
    },
    kelompok4: {
      namaKelompok: "Kelompok 4",
      angkatan: "2024",
      ketua: { nama: "Dimas Alfarizi", nim: "E31241246", prodi: "Teknik Informatika" },
      anggota: [
        { nama: "Farah Aulia", nim: "E31241247", prodi: "Teknik Informatika" },
        { nama: "Rani Putri", nim: "E31241248", prodi: "Teknik Informatika" },
        { nama: "Galang Putra", nim: "E31241249", prodi: "Teknik Informatika" }
      ],
      mitra: "CV Digital Kreatif Mandiri",
      dokumen: {
        proposal: "uploads/proposal_kelompok4.pdf",
        cv: "uploads/cv_kelompok4.pdf"
      }
    }
  };

  // Modal handlers
  function showModal(id) {
    document.getElementById(id).style.display = "flex";
  }

  function closeModal() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
  }

  // Lihat detail
  function showDetail(kelompokId) {
    const data = dataKelompok[kelompokId];
    if (!data) return;

    let html = `
      <h3>Detail Pengajuan Kelompok</h3>
      <p><strong>Nama Kelompok:</strong> ${data.namaKelompok}</p>
      <p><strong>Angkatan:</strong> ${data.angkatan}</p>

      <h4>Ketua Kelompok</h4>
      <div class="info-box">
        <p><strong>Nama:</strong> ${data.ketua.nama}</p>
        <p><strong>NIM:</strong> ${data.ketua.nim}</p>
        <p><strong>Program Studi:</strong> ${data.ketua.prodi}</p>
      </div>

      <h4>Anggota Kelompok</h4>
      ${data.anggota.map((a, i) => `
        <div class="info-box">
          <p><strong>Anggota ${i + 1}:</strong> ${a.nama}</p>
          <p><strong>NIM:</strong> ${a.nim}</p>
          <p><strong>Program Studi:</strong> ${a.prodi}</p>
        </div>
      `).join('')}

      <h4>Data Mitra</h4>
      <div class="info-box">
        <p><strong>Nama Mitra:</strong> ${data.mitra}</p>
      </div>

      <h4>Dokumen Upload</h4>
      <div class="doc-links">
        <a href="${data.dokumen.proposal}" target="_blank"><i class="fa-solid fa-file-pdf"></i> Lihat Proposal Magang</a>
        <a href="${data.dokumen.cv}" target="_blank"><i class="fa-solid fa-file-lines"></i> Lihat CV Mahasiswa</a>
      </div>
    `;

    document.getElementById("detailContainer").innerHTML = html;
    showModal('modalDetail');
  }

  // Modal approve / reject
  function openApprove(kelompokId) {
    showModal('modalApprove');
  }

  function openReject(kelompokId) {
    showModal('modalReject');
  }

  // Event submit simulasi
  document.getElementById('formApprove').addEventListener('submit', e => {
    e.preventDefault();
    alert('Surat pelaksanaan berhasil diupload!');
    closeModal();
  });

  document.getElementById('formReject').addEventListener('submit', e => {
    e.preventDefault();
    const alasan = e.target.alasan.value.trim();
    alert('Alasan penolakan dikirim: ' + alasan);
    closeModal();
  });

  // Klik luar modal menutup
  window.onclick = function(event) {
    document.querySelectorAll('.modal').forEach(m => {
      if (event.target === m) m.style.display = 'none';
    });
  };
</script>

</body>
</html>
