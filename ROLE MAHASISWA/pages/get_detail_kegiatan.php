<?php
// pages/get_detail_kegiatan.php
session_start();
include '../../Koneksi/koneksi.php'; // Path yang diperbaiki

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_GET['id_detail'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit;
}

$id_detail = intval($_GET['id_detail']);
$id_user = $_SESSION['id'];

// Verifikasi kepemilikan data (join ke logbook -> mahasiswa -> user)
$query = "SELECT dk.* FROM detail_kegiatan dk
          JOIN logbook_harian lh ON dk.id_logbook = lh.id_logbook
          JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
          WHERE dk.id_detail = ? AND m.id_user = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $id_detail, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data) {
    // Format jam agar sesuai input type="time" (HH:MM)
    $data['jam_mulai'] = date('H:i', strtotime($data['jam_mulai']));
    $data['jam_selesai'] = date('H:i', strtotime($data['jam_selesai']));
    
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau akses ditolak.']);
}
?>