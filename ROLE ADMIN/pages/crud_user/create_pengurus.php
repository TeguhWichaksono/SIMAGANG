<?php
include "../../../Koneksi/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // Hash password
    $password_hash = password_hash($pass, PASSWORD_DEFAULT);

    // Query insert
    $query = mysqli_query($conn, "INSERT INTO users (nama, email, password, role)
                                 VALUES ('$nama', '$email', '$password_hash', '$role')");

    if ($query) {
        // Redirect ke halaman manajemen_User.php dengan parameter success
        header("Location: ../../index.php?page=manajemen_User&success=1");
        exit();
    } else {
        // Redirect dengan parameter error
        header("Location: ../../index.php?page=manajemen_User&error=1");
        exit();
    }
} else {
    // Jika bukan POST request, redirect ke halaman manajemen
    header("Location: ../../index.php?page=manajemen_User");
    exit();
}
?>