<?php
include '../../../Koneksi/koneksi.php';

$id = $_GET['id'];

// 1. Mulai Transaksi (untuk menghapus dari users dan mahasiswa secara berurutan)
mysqli_begin_transaction($conn);
$success = true;

try {
    // Opsi 1: Hapus data di tabel mahasiswa (jika ada, walau sudah ada ON DELETE CASCADE)
    // Langkah ini penting jika relasi FK tidak diset dengan CASCADE
    $stmt_mhs = $conn->prepare("DELETE FROM mahasiswa WHERE id_user=?");
    $stmt_mhs->bind_param("i", $id);
    $stmt_mhs->execute();

    // Opsi 2: Hapus data di tabel users
    $stmt_user = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt_user->bind_param("i", $id);

    if (!$stmt_user->execute()) {
        throw new Exception("Gagal menghapus User: " . $stmt_user->error);
    }
    
    // Commit Transaksi 
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