<?php
// Koneksi database
include '../Koneksi/koneksi.php';

// Query untuk mengambil data mahasiswa dengan join ke tabel terkait
$query = "SELECT 
            m.id_mahasiswa,
            u.nim,
            u.nama,
            m.prodi,
            k.nama_kelompok,
            ud.nama AS nama_dosen,
            mt.nama_mitra,
            pm.status_pengajuan
          FROM mahasiswa m
          LEFT JOIN users u ON m.id_user = u.id
          LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
          LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
          LEFT JOIN dosen d ON k.id_dosen_pembimbing = d.id_dosen
          LEFT JOIN users ud ON d.id_user = ud.id
          LEFT JOIN pengajuan_mitra pm ON m.id_mahasiswa = pm.id_mahasiswa
          LEFT JOIN mitra_perusahaan mt ON pm.id_mitra = mt.id_mitra
          ORDER BY u.nim ASC";

$result = mysqli_query($conn, $query);

// Cek jika query error
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Mahasiswa - SI MAGANG</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="styles/data_Mahasiswa.css">
</head>
<body>
  <div class="container">
    <div class="content-section">
      <h3><i class="fas fa-user-graduate"></i> Data Mahasiswa</h3>
      <div class="search-bar-data">
        <input type="text" id="searchMahasiswa" placeholder="Cari Mahasiswa..." />
      </div>

      <table id="tabelMahasiswa">
        <thead>
          <tr>
            <th>No</th>
            <th>NIM</th>
            <th>Nama Mahasiswa</th>
            <th>Program Studi</th>
            <th>Kelompok</th>
            <th>Dosen Pembimbing</th>
            <th>Mitra / Tempat Magang</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($result) > 0) {
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
              // Tentukan class status berdasarkan status_pengajuan
              $statusClass = '';
              $statusText = '';
              
              if ($row['status_pengajuan'] == 'disetujui') {
                $statusClass = 'status-aktif';
                $statusText = 'Aktif';
              } elseif ($row['status_pengajuan'] == 'menunggu') {
                $statusClass = 'status-proses';
                $statusText = 'Proses';
              } elseif ($row['status_pengajuan'] == 'ditolak') {
                $statusClass = 'status-ditolak';
                $statusText = 'Ditolak';
              } else {
                $statusClass = 'status-proses';
                $statusText = 'Belum Terdaftar';
              }
              
              echo "<tr>";
              echo "<td>" . $no++ . "</td>";
              echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
              echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
              echo "<td>" . htmlspecialchars($row['prodi']) . "</td>";
              echo "<td>" . ($row['nama_kelompok'] ? htmlspecialchars($row['nama_kelompok']) : '-') . "</td>";
              echo "<td>" . ($row['nama_dosen'] ? htmlspecialchars($row['nama_dosen']) : '-') . "</td>";
              echo "<td>" . ($row['nama_mitra'] ? htmlspecialchars($row['nama_mitra']) : '-') . "</td>";
              echo "<td><span class='" . $statusClass . "'>" . $statusText . "</span></td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='8' style='text-align:center;'>Tidak ada data mahasiswa</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Fungsi pencarian Mahasiswa
    document
      .getElementById("searchMahasiswa")
      .addEventListener("keyup", function () {
        let value = this.value.toLowerCase();
        document.querySelectorAll("#tabelMahasiswa tbody tr").forEach((row) => {
          row.style.display = row.textContent.toLowerCase().includes(value)
            ? ""
            : "none";
        });
      });
  </script>
</body>
</html>

<?php
// Tutup koneksi
mysqli_close($conn);
?>