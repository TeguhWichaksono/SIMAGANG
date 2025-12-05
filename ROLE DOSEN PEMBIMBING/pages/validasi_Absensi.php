<?php
// KODE LENGKAP TERINTEGRASI SUDAH DISIAPKAN OLEH CHATGPT
// SILAKAN EDIT PATH UPDATE_VALIDASI.PHP SESUAI LOKASI ANDA

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../Koneksi/koneksi.php';

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
    nim,
    a.tanggal,
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
    $statusClass = $statusMap[$status]['class'];
    $statusText = $statusMap[$status]['text'];

    $tanggal = date('d-m-Y', strtotime($row['tanggal']));
    $kegiatan = htmlspecialchars($row['lokasi']);
    $fotoPath = htmlspecialchars($row['foto_mahasiswa']);
?>
    <tr data-id="<?= $row['id_absen'] ?>">
      <td class="text-center"><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
      <td class="text-center"><?= htmlspecialchars($row['nim']) ?></td>
      <td class="text-center"><?= $tanggal ?></td>
      <td><?= $kegiatan ?></td>

      <td class="text-center <?= $statusClass ?>"><?= $statusText ?></td>

      <td class="text-center">
        <?php if ($fotoPath): ?>
          <button class="btn btn-primary btn-sm btn-foto" data-foto="<?= $fotoPath ?>">
            <i class="fas fa-image"></i> Lihat Foto
          </button>
        <?php else: ?> - <?php endif; ?>
      </td>

      <td class="text-center">
        <?php if ($status === 'pending'): ?>
            <button class="btn btn-success btn-sm btn-approve">Setujui</button>
            <button class="btn btn-danger btn-sm btn-reject">Tolak</button>
        <?php elseif ($status === 'disetujui'): ?>
            <i class="fas fa-check-circle text-success"></i>
        <?php else: ?>
            <span class="text-danger fw-bold">Ditolak</span>
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

    // FOTO
    if (e.target.closest(".btn-foto")) {
      const btn = e.target.closest(".btn-foto");
      modal.style.display = "block";
      modalImg.src = btn.getAttribute("data-foto");
    }

    // SETUJUI / TOLAK
    if (e.target.closest(".btn-approve") || e.target.closest(".btn-reject")) {

        const row = e.target.closest("tr");
        const idAbsen = row.getAttribute("data-id");
        const statusCell = row.querySelector("td:nth-child(6)");
        const aksiCell = row.querySelector("td:nth-child(8)");

        const status = e.target.closest(".btn-approve") ? "disetujui" : "ditolak";
        const statusText = status === "disetujui" ? "Disetujui" : "Ditolak";
        const statusClass = status === "disetujui" ? "status-valid" : "status-belum";

        // UPDATE UI CEPAT
        statusCell.textContent = statusText;
        statusCell.className = "text-center " + statusClass;

        aksiCell.innerHTML = status === "disetujui" 
            ? '<i class="fas fa-check-circle text-success"></i>'
            : '<span class="text-danger fw-bold">Ditolak</span>';

        // KIRIM AJAX
        fetch('pages/update_validasi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_absen=' + encodeURIComponent(idAbsen) + '&status=' + encodeURIComponent(status)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Gagal update status validasi: ' + data.message);
                location.reload();
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan koneksi');
            location.reload();
        });
    }
  });

  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", e => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });
});
</script>

</body>
</html>
