<?php
/**
 * File: getMitra.php
 * Fungsi: Mengambil data mitra dari database
 * Lokasi: ROLE MAHASISWA/pages/getMitra.php
 */

header('Content-Type: application/json');

// Include koneksi database
include_once '../../Koneksi/koneksi.php';

try {
    // Query untuk mengambil data mitra yang statusnya aktif
    $query = "SELECT id_mitra, nama_mitra, alamat, bidang, kontak 
              FROM mitra_perusahaan 
              WHERE status = 'aktif' 
              ORDER BY nama_mitra ASC";
    
    $result = mysqli_query($conn, $query);
    
    // Cek apakah query berhasil
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    $data = array();
    
    // Ambil semua data
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
            'id' => $row['id_mitra'],
            'nama' => $row['nama_mitra'],
            'alamat' => $row['alamat'],
            'bidang' => $row['bidang'],
            'kontak' => $row['kontak']
        );
    }
    
    // Kirim response sukses
    echo json_encode(array(
        'success' => true,
        'data' => $data,
        'total' => count($data),
        'message' => 'Data berhasil diambil'
    ));
    
} catch (Exception $e) {
    // Kirim response error
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'data' => array(),
        'total' => 0
    ));
}

// Tutup koneksi
if (isset($conn)) {
    mysqli_close($conn);
}
?>