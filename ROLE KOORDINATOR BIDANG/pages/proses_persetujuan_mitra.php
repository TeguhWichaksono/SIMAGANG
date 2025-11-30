<?php
/**
 * File: proses_persetujuan_mitra.php
 * Role: Koordinator Bidang Magang
 * Fungsi: Memproses persetujuan atau penolakan pengajuan mitra baru
 * Lokasi: ROLE KORBID/pages/proses_persetujuan_mitra.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../Koneksi/koneksi.php';

// Cek login dan role
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Koordinator Bidang Magang') {
    header("Location: ../../Login/login.php");
    exit;
}

$id_korbid = $_SESSION['id'];

// PROSES SETUJUI MITRA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {

    $id_pengajuan = intval($_POST['id_pengajuan']);
    $catatan = trim($_POST['catatan'] ?? '');

    mysqli_begin_transaction($conn);

    try {
        // Ambil data pengajuan mitra
        $query_get = "SELECT * FROM pengajuan_mitra WHERE id_pengajuan = ?";
        $stmt = mysqli_prepare($conn, $query_get);
        mysqli_stmt_bind_param($stmt, 'i', $id_pengajuan);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $pengajuan = mysqli_fetch_assoc($result);

        if (!$pengajuan) {
            throw new Exception("Data pengajuan tidak ditemukan");
        }

        // Insert ke tabel mitra_perusahaan
        $query_insert_mitra = "INSERT INTO mitra_perusahaan 
                              (nama_mitra, alamat, bidang, kontak, status) 
                              VALUES (?, ?, ?, ?, 'aktif')";
        
        $stmt2 = mysqli_prepare($conn, $query_insert_mitra);
        mysqli_stmt_bind_param($stmt2, 'ssss', 
            $pengajuan['nama_perusahaan'],
            $pengajuan['alamat'],
            $pengajuan['bidang'],
            $pengajuan['kontak']
        );

        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Gagal menambahkan mitra ke database");
        }

        $id_mitra_baru = mysqli_insert_id($conn);

        // Update status pengajuan mitra
        $query_update = "UPDATE pengajuan_mitra 
                        SET status_pengajuan = 'disetujui',
                            id_mitra = ?,
                            catatan = ?,
                            tanggal_diproses = NOW()
                        WHERE id_pengajuan = ?";

        $stmt3 = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt3, 'isi', $id_mitra_baru, $catatan, $id_pengajuan);

        if (!mysqli_stmt_execute($stmt3)) {
            throw new Exception("Gagal mengupdate status pengajuan");
        }

        // Kirim notifikasi ke mahasiswa pengaju
        if ($pengajuan['id_mahasiswa']) {
            $query_user = "SELECT id_user FROM mahasiswa WHERE id_mahasiswa = ?";
            $stmt4 = mysqli_prepare($conn, $query_user);
            mysqli_stmt_bind_param($stmt4, 'i', $pengajuan['id_mahasiswa']);
            mysqli_stmt_execute($stmt4);
            $result_user = mysqli_stmt_get_result($stmt4);
            $user = mysqli_fetch_assoc($result_user);

            if ($user) {
                $pesan = "Selamat! Pengajuan mitra '{$pengajuan['nama_perusahaan']}' telah disetujui dan ditambahkan ke database. Mitra ini sekarang dapat dipilih untuk magang.";
                $query_notif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal) 
                               VALUES (?, ?, 'baru', NOW())";
                $stmt5 = mysqli_prepare($conn, $query_notif);
                mysqli_stmt_bind_param($stmt5, 'is', $user['id_user'], $pesan);
                mysqli_stmt_execute($stmt5);
            }
        }

        // Note: Notifikasi kelompok akan ditambahkan jika struktur tabel mendukung

        // Log activity
        $query_log = "INSERT INTO log_aktivitas 
                     (id_user, aktivitas, waktu) 
                     VALUES (?, ?, NOW())";
        $aktivitas_log = "Menyetujui pengajuan mitra: {$pengajuan['nama_perusahaan']}";
        $stmt8 = mysqli_prepare($conn, $query_log);
        mysqli_stmt_bind_param($stmt8, 'is', $id_korbid, $aktivitas_log);
        mysqli_stmt_execute($stmt8);

        mysqli_commit($conn);

        $_SESSION['success'] = "Mitra berhasil disetujui dan ditambahkan ke database!";
        header("Location: ../index.php?page=persetujuan_mitra_korbid");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../index.php?page=persetujuan_mitra_korbid");
        exit;
    }
}

// PROSES TOLAK MITRA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject') {

    $id_pengajuan = intval($_POST['id_pengajuan']);
    $alasan = trim($_POST['alasan']);

    if (empty($alasan)) {
        $_SESSION['error'] = "Alasan penolakan harus diisi";
        header("Location: ../index.php?page=persetujuan_mitra_korbid");
        exit;
    }

    mysqli_begin_transaction($conn);

    try {
        // Ambil data pengajuan
        $query_get = "SELECT * FROM pengajuan_mitra WHERE id_pengajuan = ?";
        $stmt = mysqli_prepare($conn, $query_get);
        mysqli_stmt_bind_param($stmt, 'i', $id_pengajuan);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $pengajuan = mysqli_fetch_assoc($result);

        if (!$pengajuan) {
            throw new Exception("Data pengajuan tidak ditemukan");
        }

        // Update status pengajuan
        $query_update = "UPDATE pengajuan_mitra 
                        SET status_pengajuan = 'ditolak',
                            catatan = ?,
                            tanggal_diproses = NOW()
                        WHERE id_pengajuan = ?";

        $stmt2 = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt2, 'si', $alasan, $id_pengajuan);

        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Gagal mengupdate status pengajuan");
        }

        // Kirim notifikasi ke mahasiswa pengaju
        if ($pengajuan['id_mahasiswa']) {
            $query_user = "SELECT id_user FROM mahasiswa WHERE id_mahasiswa = ?";
            $stmt3 = mysqli_prepare($conn, $query_user);
            mysqli_stmt_bind_param($stmt3, 'i', $pengajuan['id_mahasiswa']);
            mysqli_stmt_execute($stmt3);
            $result_user = mysqli_stmt_get_result($stmt3);
            $user = mysqli_fetch_assoc($result_user);

            if ($user) {
                $pesan = "Pengajuan mitra '{$pengajuan['nama_perusahaan']}' ditolak. Alasan: {$alasan}";
                $query_notif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal) 
                               VALUES (?, ?, 'baru', NOW())";
                $stmt4 = mysqli_prepare($conn, $query_notif);
                mysqli_stmt_bind_param($stmt4, 'is', $user['id_user'], $pesan);
                mysqli_stmt_execute($stmt4);
            }
        }

        // Note: Notifikasi kelompok akan ditambahkan jika struktur tabel mendukung

        // Log activity
        $query_log = "INSERT INTO log_aktivitas 
                     (id_user, aktivitas, waktu) 
                     VALUES (?, ?, NOW())";
        $aktivitas_log = "Menolak pengajuan mitra: {$pengajuan['nama_perusahaan']} - Alasan: {$alasan}";
        $stmt7 = mysqli_prepare($conn, $query_log);
        mysqli_stmt_bind_param($stmt7, 'is', $id_korbid, $aktivitas_log);
        mysqli_stmt_execute($stmt7);

        mysqli_commit($conn);

        $_SESSION['success'] = "Pengajuan mitra berhasil ditolak";
        header("Location: ../index.php?page=persetujuan_mitra_korbid");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../index.php?page=persetujuan_mitra_korbid");
        exit;
    }
}

// Jika request tidak valid
$_SESSION['error'] = "Request tidak valid";
header("Location: ../index.php?page=persetujuan_mitra_korbid");
exit;
?>