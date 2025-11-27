<?php
// manajemen_User.php
// Halaman utama Manajemen User (tanpa header dan sidebar)
?>

<link rel="stylesheet" href="styles/manajemen_User.css" />

<div class="user-container">
  <div class="header-action">
    <button class="add-btn">
      <i class="fas fa-plus"></i> Tambah User
    </button>
  </div>

  <table class="user-table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama User</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Septiya Qorrata Ayun</td>
        <td>septiya@mail.com</td>
        <td>Admin</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Diva Hafizdatul Albin</td>
        <td>diva@mail.com</td>
        <td>Korbid</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>3</td>
        <td>Khoiril Nisrullah</td>
        <td>aril@mail.com</td>
        <td>Dosen Pembimbing</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>4</td>
        <td>Saskia Aurelia</td>
        <td>saskia@mail.com</td>
        <td>Dosen Pembimbing</td>
        <td>Aktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
      <tr>
        <td>5</td>
        <td>Teguh Wichaksono</td>
        <td>teguh@mail.com</td>
        <td>Mahasiswa</td>
        <td>Nonaktif</td>
        <td>
          <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
          <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
