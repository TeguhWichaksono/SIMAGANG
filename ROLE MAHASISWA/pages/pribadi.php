<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../Koneksi/koneksi.php';

// Cegah akses tanpa login
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$id_user = $_SESSION['id'];
$success_message = '';
$error_message = '';

/* =========================================
    PROSES UPDATE DATA (jika form disubmit)
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $prodi_input = trim($_POST['prodi'] ?? '');
    $kontak_input = trim($_POST['kontak'] ?? '');
    $angkatan_input = trim($_POST['angkatan'] ?? '');
    
    // Validasi input
    if (empty($prodi_input) || empty($kontak_input) || empty($angkatan_input)) {
        $error_message = "Semua field harus diisi!";
    } else {
        // --- START PERUBAHAN DI BAGIAN KONTAK ---
        
        // Tambahan Validasi Server-Side untuk memastikan kontak hanya angka
        // dan mencegah angka yang terlalu panjang/pendek
        $kontak_clean = preg_replace('/[^0-9]/', '', $kontak_input); // Hapus karakter non-digit
        
        if (empty($kontak_clean) || strlen($kontak_clean) < 10 || strlen($kontak_clean) > 14) {
             $error_message = "Kontak harus berupa angka dan memiliki panjang 10 sampai 14 digit!";
        } else {
             $kontak_input = $kontak_clean; // Gunakan angka yang sudah bersih
             
        // --- END PERUBAHAN DI BAGIAN KONTAK ---
        
            // Cek apakah data mahasiswa sudah ada
            $query_check = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
            $stmt_check = mysqli_prepare($conn, $query_check);
            mysqli_stmt_bind_param($stmt_check, 'i', $id_user);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $exists = mysqli_fetch_assoc($result_check);
            mysqli_stmt_close($stmt_check);
            
            if ($exists) {
                // UPDATE data yang sudah ada
                $query_update = "UPDATE mahasiswa 
                                SET prodi = ?, angkatan = ?, kontak = ? 
                                WHERE id_user = ?";
                $stmt_update = mysqli_prepare($conn, $query_update);
                
                if (!$stmt_update) {
                    $error_message = "Prepare gagal UPDATE: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt_update, 'sssi', 
                        $prodi_input, 
                        $angkatan_input, 
                        $kontak_input, 
                        $id_user
                    );
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        $success_message = "Data berhasil diperbarui!";
                    } else {
                        $error_message = "Gagal memperbarui data: " . mysqli_stmt_error($stmt_update);
                    }
                    mysqli_stmt_close($stmt_update);
                }
            } else {
                // INSERT data baru (jika belum ada record di tabel mahasiswa)
                $query_insert = "INSERT INTO mahasiswa (id_user, prodi, angkatan, kontak, status) 
                                VALUES (?, ?, ?, ?, 'pra-magang')";
                $stmt_insert = mysqli_prepare($conn, $query_insert);
                
                if (!$stmt_insert) {
                    $error_message = "Prepare gagal INSERT: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt_insert, 'isss', 
                        $id_user,
                        $prodi_input, 
                        $angkatan_input, 
                        $kontak_input
                    );
                    
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $success_message = "Data berhasil disimpan!";
                    } else {
                        $error_message = "Gagal menyimpan data: " . mysqli_stmt_error($stmt_insert);
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
        } // Penutup dari else (setelah validasi kontak)
    }
}

/* =========================================
    1. AMBIL DATA USERS (nama, email)
========================================= */
$query_user = "SELECT nama, email, nim FROM users WHERE id = ?";
$stmt1 = mysqli_prepare($conn, $query_user);

if (!$stmt1) {
    die("Prepare gagal (users): " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt1, 'i', $id_user);
mysqli_stmt_execute($stmt1);
$res1 = mysqli_stmt_get_result($stmt1);
$user = mysqli_fetch_assoc($res1);

$nama   = htmlspecialchars($user['nama'] ?? '');
$email = htmlspecialchars($user['email'] ?? '');
$nim   = htmlspecialchars($user['nim'] ?? '');

mysqli_stmt_close($stmt1);


/* =========================================
    2. AMBIL DATA MAHASISWA (prodi, angkatan, kontak)
========================================= */
$query_mhs = "SELECT prodi, angkatan, kontak FROM mahasiswa WHERE id_user = ?";
$stmt2 = mysqli_prepare($conn, $query_mhs);

if (!$stmt2) {
    die("Prepare gagal SELECT mahasiswa: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt2, 'i', $id_user);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
$mhs = mysqli_fetch_assoc($res2);
mysqli_stmt_close($stmt2);

// Variabel aman untuk FORM
$prodi    = htmlspecialchars($mhs['prodi'] ?? '');
$angkatan = htmlspecialchars($mhs['angkatan'] ?? '');
$kontak   = htmlspecialchars($mhs['kontak'] ?? '');

?>

<link rel="stylesheet" href="styles/pribadi.css" />

<div class="form-container">
    <h2>Profil Mahasiswa</h2>
    <p>
        Silakan lengkapi data profil Anda. Nama, email, dan NIM 
        otomatis diambil dari sistem dan tidak dapat diubah.<br>
        Jika terdapat kesalahan dalam penulisan nama, silakan hubungi admin untuk perbaikan.
    </p>

    <?php if (!empty($success_message)): ?>
    <div class="alert-sukses" style="display: block;">
        <?= htmlspecialchars($success_message) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
    <div class="alert-error" style="display: block; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
        <?= htmlspecialchars($error_message) ?>
    </div>
    <?php endif; ?>

    <form id="formProfil" method="POST" action="">
        <input type="hidden" name="id_user" value="<?= $id_user ?>">

        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" value="<?= $nama ?>" readonly />
        </div>

        <div class="form-group">
            <label>NIM</label>
            <input type="text" value="<?= $nim ?>" readonly />
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?= $email ?>" readonly />
        </div>

        <div class="form-group">
            <label>Program Studi <span style="color: red;">*</span></label>
            <input type="text" name="prodi" value="<?= $prodi ?>" placeholder="Masukkan Program Studi" required />
        </div>

        <div class="form-group">
            <label>Kontak <span style="color: red;">*</span></label>
            <input 
                type="text" 
                name="kontak" 
                value="<?= $kontak ?>" 
                placeholder="Masukkan Nomor Telepon/WhatsApp (Contoh: 081234567890)" 
                required 
                pattern="[0-9]{10,14}" 
                title="Hanya angka, minimal 10 digit, maksimal 14 digit."
            />
            </div>

        <div class="form-group">
            <label>Tahun Angkatan <span style="color: red;">*</span></label>
            <input type="number" name="angkatan" value="<?= $angkatan ?>" placeholder="Contoh: 2022" min="2000" max="2099" required />
        </div>

        <div class="form-actions">
            <button type="submit">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
// Auto-hide alert setelah 3 detik
document.addEventListener('DOMContentLoaded', function() {
    const alertSukses = document.querySelector('.alert-sukses');
    const alertError = document.querySelector('.alert-error');
    
    if (alertSukses && alertSukses.style.display === 'block') {
        setTimeout(() => {
            alertSukses.style.display = 'none';
        }, 3000);
    }
    
    if (alertError && alertError.style.display === 'block') {
        setTimeout(() => {
            alertError.style.display = 'none';
        }, 5000);
    }
});
</script>