<?php
include '../../../Koneksi/koneksi.php';

// Ambil data POST
$nama = $_POST['nama'];
$email = $_POST['email'];
$role = $_POST['role'];
$nim = $_POST['nim'] ?? null; // Ambil NIM, jika ada (hanya untuk Mahasiswa)
$password_plain = $_POST['password'];
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// 1. Mulai Transaksi
mysqli_begin_transaction($conn);
$success = true;

try {
    // 2. INSERT ke tabel users
    $stmt_user = $conn->prepare("INSERT INTO users (nama, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt_user->bind_param("ssss", $nama, $email, $role, $password_hash);
    
    if (!$stmt_user->execute()) {
        throw new Exception("Gagal menyimpan data User: " . $stmt_user->error);
    }

    // Ambil ID yang baru saja di-generate (Last Insert ID)
    $id_user_baru = mysqli_insert_id($conn);

    // 3. Jika role-nya Mahasiswa, INSERT ke tabel mahasiswa
    if ($role === 'Mahasiswa' && !empty($nim)) {
        $stmt_mhs = $conn->prepare("INSERT INTO mahasiswa (id_user, nim) VALUES (?, ?)");
        $stmt_mhs->bind_param("is", $id_user_baru, $nim);
        
        if (!$stmt_mhs->execute()) {
            throw new Exception("Gagal menyimpan data Mahasiswa (NIM): " . $stmt_mhs->error);
        }
    }

    // 4. Commit Transaksi jika semua berhasil
    mysqli_commit($conn);

} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    $success = false;
    // Tampilkan pesan error
    echo "SQL ERROR: " . $e->getMessage();
    exit;
}

// Redirect setelah berhasil
if ($success) {
    header("Location: ../../index.php?page=manajemen_User");
    exit;
}
?>