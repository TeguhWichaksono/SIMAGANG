<?php
session_start();
include '../../Koneksi/koneksi.php';

header('Content-Type: application/json');

// Pastikan koneksi berhasil
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}

// Validasi login
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User tidak terautentikasi']);
    exit;
}

// Validasi permintaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['id_absen']) || 
    !isset($_POST['status'])) {

    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

$id_absen = (int) $_POST['id_absen'];
$status   = $_POST['status']; // disetujui / ditolak
$id_dosen = $_SESSION['id'];

// Validasi nilai status ENUМ
$allowed_status = ['pending', 'disetujui', 'ditolak'];

if (!in_array($status, $allowed_status)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

if ($id_absen <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID absensi tidak valid']);
    exit;
}

$stmt = mysqli_prepare($conn, "
    UPDATE absensi 
    SET status_validasi = ?, id_dosen_validator = ?
    WHERE id_absen = ?
");

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Prepare statement gagal: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "sii", $status, $id_dosen, $id_absen);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Status validasi diperbarui']);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal memperbarui status validasi: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
?>
