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
// ACTION: TAMBAH ANGGOTA
// ============================================
if ($action == 'tambah_anggota') {
    $id_kelompok = $_POST['id_kelompok'] ?? null;
    $nim = trim($_POST['nim'] ?? '');
    
    // Validasi input
    if (empty($id_kelompok) || empty($nim)) {
        $_SESSION['error'] = "Data tidak lengkap!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Cek apakah user yang login adalah ketua kelompok ini
    $id_user_login = $_SESSION['id'];
    $stmt_cek_ketua = $conn->prepare("
        SELECT ak.peran 
        FROM anggota_kelompok ak
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
        WHERE ak.id_kelompok = ? AND m.id_user = ?
    ");
    $stmt_cek_ketua->bind_param("ii", $id_kelompok, $id_user_login);
    $stmt_cek_ketua->execute();
    $result_ketua = $stmt_cek_ketua->get_result();
    
    if ($result_ketua->num_rows == 0) {
        $_SESSION['error'] = "Anda tidak memiliki akses ke kelompok ini!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    $data_ketua = $result_ketua->fetch_assoc();
    if ($data_ketua['peran'] != 'ketua') {
        $_SESSION['error'] = "Hanya Ketua yang dapat menambahkan anggota!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Cari mahasiswa berdasarkan NIM di tabel users
    $stmt_mhs = $conn->prepare("
        SELECT m.id_mahasiswa, u.nama 
        FROM mahasiswa m 
        JOIN users u ON m.id_user = u.id 
        WHERE u.nim = ?
    ");
    $stmt_mhs->bind_param("s", $nim);
    $stmt_mhs->execute();
    $result_mhs = $stmt_mhs->get_result();
    
    if ($result_mhs->num_rows == 0) {
        $_SESSION['error'] = "Mahasiswa dengan NIM '$nim' tidak ditemukan!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    $data_mhs = $result_mhs->fetch_assoc();
    $id_mahasiswa_target = $data_mhs['id_mahasiswa'];
    $nama_target = $data_mhs['nama'];
    
    // Cek apakah mahasiswa ini sudah terdaftar di kelompok manapun
    $stmt_cek_kelompok = $conn->prepare("
        SELECT k.nama_kelompok 
        FROM anggota_kelompok ak 
        JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
        WHERE ak.id_mahasiswa = ?
    ");
    $stmt_cek_kelompok->bind_param("i", $id_mahasiswa_target);
    $stmt_cek_kelompok->execute();
    $result_cek = $stmt_cek_kelompok->get_result();
    
    if ($result_cek->num_rows > 0) {
        $data_cek = $result_cek->fetch_assoc();
        $_SESSION['error'] = "Mahasiswa '$nama_target' sudah terdaftar di kelompok '{$data_cek['nama_kelompok']}'!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Insert anggota baru dengan peran 'anggota'
    $stmt_insert = $conn->prepare("INSERT INTO anggota_kelompok (id_kelompok, id_mahasiswa, peran) VALUES (?, ?, 'anggota')");
    $stmt_insert->bind_param("ii", $id_kelompok, $id_mahasiswa_target);
    
    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Anggota '$nama_target' berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan anggota: " . $conn->error;
    }
    
    $_SESSION['active_tab'] = 'anggota';
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// ============================================
// ACTION: EDIT PERAN ANGGOTA
// ============================================
elseif ($action == 'edit_peran') {
    $id_anggota = $_POST['id_anggota'] ?? null;
    $id_kelompok = $_POST['id_kelompok'] ?? null;
    $peran_baru = $_POST['peran'] ?? '';
    $id_mahasiswa_target = $_POST['id_mahasiswa_target'] ?? null;
    $id_mahasiswa_ketua = $_POST['id_mahasiswa_ketua'] ?? null;
    
    // Validasi input
    if (empty($id_anggota) || empty($id_kelompok) || empty($peran_baru)) {
        $_SESSION['error'] = "Data tidak lengkap!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Validasi: pastikan user yang login adalah ketua
    $id_user_login = $_SESSION['id'];
    $stmt_validasi = $conn->prepare("
        SELECT ak.peran 
        FROM anggota_kelompok ak
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
        WHERE ak.id_kelompok = ? AND m.id_user = ?
    ");
    $stmt_validasi->bind_param("ii", $id_kelompok, $id_user_login);
    $stmt_validasi->execute();
    $result_validasi = $stmt_validasi->get_result();
    
    if ($result_validasi->num_rows == 0) {
        $_SESSION['error'] = "Anda tidak memiliki akses ke kelompok ini!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    $data_validasi = $result_validasi->fetch_assoc();
    if ($data_validasi['peran'] != 'ketua') {
        $_SESSION['error'] = "Hanya Ketua yang dapat mengubah peran anggota!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Jika mengubah anggota menjadi KETUA
    if ($peran_baru == 'ketua') {
        
        // Step 1: Ubah ketua lama menjadi anggota
        $stmt_demote = $conn->prepare("UPDATE anggota_kelompok SET peran = 'anggota' WHERE id_kelompok = ? AND id_mahasiswa = ?");
        $stmt_demote->bind_param("ii", $id_kelompok, $id_mahasiswa_ketua);
        $stmt_demote->execute();
        
        // Step 2: Ubah anggota target menjadi ketua
        $stmt_promote = $conn->prepare("UPDATE anggota_kelompok SET peran = 'ketua' WHERE id_anggota = ?");
        $stmt_promote->bind_param("i", $id_anggota);
        
        if ($stmt_promote->execute()) {
            $_SESSION['success'] = "Ketua berhasil dipindahkan! Anda sekarang menjadi Anggota.";
        } else {
            $_SESSION['error'] = "Gagal mengubah peran: " . $conn->error;
        }
    } 
    // Jika mengubah ketua menjadi ANGGOTA (tidak diperbolehkan, ketua tidak bisa turun tahta langsung)
    else {
        $_SESSION['error'] = "Tidak dapat mengubah Ketua menjadi Anggota secara langsung! Angkat anggota lain menjadi Ketua terlebih dahulu.";
    }
    
    $_SESSION['active_tab'] = 'anggota';
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// ============================================
// ACTION: HAPUS ANGGOTA
// ============================================
elseif ($action == 'hapus_anggota') {
    $id_anggota = $_POST['id_anggota'] ?? null;
    $id_kelompok = $_POST['id_kelompok'] ?? null;
    
    // Validasi input
    if (empty($id_anggota) || empty($id_kelompok)) {
        $_SESSION['error'] = "Data tidak lengkap!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Validasi: pastikan user yang login adalah ketua
    $id_user_login = $_SESSION['id'];
    $stmt_validasi = $conn->prepare("
        SELECT ak.peran 
        FROM anggota_kelompok ak
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
        WHERE ak.id_kelompok = ? AND m.id_user = ?
    ");
    $stmt_validasi->bind_param("ii", $id_kelompok, $id_user_login);
    $stmt_validasi->execute();
    $result_validasi = $stmt_validasi->get_result();
    
    if ($result_validasi->num_rows == 0) {
        $_SESSION['error'] = "Anda tidak memiliki akses ke kelompok ini!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    $data_validasi = $result_validasi->fetch_assoc();
    if ($data_validasi['peran'] != 'ketua') {
        $_SESSION['error'] = "Hanya Ketua yang dapat menghapus anggota!";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Cek apakah yang dihapus adalah diri sendiri (ketua)
    $stmt_cek = $conn->prepare("
        SELECT ak.id_mahasiswa, m.id_user 
        FROM anggota_kelompok ak 
        JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa 
        WHERE ak.id_anggota = ?
    ");
    $stmt_cek->bind_param("i", $id_anggota);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    $data_cek = $result_cek->fetch_assoc();
    
    if ($data_cek['id_user'] == $id_user_login) {
        $_SESSION['error'] = "Tidak dapat menghapus diri sendiri! Gunakan tombol 'Bubarkan Kelompok' jika ingin keluar.";
        $_SESSION['active_tab'] = 'anggota';
        header("Location: ../../index.php?page=kelompok");
        exit;
    }
    
    // Hapus anggota
    $stmt_delete = $conn->prepare("DELETE FROM anggota_kelompok WHERE id_anggota = ?");
    $stmt_delete->bind_param("i", $id_anggota);
    
    if ($stmt_delete->execute()) {
        $_SESSION['success'] = "Anggota berhasil dihapus dari kelompok!";
    } else {
        $_SESSION['error'] = "Gagal menghapus anggota: " . $conn->error;
    }
    
    $_SESSION['active_tab'] = 'anggota';
    header("Location: ../../index.php?page=kelompok");
    exit;
}

// ============================================
// INVALID ACTION
// ============================================
else {
    $_SESSION['error'] = "Aksi tidak valid!";
    $_SESSION['active_tab'] = 'anggota';
    header("Location: ../../index.php?page=kelompok");
    exit;
}
?>