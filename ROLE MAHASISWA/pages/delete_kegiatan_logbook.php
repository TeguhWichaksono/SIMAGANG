<?php
// pages/delete_kegiatan_logbook.php
session_start();
include '../../Koneksi/koneksi.php';

header('Content-Type: application/json');

// Cek permission
if (!isset($_SESSION['id']) || !isset($_SESSION['can_crud_magang']) || !$_SESSION['can_crud_magang']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Method']);
    exit;
}

$id_detail = intval($_POST['id_detail']);
$id_user = $_SESSION['id'];

// 1. Ambil data detail & cek status validasi parent logbook
// Kita harus pastikan logbooknya punya user yang login DAN statusnya masih pending
$query_check = "SELECT dk.foto_kegiatan, lh.status_validasi, lh.id_logbook
                FROM detail_kegiatan dk
                JOIN logbook_harian lh ON dk.id_logbook = lh.id_logbook
                JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
                WHERE dk.id_detail = ? AND m.id_user = ?";

$stmt = mysqli_prepare($conn, $query_check);
mysqli_stmt_bind_param($stmt, 'ii', $id_detail, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
    exit;
}

if ($data['status_validasi'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus kegiatan yang sudah divalidasi.']);
    exit;
}

// 2. Hapus file foto jika ada
if (!empty($data['foto_kegiatan'])) {
    $file_path = '../uploads/' . $data['foto_kegiatan'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// 3. Hapus data dari database
$query_delete = "DELETE FROM detail_kegiatan WHERE id_detail = ?";
$stmt_delete = mysqli_prepare($conn, $query_delete);
mysqli_stmt_bind_param($stmt_delete, 'i', $id_detail);

if (mysqli_stmt_execute($stmt_delete)) {
    // Log aktivitas
    $aktivitas = "Menghapus kegiatan magang (ID Detail: $id_detail)";
    $query_log = "INSERT INTO log_aktivitas (id_user, aktivitas, waktu) VALUES (?, ?, NOW())";
    $stmt_log = mysqli_prepare($conn, $query_log);
    mysqli_stmt_bind_param($stmt_log, 'is', $id_user, $aktivitas);
    mysqli_stmt_execute($stmt_log);

    echo json_encode(['success' => true, 'message' => 'Kegiatan berhasil dihapus.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
}
?>