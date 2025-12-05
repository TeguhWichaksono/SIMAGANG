<?php
include '../../Koneksi/koneksi.php';

// Ambil ID dari URL
$id_mitra = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_mitra > 0) {
    
    // Cek apakah mitra masih digunakan di tabel pengajuan_magang
    $cekQuery = "SELECT COUNT(*) as total FROM pengajuan_magang WHERE id_mitra = $id_mitra";
    $cekResult = mysqli_query($conn, $cekQuery);
    $cekData = mysqli_fetch_assoc($cekResult);
    
    if ($cekData['total'] > 0) {
        // Jika mitra masih digunakan, jangan hapus
        echo "<script>
                alert('Mitra tidak bisa dihapus karena masih digunakan dalam pengajuan magang!');
                window.location.href='../index.php?page=data_Mitra';
              </script>";
        exit();
    }
    
    // Hapus data mitra
    $deleteQuery = "DELETE FROM mitra_perusahaan WHERE id_mitra = $id_mitra";
    
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>
                alert('Data mitra berhasil dihapus!');
                window.location.href='../index.php?page=data_Mitra';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error: " . mysqli_error($conn) . "');
                window.location.href='../index.php?page=data_Mitra';
              </script>";
        exit();
    }
    
} else {
    echo "<script>
            alert('ID tidak valid!');
            window.location.href='../index.php?page=data_Mitra';
          </script>";
    exit();
}
?>