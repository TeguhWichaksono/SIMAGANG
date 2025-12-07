<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../Koneksi/koneksi.php';

// Ambil ID dari URL
$id_mitra = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_mitra > 0) {
    
    // Cek apakah mitra masih digunakan di tabel pengajuan_magang
    $cekQuery = "SELECT COUNT(*) as total FROM pengajuan_magang WHERE id_mitra = $id_mitra";
    $cekResult = mysqli_query($conn, $cekQuery);
    $cekData = mysqli_fetch_assoc($cekResult);
    
    if ($cekData['total'] > 0) {
        $_SESSION['error'] = 'Mitra tidak bisa dihapus karena masih digunakan dalam pengajuan magang!';
        header('Location: ../index.php?page=data_Mitra');
        exit();
    }
    
    // Hapus data mitra
    $deleteQuery = "DELETE FROM mitra_perusahaan WHERE id_mitra = $id_mitra";
    
    if (mysqli_query($conn, $deleteQuery)) {
        $_SESSION['success'] = 'Data mitra berhasil dihapus!';
        header('Location: ../index.php?page=data_Mitra');
        exit();
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
        header('Location: ../index.php?page=data_Mitra');
        exit();
    }
    
} else {
    $_SESSION['error'] = 'ID tidak valid!';
    header('Location: ../index.php?page=data_Mitra');
    exit();
}
?>