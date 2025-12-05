<?php
session_start();
// Sesuaikan path koneksi dengan struktur folder
<<<<<<< HEAD
include '../Koneksi/koneksi.php';
=======
include '../../Koneksi/koneksi.php';
>>>>>>> origin/arilmun

if (!isset($_SESSION['id'])) {
    $_SESSION['upload_message'] = "Error: Sesi pengguna tidak ditemukan.";
    header("Location: Akun.php");
    exit;
}

$user_id = $_SESSION['id'];

if (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] !== 0) {
    $_SESSION['upload_message'] = "Error: Tidak ada file yang diupload.";
    header("Location: Akun.php");
    exit;
}

$file = $_FILES['foto_profil'];
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    $_SESSION['upload_message'] = "Error: Format foto tidak diizinkan.";
    header("Location: Akun.php");
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    $_SESSION['upload_message'] = "Error: Ukuran foto maksimal 2MB.";
    header("Location: Akun.php");
    exit;
}

$upload_dir = "uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Buat nama file unik
$new_filename = "foto_" . $user_id . "_" . time() . "." . $ext;

// Path tujuan
$destination = $upload_dir . $new_filename;

// Pindahkan file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['upload_message'] = "Error: Gagal mengupload foto.";
    header("Location: Akun.php");
    exit;
}

// Update database
$stmt = mysqli_prepare($conn, "UPDATE users SET foto_profil = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['upload_message'] = ".";
} else {
    $_SESSION['upload_message'] = "Error: Gagal menyimpan ke database.";
}

mysqli_stmt_close($stmt);

header("Location: Akun.php");
exit;
?>