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
$id_mahasiswa = isset($_POST['id_mahasiswa']) ? intval($_POST['id_mahasiswa']) : 0;
$foto_base64 = isset($_POST['foto']) ? $_POST['foto'] : '';
$lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';
$latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : '';
$longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : '';
$timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : '';

// Validasi input
if (empty($id_mahasiswa) || empty($foto_base64) || empty($lokasi) || empty($timestamp)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Cek apakah mahasiswa ini valid
$query_check = "SELECT m.id_mahasiswa, m.prodi, u.nama, k.nama_kelompok 
                FROM mahasiswa m 
                LEFT JOIN users u ON m.id_user = u.id 
                LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa 
                LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
                WHERE m.id_mahasiswa = ?";
$stmt_check = mysqli_prepare($conn, $query_check);
mysqli_stmt_bind_param($stmt_check, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$mahasiswa_data = mysqli_fetch_assoc($result_check);

if (!$mahasiswa_data) {
    echo json_encode(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan']);
    exit;
}

// Cek apakah sudah absen hari ini
$today = date('Y-m-d');
$query_today = "SELECT id_absen FROM absensi WHERE id_mahasiswa = ? AND tanggal = ?";
$stmt_today = mysqli_prepare($conn, $query_today);
mysqli_stmt_bind_param($stmt_today, 'is', $id_mahasiswa, $today);
mysqli_stmt_execute($stmt_today);
$result_today = mysqli_stmt_get_result($stmt_today);

if (mysqli_num_rows($result_today) > 0) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah absen hari ini. Gunakan tombol Hapus/Retake jika ingin mengubah.']);
    exit;
}

// Ambil data untuk generate nama file
$prodi = $mahasiswa_data['prodi'];
$nama_mahasiswa = $mahasiswa_data['nama'];
$nama_kelompok = $mahasiswa_data['nama_kelompok'] ?? 'NoGroup';

// Generate alias prodi
$alias_prodi = 'UNKNOWN';
if (stripos($prodi, 'Manajemen Informatika') !== false) {
    $alias_prodi = 'MIF';
} elseif (stripos($prodi, 'Teknik Komputer') !== false) {
    $alias_prodi = 'TKK';
} elseif (stripos($prodi, 'Teknik Informatika') !== false) {
    $alias_prodi = 'TIF';
}

// Clean nama untuk filename (remove special chars)
$nama_clean = preg_replace('/[^a-zA-Z0-9]/', '', $nama_mahasiswa);
$kelompok_clean = preg_replace('/[^a-zA-Z0-9]/', '', $nama_kelompok);

// Generate timestamp untuk filename
try {
    $datetime = new DateTime($timestamp); // Ini masih waktu UTC dari JS
    $datetime->setTimezone(new DateTimeZone('Asia/Jakarta')); // KONVERSI KE WIB (Fix mundur 7 jam)
} catch (Exception $e) {
    // Fallback jika timestamp invalid, gunakan waktu server saat ini
    $datetime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
}

$timestamp_str = $datetime->format('Ymd_His');

// Format: MIF_KelompokA_BudiSantoso_20231203_143022.jpg
$filename = $alias_prodi . '_' . $kelompok_clean . '_' . $nama_clean . '_' . $timestamp_str . '.jpg';

// Decode base64 image
$foto_parts = explode(',', $foto_base64);
$foto_base64_decode = base64_decode($foto_parts[1]);

// Path upload
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$filepath = $upload_dir . $filename;

// Save file
if (file_put_contents($filepath, $foto_base64_decode) === false) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan foto']);
    exit;
}

// Insert ke database
$tanggal = $datetime->format('Y-m-d');
$jam = $datetime->format('H:i:s');

// **REVISI LOGIKA PENYIMPANAN LOKASI**
// Cek apakah lokasi yang dikirim adalah pesan error dari JS
if (stripos($lokasi, 'tidak dapat mendapatkan alamat') !== false || empty($lokasi)) {
    // Jika gagal, simpan HANYA koordinat
    $lokasi_full = $latitude . ", " . $longitude;
} else {
    // Jika berhasil, simpan alamat lengkap + koordinat sebagai cadangan
    $lokasi_full = $lokasi . ' (' . $latitude . ', ' . $longitude . ')';
}

$query_insert = "INSERT INTO absensi (id_mahasiswa, tanggal, jam, lokasi, foto_mahasiswa, status_validasi) 
                 VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt_insert = mysqli_prepare($conn, $query_insert);
mysqli_stmt_bind_param($stmt_insert, 'issss', $id_mahasiswa, $tanggal, $jam, $lokasi_full, $filename);

if (mysqli_stmt_execute($stmt_insert)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Absensi berhasil disimpan',
        'filename' => $filename
    ]);
} else {
    // Hapus file jika insert gagal
    unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt_insert);
mysqli_close($conn);
?>