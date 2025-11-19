<?php
// monitoring.php
?>

<link rel="stylesheet" href="styles/monitoring.css">

<div class="container-monitoring">
  <h2>Data Monitoring Mahasiswa Magang</h2>

  <table id="tabelMonitoring">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Prodi</th>
        <th>Tempat Magang</th>
        <th>Progress</th>
        <th>Status Laporan</th>
        <th>Kehadiran</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <!-- Data otomatis -->
    </tbody>
  </table>

  <p class="note">
    <i class="fas fa-info-circle"></i>
    Data ini diperoleh dari sistem koordinasi bidang magang dan diperbarui secara berkala.
  </p>
</div>

<script>
  const dataMonitoring = [
    {
      nama: "Ahmad Fauzan",
      nim: "E31241111",
      prodi: "Manajemen Informatika",
      mitra: "PT. Sinar Teknologi",
      progress: 85,
      laporan: "Selesai",
      kehadiran: "95%",
    },
    {
      nama: "Siti Rahmawati",
      nim: "E31241122",
      prodi: "Manajemen Informatika",
      mitra: "Dinas Kominfo Probolinggo",
      progress: 65,
      laporan: "Dalam Proses",
      kehadiran: "88%",
    },
    {
      nama: "Bagus Setiawan",
      nim: "E31241133",
      prodi: "Teknik Komputer",
      mitra: "CV. Kreatif Digital",
      progress: 40,
      laporan: "Belum Dikirim",
      kehadiran: "70%",
    },
  ];

  const tbody = document.querySelector("#tabelMonitoring tbody");

  dataMonitoring.forEach((mhs, i) => {
    const row = document.createElement("tr");

    let statusClass =
      mhs.laporan === "Selesai"
        ? "status-selesai"
        : mhs.laporan === "Dalam Proses"
        ? "status-proses"
        : "status-belum";

    row.innerHTML = `
      <td>${i + 1}</td>
      <td>${mhs.nama}</td>
      <td>${mhs.nim}</td>
      <td>${mhs.prodi}</td>
      <td>${mhs.mitra}</td>
      <td>
        <div class="progress-bar">
          <div class="progress-fill" style="width:${mhs.progress}%"></div>
        </div>
        <small>${mhs.progress}%</small>
      </td>
      <td class="${statusClass}">${mhs.laporan}</td>
      <td>${mhs.kehadiran}</td>
      <td><button class="btn-detail"><i class="fas fa-eye"></i> Detail</button></td>
    `;

    tbody.appendChild(row);
  });
</script>
