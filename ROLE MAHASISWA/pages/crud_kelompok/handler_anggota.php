<?php
session_start();
require '../../../Koneksi/koneksi.php';

$action = $_POST['action'] ?? '';

// Fungsi redirect kembali ke halaman kelompok + set tab aktif
function goBackToAnggota() {
    $_SESSION['active_tab'] = 'anggota';
    header("Location: ../../pages/kelompok.php");
    exit;
}

/* -------------------------------
   TAMBAH ANGGOTA
--------------------------------*/
if ($action == "tambah_anggota") {

    $id_kelompok = $_POST['id_kelompok'];
    $nim = trim($_POST['nim_anggota']);

    // Ambil user berdasarkan NIM
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE nim=?");
    $stmt->execute([$nim]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "NIM tidak ditemukan.";
        goBackToAnggota();
    }

    $id_mahasiswa = $user['id_user'];

    // Cek apakah sudah punya kelompok
    $stmt = $pdo->prepare("SELECT 1 FROM tb_anggota_kelompok WHERE id_mahasiswa=?");
    $stmt->execute([$id_mahasiswa]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = "Mahasiswa ini sudah memiliki kelompok.";
        goBackToAnggota();
    }

    // Tambah anggota
    $stmt = $pdo->prepare("
        INSERT INTO tb_anggota_kelompok(id_kelompok, id_mahasiswa, peran)
        VALUES (?, ?, 'anggota')
    ");
    $stmt->execute([$id_kelompok, $id_mahasiswa]);

    $_SESSION['success'] = "Anggota berhasil ditambahkan!";
    goBackToAnggota();
}

/* -------------------------------
   HAPUS ANGGOTA
--------------------------------*/
if ($action == "hapus_anggota") {

    $id_anggota = $_POST['id_anggota'];

    $stmt = $pdo->prepare("DELETE FROM tb_anggota_kelompok WHERE id_anggota=?");
    $stmt->execute([$id_anggota]);

    $_SESSION['success'] = "Anggota berhasil dihapus.";
    goBackToAnggota();
}

/* -------------------------------
   EDIT PERAN
--------------------------------*/
if ($action == "edit_peran") {

    $id_kelompok = $_POST['id_kelompok'];
    $id_anggota = $_POST['id_anggota'];
    $peran_baru = $_POST['peran'];

    if ($peran_baru == "ketua") {
        $pdo->prepare("UPDATE tb_anggota_kelompok SET peran='anggota' WHERE id_kelompok=?")
            ->execute([$id_kelompok]);
    }

    $pdo->prepare("UPDATE tb_anggota_kelompok SET peran=? WHERE id_anggota=?")
        ->execute([$peran_baru, $id_anggota]);

    $_SESSION['success'] = "Peran berhasil diperbarui.";
    goBackToAnggota();
}

$_SESSION['error'] = "Aksi tidak dikenal.";
goBackToAnggota();
