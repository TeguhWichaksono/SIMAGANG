<?php
session_start();
include '../Koneksi/koneksi.php';

header('Content-Type: application/json');
error_reporting(0); 
ob_clean(); 

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception('Sesi habis. Silakan login kembali.');
    }

    $id_user = $_POST['id_user'] ?? null;
    
    // Validasi User ID agar tidak edit punya orang lain
    if ($id_user != $_SESSION['id']) {
        throw new Exception('Validasi user gagal.');
    }

    // Ambil Input
    $nidn   = trim($_POST['nidn'] ?? '');
    $prodi  = trim($_POST['prodi'] ?? '');
    $kontak = trim($_POST['kontak'] ?? '');

    // 1. Cek apakah data dosen sudah ada?
    $stmt_cek = mysqli_prepare($conn, "SELECT id_dosen FROM dosen WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt_cek, 'i', $id_user);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    $exists = mysqli_stmt_num_rows($stmt_cek) > 0;
    mysqli_stmt_close($stmt_cek);

    if ($exists) {
        // UPDATE
        $stmt = mysqli_prepare($conn, "UPDATE dosen SET nidn = ?, prodi = ?, kontak = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, 'sssi', $nidn, $prodi, $kontak, $id_user);
    } else {
        // INSERT (Jika baru pertama kali isi profil)
        $stmt = mysqli_prepare($conn, "INSERT INTO dosen (id_user, nidn, prodi, kontak) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $id_user, $nidn, $prodi, $kontak);
    }

    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = "Profil berhasil diperbarui!";
    } else {
        throw new Exception("Database Error: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>