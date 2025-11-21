<?php
include '../../../Koneksi/koneksi.php';

$id = $_POST['id'];
$nama = $_POST['nama'];
$email = $_POST['email'];
$role = $_POST['role'];

$query = "UPDATE users SET nama='$nama',email='$email', role='$role' WHERE id=$id";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "SQL ERROR: " . mysqli_error($conn);
    exit;
}

header("Location: ../../index.php?page=manajemen_User");
exit;
?>