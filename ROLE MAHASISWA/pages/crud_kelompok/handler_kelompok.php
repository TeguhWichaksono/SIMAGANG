<?php
session_start();
include '../../../Koneksi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'simpan_profil') {
    
    $nama_kelompok = trim($_POST['nama_kelompok']);
    $id_kelompok = $_POST['id_kelompok']; // Kosong jika belum punya
    $id_mahasiswa = $_POST['id_mahasiswa'];
    $tahun = date('Y'); // Default tahun sekarang

    if (empty($nama_kelompok)) {
        $_SESSION['error'] = "Nama kelompok tidak boleh kosong.";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }

    if (empty($id_kelompok)) {
        // === CASE 1: BUAT KELOMPOK BARU ===
        
        // Start Transaction biar aman
        $conn->begin_transaction();
        try {
            // 1. Insert ke tabel kelompok
            $stmt = $conn->prepare("INSERT INTO kelompok (nama_kelompok, tahun) VALUES (?, ?)");
            $stmt->bind_param("si", $nama_kelompok, $tahun);
            $stmt->execute();
            $new_id_kelompok = $conn->insert_id;

            // 2. Masukkan user pembuat sebagai KETUA
            $peran = 'ketua';
            $stmt2 = $conn->prepare("INSERT INTO anggota_kelompok (id_kelompok, id_mahasiswa, peran) VALUES (?, ?, ?)");
            $stmt2->bind_param("iis", $new_id_kelompok, $id_mahasiswa, $peran);
            $stmt2->execute();

            $conn->commit();
            $_SESSION['success'] = "Kelompok berhasil dibuat!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Gagal membuat kelompok: " . $e->getMessage();
        }

    } else {
        // === CASE 2: UPDATE NAMA KELOMPOK ===
        $stmt = $conn->prepare("UPDATE kelompok SET nama_kelompok = ? WHERE id_kelompok = ?");
        $stmt->bind_param("si", $nama_kelompok, $id_kelompok);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Nama kelompok berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui nama kelompok.";
        }
    }
    
    header("Location: ../../index.php?page=kelompok");
    exit;
}
?>