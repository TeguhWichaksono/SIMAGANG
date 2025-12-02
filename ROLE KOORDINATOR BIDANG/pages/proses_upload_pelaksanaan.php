<?php
session_start();
include '../../Koneksi/koneksi.php'; // Sesuaikan path koneksi jika perlu

// Cek hak akses Korbid
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Koordinator Bidang Magang') {
    header("Location: ../../Login/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan = intval($_POST['id_pengajuan']);
    
    // Validasi File
    if (isset($_FILES['file_pelaksanaan']) && $_FILES['file_pelaksanaan']['error'] === 0) {
        $allowed = ['pdf'];
        $filename = $_FILES['file_pelaksanaan']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if ($ext !== 'pdf') {
            $_SESSION['error'] = "File harus format PDF";
            header("Location: ../index.php?page=persetujuan_magang_korbid");
            exit;
        }

        // Ambil Data Kelompok untuk Penamaan File
        $qInfo = "SELECT k.nama_kelompok, k.id_kelompok 
                  FROM pengajuan_magang pm 
                  JOIN kelompok k ON pm.id_kelompok = k.id_kelompok 
                  WHERE pm.id_pengajuan = ?";
        $stmt = mysqli_prepare($conn, $qInfo);
        mysqli_stmt_bind_param($stmt, 'i', $id_pengajuan);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($res);

        // FORMAT NAMA FILE: SuratPelaksanaan_Kel[ID]_[NamaKel]_[Timestamp].pdf
        $clean_nama = preg_replace('/[^A-Za-z0-9]/', '', $data['nama_kelompok']);
        $timestamp = date('YmdHis');
        $new_name = "SuratPelaksanaan_Kel{$data['id_kelompok']}_{$clean_nama}_{$timestamp}.pdf";
        
        // Simpan ke folder uploads/dokumen_magang
        $target_dir = "../../uploads/dokumen_magang/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($_FILES['file_pelaksanaan']['tmp_name'], $target_file)) {
            
            // LOGIKA DATABASE: Insert atau Update
            $jenis = 'surat_pelaksanaan';
            $today = date('Y-m-d');
            
            // Cek apakah sudah ada sebelumnya
            $cek = mysqli_query($conn, "SELECT id_dokumen FROM dokumen_magang WHERE id_pengajuan=$id_pengajuan AND jenis='$jenis'");
            
            if (mysqli_num_rows($cek) > 0) {
                // Update
                $qSql = "UPDATE dokumen_magang SET file_path=?, tanggal_upload=? WHERE id_pengajuan=? AND jenis=?";
                $stmt2 = mysqli_prepare($conn, $qSql);
                mysqli_stmt_bind_param($stmt2, 'ssis', $new_name, $today, $id_pengajuan, $jenis);
            } else {
                // Insert Baru
                $qSql = "INSERT INTO dokumen_magang (id_pengajuan, jenis, file_path, tanggal_upload) VALUES (?, ?, ?, ?)";
                $stmt2 = mysqli_prepare($conn, $qSql);
                mysqli_stmt_bind_param($stmt2, 'isss', $id_pengajuan, $jenis, $new_name, $today);
            }
            mysqli_stmt_execute($stmt2);
            
            $_SESSION['success'] = "Surat Pelaksanaan berhasil diupload dan dikirim ke mahasiswa.";
        } else {
            $_SESSION['error'] = "Gagal memindahkan file upload.";
        }
    } else {
        $_SESSION['error'] = "File tidak valid atau error saat upload.";
    }
}

// Redirect kembali ke halaman korbid
header("Location: ../index.php?page=persetujuan_magang_korbid");
exit;
?>