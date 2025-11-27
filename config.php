<?php
function cekRole(string $role){
    if($_SESSION['role'] !== $role){
        switch($_SESSION['role']){
            case 'Mahasiswa':
                $content = '/ROLE MAHASISWA/index.php';
                break;
            case 'Koordinator Bidang Magang':
                $content = '/ROLE KOORDINATOR BIDANG/index.php';
                break;
            case 'Dosen Pembimbing':
                $content = '/ROLE DOSEN PEMBIMBING/index.php';
                break;
            case 'Admin':
                $content = '/ROLE ADMIN/index.php';
                break;
        }
        echo "<script>
                alert('Akses Ditolak! Anda tidak memiliki izin ke halaman ini.');
                window.location.href = '$content';
              </script>";
        exit;
    }
}

?>