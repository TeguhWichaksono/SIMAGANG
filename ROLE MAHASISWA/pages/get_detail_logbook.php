<?php
session_start();
include '../../Koneksi/koneksi.php';
// require_once '../../../config.php';
header('Content-Type: application/json');
date_default_timezone_set(timezoneId: 'Asia/Jakarta');

// Security check
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get mahasiswa ID
$id_user = $_SESSION['id'];
$query_mahasiswa = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query_mahasiswa);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mahasiswa = mysqli_fetch_assoc($result);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// Get ID logbook
$id_logbook = $_GET['id'] ?? null;

if (empty($id_logbook)) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// Get logbook data
$query_logbook = "SELECT * FROM logbook_harian WHERE id_logbook = ? AND id_mahasiswa = ?";
$stmt_logbook = mysqli_prepare($conn, $query_logbook);
mysqli_stmt_bind_param($stmt_logbook, 'ii', $id_logbook, $id_mahasiswa);
mysqli_stmt_execute($stmt_logbook);
$result_logbook = mysqli_stmt_get_result($stmt_logbook);

if (mysqli_num_rows($result_logbook) === 0) {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    exit;
}

$logbook = mysqli_fetch_assoc($result_logbook);

// Get detail kegiatan
$query_kegiatan = "SELECT * FROM detail_kegiatan WHERE id_logbook = ? ORDER BY jam_mulai ASC, urutan ASC";
$stmt_kegiatan = mysqli_prepare($conn, $query_kegiatan);
mysqli_stmt_bind_param($stmt_kegiatan, 'i', $id_logbook);
mysqli_stmt_execute($stmt_kegiatan);
$result_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);

$kegiatan = [];
while ($row = mysqli_fetch_assoc($result_kegiatan)) {
    $kegiatan[] = [
        'id_detail' => $row['id_detail'],
        'jam_mulai' => substr($row['jam_mulai'], 0, 5), // HH:MM
        'jam_selesai' => substr($row['jam_selesai'], 0, 5), // HH:MM
        'deskripsi_kegiatan' => $row['deskripsi_kegiatan'],
        'foto_kegiatan' => $row['foto_kegiatan']
    ];
}

echo json_encode([
    'success' => true,
    'data' => [
        'id_logbook' => $logbook['id_logbook'],
        'tanggal' => $logbook['tanggal'],
        'foto_absensi' => $logbook['foto_absensi'],
        'lokasi_absensi' => $logbook['lokasi_absensi'],
        'jam_absensi' => substr($logbook['jam_absensi'], 0, 5), // HH:MM
        'status_validasi' => $logbook['status_validasi'],
        'catatan_dosen' => $logbook['catatan_dosen'],
        'kegiatan' => $kegiatan
    ]
]);

mysqli_close($conn);
?>