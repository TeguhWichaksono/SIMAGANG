<?php
session_start();
include '../Koneksi/koneksi.php'; 

if (!isset($_SESSION['id'])) {
    $_SESSION['upload_message'] = "Error: Sesi pengguna tidak ditemukan.";
    header("Location: Akun.php");
    exit;
}

$user_id = $_SESSION['id'];

if (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] !== 0) {
    $_SESSION['upload_message'] = "Error: Tidak ada file yang dipilih.";
    header("Location: Akun.php");
    exit;
}

$file = $_FILES['foto_profil'];
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Validasi Format
if (!in_array($ext, $allowed)) {
    $_SESSION['upload_message'] = "Error: Hanya format JPG, JPEG, PNG, GIF yang diperbolehkan.";
    header("Location: Akun.php");
    exit;
}

// Validasi Ukuran (Max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    $_SESSION['upload_message'] = "Error: Ukuran foto maksimal 2MB.";
    header("Location: Akun.php");
    exit;
}

// Folder Upload (Pastikan folder ini ada di direktori ROLE Dosen/Koordinator)
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Rename File agar unik
$new_filename = "profil_" . $user_id . "_" . time() . "." . $ext;
$destination = $upload_dir . $new_filename;

// Proses Upload
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Update Database User (Karena foto profil ada di tabel users, bukan dosen)
    $stmt = mysqli_prepare($conn, "UPDATE users SET foto_profil = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['upload_message'] = "Foto profil berhasil diperbarui.";
    } else {
        unlink($destination); // Hapus file jika DB gagal update
        $_SESSION['upload_message'] = "Error: Gagal menyimpan ke database.";
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['upload_message'] = "Error: Gagal mengupload file.";
}

header("Location: Akun.php");
exit;
?>