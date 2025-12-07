<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../Koneksi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nama = mysqli_real_escape_string($conn, $_POST['nama_mitra']);
    $bidang = mysqli_real_escape_string($conn, $_POST['bidang']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Query Insert
    $query = "INSERT INTO mitra_perusahaan (nama_mitra, bidang, alamat, kontak, status) 
              VALUES ('$nama', '$bidang', '$alamat', '$kontak', '$status')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Data mitra berhasil ditambahkan!';
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

        input:focus, textarea:focus, select:focus {
            border-color: #1f3c88;
            outline: none;
        }

        /* Layout baris untuk input sejajar */
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
        
        small {
            font-weight: normal;
            color: #666;
            font-size: 12px;
        }

        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Tambah Mitra</h2>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <label>Nama Mitra</label>
            <input type="text" name="nama_mitra" placeholder="Masukkan nama mitra..." required>

            <div class="form-row">
                <div class="form-col">
                    <label>Bidang</label>
                    <input type="text" name="bidang" placeholder="Contoh: Teknologi..." required>
                </div>
                <div class="form-col">
                    <label>Kontak (WhatsApp)</label>
                    <input type="text" name="kontak" placeholder="0812xxxx" required>
                </div>
            </div>

            <label>Alamat</label>
            <textarea name="alamat" rows="3" placeholder="Alamat lengkap mitra..." required></textarea>

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