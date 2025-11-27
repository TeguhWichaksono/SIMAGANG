<?php
// help_Center.php
// Fragment content — tidak mengandung <html>, <head>, <body>, sidebar atau header.
// Pastikan index.php (parent) sudah memuat FontAwesome dan styles/styles.css
?>
<link rel="stylesheet" href="styles/help_Center.css">

<div class="help-container">
  <div class="help-header">
    <div class="tips-card">
      <h3>Tips Cepat</h3>
      <ul class="tips-list">
        <li>Selalu periksa progress mahasiswa secara rutin.</li>
        <li>Gunakan notifikasi untuk mengingatkan mahasiswa/dosen yang lama tidak update.</li>
        <li>Periksa log aktivitas secara berkala untuk keamanan sistem.</li>
      </ul>
    </div>

    <button class="support-btn"><i class="fas fa-envelope"></i> Hubungi Support</button>
  </div>

  <h2>FAQ - Pertanyaan Umum</h2>

  <div class="faq-card" onclick="toggleFAQ(this)">
    <div class="faq-question">
      Bagaimana cara menambah user baru?
      <i class="fas fa-chevron-down"></i>
    </div>
    <div class="faq-answer">
      Klik tombol "Tambah User", isi data lengkap, dan pilih role yang sesuai (Admin, Korbid, Dosen Pembimbing, Mahasiswa).
    </div>
  </div>

  <div class="faq-card" onclick="toggleFAQ(this)">
    <div class="faq-question">
      Bagaimana cara mengirim pengingat ke mahasiswa/dosen?
      <i class="fas fa-chevron-down"></i>
    </div>
    <div class="faq-answer">
      Gunakan tombol "Ingatkan" pada halaman Notifikasi untuk mengirim reminder otomatis.
    </div>
  </div>

  <div class="faq-card" onclick="toggleFAQ(this)">
    <div class="faq-question">
      Bagaimana melihat log aktivitas sistem?
      <i class="fas fa-chevron-down"></i>
    </div>
    <div class="faq-answer">
      Masuk ke menu Laporan Sistem, pilih periode yang ingin dilihat, lalu klik "Tampilkan".
    </div>
  </div>

  <div class="faq-card" onclick="toggleFAQ(this)">
    <div class="faq-question">
      Bagaimana jika lupa password admin?
      <i class="fas fa-chevron-down"></i>
    </div>
    <div class="faq-answer">
      Klik tombol "Lupa Password?" di halaman login, lalu ikuti petunjuk reset.
    </div>
  </div>
</div>

<script>
  function toggleFAQ(card) {
    const answer = card.querySelector('.faq-answer');
    const icon = card.querySelector('.faq-question i');

    if (answer.style.display === 'block') {
      answer.style.display = 'none';
      icon.classList.remove('fa-chevron-up');
      icon.classList.add('fa-chevron-down');
    } else {
      answer.style.display = 'block';
      icon.classList.remove('fa-chevron-down');
      icon.classList.add('fa-chevron-up');
    }
  }
</script>
