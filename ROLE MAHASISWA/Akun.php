<?php
session_start();
include '../Koneksi/koneksi.php';

$id = $_SESSION['id'] ?? null;
$user = null;

// ---- Mengambil Data User ----
if (!empty($id)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
<<<<<<< HEAD
=======
} else {
    header('Location: '. '../Login/login.php');
    exit;
>>>>>>> origin/arilmun
}

if (!$user) {
    $user = [
        'nama' => 'Sesi Tidak Ditemukan',
        'nim' => 'NIM Hilang',
        'email' => 'Sesi Hilang',
        'role' => 'Pengguna',
        'foto_profil' => null
    ];
}

$nama  = htmlspecialchars($user['nama']);
<<<<<<< HEAD
$nim = htmlspecialchars($user['nim']);
=======
$nim   = htmlspecialchars($user['nim'] ?: '');
>>>>>>> origin/arilmun
$email = htmlspecialchars($user['email'] ?: '');
$role  = ucfirst($user['role']);

$prodi = '';
$angkatan = '';

// ---- Ambil Data mahasiswa ----
<<<<<<< HEAD
$stmt_mhs = mysqli_prepare($conn, "SELECT prodi, angkatan FROM mahasiswa WHERE id_user = ?");
=======
$stmt_mhs = mysqli_prepare($conn, "SELECT prodi, angkatan, kontak FROM mahasiswa WHERE id_user = ?");
>>>>>>> origin/arilmun
mysqli_stmt_bind_param($stmt_mhs, 'i', $id);
mysqli_stmt_execute($stmt_mhs);
$result_mhs = mysqli_stmt_get_result($stmt_mhs);

if ($result_mhs && mysqli_num_rows($result_mhs) > 0) {
    $m = mysqli_fetch_assoc($result_mhs);
    $prodi = htmlspecialchars($m['prodi']);
    $angkatan = htmlspecialchars($m['angkatan']);
<<<<<<< HEAD
=======
    $kontak = htmlspecialchars($m['kontak'] ?? '');
>>>>>>> origin/arilmun
}
mysqli_stmt_close($stmt_mhs);

$fotoCover  = 'images/JTI.jpg';
$fotoProfil = !empty($user['foto_profil']) ? "uploads/" . $user['foto_profil'] : "https://i.pravatar.cc/150?img=8";

// Tampilkan pesan upload jika ada
$upload_message = '';
if (isset($_SESSION['upload_message'])) {
    $upload_message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']);
}
?>

<<<<<<< HEAD
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/Akun.css">
</head>
<body>
=======
    <link rel="stylesheet" href="styles/Akun.css">
>>>>>>> origin/arilmun
    <div class="profile-header" style="background-image: url('<?= $fotoCover ?>');"></div>

    <div class="profile-section">

        <!-- Tampilkan pesan upload -->
        <?php if (!empty($upload_message)): ?>
            <div class="alert <?= strpos($upload_message, 'Error') !== false ? 'alert-error' : 'alert-success' ?>">
                <?= htmlspecialchars($upload_message) ?>
            </div>
        <?php endif; ?>

        <div class="profile-info-top">

            <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
                <label for="foto_profil">
                    <img src="<?= $fotoProfil ?>" class="profile-pic" alt="Foto Profil">
                </label>
                <input type="file" name="foto_profil" id="foto_profil" hidden onchange="this.form.submit()">
            </form>

            <div class="profile-details">
                <h2><?= $nama ?></h2>
                <p><?= $role ?></p>
            </div>
        </div>

        <!-- FORM UPDATE PROFILE -->
        <form id="saveForm">
            <input type="hidden" name="id_user" value="<?= $id ?>">

            <div class="info-row">
                <span class="info-label">NIM</span>
<<<<<<< HEAD
                <input type="text" class="info-input" name="nim" value="<?= $nim ?>">
            </div>

            <div class="info-row">
                <span class="info-label">Email</span>
                <input type="email" class="info-input" name="email" value="<?= $email ?>">
=======
                <input type="text" class="info-input readonly-input" name="nim" value="<?= $nim ?>" readonly title="NIM tidak dapat diubah">            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <input type="email" class="info-input readonly-input" name="email" value="<?= $email ?>" readonly title="Email tidak dapat diubah">            </div>
            <div class="info-row">
                <span class="info-label">No. Kontak</span>
                <input type="text" class="info-input" name="kontak" value="<?= $kontak ?>" placeholder="08xxxxxxxxxx">
>>>>>>> origin/arilmun
            </div>

            <div class="info-row">
                <span class="info-label">Program Studi</span>
                <input type="text" class="info-input" name="prodi" value="<?= $prodi ?>">
            </div>

            <div class="info-row">
                <span class="info-label">Angkatan</span>
                <input type="text" class="info-input" name="angkatan" value="<?= $angkatan ?>">
            </div>

            <button type="submit" class="save-btn">Simpan Perubahan</button>
        </form>

        <!-- Tombol Kembali ke Index -->
        <a href="index.php" class="back-btn">Kembali</a>
    </div>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("saveForm");

        form.addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(form);

            fetch("update_profile.php", {
                method: "POST",
                body: formData
            })
            .then(async (res) => {
                const contentType = res.headers.get('content-type');
                
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await res.text();
                    throw new Error(`Response bukan JSON: ${text.substring(0, 100)}...`);
                }
                
                return res.json();
            })
            .then(data => {
                Swal.fire({
                    icon: data.success ? "success" : "error",
                    title: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                Swal.fire({
                    icon: "error",
                    title: "Terjadi Kesalahan!",
                    text: err.message || 'Tidak dapat terhubung ke server'
                });
            });
        });
    });
    </script>
<<<<<<< HEAD
</body>
</html>
=======
>>>>>>> origin/arilmun
