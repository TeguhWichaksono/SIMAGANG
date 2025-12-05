<?php
include "../../../Koneksi/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    // Hash password
    $password_hash = password_hash($pass, PASSWORD_DEFAULT);

    // Role mahasiswa
    $role = 'mahasiswa';

    // Cek apakah NIM sudah ada
    $checkQuery = mysqli_query($conn, "SELECT id FROM users WHERE nim = '$nim'");
    
    if (mysqli_num_rows($checkQuery) > 0) {
        // NIM sudah ada
        header("Location: ../../index.php?page=manajemen_User&error=duplicate_nim");
        exit();
    }

    // Query insert
    $query = mysqli_query($conn, "INSERT INTO users (nama, nim, email, password, role)
                                 VALUES ('$nama', '$nim', '$email', '$password_hash', '$role')");

    if ($query) {
        header("Location: ../../index.php?page=manajemen_User&success=1&tab=mahasiswa");
        exit();
    } else {
        header("Location: ../../index.php?page=manajemen_User&error=1&tab=mahasiswa");
        exit();
    }
} else {
    header("Location: ../../index.php?page=manajemen_User&tab=mahasiswa");
    exit();
}
?>