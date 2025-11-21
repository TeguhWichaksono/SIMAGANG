<?php
// notifikasi.php
?>

<link rel="stylesheet" href="styles/notifikasi.css">

<div class="container-notif">
  <h2>Notifikasi untuk Anda</h2>
  <div class="notif-list" id="notifList"></div>
</div>

<script>
  const dataNotifikasi = [
    {
      judul: "📅 Jadwal Rapat Pembimbing",
      pesan:
        "Admin telah menjadwalkan rapat dosen pembimbing pada hari Kamis, 10 Oktober 2025 pukul 09.00 WIB.",
      waktu: "6 Oktober 2025, 08:00 WIB",
      tipe: "info",
    },
    {
      judul: "⚠️ Revisi Penilaian Mahasiswa",
      pesan:
        "Mohon melakukan pembaruan nilai untuk mahasiswa bimbingan Anda sesuai hasil evaluasi terbaru.",
      waktu: "5 Oktober 2025, 14:30 WIB",
      tipe: "warning",
    },
    {
      judul: "ℹ️ Pemeliharaan Sistem",
      pesan:
        "Sistem SI Magang akan mengalami perawatan server pada 8 Oktober 2025 pukul 20.00 WIB. Mohon selesaikan pekerjaan sebelum waktu tersebut.",
      waktu: "4 Oktober 2025, 10:15 WIB",
      tipe: "info",
    },
  ];

  const notifList = document.getElementById("notifList");

  dataNotifikasi.forEach((notif) => {
    const card = document.createElement("div");
    card.className = `notif-card ${notif.tipe}`;
    card.innerHTML = `
      <div class="notif-icon">
        <i class="fa-solid ${
          notif.tipe === "warning"
            ? "fa-triangle-exclamation"
            : "fa-circle-info"
        }"></i>
      </div>
      <div class="notif-content">
        <h3>${notif.judul}</h3>
        <p>${notif.pesan}</p>
        <span class="notif-time">Dikirim: ${notif.waktu}</span>
      </div>
    `;
    notifList.appendChild(card);
  });
</script>
