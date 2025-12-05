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
    PROSES UPDATE DATA
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodi_input = trim($_POST['prodi'] ?? '');
    $kontak_input = trim($_POST['kontak'] ?? '');
    $angkatan_input = trim($_POST['angkatan'] ?? '');
    
    if (empty($prodi_input) || empty($kontak_input) || empty($angkatan_input)) {
        $error_message = "Semua field harus diisi!";
    } else {
        // Validasi Kontak
        $kontak_clean = preg_replace('/[^0-9]/', '', $kontak_input);
        
        if (empty($kontak_clean) || strlen($kontak_clean) < 10 || strlen($kontak_clean) > 14) {
             $error_message = "Kontak harus berupa angka (10-14 digit)!";
        } else {
             $kontak_input = $kontak_clean;
             
             // Cek data
             $query_check = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
             $stmt_check = mysqli_prepare($conn, $query_check);
             mysqli_stmt_bind_param($stmt_check, 'i', $id_user);
             mysqli_stmt_execute($stmt_check);
             $result_check = mysqli_stmt_get_result($stmt_check);
             $exists = mysqli_fetch_assoc($result_check);
             mysqli_stmt_close($stmt_check);
            
             if ($exists) {
                // UPDATE - Tidak mengubah status_magang
                $query_update = "UPDATE mahasiswa 
                                 SET prodi = ?, angkatan = ?, kontak = ? 
                                 WHERE id_user = ?";
                $stmt_update = mysqli_prepare($conn, $query_update);
                
                if ($stmt_update === false) {
                    $error_message = "Error prepare UPDATE: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt_update, 'sssi', $prodi_input, $angkatan_input, $kontak_input, $id_user);
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        $success_message = "Data berhasil diperbarui!";
                    } else {
                        $error_message = "Gagal update: " . mysqli_stmt_error($stmt_update);
                    }
                    mysqli_stmt_close($stmt_update);
                }
             } else {
                // INSERT - Set status_magang = 'pra-magang'
                $query_insert = "INSERT INTO mahasiswa (id_user, prodi, angkatan, kontak, status_magang) 
                                 VALUES (?, ?, ?, ?, 'pra-magang')";
                $stmt_insert = mysqli_prepare($conn, $query_insert);
                
                if ($stmt_insert === false) {
                    $error_message = "Error prepare INSERT: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt_insert, 'isss', $id_user, $prodi_input, $angkatan_input, $kontak_input);
                    
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $success_message = "Data berhasil disimpan! Status Anda: Pra-Magang.";
                    } else {
                        $error_message = "Gagal simpan: " . mysqli_stmt_error($stmt_insert);
                    }
                    mysqli_stmt_close($stmt_insert);
                }
             }
        }
    }
}

/* =========================================
    AMBIL DATA (User & Mahasiswa)
========================================= */
$query_user = "SELECT nama, email, nim FROM users WHERE id = ?";
$stmt1 = mysqli_prepare($conn, $query_user);
mysqli_stmt_bind_param($stmt1, 'i', $id_user);
mysqli_stmt_execute($stmt1);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt1));
$nama = htmlspecialchars($user['nama'] ?? '');
$email = htmlspecialchars($user['email'] ?? '');
$nim = htmlspecialchars($user['nim'] ?? '');
mysqli_stmt_close($stmt1);

$query_mhs = "SELECT prodi, angkatan, kontak, status_magang FROM mahasiswa WHERE id_user = ?";
$stmt2 = mysqli_prepare($conn, $query_mhs);
mysqli_stmt_bind_param($stmt2, 'i', $id_user);
mysqli_stmt_execute($stmt2);
$mhs = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
mysqli_stmt_close($stmt2);

$prodi = htmlspecialchars($mhs['prodi'] ?? '');
$angkatan = htmlspecialchars($mhs['angkatan'] ?? '');
$kontak = htmlspecialchars($mhs['kontak'] ?? '');
$status_magang = htmlspecialchars($mhs['status_magang'] ?? 'pra-magang');
?>

<link rel="stylesheet" href="styles/pribadi.css?v=<?= time(); ?>" />

<div class="form-container">
    <h2>Profil Mahasiswa</h2>
    <p>
        Silakan lengkapi data profil Anda. Data Nama, NIM, dan Email 
        bersifat tetap (read-only).
    </p>

    <?php if (!empty($success_message)): ?>
    <div class="alert-sukses" style="display: block;">
        <?= htmlspecialchars($success_message) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
    <div class="alert-error" style="display: block;">
        <?= htmlspecialchars($error_message) ?>
    </div>
    <?php endif; ?>

    <div style="background: #e3f2fd; padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <strong>Status Magang Anda:</strong> 
        <span style="color: #1976d2; font-weight: bold;">
            <?php 
            if ($status_magang === 'pra-magang') {
                echo 'PRA-MAGANG (Belum Mengajukan)';
            } elseif ($status_magang === 'magang_aktif') {
                echo 'MAGANG AKTIF';
            } elseif ($status_magang === 'selesai') {
                echo 'SELESAI MAGANG';
            } else {
                echo strtoupper($status_magang);
            }
            ?>
        </span>
    </div>

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
            <input type="text" name="prodi" value="<?= $prodi ?>" placeholder="Contoh: Teknik Informatika" required />
        </div>

        <div class="form-group">
            <label>Kontak (WA/Telp) <span style="color: red;">*</span></label>
            <input 
                type="text" 
                name="kontak" 
                value="<?= $kontak ?>" 
                placeholder="08xxxxxxxxxx" 
                required 
                pattern="[0-9]{10,14}" 
                title="Hanya angka, 10-14 digit"
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
// Auto-hide alert
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-sukses, .alert-error');
    alerts.forEach(alert => {
        if (alert.style.display === 'block') {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 4000);
        }
    });
});
</script>