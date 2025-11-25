<?php
session_start();
require '../../../Koneksi/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "simpan_profil") {

    $nama_kelompok = trim($_POST['nama_kelompok']);
    $id_mahasiswa = $_SESSION['id_user'];

    if ($nama_kelompok == '') {
        header("Location: ../kelompok.php?error=Nama tidak boleh kosong");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO tb_kelompok (nama_kelompok, ketua_id) VALUES (?,?)");
        $stmt->execute([$nama_kelompok, $id_mahasiswa]);

        $id_kelompok = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO tb_anggota_kelompok(id_kelompok, id_mahasiswa, peran) VALUES (?,?, 'ketua')");
        $stmt->execute([$id_kelompok, $id_mahasiswa]);

        $pdo->commit();

        header("Location: ../kelompok.php?sukses=kelompok_dibuat");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: ../kelompok.php?error=Gagal membuat kelompok");
        exit;
    }
}

if ($action == "update_nama") {

    $id_kelompok = $_POST['id_kelompok'];
    $nama_kelompok = trim($_POST['nama_kelompok']);

    $stmt = $pdo->prepare("UPDATE tb_kelompok SET nama_kelompok=? WHERE id_kelompok=?");
    $stmt->execute([$nama_kelompok, $id_kelompok]);

    header("Location: ../kelompok.php?sukses=nama_diubah");
    exit;
}

header("Location: ../kelompok.php?error=aksi_tidak_dikenal");
exit;
