<?php
session_start();
include '../../Koneksi/koneksi.php';

// Pastikan output JSON
header('Content-Type: application/json');
ob_clean(); // BERSIHKAN output sampah apa pun

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception('User tidak terautentikasi. Silakan login kembali.');
    }

    $id_user  = $_POST['id_user'] ?? null;
    $nim      = trim($_POST['nim'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $prodi    = trim($_POST['prodi'] ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');

    // --- Validasi dasar ---
    if ($id_user != $_SESSION['id']) {
        throw new Exception('ID user tidak valid');
    }
    if ($nim == '') throw new Exception('NIM tidak boleh kosong');
    if ($email == '') throw new Exception('Email tidak boleh kosong');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }

    // --- Update tabel USERS (NIM & EMAIL) ---
    $stmt1 = mysqli_prepare($conn,
        "UPDATE users SET nim = ?, email = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt1, 'ssi', $nim, $email, $id_user);

    if (!mysqli_stmt_execute($stmt1)) {
        throw new Exception("Gagal update users: " . mysqli_stmt_error($stmt1));
    }
    mysqli_stmt_close($stmt1);

    // --- Cek apakah baris mahasiswa sudah ada ---
    $stmt2 = mysqli_prepare($conn,
        "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?"
    );
    mysqli_stmt_bind_param($stmt2, 'i', $id_user);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_store_result($stmt2);

    $exists = mysqli_stmt_num_rows($stmt2) > 0;
    mysqli_stmt_close($stmt2);

    // --- UPDATE / INSERT tabel mahasiswa (DENGAN NIM!) ---
    if ($exists) {
        $stmt3 = mysqli_prepare($conn,
            "UPDATE mahasiswa 
             SET nim = ?, prodi = ?, angkatan = ? 
             WHERE id_user = ?"
        );
        mysqli_stmt_bind_param($stmt3, 'ssii', $nim, $prodi, $angkatan, $id_user);

    } else {
        $stmt3 = mysqli_prepare($conn,
            "INSERT INTO mahasiswa (id_user, nim, prodi, angkatan, status)
             VALUES (?, ?, ?, ?, 'pra-magang')"
        );
        mysqli_stmt_bind_param($stmt3, 'isss', $id_user, $nim, $prodi, $angkatan);
    }

    if (!mysqli_stmt_execute($stmt3)) {
        throw new Exception("Gagal update mahasiswa: " . mysqli_stmt_error($stmt3));
    }

    mysqli_stmt_close($stmt3);

    // --- SUCCESS ---
    $response['success'] = true;
    $response['message'] = "Data berhasil diperbarui";

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();

} finally {
    echo json_encode($response);
    exit;
}
