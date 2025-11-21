<?php
// simpanMitra.php
// File untuk menyimpan mitra baru ke database
// Letakkan file ini di: ROLE MAHASISWA/pages/simpanMitra.php

header('Content-Type: application/json');

// Include koneksi database
include_once '../../Koneksi/koneksi.php';

try {
    // Ambil data JSON dari request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validasi data
    if (empty($data['nama']) || empty($data['alamat']) || empty($data['bidang']) || empty($data['kontak'])) {
        throw new Exception("Semua field harus diisi!");
    }
    
    // Sanitasi input
    $nama = mysqli_real_escape_string($conn, trim($data['nama']));
    $alamat = mysqli_real_escape_string($conn, trim($data['alamat']));
    $bidang = mysqli_real_escape_string($conn, trim($data['bidang']));
    $kontak = mysqli_real_escape_string($conn, trim($data['kontak']));
    
    // Cek apakah mitra sudah ada (berdasarkan nama)
    $checkQuery = "SELECT id_mitra FROM mitra_perusahaan WHERE nama_mitra = '$nama'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        throw new Exception("Mitra dengan nama tersebut sudah terdaftar!");
    }
    
    // Insert data ke database
    $query = "INSERT INTO mitra_perusahaan (nama_mitra, alamat, bidang, kontak, status) 
              VALUES ('$nama', '$alamat', '$bidang', '$kontak', 'aktif')";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    // Get ID yang baru di-insert
    $newId = mysqli_insert_id($conn);
    
    // Kirim response sukses
    echo json_encode(array(
        'success' => true,
        'message' => 'Mitra berhasil ditambahkan',
        'data' => array(
            'id' => $newId,
            'nama' => $nama,
            'alamat' => $alamat,
            'bidang' => $bidang,
            'kontak' => $kontak
        )
    ));
    
} catch (Exception $e) {
    // Kirim response error
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

// Tutup koneksi
if (isset($conn)) {
    mysqli_close($conn);
}
?>