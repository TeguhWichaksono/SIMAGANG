<?php
include "../../../Koneksi/koneksi.php";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Hapus data
    $query = mysqli_query($conn, "DELETE FROM users WHERE id = '$id' AND role <> 'mahasiswa'");
    
    if ($query) {
        header("Location: ../../index.php?page=manajemen_User&success_delete=1");
    } else {
        header("Location: ../../index.php?page=manajemen_User&error=delete_failed");
    }
} else {
    header("Location: ../../index.php?page=manajemen_User");
}
exit();
?>