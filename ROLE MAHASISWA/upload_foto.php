<?php
session_start();

// --- PERBAIKAN DI SINI ---
// Gunakan ../ (naik 1 folder), bukan ../../ (naik 2 folder)
include '../Koneksi/koneksi.php'; 
// -------------------------

if (!isset($_SESSION['id'])) {
    $_SESSION['upload_message'] = "Error: Sesi pengguna tidak ditemukan.";
    header("Location: Akun.php");
    exit;
}

$user_id = $_SESSION['id'];

if (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] !== 0) {
    $_SESSION['upload_message'] = "Error: Tidak ada file yang diupload atau terjadi error upload.";
    header("Location: Akun.php");
    exit;
}

$file = $_FILES['foto_profil'];
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    $_SESSION['upload_message'] = "Error: Format foto tidak diizinkan (Hanya JPG, JPEG, PNG, GIF).";
    header("Location: Akun.php");
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    $_SESSION['upload_message'] = "Error: Ukuran foto maksimal 2MB.";
    header("Location: Akun.php");
    exit;
}

$upload_dir = "uploads/";

// Pastikan folder uploads ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Buat nama file unik agar tidak bentrok
$new_filename = "foto_" . $user_id . "_" . time() . "." . $ext;

// Path tujuan penyimpanan
$destination = $upload_dir . $new_filename;

// Pindahkan file dari temp ke folder uploads
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['upload_message'] = "Error: Gagal memindahkan file ke folder tujuan.";
    header("Location: Akun.php");
    exit;
}

// Update nama file di database
$stmt = mysqli_prepare($conn, "UPDATE users SET foto_profil = ? WHERE id = ?");

// Cek jika koneksi berhasil (untuk safety tambahan)
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['upload_message'] = "Foto profil berhasil diperbarui.";
    } else {
        $_SESSION['upload_message'] = "Error: Gagal menyimpan data ke database.";
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['upload_message'] = "Error Database: " . mysqli_error($conn);
}

header("Location: Akun.php");
exit;
?>