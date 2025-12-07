<?php
/**
 * File: simpanMitra.php
 * Fungsi: Menyimpan pengajuan mitra baru (TIDAK langsung ke mitra_perusahaan)
 * Lokasi: ROLE MAHASISWA/pages/simpanMitra.php
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../Koneksi/koneksi.php';

// Cek login
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu'
    ]);
    exit;
}

$id_user = $_SESSION['id'];

try {
    // Ambil data JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validasi data
    if (empty($data['nama']) || empty($data['alamat']) || empty($data['bidang']) || empty($data['kontak'])) {
        throw new Exception("Semua field harus diisi!");
    }

    // Ambil id_mahasiswa dari user yang login
    $query_mhs = "SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $query_mhs);
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $mhs = mysqli_fetch_assoc($result);

    if (!$mhs) {
        throw new Exception("Data mahasiswa tidak ditemukan");
    }

    $id_mahasiswa = $mhs['id_mahasiswa'];

    // --- REVISI: VALIDASI STATUS & DUPLIKASI ---

    // 1. Cek Status Magang (Harus Pra-Magang)
    $q_status = "SELECT status_magang FROM mahasiswa WHERE id_mahasiswa = ?";
    $stmt_st = mysqli_prepare($conn, $q_status);
    mysqli_stmt_bind_param($stmt_st, 'i', $id_mahasiswa);
    mysqli_stmt_execute($stmt_st);
    $res_st = mysqli_stmt_get_result($stmt_st);
    $row_st = mysqli_fetch_assoc($res_st);

    if ($row_st['status_magang'] !== 'pra-magang') {
        throw new Exception("Gagal! Status Anda sudah Magang Aktif/Selesai. Tidak bisa mengajukan mitra.");
    }

    // 2. Cek Apakah Sudah Punya Mitra DISETUJUI
    $q_app = "SELECT id_pengajuan FROM pengajuan_mitra 
              WHERE id_mahasiswa = ? AND status_pengajuan = 'diterima'";
    $stmt_app = mysqli_prepare($conn, $q_app);
    mysqli_stmt_bind_param($stmt_app, 'i', $id_mahasiswa);
    mysqli_stmt_execute($stmt_app);
    $res_app = mysqli_stmt_get_result($stmt_app);

    if (mysqli_num_rows($res_app) > 0) {
        throw new Exception("Anda sudah memiliki mitra yang disetujui. Tidak bisa mengajukan lagi.");
    }
    // -------------------------------------------

    // Sanitasi input
    $nama = mysqli_real_escape_string($conn, trim($data['nama']));
    $alamat = mysqli_real_escape_string($conn, trim($data['alamat']));
    $bidang = mysqli_real_escape_string($conn, trim($data['bidang']));
    $kontak = mysqli_real_escape_string($conn, trim($data['kontak']));

    // Cek apakah mitra sudah ada di tabel mitra_perusahaan
    $checkQuery = "SELECT id_mitra FROM mitra_perusahaan WHERE nama_mitra = ?";
    $stmt_check = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt_check, 's', $nama);
    mysqli_stmt_execute($stmt_check);
    $checkResult = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($checkResult) > 0) {
        throw new Exception("Mitra dengan nama tersebut sudah terdaftar di sistem!");
    }

    // Cek apakah mahasiswa ini sudah pernah mengajukan mitra yang sama dan masih pending
    $checkPengajuan = "SELECT id_pengajuan FROM pengajuan_mitra 
                       WHERE id_mahasiswa = ? 
                       AND nama_perusahaan = ? 
                       AND status_pengajuan = 'menunggu'";
    $stmt_check2 = mysqli_prepare($conn, $checkPengajuan);
    mysqli_stmt_bind_param($stmt_check2, 'is', $id_mahasiswa, $nama);
    mysqli_stmt_execute($stmt_check2);
    $checkResult2 = mysqli_stmt_get_result($stmt_check2);

    if (mysqli_num_rows($checkResult2) > 0) {
        throw new Exception("Anda sudah mengajukan mitra ini sebelumnya. Mohon tunggu persetujuan dari Koordinator Bidang Magang.");
    }

    // Insert ke tabel pengajuan_mitra
    $query = "INSERT INTO pengajuan_mitra 
              (id_mahasiswa, nama_perusahaan, bidang, alamat, kontak, tanggal_pengajuan, status_pengajuan) 
              VALUES (?, ?, ?, ?, ?, NOW(), 'menunggu')";
    
    $stmt_insert = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt_insert, 'issss', $id_mahasiswa, $nama, $bidang, $alamat, $kontak);
    
    if (!mysqli_stmt_execute($stmt_insert)) {
        throw new Exception(mysqli_error($conn));
    }

    // Get ID pengajuan yang baru dibuat
    $id_pengajuan = mysqli_insert_id($conn);

    // Kirim notifikasi ke semua Korbid
    $query_korbid = "SELECT id, nama FROM users WHERE role = 'Koordinator Bidang Magang'";
    $result_korbid = mysqli_query($conn, $query_korbid);

    while ($korbid = mysqli_fetch_assoc($result_korbid)) {
        $pesan = "Pengajuan mitra baru '$nama' dari mahasiswa memerlukan persetujuan Anda.";
        $query_notif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal)
                        VALUES (?, ?, 'baru', NOW())";
        $stmt_notif = mysqli_prepare($conn, $query_notif);
        mysqli_stmt_bind_param($stmt_notif, 'is', $korbid['id'], $pesan);
        mysqli_stmt_execute($stmt_notif);
    }

    // Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Pengajuan mitra baru berhasil dikirim! Menunggu persetujuan Koordinator Bidang Magang.',
        'id_pengajuan' => $id_pengajuan,
        'status' => 'menunggu',
        'data' => [
            'nama' => $nama,
            'alamat' => $alamat,
            'bidang' => $bidang,
            'kontak' => $kontak
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>