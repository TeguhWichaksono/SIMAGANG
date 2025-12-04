<?php
session_start();
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda belum login']);
    exit;
}

include '../../Koneksi/koneksi.php';

date_default_timezone_set('Asia/Jakarta');


// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil data dari POST
$id_kegiatan = isset($_POST['id_kegiatan']) ? intval($_POST['id_kegiatan']) : 0;
$id_mahasiswa = isset($_POST['id_mahasiswa']) ? intval($_POST['id_mahasiswa']) : 0;

// Validasi input
if (empty($id_kegiatan) || empty($id_mahasiswa)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Ambil data kegiatan
$query_kegiatan = "SELECT id_kegiatan, id_mahasiswa, tanggal, lokasi_kegiatan, status_validasi 
                   FROM kegiatan_harian 
                   WHERE id_kegiatan = ?";
$stmt_kegiatan = mysqli_prepare($conn, $query_kegiatan);
mysqli_stmt_bind_param($stmt_kegiatan, 'i', $id_kegiatan);
mysqli_stmt_execute($stmt_kegiatan);
$result_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);
$kegiatan_data = mysqli_fetch_assoc($result_kegiatan);

if (!$kegiatan_data) {
    echo json_encode(['success' => false, 'message' => 'Data kegiatan tidak ditemukan']);
    exit;
}

// Cek apakah kegiatan ini milik mahasiswa yang login
if ($kegiatan_data['id_mahasiswa'] != $id_mahasiswa) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus kegiatan ini']);
    exit;
}

// Cek apakah kegiatan hari ini
$today = date('Y-m-d');
$tanggal_kegiatan = $kegiatan_data['tanggal'];

if ($tanggal_kegiatan != $today) {
    echo json_encode(['success' => false, 'message' => 'Anda hanya bisa menghapus/retake kegiatan hari ini']);
    exit;
}

// Cek apakah sudah divalidasi dosen
if ($kegiatan_data['status_validasi'] === 'disetujui') {
    echo json_encode(['success' => false, 'message' => 'Kegiatan sudah disetujui oleh Dosen Validator. Tidak dapat dihapus.']);
    exit;
}

if ($kegiatan_data['status_validasi'] === 'ditolak') {
    echo json_encode(['success' => false, 'message' => 'Kegiatan sudah ditolak oleh Dosen Validator. Tidak dapat dihapus.']);
    exit;
}

// Hapus file foto
$foto_path = '../uploads/' . $kegiatan_data['lokasi_kegiatan'];
if (file_exists($foto_path)) {
    unlink($foto_path);
}

// Hapus dari database
$query_delete = "DELETE FROM kegiatan_harian WHERE id_kegiatan = ?";
$stmt_delete = mysqli_prepare($conn, $query_delete);
mysqli_stmt_bind_param($stmt_delete, 'i', $id_kegiatan);

if (mysqli_stmt_execute($stmt_delete)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Kegiatan berhasil dihapus. Anda dapat mengambil foto ulang.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari database: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt_delete);
mysqli_close($conn);
?>