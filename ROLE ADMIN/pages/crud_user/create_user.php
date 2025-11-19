<?php
include '../../../Koneksi/koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$role = $_POST['role'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$query = "INSERT INTO users (nama, email, role, password) 
          VALUES ('$nama', '$email', '$role', '$password')";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "SQL ERROR: " . mysqli_error($conn);
    exit;
}

header("Location: ../../index.php?page=manajemen_User");
exit;
?>