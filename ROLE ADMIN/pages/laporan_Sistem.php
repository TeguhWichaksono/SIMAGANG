<?php
// laporan_Sistem.php
// Konten utama halaman laporan sistem — tidak memuat header/sidebar
?>

<link rel="stylesheet" href="styles/laporan_Sistem.css" />

<div class="laporan-container">
  <div class="header-actions">
    <div class="filter-group">
      <button class="filter-btn"><i class="fas fa-calendar"></i> Hari Ini</button>
      <button class="filter-btn"><i class="fas fa-calendar-week"></i> Minggu Ini</button>
      <button class="filter-btn"><i class="fas fa-calendar-alt"></i> Bulan Ini</button>
    </div>

    <div class="export-group">
      <button class="export-btn"><i class="fas fa-file-csv"></i> Export CSV</button>
    </div>
  </div>

  <table class="log-table">
    <thead>
      <tr>
        <th>No</th>
        <th>Waktu</th>
        <th>Pengguna</th>
        <th>Role</th>
        <th>Aktivitas</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>03/10/2025 10:15</td>
        <td>Septiya Qorrata Ayun</td>
        <td>Admin</td>
        <td>Menambahkan user baru</td>
        <td>Berhasil</td>
      </tr>
      <tr>
        <td>2</td>
        <td>03/10/2025 11:20</td>
        <td>Diva Hafizdatul Albin</td>
        <td>Korbid</td>
        <td>Memvalidasi laporan mahasiswa</td>
        <td>Berhasil</td>
      </tr>
      <tr>
        <td>3</td>
        <td>03/10/2025 12:05</td>
        <td>Khoiril Nisrullah</td>
        <td>Dosen Pembimbing</td>
        <td>Memberi catatan progres magang</td>
        <td>Berhasil</td>
      </tr>
      <tr>
        <td>4</td>
        <td>02/10/2025 15:30</td>
        <td>Saskia Aurelia</td>
        <td>Dosen Pembimbing</td>
        <td>Memperbarui jadwal bimbingan</td>
        <td>Berhasil</td>
      </tr>
      <tr>
        <td>5</td>
        <td>01/10/2025 09:10</td>
        <td>Teguh Wichaksono</td>
        <td>Mahasiswa</td>
        <td>Submit laporan mingguan</td>
        <td>Berhasil</td>
      </tr>
    </tbody>
  </table>
</div>
