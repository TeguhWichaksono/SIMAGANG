<?php
// ========================================
// GET DETAIL RIWAYAT LOGBOOK
// Handler untuk mengambil detail lengkap logbook (absensi + kegiatan)
// ========================================

session_start();
require_once '../config/connection.php';

header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesi tidak valid.'
    ]);
    exit;
}

// Validate GET data
if (!isset($_GET['id_logbook'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID logbook tidak valid.'
    ]);
    exit;
}

// Get mahasiswa data
$id_user = $_SESSION['id'];
$query_mahasiswa = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query_mahasiswa);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mahasiswa = mysqli_fetch_assoc($result);

if (!$mahasiswa) {
    echo json_encode([
        'success' => false,
        'message' => 'Data mahasiswa tidak ditemukan.'
    ]);
    exit;
}

$id_mahasiswa = $mahasiswa['id_mahasiswa'];
$id_logbook = intval($_GET['id_logbook']);

// Get logbook harian data
$query_logbook = "SELECT lh.*, d.nama as nama_dosen
                  FROM logbook_harian lh
                  LEFT JOIN dosen d ON lh.id_dosen_validator = d.id_dosen
                  WHERE lh.id_logbook = ? AND lh.id_mahasiswa = ?";
$stmt_logbook = mysqli_prepare($conn, $query_logbook);
mysqli_stmt_bind_param($stmt_logbook, 'ii', $id_logbook, $id_mahasiswa);
mysqli_stmt_execute($stmt_logbook);
$result_logbook = mysqli_stmt_get_result($stmt_logbook);
$logbook = mysqli_fetch_assoc($result_logbook);

if (!$logbook) {
    echo json_encode([
        'success' => false,
        'message' => 'Logbook tidak ditemukan.'
    ]);
    exit;
}

// Get detail kegiatan
$query_kegiatan = "SELECT * FROM detail_kegiatan 
                   WHERE id_logbook = ? 
                   ORDER BY jam_mulai ASC, urutan ASC";
$stmt_kegiatan = mysqli_prepare($conn, $query_kegiatan);
mysqli_stmt_bind_param($stmt_kegiatan, 'i', $id_logbook);
mysqli_stmt_execute($stmt_kegiatan);
$result_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);

$kegiatan_list = [];
while ($row = mysqli_fetch_assoc($result_kegiatan)) {
    $kegiatan_list[] = $row;
}

// Format response
$response = [
    'success' => true,
    'data' => [
        'logbook' => [
            'id_logbook' => $logbook['id_logbook'],
            'tanggal' => $logbook['tanggal'],
            'tanggal_formatted' => date('l, d F Y', strtotime($logbook['tanggal'])),
            'jam_absensi' => $logbook['jam_absensi'],
            'jam_absensi_formatted' => date('H:i', strtotime($logbook['jam_absensi'])),
            'foto_absensi' => $logbook['foto_absensi'],
            'lokasi_absensi' => $logbook['lokasi_absensi'],
            'latitude_absensi' => $logbook['latitude_absensi'],
            'longitude_absensi' => $logbook['longitude_absensi'],
            'status_validasi' => $logbook['status_validasi'],
            'catatan_dosen' => $logbook['catatan_dosen'],
            'nama_dosen_validator' => $logbook['nama_dosen'],
            'tanggal_validasi' => $logbook['tanggal_validasi'],
            'tanggal_validasi_formatted' => $logbook['tanggal_validasi'] 
                ? date('d/m/Y H:i', strtotime($logbook['tanggal_validasi'])) 
                : null
        ],
        'kegiatan' => $kegiatan_list,
        'jumlah_kegiatan' => count($kegiatan_list)
    ]
];

echo json_encode($response);

mysqli_close($conn);
?>