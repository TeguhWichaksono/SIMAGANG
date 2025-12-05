<?php
// ========================================
// SAVE ABSENSI LOGBOOK
// Handler untuk menyimpan foto absensi
// ========================================

session_start();
include '../../Koneksi/koneksi.php';
// require_once '../../../config.php';

header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesi tidak valid. Silakan login kembali.'
    ]);
    exit;
}

// Check CRUD permission
if (!isset($_SESSION['can_crud_magang']) || !$_SESSION['can_crud_magang']) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda tidak memiliki izin untuk melakukan absensi.'
    ]);
    exit;
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak valid.'
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
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

// Check if already absen today
$query_check = "SELECT id_logbook FROM logbook_harian WHERE id_mahasiswa = ? AND tanggal = ?";
$stmt_check = mysqli_prepare($conn, $query_check);
mysqli_stmt_bind_param($stmt_check, 'is', $id_mahasiswa, $today);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda sudah melakukan absensi hari ini.'
    ]);
    exit;
}

// Validate required fields
if (!isset($_POST['foto']) || !isset($_POST['lokasi']) || !isset($_POST['timestamp'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap.'
    ]);
    exit;
}

// Process base64 image
$foto_base64 = $_POST['foto'];
$lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$timestamp = $_POST['timestamp'];

// Extract image data
if (preg_match('/^data:image\/(\w+);base64,/', $foto_base64, $type)) {
    $foto_base64 = substr($foto_base64, strpos($foto_base64, ',') + 1);
    $type = strtolower($type[1]);
    
    if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Format foto tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.'
        ]);
        exit;
    }
    
    $foto_base64 = base64_decode($foto_base64);
    
    if ($foto_base64 === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memproses foto.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Format data foto tidak valid.'
    ]);
    exit;
}

// Generate unique filename
$filename = 'absensi_' . $id_mahasiswa . '_' . date('Ymd_His') . '.jpg';
$upload_dir = '../uploads/';

// Create uploads directory if not exists
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$filepath = $upload_dir . $filename;

// Save image file
if (!file_put_contents($filepath, $foto_base64)) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan foto ke server.'
    ]);
    exit;
}

// Get time from timestamp
$datetime = new DateTime($timestamp);
$jam_absensi = $datetime->format('H:i:s');

// Insert to database
$query_insert = "INSERT INTO logbook_harian 
                 (id_mahasiswa, tanggal, foto_absensi, lokasi_absensi, latitude_absensi, longitude_absensi, jam_absensi, status_validasi) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt_insert = mysqli_prepare($conn, $query_insert);
mysqli_stmt_bind_param($stmt_insert, 'isssdds', 
    $id_mahasiswa, 
    $today, 
    $filename, 
    $lokasi, 
    $latitude, 
    $longitude, 
    $jam_absensi
);

if (mysqli_stmt_execute($stmt_insert)) {
    // Log activity
    $id_logbook = mysqli_insert_id($conn);
    $aktivitas = "Melakukan absensi untuk tanggal " . date('d/m/Y', strtotime($today));
    $query_log = "INSERT INTO log_aktivitas (id_user, aktivitas, waktu) VALUES (?, ?, NOW())";
    $stmt_log = mysqli_prepare($conn, $query_log);
    mysqli_stmt_bind_param($stmt_log, 'is', $id_user, $aktivitas);
    mysqli_stmt_execute($stmt_log);
    
    echo json_encode([
        'success' => true,
        'message' => 'Absensi berhasil disimpan!',
        'id_logbook' => $id_logbook
    ]);
} else {
    // Delete uploaded file if database insert fails
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan data absensi: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>