<?php
session_start();
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda belum login']);
    exit;
}

include '../../Koneksi/koneksi.php';


// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil data dari POST
$id_absen = isset($_POST['id_absen']) ? intval($_POST['id_absen']) : 0;
$id_mahasiswa = isset($_POST['id_mahasiswa']) ? intval($_POST['id_mahasiswa']) : 0;

// Validasi input
if (empty($id_absen) || empty($id_mahasiswa)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Ambil data absensi
$query_absen = "SELECT id_absen, id_mahasiswa, tanggal, foto_mahasiswa, status_validasi 
                FROM absensi 
                WHERE id_absen = ?";
$stmt_absen = mysqli_prepare($conn, $query_absen);
mysqli_stmt_bind_param($stmt_absen, 'i', $id_absen);
mysqli_stmt_execute($stmt_absen);
$result_absen = mysqli_stmt_get_result($stmt_absen);
$absensi_data = mysqli_fetch_assoc($result_absen);

if (!$absensi_data) {
    echo json_encode(['success' => false, 'message' => 'Data absensi tidak ditemukan']);
    exit;
}

// Cek apakah absensi ini milik mahasiswa yang login
if ($absensi_data['id_mahasiswa'] != $id_mahasiswa) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus absensi ini']);
    exit;
}

// Cek apakah absensi hari ini
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$tanggal_absen = $absensi_data['tanggal'];

if ($tanggal_absen != $today) {
    echo json_encode(['success' => false, 'message' => 'Anda hanya bisa menghapus/retake foto absensi hari ini']);
    exit;
}

// Cek apakah sudah divalidasi dosen
if ($absensi_data['status_validasi'] === 'disetujui') {
    echo json_encode(['success' => false, 'message' => 'Absensi sudah disetujui oleh Dosen Validator. Tidak dapat dihapus.']);
    exit;
}

if ($absensi_data['status_validasi'] === 'ditolak') {
    echo json_encode(['success' => false, 'message' => 'Absensi sudah ditolak oleh Dosen Validator. Tidak dapat dihapus.']);
    exit;
}

// Hapus file foto
$foto_path = '../uploads/' . $absensi_data['foto_mahasiswa'];
if (file_exists($foto_path)) {
    unlink($foto_path);
}

// Hapus dari database
$query_delete = "DELETE FROM absensi WHERE id_absen = ?";
$stmt_delete = mysqli_prepare($conn, $query_delete);
mysqli_stmt_bind_param($stmt_delete, 'i', $id_absen);

if (mysqli_stmt_execute($stmt_delete)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Absensi berhasil dihapus. Anda dapat mengambil foto ulang.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari database: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt_delete);
mysqli_close($conn);
?>