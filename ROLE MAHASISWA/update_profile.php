<?php
session_start();
// Sesuaikan path koneksi dengan struktur folder
include '../Koneksi/koneksi.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {    if (!isset($_SESSION['id'])) {
        throw new Exception('User tidak terautentikasi. Silakan login kembali.');
    }

    $id_user = $_POST['id_user'] ?? null;
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');

    if (empty($id_user) || $id_user != $_SESSION['id']) {
        throw new Exception('ID user tidak valid');
    }

    if (empty($nim)) {
        throw new Exception('NIM tidak boleh kosong');
    }

    if (empty($email)) {
        throw new Exception('Email tidak boleh kosong');
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }

    // Update tabel users
    $stmt1 = mysqli_prepare($conn, "UPDATE users SET nim = ?, email = ? WHERE id = ?");
    if (!$stmt1) {
        throw new Exception('Prepare statement failed: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt1, 'ssi', $nim, $email, $id_user);
    
    if (!mysqli_stmt_execute($stmt1)) {
        throw new Exception('Gagal update data user: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt1);

    // Check if record exists in mahasiswa table
    $stmt2 = mysqli_prepare($conn, "SELECT id_user FROM mahasiswa WHERE id_user = ?");
    if (!$stmt2) {
        throw new Exception('Prepare statement failed: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt2, 'i', $id_user);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_store_result($stmt2);
    
    if (mysqli_stmt_num_rows($stmt2) > 0) {
        // Update existing record
        $stmt3 = mysqli_prepare($conn, "UPDATE mahasiswa SET prodi = ?, angkatan = ? WHERE id_user = ?");
        if (!$stmt3) {
            throw new Exception('Prepare statement failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt3, 'ssi', $prodi, $angkatan, $id_user);
    } else {
        // Insert new record
        $stmt3 = mysqli_prepare($conn, "INSERT INTO mahasiswa (id_user, prodi, angkatan) VALUES (?, ?, ?)");
        if (!$stmt3) {
            throw new Exception('Prepare statement failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt3, 'iss', $id_user, $prodi, $angkatan);
    }
    
    if (!mysqli_stmt_execute($stmt3)) {
        throw new Exception('Gagal update data mahasiswa: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt2);
    mysqli_stmt_close($stmt3);

    $response['success'] = true;
    $response['message'] = 'Profil berhasil diperbarui';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
    exit;
}
?>