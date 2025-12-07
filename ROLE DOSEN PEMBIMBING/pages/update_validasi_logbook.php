<?php
// ========================================
// update_validasi_logbook.php
// AJAX Endpoint - Update Validasi Logbook
// DIPANGGIL VIA ajax_handler.php
// ========================================

date_default_timezone_set('Asia/Jakarta');

// Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Validate Input
if (!isset($_POST['id_logbook']) || !isset($_POST['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$id_logbook = (int) $_POST['id_logbook'];
$status = $_POST['status'];
$catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
$id_user_login = $_SESSION['id'];

// Validate status
$allowed_status = ['disetujui', 'ditolak'];
if (!in_array($status, $allowed_status)) {
    echo json_encode([
        'success' => false,
        'message' => 'Status tidak valid'
    ]);
    exit;
}

// Get ID Dosen
$query_dosen = "SELECT id_dosen FROM dosen WHERE id_user = ?";
$stmt_dosen = mysqli_prepare($conn, $query_dosen);

if (!$stmt_dosen) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt_dosen, 'i', $id_user_login);
mysqli_stmt_execute($stmt_dosen);
$result_dosen = mysqli_stmt_get_result($stmt_dosen);
$row_dosen = mysqli_fetch_assoc($result_dosen);

if (!$row_dosen) {
    echo json_encode([
        'success' => false,
        'message' => 'Data dosen tidak ditemukan'
    ]);
    exit;
}

$id_dosen = $row_dosen['id_dosen'];

// Verify Ownership
$query_verify = "
    SELECT lh.id_logbook, lh.status_validasi
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    WHERE lh.id_logbook = ?
    AND k.id_dosen_pembimbing = ?
";

$stmt_verify = mysqli_prepare($conn, $query_verify);

if (!$stmt_verify) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt_verify, 'ii', $id_logbook, $id_dosen);
mysqli_stmt_execute($stmt_verify);
$result_verify = mysqli_stmt_get_result($stmt_verify);
$verify_data = mysqli_fetch_assoc($result_verify);

if (!$verify_data) {
    echo json_encode([
        'success' => false,
        'message' => 'Logbook tidak ditemukan atau bukan mahasiswa bimbingan Anda'
    ]);
    exit;
}

if ($verify_data['status_validasi'] !== 'pending') {
    echo json_encode([
        'success' => false,
        'message' => 'Logbook sudah divalidasi sebelumnya'
    ]);
    exit;
}

// Update Status
$query_update = "
    UPDATE logbook_harian 
    SET status_validasi = ?,
        catatan_dosen = ?,
        id_dosen_validator = ?,
        tanggal_validasi = NOW()
    WHERE id_logbook = ?
";

$stmt_update = mysqli_prepare($conn, $query_update);

if (!$stmt_update) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt_update, 'ssii', $status, $catatan, $id_dosen, $id_logbook);

if (mysqli_stmt_execute($stmt_update)) {
    
    $status_text = $status === 'disetujui' ? 'disetujui' : 'ditolak';
    
    // Create Notification
    try {
        $query_mahasiswa = "
            SELECT m.id_user 
            FROM logbook_harian lh
            JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
            WHERE lh.id_logbook = ?
        ";
        
        $stmt_mahasiswa = mysqli_prepare($conn, $query_mahasiswa);
        mysqli_stmt_bind_param($stmt_mahasiswa, 'i', $id_logbook);
        mysqli_stmt_execute($stmt_mahasiswa);
        $result_mahasiswa = mysqli_stmt_get_result($stmt_mahasiswa);
        $row_mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
        
        if ($row_mahasiswa) {
            $id_user_mahasiswa = $row_mahasiswa['id_user'];
            
            $pesan_notif = $status === 'disetujui' 
                ? "Logbook Anda telah disetujui oleh dosen pembimbing" 
                : "Logbook Anda ditolak. " . ($catatan ? "Catatan: $catatan" : "");
            
            $query_notif = "
                INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal)
                VALUES (?, ?, 'baru', NOW())
            ";
            
            $stmt_notif = mysqli_prepare($conn, $query_notif);
            if ($stmt_notif) {
                mysqli_stmt_bind_param($stmt_notif, 'is', $id_user_mahasiswa, $pesan_notif);
                mysqli_stmt_execute($stmt_notif);
            }
        }
    } catch (Exception $e) {
        // Ignore notification errors
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Logbook berhasil $status_text",
        'status' => $status
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memperbarui status: ' . mysqli_error($conn)
    ]);
}

exit;
?>