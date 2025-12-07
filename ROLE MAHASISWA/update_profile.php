<?php
session_start();
// PERBAIKAN: Gunakan ../ agar path sesuai dengan struktur folder
include '../Koneksi/koneksi.php';

// Pastikan output JSON
header('Content-Type: application/json');

// Matikan pelaporan error visual agar tidak merusak JSON jika ada warning kecil
error_reporting(0); 

ob_clean(); // BERSIHKAN output sampah apa pun sebelum JSON dibuat

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception('User tidak terautentikasi. Silakan login kembali.');
    }

    $id_user  = $_POST['id_user'] ?? null;
    // Ambil data yang dikirim (NIM dan Email readonly di form, tapi tetap dikirim)
    $nim      = trim($_POST['nim'] ?? '');
    $prodi    = trim($_POST['prodi'] ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');
    $kontak   = trim($_POST['kontak'] ?? '');

    // --- Validasi dasar ---
    if ($id_user != $_SESSION['id']) {
        throw new Exception('ID user tidak valid');
    }
    
    // Validasi input
    if (empty($nim)) throw new Exception('NIM tidak ditemukan');

    // --- Cek apakah baris mahasiswa sudah ada ---
    $stmt2 = mysqli_prepare($conn, "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt2, 'i', $id_user);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_store_result($stmt2);

    $exists = mysqli_stmt_num_rows($stmt2) > 0;
    mysqli_stmt_close($stmt2);

    // --- UPDATE / INSERT tabel mahasiswa ---
    if ($exists) {
        // Update data mahasiswa
        $stmt3 = mysqli_prepare($conn,
            "UPDATE mahasiswa 
            SET prodi = ?, angkatan = ?, kontak = ? 
            WHERE id_user = ?"
        );
        mysqli_stmt_bind_param($stmt3, 'sssi', $prodi, $angkatan, $kontak, $id_user);

    } else {
        // Insert baru jika belum ada
        $stmt3 = mysqli_prepare($conn,
            "INSERT INTO mahasiswa (id_user, prodi, angkatan, kontak, status_magang) 
            VALUES (?, ?, ?, ?, 'pra-magang')"
        );
        // Catatan: NIM biasanya ada di tabel users atau mahasiswa, sesuaikan jika perlu insert NIM
        mysqli_stmt_bind_param($stmt3, 'isss', $id_user, $prodi, $angkatan, $kontak);
    }

    if (!mysqli_stmt_execute($stmt3)) {
        throw new Exception("Gagal update data mahasiswa: " . mysqli_stmt_error($stmt3));
    }

    mysqli_stmt_close($stmt3);

    // --- SUCCESS ---
    $response['success'] = true;
    $response['message'] = "Data berhasil diperbarui";

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();

} finally {
    // Kembalikan output JSON
    echo json_encode($response);
    exit;
}
?>