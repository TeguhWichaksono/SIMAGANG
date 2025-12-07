<?php
session_start();
include '../Koneksi/koneksi.php';

// Cek Sesi
$id = $_SESSION['id'] ?? null;
if (empty($id)) {
    header('Location: ../Login/login.php');
    exit;
}

$user = null;

// 1. Mengambil Data User (Login Info)
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

if (!$user) {
    echo "Data user tidak ditemukan.";
    exit;
}

// Variabel Dasar User
$nama  = htmlspecialchars($user['nama']);
$email = htmlspecialchars($user['email'] ?: '');
$role  = ucfirst($user['role']); // Dosen Pembimbing / Koordinator

// Default Data Dosen
$nidn   = '-';
$prodi  = '-';
$kontak = '';

// 2. Mengambil Data Detail Dosen (Tabel 'dosen')
$stmt_dosen = mysqli_prepare($conn, "SELECT nidn, prodi, kontak FROM dosen WHERE id_user = ?");
mysqli_stmt_bind_param($stmt_dosen, 'i', $id);
mysqli_stmt_execute($stmt_dosen);
$result_dosen = mysqli_stmt_get_result($stmt_dosen);

if ($result_dosen && mysqli_num_rows($result_dosen) > 0) {
    $d = mysqli_fetch_assoc($result_dosen);
    $nidn   = htmlspecialchars($d['nidn'] ?? '-');
    $prodi  = htmlspecialchars($d['prodi'] ?? '-');
    $kontak = htmlspecialchars($d['kontak'] ?? '');
}
mysqli_stmt_close($stmt_dosen);

// Foto Profil
$fotoCover  = 'images/JTI.jpg'; // Pastikan gambar ini ada atau ganti linknya
$fotoProfil = !empty($user['foto_profil']) ? "uploads/" . $user['foto_profil'] : "https://i.pravatar.cc/150?img=12"; // Img 12 for teacher look

// Pesan Upload
$upload_message = '';
if (isset($_SESSION['upload_message'])) {
    $upload_message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']);
}
?>

<link rel="stylesheet" href="styles/Akun.css">

<div class="profile-header" style="background-image: url('<?= $fotoCover ?>');"></div>

<div class="profile-section">

    <?php if (!empty($upload_message)): ?>
        <div class="alert <?= strpos($upload_message, 'Error') !== false ? 'alert-error' : 'alert-success' ?>">
            <?= htmlspecialchars($upload_message) ?>
        </div>
    <?php endif; ?>

    <div class="profile-info-top">
        <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
            <label for="foto_profil" title="Klik untuk ganti foto">
                <img src="<?= $fotoProfil ?>" class="profile-pic" alt="Foto Profil">
                <div class="edit-icon"><i class="fas fa-camera"></i></div>
            </label>
            <input type="file" name="foto_profil" id="foto_profil" hidden onchange="this.form.submit()">
        </form>

        <div class="profile-details">
            <h2><?= $nama ?></h2>
            <p class="role-badge"><?= $role ?></p>
        </div>
    </div>

    <form id="saveForm">
        <input type="hidden" name="id_user" value="<?= $id ?>">

        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Nama Lengkap</span>
                <input type="text" class="info-input readonly-input" value="<?= $nama ?>" readonly title="Hubungi admin untuk ubah nama">
            </div>

            <div class="info-row">
                <span class="info-label">Email</span>
                <input type="email" class="info-input readonly-input" value="<?= $email ?>" readonly title="Email tidak dapat diubah">
            </div>

            <div class="info-row">
                <span class="info-label">NIDN</span>
                <input type="text" class="info-input" name="nidn" value="<?= $nidn ?>" placeholder="Nomor Induk Dosen Nasional">
            </div>

            <div class="info-row">
                <span class="info-label">Program Studi</span>
                <input type="text" class="info-input" name="prodi" value="<?= $prodi ?>" placeholder="Contoh: Teknik Informatika">
            </div>

            <div class="info-row full-width">
                <span class="info-label">No. Kontak (WhatsApp)</span>
                <input type="text" class="info-input" name="kontak" value="<?= $kontak ?>" placeholder="08xxxxxxxxxx">
            </div>
        </div>

        <button type="submit" class="save-btn"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </form>

    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("saveForm");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        // Tampilkan loading
        const btn = form.querySelector('.save-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Menyimpan...';
        btn.disabled = true;

        let formData = new FormData(form);

        fetch("update_profile.php", {
            method: "POST",
            body: formData
        })
        .then(async (res) => {
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await res.text();
                throw new Error(`Response Error: ${text.substring(0, 100)}...`);
            }
            return res.json();
        })
        .then(data => {
            Swal.fire({
                icon: data.success ? "success" : "error",
                title: data.success ? "Berhasil" : "Gagal",
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            });

            if (data.success) {
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            Swal.fire({
                icon: "error",
                title: "Terjadi Kesalahan",
                text: err.message || 'Gagal terhubung ke server'
            });
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
});
</script>