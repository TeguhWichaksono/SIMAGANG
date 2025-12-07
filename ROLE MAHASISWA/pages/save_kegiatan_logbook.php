<?php
// ========================================
// SAVE KEGIATAN LOGBOOK
// Handler untuk menyimpan/update detail kegiatan
// ========================================

session_start();
include '../../Koneksi/koneksi.php';
// require_once '../../../config.php';
date_default_timezone_set(timezoneId: 'Asia/Jakarta');

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
        'message' => 'Anda tidak memiliki izin untuk menambah kegiatan.'
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

// Validate required fields
if (!isset($_POST['id_logbook']) || !isset($_POST['jam_mulai']) || !isset($_POST['jam_selesai']) || !isset($_POST['deskripsi_kegiatan'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap.'
    ]);
    exit;
}

$id_logbook = intval($_POST['id_logbook']);
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$deskripsi_kegiatan = mysqli_real_escape_string($conn, trim($_POST['deskripsi_kegiatan']));

// Validate deskripsi length
if (strlen($deskripsi_kegiatan) < 20) {
    echo json_encode([
        'success' => false,
        'message' => 'Deskripsi kegiatan minimal 20 karakter.'
    ]);
    exit;
}

// Validate time
if ($jam_mulai >= $jam_selesai) {
    echo json_encode([
        'success' => false,
        'message' => 'Jam selesai harus lebih besar dari jam mulai.'
    ]);
    exit;
}

// Verify logbook ownership
$query_check = "SELECT lh.id_logbook, lh.status_validasi 
                FROM logbook_harian lh 
                WHERE lh.id_logbook = ? AND lh.id_mahasiswa = ?";
$stmt_check = mysqli_prepare($conn, $query_check);
mysqli_stmt_bind_param($stmt_check, 'ii', $id_logbook, $id_mahasiswa);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$logbook = mysqli_fetch_assoc($result_check);

if (!$logbook) {
    echo json_encode([
        'success' => false,
        'message' => 'Logbook tidak ditemukan atau bukan milik Anda.'
    ]);
    exit;
}

// Check if logbook is already validated
if ($logbook['status_validasi'] !== 'pending') {
    echo json_encode([
        'success' => false,
        'message' => 'Logbook sudah divalidasi. Tidak dapat menambah kegiatan.'
    ]);
    exit;
}

// Get current max urutan
$query_urutan = "SELECT COALESCE(MAX(urutan), 0) + 1 as next_urutan 
                 FROM detail_kegiatan 
                 WHERE id_logbook = ?";
$stmt_urutan = mysqli_prepare($conn, $query_urutan);
mysqli_stmt_bind_param($stmt_urutan, 'i', $id_logbook);
mysqli_stmt_execute($stmt_urutan);
$result_urutan = mysqli_stmt_get_result($stmt_urutan);
$urutan_data = mysqli_fetch_assoc($result_urutan);
$urutan = $urutan_data['next_urutan'];

// Handle foto kegiatan (opsional)
$filename = null;
if (isset($_FILES['foto_kegiatan']) && $_FILES['foto_kegiatan']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $file_type = $_FILES['foto_kegiatan']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format foto tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.'
        ]);
        exit;
    }
    
    // Max 5MB
    if ($_FILES['foto_kegiatan']['size'] > 5242880) {
        echo json_encode([
            'success' => false,
            'message' => 'Ukuran foto maksimal 5MB.'
        ]);
        exit;
    }
    
    $extension = pathinfo($_FILES['foto_kegiatan']['name'], PATHINFO_EXTENSION);
    $filename = 'kegiatan_' . $id_mahasiswa . '_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_dir = '../uploads/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($_FILES['foto_kegiatan']['tmp_name'], $filepath)) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan foto kegiatan.'
        ]);
        exit;
    }
}

// Check if this is an update (edit)
$is_edit = isset($_POST['id_detail']) && !empty($_POST['id_detail']);

if ($is_edit) {
    // UPDATE existing kegiatan
    $id_detail = intval($_POST['id_detail']);
    
    // Verify detail ownership
    $query_verify = "SELECT dk.id_detail, dk.foto_kegiatan
                     FROM detail_kegiatan dk
                     INNER JOIN logbook_harian lh ON dk.id_logbook = lh.id_logbook
                     WHERE dk.id_detail = ? AND lh.id_mahasiswa = ?";
    $stmt_verify = mysqli_prepare($conn, $query_verify);
    mysqli_stmt_bind_param($stmt_verify, 'ii', $id_detail, $id_mahasiswa);
    mysqli_stmt_execute($stmt_verify);
    $result_verify = mysqli_stmt_get_result($stmt_verify);
    $detail = mysqli_fetch_assoc($result_verify);
    
    if (!$detail) {
        echo json_encode([
            'success' => false,
            'message' => 'Detail kegiatan tidak ditemukan.'
        ]);
        exit;
    }
    
    // If new photo uploaded, delete old photo
    if ($filename && $detail['foto_kegiatan']) {
        $old_filepath = '../uploads/' . $detail['foto_kegiatan'];
        if (file_exists($old_filepath)) {
            unlink($old_filepath);
        }
    }
    
    // Update query
    if ($filename) {
        $query_update = "UPDATE detail_kegiatan 
                         SET jam_mulai = ?, jam_selesai = ?, deskripsi_kegiatan = ?, foto_kegiatan = ?
                         WHERE id_detail = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, 'ssssi', $jam_mulai, $jam_selesai, $deskripsi_kegiatan, $filename, $id_detail);
    } else {
        $query_update = "UPDATE detail_kegiatan 
                         SET jam_mulai = ?, jam_selesai = ?, deskripsi_kegiatan = ?
                         WHERE id_detail = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, 'sssi', $jam_mulai, $jam_selesai, $deskripsi_kegiatan, $id_detail);
    }
    
    if (mysqli_stmt_execute($stmt_update)) {
        // Log activity
        $aktivitas = "Mengupdate detail kegiatan (ID: $id_detail)";
        $query_log = "INSERT INTO log_aktivitas (id_user, aktivitas, waktu) VALUES (?, ?, NOW())";
        $stmt_log = mysqli_prepare($conn, $query_log);
        mysqli_stmt_bind_param($stmt_log, 'is', $id_user, $aktivitas);
        mysqli_stmt_execute($stmt_log);
        
        echo json_encode([
            'success' => true,
            'message' => 'Kegiatan berhasil diupdate!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengupdate kegiatan: ' . mysqli_error($conn)
        ]);
    }
    
} else {
    // INSERT new kegiatan
    $query_insert = "INSERT INTO detail_kegiatan 
                     (id_logbook, jam_mulai, jam_selesai, deskripsi_kegiatan, foto_kegiatan, urutan) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = mysqli_prepare($conn, $query_insert);
    mysqli_stmt_bind_param($stmt_insert, 'issssi', 
        $id_logbook, 
        $jam_mulai, 
        $jam_selesai, 
        $deskripsi_kegiatan, 
        $filename, 
        $urutan
    );
    
    if (mysqli_stmt_execute($stmt_insert)) {
        // Log activity
        $aktivitas = "Menambah detail kegiatan pada logbook (ID: $id_logbook)";
        $query_log = "INSERT INTO log_aktivitas (id_user, aktivitas, waktu) VALUES (?, ?, NOW())";
        $stmt_log = mysqli_prepare($conn, $query_log);
        mysqli_stmt_bind_param($stmt_log, 'is', $id_user, $aktivitas);
        mysqli_stmt_execute($stmt_log);
        
        echo json_encode([
            'success' => true,
            'message' => 'Kegiatan berhasil ditambahkan!'
        ]);
    } else {
        // Delete uploaded file if database insert fails
        if ($filename) {
            $filepath = '../uploads/' . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambahkan kegiatan: ' . mysqli_error($conn)
        ]);
    }
}

mysqli_close($conn);
?>