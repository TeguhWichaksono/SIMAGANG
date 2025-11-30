<?php

/**
 * File: proses_persetujuan.php
 * Role: Koordinator Bidang Magang
 * Fungsi: Memproses persetujuan atau penolakan pengajuan magang
 * Lokasi: ROLE KORBID/pages/proses_persetujuan.php
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

// PROSES SETUJUI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {

    $id_pengajuan = intval($_POST['id_pengajuan']);
    $catatan = trim($_POST['catatan'] ?? '');

    // Handle file upload surat pelaksanaan (opsional)
    $surat_pelaksanaan = null;
    if (isset($_FILES['surat_pelaksanaan']) && $_FILES['surat_pelaksanaan']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['surat_pelaksanaan'];

        // Validasi file
        $allowed_ext = ['pdf'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_ext, $allowed_ext)) {
            $_SESSION['error'] = "File harus berformat PDF";
            header("Location: ../index.php?page=persetujuan_magang");
            exit;
        }

        if ($file['size'] > $max_size) {
            $_SESSION['error'] = "Ukuran file maksimal 5MB";
            header("Location: ../index.php?page=persetujuan_magang");
            exit;
        }

        // Upload file
        $upload_dir = "../../uploads/surat_pelaksanaan/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $timestamp = time();
        $filename = "SURAT_PELAKSANAAN_" . $id_pengajuan . "_" . $timestamp . ".pdf";
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $surat_pelaksanaan = $filename;
        }
    }

    mysqli_begin_transaction($conn);

    try {
        // Update status pengajuan
        $query_update = "UPDATE pengajuan_magang 
                        SET status_pengajuan = 'diterima',
                            catatan_korbid = ?,
                            tanggal_diproses = NOW()
                        WHERE id_pengajuan = ?";

        $stmt = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt, 'si', $catatan, $id_pengajuan);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal mengupdate status pengajuan");
        }

        // Update status mahasiswa dalam kelompok menjadi "magang"
        $query_kelompok = "SELECT id_kelompok FROM pengajuan_magang WHERE id_pengajuan = ?";
        $stmt2 = mysqli_prepare($conn, $query_kelompok);
        mysqli_stmt_bind_param($stmt2, 'i', $id_pengajuan);
        mysqli_stmt_execute($stmt2);
        $result = mysqli_stmt_get_result($stmt2);
        $kelompok = mysqli_fetch_assoc($result);

        if ($kelompok) {
            $query_update_mhs = "UPDATE mahasiswa m
                                JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
                                SET m.status = 'magang'
                                WHERE ak.id_kelompok = ?";
            $stmt3 = mysqli_prepare($conn, $query_update_mhs);
            mysqli_stmt_bind_param($stmt3, 'i', $kelompok['id_kelompok']);
            mysqli_stmt_execute($stmt3);
        }

        // Simpan ke tabel persetujuan_korbid
        $status_acc = 'ya';
        $query_persetujuan = "INSERT INTO persetujuan_korbid 
                              (id_pengajuan, id_korbid, status_acc, catatan) 
                              VALUES (?, ?, ?, ?)";
        $stmt4 = mysqli_prepare($conn, $query_persetujuan);
        mysqli_stmt_bind_param($stmt4, 'iiss', $id_pengajuan, $id_korbid, $status_acc, $catatan);
        mysqli_stmt_execute($stmt4);

        // Kirim notifikasi ke semua anggota kelompok
        if ($kelompok) {
            $query_anggota = "SELECT m.id_user 
                             FROM anggota_kelompok ak 
                             JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa 
                             WHERE ak.id_kelompok = ?";
            $stmt5 = mysqli_prepare($conn, $query_anggota);
            mysqli_stmt_bind_param($stmt5, 'i', $kelompok['id_kelompok']);
            mysqli_stmt_execute($stmt5);
            $result_anggota = mysqli_stmt_get_result($stmt5);

            while ($anggota = mysqli_fetch_assoc($result_anggota)) {
                $pesan = "Selamat! Pengajuan magang kelompok Anda telah disetujui. Silakan cek status pengajuan untuk detail lebih lanjut.";
                $query_notif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal) 
                               VALUES (?, ?, 'baru', NOW())";
                $stmt6 = mysqli_prepare($conn, $query_notif);
                mysqli_stmt_bind_param($stmt6, 'is', $anggota['id_user'], $pesan);
                mysqli_stmt_execute($stmt6);
            }
        }

        // Simpan history
        $query_history = "INSERT INTO history_pengajuan 
                         (id_pengajuan, status_lama, status_baru, catatan, diubah_oleh) 
                         VALUES (?, 'menunggu', 'diterima', ?, ?)";
        $stmt7 = mysqli_prepare($conn, $query_history);
        mysqli_stmt_bind_param($stmt7, 'isi', $id_pengajuan, $catatan, $id_korbid);
        mysqli_stmt_execute($stmt7);

        mysqli_commit($conn);

        $_SESSION['success'] = "Pengajuan berhasil disetujui!";
        header("Location: ../index.php?page=persetujuan_magang_korbid");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);

        // Hapus file jika ada error
        if ($surat_pelaksanaan && file_exists($filepath)) {
            unlink($filepath);
        }

        $_SESSION['error'] = $e->getMessage();
        header("Location: ../index.php?page=persetujuan_magang");
        exit;
    }
}

// PROSES TOLAK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject') {

    $id_pengajuan = intval($_POST['id_pengajuan']);
    $alasan = trim($_POST['alasan']);

    if (empty($alasan)) {
        $_SESSION['error'] = "Alasan penolakan harus diisi";
        header("Location: ../index.php?page=persetujuan_magang");
        exit;
    }

    mysqli_begin_transaction($conn);

    try {
        // Update status pengajuan
        $query_update = "UPDATE pengajuan_magang 
                        SET status_pengajuan = 'ditolak',
                            catatan_korbid = ?,
                            tanggal_diproses = NOW()
                        WHERE id_pengajuan = ?";

        $stmt = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt, 'si', $alasan, $id_pengajuan);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal mengupdate status pengajuan");
        }

        // Simpan ke tabel persetujuan_korbid
        $status_acc = 'tidak';
        $query_persetujuan = "INSERT INTO persetujuan_korbid 
                              (id_pengajuan, id_korbid, status_acc, catatan) 
                              VALUES (?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $query_persetujuan);
        mysqli_stmt_bind_param($stmt2, 'iiss', $id_pengajuan, $id_korbid, $status_acc, $alasan);
        mysqli_stmt_execute($stmt2);

        // Kirim notifikasi ke semua anggota kelompok
        $query_kelompok = "SELECT id_kelompok FROM pengajuan_magang WHERE id_pengajuan = ?";
        $stmt3 = mysqli_prepare($conn, $query_kelompok);
        mysqli_stmt_bind_param($stmt3, 'i', $id_pengajuan);
        mysqli_stmt_execute($stmt3);
        $result = mysqli_stmt_get_result($stmt3);
        $kelompok = mysqli_fetch_assoc($result);

        if ($kelompok) {
            $query_anggota = "SELECT m.id_user 
                             FROM anggota_kelompok ak 
                             JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa 
                             WHERE ak.id_kelompok = ?";
            $stmt4 = mysqli_prepare($conn, $query_anggota);
            mysqli_stmt_bind_param($stmt4, 'i', $kelompok['id_kelompok']);
            mysqli_stmt_execute($stmt4);
            $result_anggota = mysqli_stmt_get_result($stmt4);

            while ($anggota = mysqli_fetch_assoc($result_anggota)) {
                $pesan = "Pengajuan magang kelompok Anda ditolak. Silakan cek status pengajuan untuk detail alasan penolakan.";
                $query_notif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal) 
                               VALUES (?, ?, 'baru', NOW())";
                $stmt5 = mysqli_prepare($conn, $query_notif);
                mysqli_stmt_bind_param($stmt5, 'is', $anggota['id_user'], $pesan);
                mysqli_stmt_execute($stmt5);
            }
        }

        // Simpan history
        $query_history = "INSERT INTO history_pengajuan 
                         (id_pengajuan, status_lama, status_baru, catatan, diubah_oleh) 
                         VALUES (?, 'menunggu', 'ditolak', ?, ?)";
        $stmt6 = mysqli_prepare($conn, $query_history);
        mysqli_stmt_bind_param($stmt6, 'isi', $id_pengajuan, $alasan, $id_korbid);
        mysqli_stmt_execute($stmt6);

        mysqli_commit($conn);

        $_SESSION['success'] = "Pengajuan berhasil ditolak";
        header("Location: ../index.php?page=persetujuan_magang_korbid");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../index.php?page=persetujuan_magang_korbid");
        exit;
    }
}

// Jika request tidak valid
$_SESSION['error'] = "Request tidak valid";
header("Location: ../index.php?page=persetujuan_magang_korbid");
exit;
