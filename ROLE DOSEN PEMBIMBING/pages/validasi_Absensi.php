<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../Koneksi/koneksi.php';

// Pastikan koneksi berhasil
if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

$id = $_SESSION['id'] ?? null;
if (!$id) {
    header('Location: login.php');
    exit;
}

$query = "
  SELECT 
    a.id_absen,
    m.id_mahasiswa,
    u.nama AS nama_mahasiswa,
    m.nim,
    a.tanggal,
    a.jam,
    a.lokasi,
    a.foto_mahasiswa,
    a.status_validasi
  FROM absensi a
  JOIN mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
  JOIN users u ON m.id_user = u.id
  ORDER BY a.tanggal DESC, a.jam DESC
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die('Query error: ' . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Validasi Absensi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
.status-valid { color: green; font-weight: bold; }
.status-belum { color: red; font-weight: bold; }
.modal { display:none; position: fixed; z-index: 999; padding-top: 60px; left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.5);}
.modal-content { background:#fff; margin: auto; padding: 20px; border-radius: 5px; width: 80%; max-width: 500px; text-align: center;}
.close { color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;}
.close:hover { color:#000; }
</style>
</head>
<body class="bg-light">

<div class="container my-5 p-4 bg-white rounded shadow-sm">
  <h2 class="mb-4">Daftar Absensi Mahasiswa Bimbingan</h2>
  <table id="tabelAbsensi" class="table table-bordered table-striped align-middle" style="width: 100%;">
    <thead class="table-light text-center">
      <tr>
        <th>No</th>
        <th>Nama Mahasiswa</th>
        <th>NIM</th>
        <th>Tanggal</th>
        <th>Kegiatan</th>
        <th>Jam Masuk</th>
        <th>Jam Keluar</th>
        <th>Status Validasi</th>
        <th>Bukti Foto</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
<?php
$no = 1;
while ($row = mysqli_fetch_assoc($result)):
    $statusMap = [
        'pending' => ['class' => 'status-belum', 'text' => 'Pending'],
        'disetujui' => ['class' => 'status-valid', 'text' => 'Disetujui'],
        'ditolak' => ['class' => 'status-belum', 'text' => 'Ditolak'],
    ];
    $status = $row['status_validasi'] ?? 'pending';
    $statusClass = $statusMap[$status]['class'] ?? 'status-belum';
    $statusText = $statusMap[$status]['text'] ?? 'Pending';

    $tanggal = date('d-m-Y', strtotime($row['tanggal']));
    $jam_masuk = htmlspecialchars($row['jam']);
    $jam_keluar = '-';
    $fotoPath = htmlspecialchars($row['foto_mahasiswa']);
    $kegiatan = htmlspecialchars($row['lokasi']);
?>
    <tr data-id="<?= $row['id_absen'] ?>">
      <td class="text-center"><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
      <td class="text-center"><?= htmlspecialchars($row['nim']) ?></td>
      <td class="text-center"><?= $tanggal ?></td>
      <td><?= $kegiatan ?></td>
      <td class="text-center"><?= $jam_masuk ?></td>
      <td class="text-center"><?= $jam_keluar ?></td>
      <td class="text-center <?= $statusClass ?>"><?= $statusText ?></td>
      <td class="text-center">
        <?php if ($fotoPath): ?>
          <button class="btn btn-primary btn-sm btn-foto" data-foto="<?= $fotoPath ?>">
            <i class="fas fa-image"></i> Lihat Foto
          </button>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
      <td class="text-center">
        <?php if ($status !== 'disetujui'): ?>
          <button class="btn btn-success btn-sm btn-validasi">Validasi</button>
        <?php else: ?>
          <i class="fas fa-check-circle text-success"></i>
        <?php endif; ?>
      </td>
    </tr>
<?php endwhile; ?>
    </tbody>
  </table>

  <p class="small fst-italic text-muted">
    <i class="fas fa-info-circle"></i> Dosen hanya melakukan validasi absensi mahasiswa. Data kegiatan dan foto bukti dikirim otomatis oleh mahasiswa.
  </p>
</div>

<!-- Modal Foto -->
<div id="modalFoto" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h5>Foto Bukti Kehadiran</h5>
    <img id="fotoMahasiswa" src="" alt="Foto Mahasiswa" style="max-width:100%; height:auto; border-radius: 5px;" />
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tbody = document.querySelector("#tabelAbsensi tbody");
  const modal = document.getElementById("modalFoto");
  const modalImg = document.getElementById("fotoMahasiswa");
  const closeModal = document.querySelector(".close");

  tbody.addEventListener("click", e => {
    if (e.target.closest(".btn-foto")) {
      const btnFoto = e.target.closest(".btn-foto");
      modal.style.display = "block";
      modalImg.src = btnFoto.getAttribute("data-foto");
    }

    if (e.target.closest(".btn-validasi")) {
      const btnValidasi = e.target.closest(".btn-validasi");
      const row = btnValidasi.closest("tr");
      const idAbsen = row.getAttribute("data-id");
      const statusCell = row.querySelector("td:nth-child(8)");

      // Ubah tampilan sementara di UI
      statusCell.textContent = "Disetujui";
      statusCell.className = "status-valid text-center";

      // Ganti tombol validasi dengan icon cek hijau
      btnValidasi.outerHTML = '<i class="fas fa-check-circle text-success"></i>';

      // Kirim AJAX ke server untuk update status
      fetch('pages/update_validasi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_absen=' + encodeURIComponent(idAbsen)
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          alert('Gagal update status validasi: ' + data.message);
          // Rollback UI jika gagal
          statusCell.textContent = "Pending";
          statusCell.className = "status-belum text-center";
          row.querySelector("td:nth-child(10)").innerHTML = '<button class="btn btn-success btn-sm btn-validasi">Validasi</button>';
        }
      })
      .catch(() => {
        alert('Terjadi kesalahan koneksi.');
        // Rollback UI jika error jaringan
        statusCell.textContent = "Pending";
        statusCell.className = "status-belum text-center";
        row.querySelector("td:nth-child(10)").innerHTML = '<button class="btn btn-success btn-sm btn-validasi">Validasi</button>';
      });
    }
  });

  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
    modalImg.src = "";
  });

  window.addEventListener("click", e => {
    if (e.target === modal) {
      modal.style.display = "none";
      modalImg.src = "";
    }
  });
});
</script>

</body>
</html>
