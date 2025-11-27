<?php
session_start();
include '../Koneksi/koneksi.php';

header('Content-Type: application/json');

// Pastikan user login
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid']);
    exit;
}

// Pastikan request POST dan input tersedia
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['new_password'])) {
    echo json_encode(['success' => false, 'message' => 'Request tidak valid']);
    exit;
}

$id = $_SESSION['id'];
$new_password = trim($_POST['new_password']);

// Validasi panjang password
if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Kata sandi minimal 6 karakter']);
    exit;
}

// Hash password baru
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Query update password
$query = "UPDATE users SET password = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan database (prepare gagal)']);
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $hashed_password, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Kata sandi berhasil diubah']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengubah kata sandi']);
}

mysqli_stmt_close($stmt);
?>
