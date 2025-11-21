<?php
session_start();
include '../../../Koneksi/koneksi.php';

// Validasi akses
if (!isset($_SESSION['id'])) { exit; }

$action = $_POST['action'] ?? '';

// --- 1. TAMBAH ANGGOTA (BY NIM) ---
if ($action == 'tambah_anggota') {
    $id_kelompok = $_POST['id_kelompok'];
    $nim_target = trim($_POST['nim_anggota']);

    // Cari mahasiswa berdasarkan NIM
    $stmt = $conn->prepare("
        SELECT m.id_mahasiswa, u.nama 
        FROM mahasiswa m 
        JOIN users u ON m.id_user = u.id 
        WHERE u.nim = ?
    ");
    $stmt->bind_param("s", $nim_target);
    $stmt->execute();
    $result = $stmt->get_result();
    $target = $result->fetch_assoc();

    if (!$target) {
        $_SESSION['error'] = "NIM $nim_target tidak ditemukan dalam sistem.";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }

    // Cek apakah mahasiswa itu sudah punya kelompok
    $stmt_cek = $conn->prepare("SELECT id_anggota FROM anggota_kelompok WHERE id_mahasiswa = ?");
    $stmt_cek->bind_param("i", $target['id_mahasiswa']);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Mahasiswa tersebut sudah tergabung dalam kelompok lain.";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }

    // Insert sebagai anggota
    $stmt_ins = $conn->prepare("INSERT INTO anggota_kelompok (id_kelompok, id_mahasiswa, peran) VALUES (?, ?, 'anggota')");
    $stmt_ins->bind_param("ii", $id_kelompok, $target['id_mahasiswa']);
    
    if ($stmt_ins->execute()) {
        $_SESSION['success'] = "Berhasil menambahkan " . $target['nama'];
    } else {
        $_SESSION['error'] = "Gagal menambahkan anggota.";
    }
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// --- 2. HAPUS ANGGOTA ---
if ($action == 'hapus_anggota') {
    $id_anggota = $_POST['id_anggota'];
    
    // Mencegah ketua menghapus dirinya sendiri jika dia satu-satunya ketua (opsional logic)
    // Disini kita hapus saja langsung
    $stmt = $conn->prepare("DELETE FROM anggota_kelompok WHERE id_anggota = ?");
    $stmt->bind_param("i", $id_anggota);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Anggota berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus anggota.";
    }
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// --- 3. EDIT PERAN ---
if ($action == 'edit_peran') {
    $id_anggota = $_POST['id_anggota'];
    $peran_baru = $_POST['peran']; // 'ketua' atau 'anggota'

    // Logic: Jika set 'ketua', harus cek apakah sudah ada ketua lain?
    // Jika user ingin mengganti ketua, biasanya sistem akan mengubah ketua lama jadi anggota
    // Namun untuk simplifikasi sesuai tabel Anda:
    
    // 1. Dapatkan id_kelompok dari id_anggota ini
    $q = $conn->query("SELECT id_kelompok FROM anggota_kelompok WHERE id_anggota = $id_anggota");
    $row = $q->fetch_assoc();
    $id_kelompok = $row['id_kelompok'];

    // Jika peran baru adalah KETUA, ubah dulu semua ketua di kelompok ini menjadi anggota
    if ($peran_baru == 'ketua') {
        $conn->query("UPDATE anggota_kelompok SET peran = 'anggota' WHERE id_kelompok = $id_kelompok AND peran = 'ketua'");
    }

    // Update target
    $stmt = $conn->prepare("UPDATE anggota_kelompok SET peran = ? WHERE id_anggota = ?");
    $stmt->bind_param("si", $peran_baru, $id_anggota);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Peran anggota berhasil diubah.";
    } else {
        $_SESSION['error'] = "Gagal mengubah peran.";
    }
    header("Location: ../../index.php?page=kelompok");
    exit;
}
?>