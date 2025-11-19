<link rel="stylesheet" href="styles/notifikasi.css" />

<div class="notification-container">
  <h2>Notifikasi Terbaru</h2>
  <div id="notificationList" class="notification-list">
    <!-- Notifikasi akan dimasukkan lewat JavaScript -->
  </div>
  <p id="emptyMessage" class="empty-message">
    Belum ada notifikasi yang diterima.
  </p>
</div>

<script>
  const notifications = [
    {
      title: "Persetujuan Pengajuan Magang",
      message:
        "Selamat! Pengajuan magang kamu di PT Sinar Teknologi telah disetujui oleh admin.",
      date: "2025-10-05",
      unread: true,
    },
    {
      title: "Revisi Laporan Mingguan",
      message:
        "Admin meminta revisi pada laporan mingguan tanggal 1-5 Oktober 2025. Mohon segera diperbaiki.",
      date: "2025-10-04",
      unread: false,
    },
    {
      title: "Pengumuman",
      message:
        "Jangan lupa mengisi absensi harian paling lambat pukul 10:00 setiap hari.",
      date: "2025-10-02",
      unread: false,
    },
  ];

  const listContainer = document.getElementById("notificationList");
  const emptyMessage = document.getElementById("emptyMessage");

  if (notifications.length > 0) {
    emptyMessage.style.display = "none";
    notifications.forEach((notif) => {
      const item = document.createElement("div");
      item.classList.add("notification-item");
      if (notif.unread) item.classList.add("unread");

      item.innerHTML = `
        <i class="fas fa-bell notification-icon"></i>
        <div class="notification-content">
          <h4>${notif.title}</h4>
          <p>${notif.message}</p>
          <div class="notification-date">
            <i class="far fa-clock"></i> ${notif.date}
          </div>
        </div>
      `;
      listContainer.appendChild(item);
    });
  } else {
    emptyMessage.style.display = "block";
  }
</script>
