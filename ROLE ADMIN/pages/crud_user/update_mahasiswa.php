<?php
include '../../../Koneksi/koneksi.php';

// Ambil data POST
$id = $_POST['id'];
$nama = $_POST['nama'];
$email = $_POST['email'];
$role = $_POST['role'];
$nim = $_POST['nim'] ?? null; // Ambil NIM, jika ada

// 1. Mulai Transaksi
mysqli_begin_transaction($conn);
$success = true;

try {
    // 2. UPDATE tabel users
    $stmt_user = $conn->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
    $stmt_user->bind_param("sssi", $nama, $email, $role, $id);

    if (!$stmt_user->execute()) {
        throw new Exception("Gagal mengupdate data User: " . $stmt_user->error);
    }

    // 3. UPDATE/INSERT/DELETE tabel mahasiswa berdasarkan role
    if ($role === 'Mahasiswa' && !empty($nim)) {
        // Cek apakah data mahasiswa sudah ada (untuk user lama yang diubah role)
        $stmt_check_mhs = $conn->prepare("SELECT COUNT(*) FROM mahasiswa WHERE id_user = ?");
        $stmt_check_mhs->bind_param("i", $id);
        $stmt_check_mhs->execute();
        $stmt_check_mhs->bind_result($count);
        $stmt_check_mhs->fetch();
        $stmt_check_mhs->close();

        if ($count > 0) {
            // Jika sudah ada, update NIM
            $stmt_update_mhs = $conn->prepare("UPDATE mahasiswa SET nim=? WHERE id_user=?");
            $stmt_update_mhs->bind_param("si", $nim, $id);
            if (!$stmt_update_mhs->execute()) {
                throw new Exception("Gagal mengupdate NIM Mahasiswa: " . $stmt_update_mhs->error);
            }
        } else {
            // Jika belum ada (misal diubah dari Admin ke Mahasiswa), insert data NIM
            $stmt_insert_mhs = $conn->prepare("INSERT INTO mahasiswa (id_user, nim) VALUES (?, ?)");
            $stmt_insert_mhs->bind_param("is", $id, $nim);
            if (!$stmt_insert_mhs->execute()) {
                throw new Exception("Gagal menyimpan NIM baru Mahasiswa: " . $stmt_insert_mhs->error);
            }
        }

    } else {
        // Jika role bukan Mahasiswa (misal diubah ke Admin), hapus data di tabel mahasiswa
        $stmt_delete_mhs = $conn->prepare("DELETE FROM mahasiswa WHERE id_user=?");
        $stmt_delete_mhs->bind_param("i", $id);
        // Jalankan DELETE, abaikan jika tidak ada data (karena tidak semua user punya data mhs)
        $stmt_delete_mhs->execute();
    }
    
    // 4. Commit Transaksi
    mysqli_commit($conn);

} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    $success = false;
    echo "SQL ERROR: " . $e->getMessage();
    exit;
}

// Redirect setelah berhasil
if ($success) {
    header("Location: ../../index.php?page=manajemen_User");
    exit;
}
?>