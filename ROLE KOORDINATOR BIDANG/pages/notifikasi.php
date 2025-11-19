<?php
// notifikasi.php
?>

<link rel="stylesheet" href="styles/notifikasi.css">

<div class="container-notif">
  <h2>Notifikasi dari Admin</h2>

  <div class="notif-item">
    <div class="notif-left">
      <div class="notif-icon"><i class="fas fa-file-upload"></i></div>
      <div class="notif-content">
        <div class="notif-title">Admin mengunggah dokumen Panduan Magang</div>
        <div class="notif-time"><i class="far fa-clock"></i> 6 Okt 2025, 10:15</div>
      </div>
    </div>
    <div class="notif-status status-new">Baru</div>
  </div>

  <div class="notif-item">
    <div class="notif-left">
      <div class="notif-icon"><i class="fas fa-check-circle"></i></div>
      <div class="notif-content">
        <div class="notif-title">Admin menyetujui pengajuan mitra CV Sentosa</div>
        <div class="notif-time"><i class="far fa-clock"></i> 5 Okt 2025, 14:30</div>
      </div>
    </div>
    <div class="notif-status status-read">Terbaca</div>
  </div>

  <div class="notif-item">
    <div class="notif-left">
      <div class="notif-icon"><i class="fas fa-calendar-check"></i></div>
      <div class="notif-content">
        <div class="notif-title">Pengingat: Deadline Laporan Bab II besok</div>
        <div class="notif-time"><i class="far fa-clock"></i> 6 Okt 2025, 08:00</div>
      </div>
    </div>
    <div class="notif-status status-new">Baru</div>
  </div>
</div>

<script>
  // Fungsi pencarian notifikasi
  document.getElementById("searchNotif").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll(".container-notif .notif-item").forEach((item) => {
      item.style.display = item.textContent.toLowerCase().includes(value)
        ? ""
        : "none";
    });
  });
</script>
