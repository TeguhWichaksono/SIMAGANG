<?php
include '../../Koneksi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nama = mysqli_real_escape_string($conn, $_POST['nama_mitra']);
    $bidang = mysqli_real_escape_string($conn, $_POST['bidang']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "INSERT INTO mitra_perusahaan (nama_mitra, bidang, alamat, kontak, status) 
              VALUES ('$nama', '$bidang', '$alamat', '$kontak', '$status')";

    if (mysqli_query($conn, $query)) {
        header('Location: ../index.php?page=data_Mitra&status=success');
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
    <title>Tambah Mitra</title>
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
        <h2>Tambah Mitra</h2>

        <form method="POST" action="">

            <label>Nama Mitra</label>
            <input type="text" name="nama_mitra" placeholder="Masukkan nama mitra..." required>

            <label>Bidang</label>
            <input type="text" name="bidang" placeholder="Contoh: Teknologi, Industri..." required>

            <label>Alamat</label>
            <textarea name="alamat" rows="3" placeholder="Alamat lengkap mitra..." required></textarea>

            <label>Kontak (WhatsApp)</label>
            <input type="text" name="kontak" placeholder="Nomor HP / Telp..." required>

            <label>Status Kerja Sama</label>
            <select name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Tidak Aktif</option>
            </select>

            <button type="submit" class="btn-primary">Simpan</button>
            <a href="../index.php?page=data_Mitra" class="btn-link btn-secondary">Kembali</a>

        </form>
    </div>

</body>

</html>