<?php
// ========================================
// get_detail_logbook.php
// AJAX Endpoint - Get Detail Logbook
// DIPANGGIL VIA ajax_handler.php
// ========================================

// JANGAN ADA header() atau session_start() di sini!
// Sudah di-handle oleh ajax_handler.php
date_default_timezone_set('Asia/Jakarta');

// Validate input
if (!isset($_GET['id_logbook'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID Logbook tidak ditemukan'
    ]);
    exit;
}

$id_logbook = (int) $_GET['id_logbook'];
$id_user_login = $_SESSION['id'];

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

// Get Logbook Detail
$query_logbook = "
    SELECT 
        lh.id_logbook,
        lh.tanggal,
        lh.jam_absensi,
        lh.lokasi_absensi,
        lh.foto_absensi,
        lh.status_validasi,
        lh.catatan_dosen,
        m.id_mahasiswa,
        u.nama AS nama_mahasiswa,
        u.nim,
        u.foto_profil,
        m.prodi,
        k.nama_kelompok,
        mp.nama_mitra,
        mp.alamat AS alamat_mitra
    FROM logbook_harian lh
    JOIN mahasiswa m ON lh.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN pengajuan_magang pm ON k.id_kelompok = pm.id_kelompok
    LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    WHERE lh.id_logbook = ?
    AND k.id_dosen_pembimbing = ?
";

$stmt_logbook = mysqli_prepare($conn, $query_logbook);

if (!$stmt_logbook) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt_logbook, 'ii', $id_logbook, $id_dosen);
mysqli_stmt_execute($stmt_logbook);
$result_logbook = mysqli_stmt_get_result($stmt_logbook);
$logbook = mysqli_fetch_assoc($result_logbook);

if (!$logbook) {
    echo json_encode([
        'success' => false,
        'message' => 'Logbook tidak ditemukan atau bukan mahasiswa bimbingan Anda'
    ]);
    exit;
}

// Get Detail Kegiatan
$query_kegiatan = "
    SELECT 
        id_detail,
        jam_mulai,
        jam_selesai,
        deskripsi_kegiatan,
        foto_kegiatan,
        urutan
    FROM detail_kegiatan
    WHERE id_logbook = ?
    ORDER BY jam_mulai ASC, urutan ASC
";

$stmt_kegiatan = mysqli_prepare($conn, $query_kegiatan);

if (!$stmt_kegiatan) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt_kegiatan, 'i', $id_logbook);
mysqli_stmt_execute($stmt_kegiatan);
$result_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);

$kegiatan_list = [];
while ($row = mysqli_fetch_assoc($result_kegiatan)) {
    $kegiatan_list[] = $row;
}

// Format Tanggal Indonesia
$hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

$bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

$timestamp = strtotime($logbook['tanggal']);
$nama_hari = $hari[date('l', $timestamp)];
$tgl = date('d', $timestamp);
$nama_bulan = $bulan[(int)date('m', $timestamp)];
$tahun = date('Y', $timestamp);


$logbook['tanggal_formatted'] = "$nama_hari, $tgl $nama_bulan $tahun";
$logbook['jam_formatted'] = date('H:i', strtotime($logbook['jam_absensi'])) . ' WIB';

// Response Success
echo json_encode([
    'success' => true,
    'data' => [
        'logbook' => $logbook,
        'kegiatan' => $kegiatan_list
    ]
]);

exit;
?>