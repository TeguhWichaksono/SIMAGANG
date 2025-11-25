<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../../Koneksi/koneksi.php';

// Pastikan user sudah login dan role Mahasiswa
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Mahasiswa') {
    header("Location: ../../Login/login.php");
    exit;
}

$action = $_POST['action'] ?? '';

// ============================================
// ACTION: SIMPAN PROFIL (CREATE / UPDATE)
// ============================================
if ($action == 'simpan_profil') {
    $id_kelompok = $_POST['id_kelompok'] ?? null;
    $id_mahasiswa = $_POST['id_mahasiswa'] ?? null;
    $nama_kelompok = trim($_POST['nama_kelompok'] ?? '');
    
    // Validasi input
    if (empty($nama_kelompok)) {
        $_SESSION['error'] = "Nama kelompok tidak boleh kosong!";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    if (empty($id_mahasiswa)) {
        $_SESSION['error'] = "Data mahasiswa tidak valid!";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // --- CASE 1: CREATE KELOMPOK BARU ---
    if (empty($id_kelompok)) {
        
        // Cek apakah mahasiswa ini sudah punya kelompok
        $stmt_cek = $conn->prepare("SELECT COUNT(*) as total FROM anggota_kelompok WHERE id_mahasiswa = ?");
        $stmt_cek->bind_param("i", $id_mahasiswa);
        $stmt_cek->execute();
        $result = $stmt_cek->get_result();
        $cek = $result->fetch_assoc();
        
        if ($cek['total'] > 0) {
            $_SESSION['error'] = "Anda sudah terdaftar di kelompok lain!";
            header("Location: ../../index.php?page=kelompok");
            exit;
        }
        
        // Insert kelompok baru
        $tahun = date('Y');
        $stmt_kelompok = $conn->prepare("INSERT INTO kelompok (nama_kelompok, tahun) VALUES (?, ?)");
        $stmt_kelompok->bind_param("si", $nama_kelompok, $tahun);
        
        if ($stmt_kelompok->execute()) {
            $new_id_kelompok = $conn->insert_id;
            
            // Insert mahasiswa sebagai ketua
            $stmt_anggota = $conn->prepare("INSERT INTO anggota_kelompok (id_kelompok, id_mahasiswa, peran) VALUES (?, ?, 'ketua')");
            $stmt_anggota->bind_param("ii", $new_id_kelompok, $id_mahasiswa);
            
            if ($stmt_anggota->execute()) {
                $_SESSION['success'] = "Kelompok berhasil dibuat! Anda adalah Ketua kelompok.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan Anda sebagai ketua: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Gagal membuat kelompok: " . $conn->error;
        }
        
    } 
    // --- CASE 2: UPDATE KELOMPOK EXISTING ---
    else {
        
        // Validasi: pastikan mahasiswa ini adalah anggota kelompok tersebut
        $stmt_validasi = $conn->prepare("SELECT peran FROM anggota_kelompok WHERE id_kelompok = ? AND id_mahasiswa = ?");
        $stmt_validasi->bind_param("ii", $id_kelompok, $id_mahasiswa);
        $stmt_validasi->execute();
        $result_validasi = $stmt_validasi->get_result();
        
        if ($result_validasi->num_rows == 0) {
            $_SESSION['error'] = "Anda tidak memiliki akses ke kelompok ini!";
            header("Location: ../../index.php?page=kelompok");
            exit;
        }
        
        $data_validasi = $result_validasi->fetch_assoc();
        $peran = $data_validasi['peran'];
        
        // Hanya ketua yang bisa update nama kelompok
        if ($peran != 'ketua') {
            $_SESSION['error'] = "Hanya Ketua yang dapat mengubah nama kelompok!";
            header("Location: ../../index.php?page=kelompok");
            exit;
        }
        
        // Update nama kelompok
        $stmt_update = $conn->prepare("UPDATE kelompok SET nama_kelompok = ? WHERE id_kelompok = ?");
        $stmt_update->bind_param("si", $nama_kelompok, $id_kelompok);
        
        if ($stmt_update->execute()) {
            $_SESSION['success'] = "Nama kelompok berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui nama kelompok: " . $conn->error;
        }
    }
    
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// ============================================
// ACTION: BUBARKAN KELOMPOK
// ============================================
elseif ($action == 'bubarkan_kelompok') {
    $id_kelompok = $_POST['id_kelompok'] ?? null;
    $id_mahasiswa = $_POST['id_mahasiswa'] ?? null;
    
    if (empty($id_kelompok) || empty($id_mahasiswa)) {
        $_SESSION['error'] = "Data tidak valid!";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Validasi 1: Pastikan mahasiswa ini adalah ketua
    $stmt_cek_ketua = $conn->prepare("SELECT peran FROM anggota_kelompok WHERE id_kelompok = ? AND id_mahasiswa = ?");
    $stmt_cek_ketua->bind_param("ii", $id_kelompok, $id_mahasiswa);
    $stmt_cek_ketua->execute();
    $result_ketua = $stmt_cek_ketua->get_result();
    
    if ($result_ketua->num_rows == 0) {
        $_SESSION['error'] = "Anda tidak memiliki akses ke kelompok ini!";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    $data_ketua = $result_ketua->fetch_assoc();
    if ($data_ketua['peran'] != 'ketua') {
        $_SESSION['error'] = "Hanya Ketua yang dapat membubarkan kelompok!";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Validasi 2: Pastikan hanya ada 1 anggota (diri sendiri)
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM anggota_kelompok WHERE id_kelompok = ?");
    $stmt_count->bind_param("i", $id_kelompok);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $count = $result_count->fetch_assoc()['total'];
    
    if ($count > 1) {
        $_SESSION['error'] = "Tidak dapat membubarkan kelompok! Masih ada anggota lain. Hapus semua anggota terlebih dahulu.";
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Hapus anggota kelompok (diri sendiri)
    $stmt_del_anggota = $conn->prepare("DELETE FROM anggota_kelompok WHERE id_kelompok = ?");
    $stmt_del_anggota->bind_param("i", $id_kelompok);
    $stmt_del_anggota->execute();
    
    // Hapus kelompok
    $stmt_del_kelompok = $conn->prepare("DELETE FROM kelompok WHERE id_kelompok = ?");
    $stmt_del_kelompok->bind_param("i", $id_kelompok);
    
    if ($stmt_del_kelompok->execute()) {
        $_SESSION['success'] = "Kelompok berhasil dibubarkan!";
    } else {
        $_SESSION['error'] = "Gagal membubarkan kelompok: " . $conn->error;
    }
    
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// ============================================
// INVALID ACTION
// ============================================
else {
    $_SESSION['error'] = "Aksi tidak valid!";
    header("Location: ../../index.php?page=kelompok");
    exit;
}
?>