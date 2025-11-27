<?php
include '../../../Koneksi/koneksi.php';

$id = $_GET['id'];

$query = "DELETE FROM users WHERE id=$id";
$result = mysqli_query($conn, $query);
if (!$result) {
    echo "SQL ERROR: " . mysqli_error($conn);
    exit;
}

header("Location: ../../index.php?page=manajemen_User");
exit;
?>