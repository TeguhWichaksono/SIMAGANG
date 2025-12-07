<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../Koneksi/koneksi.php';

// Ambil ID dari URL
$id_mitra = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data mitra berdasarkan ID
$query = "SELECT * FROM mitra_perusahaan WHERE id_mitra = $id_mitra";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$data) {
    $_SESSION['error'] = 'Data tidak ditemukan!';
    header('Location: ../index.php?page=data_Mitra');
    exit();
}

// Proses UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nama = mysqli_real_escape_string($conn, $_POST['nama_mitra']);
    $bidang = mysqli_real_escape_string($conn, $_POST['bidang']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle koordinat (biarkan NULL jika kosong)
    $lat = !empty($_POST['latitude']) ? "'" . mysqli_real_escape_string($conn, $_POST['latitude']) . "'" : "NULL";
    $long = !empty($_POST['longitude']) ? "'" . mysqli_real_escape_string($conn, $_POST['longitude']) . "'" : "NULL";

    // Query Update dengan Latitude & Longitude
    $updateQuery = "UPDATE mitra_perusahaan SET 
                    nama_mitra = '$nama',
                    bidang = '$bidang',
                    alamat = '$alamat',
                    kontak = '$kontak',
                    latitude = $lat,
                    longitude = $long,
                    status = '$status'
                    WHERE id_mitra = $id_mitra";

    if (mysqli_query($conn, $updateQuery)) {
    // DEBUG: Cek apakah query benar-benar jalan
    echo "<pre>Query berhasil dijalankan!</pre>";
    echo "<pre>Affected rows: " . mysqli_affected_rows($conn) . "</pre>";
    
    $_SESSION['success'] = 'Data berhasil diperbarui!';
    header('Location: ../index.php?page=data_Mitra');
    exit();
}

    echo "<pre>Query: " . $updateQuery . "</pre>";
    exit();
    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['success'] = 'Data berhasil diperbarui!';
        header('Location: ../index.php?page=data_Mitra');
        exit();
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Mitra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: bold;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
            margin-top: 10px;
            display: block;
            font-size: 14px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-top: 5px;
            margin-bottom: 15px;
            font-size: 15px;
            box-sizing: border-box;
        }

        input:focus, textarea:focus {
            border-color: #ffc107;
            outline: none;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-col {
            flex: 1;
        }

        button,
        .btn-link {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #ffc107;
            color: #333;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background: #e0a800;
        }

        .btn-secondary {
            background: #f0f2f5;
            color: #555;
        }

        .btn-secondary:hover {
            background: #dcdcdc;
            color: #333;
        }

        small {
            font-weight: normal;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Edit Data Mitra</h2>

        <form method="POST" action="">

            <label>Nama Mitra</label>
            <input type="text" name="nama_mitra" value="<?= htmlspecialchars($data['nama_mitra']); ?>" required>

            <div class="form-row">
                <div class="form-col">
                    <label>Bidang</label>
                    <input type="text" name="bidang" value="<?= htmlspecialchars($data['bidang']); ?>" required>
                </div>
                <div class="form-col">
                    <label>Kontak</label>
                    <input type="text" name="kontak" value="<?= htmlspecialchars($data['kontak']); ?>" required>
                </div>
            </div>

            <label>Alamat</label>
            <textarea name="alamat" rows="3" required><?= htmlspecialchars($data['alamat']); ?></textarea>

            <div class="form-row">
                <div class="form-col">
                    <label>Latitude</label>
                    <input type="text" name="latitude" value="<?= htmlspecialchars($data['latitude'] ?? ''); ?>" placeholder="-8.xxxxx">
                </div>
                <div class="form-col">
                    <label>Longitude</label>
                    <input type="text" name="longitude" value="<?= htmlspecialchars($data['longitude'] ?? ''); ?>" placeholder="113.xxxxx">
                </div>
            </div>

            <label>Status Kerja Sama</label>
            <select name="status" required>
                <option value="aktif" <?= ($data['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                <option value="nonaktif" <?= ($data['status'] == 'nonaktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
            </select>

            <button type="submit" class="btn-primary">Update Data</button>
            <a href="../index.php?page=data_Mitra" class="btn-link btn-secondary">Batal</a>

        </form>
    </div>

</body>

</html>