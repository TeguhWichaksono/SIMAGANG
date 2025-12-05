<?php
include '../../Koneksi/koneksi.php';

// Ambil ID dari URL
$id_mitra = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data mitra berdasarkan ID
$query = "SELECT * FROM mitra_perusahaan WHERE id_mitra = $id_mitra";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='../index.php?page=data_Mitra';</script>";
    exit();
}

// Proses UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nama = mysqli_real_escape_string($conn, $_POST['nama_mitra']);
    $bidang = mysqli_real_escape_string($conn, $_POST['bidang']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $updateQuery = "UPDATE mitra_perusahaan SET 
                    nama_mitra = '$nama',
                    bidang = '$bidang',
                    alamat = '$alamat',
                    kontak = '$kontak',
                    status = '$status'
                    WHERE id_mitra = $id_mitra";

    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Data berhasil diupdate!'); window.location.href='../index.php?page=data_Mitra';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
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
            color: #1f3c88;
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: bold;
        }

        label {
            font-weight: bold;
            color: #333;
            margin-top: 10px;
            display: block;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 15px;
            box-sizing: border-box;
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
            background: #1f3c88;
            color: white;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background: #172e6a;
        }

        .btn-secondary {
            background: #999;
            color: white;
        }

        .btn-secondary:hover {
            background: #777;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Edit Mitra</h2>

        <form method="POST" action="">

            <label>Nama Mitra</label>
            <input type="text" name="nama_mitra" value="<?= htmlspecialchars($data['nama_mitra']); ?>" placeholder="Masukkan nama mitra..." required>

            <label>Bidang</label>
            <input type="text" name="bidang" value="<?= htmlspecialchars($data['bidang']); ?>" placeholder="Contoh: Teknologi, Industri..." required>

            <label>Alamat</label>
            <textarea name="alamat" rows="3" placeholder="Alamat lengkap mitra..." required><?= htmlspecialchars($data['alamat']); ?></textarea>

            <label>Kontak (WhatsApp)</label>
            <input type="text" name="kontak" value="<?= htmlspecialchars($data['kontak']); ?>" placeholder="Nomor HP / Telp..." required>

            <label>Status Kerja Sama</label>
            <select name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="aktif" <?= ($data['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                <option value="nonaktif" <?= ($data['status'] == 'nonaktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
            </select>

            <button type="submit" class="btn-primary">Update</button>
            <a href="../index.php?page=data_Mitra" class="btn-link btn-secondary">Batal</a>

        </form>
    </div>

</body>

</html>