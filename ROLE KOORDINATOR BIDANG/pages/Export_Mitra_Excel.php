<?php
include 'C:/xampp/htdocs/SIMAGANG/Koneksi/koneksi.php';

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Data_Mitra_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM mitra_perusahaan ORDER BY id_mitra DESC");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Data Mitra / Perusahaan</h2>
    <p>Tanggal Export: <?= date('d-m-Y H:i:s'); ?></p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Mitra</th>
                <th>Bidang</th>
                <th>Alamat</th>
                <th>Kontak (WA)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($query)) :
            ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_mitra']); ?></td>
                    <td><?= htmlspecialchars($row['bidang']); ?></td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                    <td><?= htmlspecialchars($row['kontak']); ?></td>
                    <td><?= ucfirst($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>