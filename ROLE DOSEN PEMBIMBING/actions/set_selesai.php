<?php
session_start();
include '../../Koneksi/koneksi.php';

// 1. Cek Security: Pastikan yang akses adalah Dosen
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Dosen Pembimbing') {
    echo "<script>alert('Akses Ditolak!'); window.history.back();</script>";
    exit;
}

// 2. Cek Parameter ID
if (isset($_GET['id'])) {
    $id_mahasiswa = intval($_GET['id']); // Sanitasi integer

    // 3. Eksekusi Update Status
    $query = "UPDATE mahasiswa SET status_magang = 'selesai' WHERE id_mahasiswa = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_mahasiswa);
    
    if (mysqli_stmt_execute($stmt)) {
        // Berhasil
        echo "<script>
                alert('Berhasil! Status mahasiswa telah diperbarui menjadi Selesai.');
                window.location.href = '../index.php?page=daftar_Bimbingan';
              </script>";
    } else {
        // Gagal Query
        echo "<script>
                alert('Gagal mengupdate database: " . mysqli_error($conn) . "');
                window.history.back();
              </script>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    // ID tidak ditemukan
    header("Location: ../index.php?page=daftar_Bimbingan");
}
?>