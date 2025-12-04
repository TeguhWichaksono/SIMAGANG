<?php
// Koneksi database
include '../Koneksi/koneksi.php';

// Query untuk mengambil data kelompok dengan join ke tabel terkait
$query = "SELECT 
            k.id_kelompok,
            k.nama_kelompok,
            k.id_dosen_pembimbing,
            k.tahun,
            d.nidn,
            d.kontak AS kontak_dosen,
            u.nama AS nama_dosen
          FROM kelompok k
          LEFT JOIN dosen d ON k.id_dosen_pembimbing = d.id_dosen
          LEFT JOIN users u ON d.id_user = u.id
          ORDER BY k.id_kelompok";

$result = mysqli_query($conn, $query);

// Array untuk menyimpan data
$dataKelompok = array();

if (mysqli_num_rows($result) > 0) {
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        // Query untuk mengambil anggota kelompok
        $id_kelompok = $row['id_kelompok'];
        $queryAnggota = "SELECT 
                          m.id_mahasiswa,
                          m.kontak,
                          ak.id_anggota,
                          u.nama AS nama_mahasiswa,
                          u.nim,
                          pm.id_mitra,
                          mp.nama_mitra
                        FROM anggota_kelompok ak
                        INNER JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
                        LEFT JOIN users u ON m.id_user = u.id
                        LEFT JOIN pengajuan_mitra pm ON m.id_mahasiswa = pm.id_mahasiswa
                        LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
                        WHERE ak.id_kelompok = '$id_kelompok'
                        ORDER BY ak.id_anggota";
        
        $resultAnggota = mysqli_query($conn, $queryAnggota);
        $anggota = array();
        $kontakKetua = '';
        $namaMitra = '';
        
        while ($rowAnggota = mysqli_fetch_assoc($resultAnggota)) {
            $anggota[] = array(
                'nama' => $rowAnggota['nama_mahasiswa'],
                'nim' => $rowAnggota['nim']
            );
            
            // Anggota pertama dianggap sebagai ketua kelompok
            if (empty($kontakKetua)) {
                $kontakKetua = $rowAnggota['kontak'];
                // Ambil nama mitra hanya jika ada
                if (!empty($rowAnggota['nama_mitra'])) {
                    $namaMitra = $rowAnggota['nama_mitra'];
                }
            }
        }
        
        $dataKelompok[] = array(
            'no' => $no++,
            'id_kelompok' => $row['id_kelompok'],
            'nama_kelompok' => $row['nama_kelompok'],
            'nama_dosen' => $row['nama_dosen'],
            'nidn_dosen' => $row['nidn'],
            'mitra' => $namaMitra,
            'kontak_ketua' => $kontakKetua,
            'tahun' => $row['tahun'],
            'anggota' => $anggota
        );
    }
}

// Return sebagai JSON (jika dipanggil via AJAX)
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($dataKelompok);
    exit;
}

// Atau bisa langsung digunakan untuk menampilkan di HTML
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Kelompok Magang - SI MAGANG</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="styles/data_Kelompok.css">
</head>
<body>
  <div class="container">
    <div class="content-section">
      <h3><i class="fas fa-users"></i> Data Kelompok Magang</h3>
      <div class="search-bar-data">
        <input type="text" id="searchKelompok" placeholder="Cari Kelompok..." />
      </div>

      <table id="tabelKelompok">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Kelompok</th>
            <th>Anggota</th>
            <th>Dosen Pembimbing</th>
            <th>Mitra / Tempat Magang</th>
            <th>Kontak Ketua (WA)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($dataKelompok) > 0): ?>
            <?php foreach ($dataKelompok as $kelompok): ?>
              <tr>
                <td><?php echo $kelompok['no']; ?></td>
                <td><?php echo $kelompok['nama_kelompok']; ?></td>
                <td>
                  <ul class="anggota-list">
                    <?php foreach ($kelompok['anggota'] as $anggota): ?>
                      <li><?php echo $anggota['nama'] . ' (' . $anggota['nim'] . ')'; ?></li>
                    <?php endforeach; ?>
                  </ul>
                </td>
                <td><?php echo $kelompok['nama_dosen'] ? $kelompok['nama_dosen'] : '-'; ?></td>
                <td><?php echo $kelompok['mitra'] ? $kelompok['mitra'] : '-'; ?></td>
                <td>
                  <?php if (!empty($kelompok['kontak_ketua'])): ?>
                    <a href="https://wa.me/62<?php echo ltrim($kelompok['kontak_ketua'], '0'); ?>" target="_blank" class="wa-link">
                      <?php echo $kelompok['kontak_ketua']; ?>
                    </a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align: center;">Tidak ada data kelompok</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Fungsi pencarian Kelompok
    document
      .getElementById("searchKelompok")
      .addEventListener("keyup", function () {
        let value = this.value.toLowerCase();
        document.querySelectorAll("#tabelKelompok tbody tr").forEach((row) => {
          row.style.display = row.textContent.toLowerCase().includes(value)
            ? ""
            : "none";
        });
      });
  </script>
</body>
</html>