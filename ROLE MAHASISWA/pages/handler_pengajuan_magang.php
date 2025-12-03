<?php
/**
 * File: handler_pengajuan_magang.php
 * Fungsi: Menangani pengajuan magang lengkap dari mahasiswa
 * UPDATED: Handle mitra pending + Allow resubmission jika ditolak
 * Lokasi: ROLE MAHASISWA/pages/handler_pengajuan_magang.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../Koneksi/koneksi.php';

// Cek login
if (!isset($_SESSION['id'])) {
    header("Location: ../../Login/login.php");
    exit;
}

$id_user = $_SESSION['id'];

// Fungsi validasi data lengkap
function validasiDataLengkap($conn, $id_user) {
    $errors = [];
    $query_mhs = "SELECT m.*, u.nama, u.nim 
                  FROM mahasiswa m 
                  JOIN users u ON m.id_user = u.id 
                  WHERE u.id = ?";
    $stmt = mysqli_prepare($conn, $query_mhs);
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $mahasiswa = mysqli_fetch_assoc($result);

    if (!$mahasiswa) {
        $errors[] = "Data mahasiswa tidak ditemukan";
        return $errors;
    }

    $id_mahasiswa = $mahasiswa['id_mahasiswa'];
    $query_kelompok = "SELECT k.*, ak.peran 
                       FROM anggota_kelompok ak 
                       JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
                       WHERE ak.id_mahasiswa = ?";
    $stmt2 = mysqli_prepare($conn, $query_kelompok);
    mysqli_stmt_bind_param($stmt2, 'i', $id_mahasiswa);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $kelompok = mysqli_fetch_assoc($result2);

    if (!$kelompok) {
        $errors[] = "Anda belum terdaftar dalam kelompok";
    } else {
        $id_kelompok = $kelompok['id_kelompok'];
        $query_count = "SELECT COUNT(*) as total FROM anggota_kelompok WHERE id_kelompok = ?";
        $stmt3 = mysqli_prepare($conn, $query_count);
        mysqli_stmt_bind_param($stmt3, 'i', $id_kelompok);
        mysqli_stmt_execute($stmt3);
        $result3 = mysqli_stmt_get_result($stmt3);
        $count = mysqli_fetch_assoc($result3);

        if ($count['total'] < 2) {
            $errors[] = "Kelompok harus memiliki minimal 2 anggota (termasuk ketua)";
        }
    }

    return $errors;
}

// PROSES SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_pengajuan') {

    // Validasi data mahasiswa
    $errors = validasiDataLengkap($conn, $id_user);

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }

    // Ambil data kelompok
    $query_data = "SELECT m.id_mahasiswa, ak.id_kelompok, ak.peran 
                   FROM mahasiswa m 
                   JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa 
                   WHERE m.id_user = ?";
    $stmt = mysqli_prepare($conn, $query_data);
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        $_SESSION['error'] = "Data tidak valid";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }

    // Cek apakah ketua
    if ($data['peran'] !== 'ketua') {
        $_SESSION['error'] = "Hanya ketua kelompok yang dapat mengajukan magang";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }

    $id_mahasiswa = $data['id_mahasiswa'];
    $id_kelompok = $data['id_kelompok'];

    // ============ PERBAIKAN: CEK PENGAJUAN AKTIF SAJA (BUKAN YANG DITOLAK) ============
    $query_cek_aktif = "SELECT id_pengajuan, status_pengajuan 
                        FROM pengajuan_magang 
                        WHERE id_kelompok = ? 
                        AND status_pengajuan IN ('menunggu', 'diterima', 'menunggu_mitra')
                        ORDER BY tanggal_pengajuan DESC LIMIT 1";
    $stmt_cek = mysqli_prepare($conn, $query_cek_aktif);
    mysqli_stmt_bind_param($stmt_cek, 'i', $id_kelompok);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        $pengajuan_aktif = mysqli_fetch_assoc($result_cek);
        $_SESSION['error'] = "Kelompok Anda masih memiliki pengajuan dengan status '{$pengajuan_aktif['status_pengajuan']}'. Tidak dapat mengajukan lagi sebelum pengajuan sebelumnya selesai diproses.";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }
    // ============ END PERBAIKAN ============

    // ====================================================
    // CEK MITRA: APPROVED atau PENDING
    // ====================================================
    
    $mitra_data = $_SESSION['selected_mitra'] ?? null;
    
    if (!$mitra_data) {
        $_SESSION['error'] = "Data mitra tidak ditemukan di session. Silakan pilih mitra kembali.";
        header("Location: ../index.php?page=pengajuan_Mitra");
        exit;
    }

    $mitra_status = $mitra_data['status'] ?? 'approved';
    $id_mitra = null;
    $id_pengajuan_mitra = null;

    if ($mitra_status === 'pending') {
        // Mitra masih pending approval dari Korbid
        $id_pengajuan_mitra = intval($mitra_data['id_pengajuan_mitra'] ?? 0);
        
        if ($id_pengajuan_mitra <= 0) {
            $_SESSION['error'] = "Data pengajuan mitra tidak valid.";
            header("Location: ../index.php?page=pengajuan_Mitra");
            exit;
        }

        // Cek status pengajuan mitra di database
        $query_check_pengajuan = "SELECT status_pengajuan FROM pengajuan_mitra WHERE id_pengajuan = ?";
        $stmt_check = mysqli_prepare($conn, $query_check_pengajuan);
        mysqli_stmt_bind_param($stmt_check, 'i', $id_pengajuan_mitra);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $pengajuan_mitra = mysqli_fetch_assoc($result_check);

        if (!$pengajuan_mitra) {
            $_SESSION['error'] = "Pengajuan mitra tidak ditemukan. Silakan pilih mitra kembali.";
            header("Location: ../index.php?page=pengajuan_Mitra");
            exit;
        }

        if ($pengajuan_mitra['status_pengajuan'] === 'ditolak') {
            $_SESSION['error'] = "Mitra yang Anda pilih telah DITOLAK oleh Koordinator. Silakan pilih mitra lain.";
            unset($_SESSION['selected_mitra']);
            header("Location: ../index.php?page=pengajuan_Mitra");
            exit;
        }

        // Jika masih menunggu, lanjutkan dengan id_mitra = NULL
        $id_mitra = null;

    } else {
        // Mitra sudah approved
        $id_mitra = intval($mitra_data['id_mitra'] ?? 0);
        
        if ($id_mitra <= 0) {
            $_SESSION['error'] = "ID Mitra tidak valid.";
            header("Location: ../index.php?page=pengajuan_Mitra");
            exit;
        }

        // Cek mitra di DB
        $query_check_mitra = "SELECT id_mitra, nama_mitra FROM mitra_perusahaan WHERE id_mitra = ?";
        $stmt_check = mysqli_prepare($conn, $query_check_mitra);
        mysqli_stmt_bind_param($stmt_check, 'i', $id_mitra);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) === 0) {
            $_SESSION['error'] = "Mitra tidak ditemukan di database.";
            header("Location: ../index.php?page=pengajuan_Mitra");
            exit;
        }
    }

    // Validasi file upload
    $allowed_extensions = ['pdf'];
    $max_file_size = 10 * 1024 * 1024;

    $file_proposal_cv = $_FILES['file_proposal_cv'] ?? null;

    if (!$file_proposal_cv) {
        $_SESSION['error'] = "File Proposal & CV wajib diupload";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }

    // Validasi Proposal & CV
    $proposal_cv_ext = strtolower(pathinfo($file_proposal_cv['name'], PATHINFO_EXTENSION));
    if (!in_array($proposal_cv_ext, $allowed_extensions)) {
        $_SESSION['error'] = "File harus PDF";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }
    if ($file_proposal_cv['size'] > (10 * 1024 * 1024)) {
        $_SESSION['error'] = "File maksimal 10MB";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }

    // Folder upload
    $upload_dir = "../../uploads/pengajuan_magang/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // PENTING: Ambil nama kelompok DAN prodi ketua untuk penamaan file
    $query_kelompok = "SELECT k.nama_kelompok, m.prodi 
                    FROM kelompok k
                    JOIN mahasiswa m ON m.id_mahasiswa = ?
                    WHERE k.id_kelompok = ?";
    $stmt_k = mysqli_prepare($conn, $query_kelompok);
    mysqli_stmt_bind_param($stmt_k, 'ii', $id_mahasiswa, $id_kelompok);
    mysqli_stmt_execute($stmt_k);
    $res_k = mysqli_stmt_get_result($stmt_k);
    $data_k = mysqli_fetch_assoc($res_k);

    // Logic Alias Prodi (sama seperti di surat penerimaan/pelaksanaan)
    $prodi_raw = strtolower(trim($data_k['prodi'] ?? '')); 
    $alias_prodi = 'UMUM'; 

    if (strpos($prodi_raw, 'manajemen informatika') !== false) {
        $alias_prodi = 'MIF';
    } elseif (strpos($prodi_raw, 'teknik komputer') !== false) {
        $alias_prodi = 'TKK';
    } elseif (strpos($prodi_raw, 'teknik informatika') !== false) {
        $alias_prodi = 'TIF';
    }

    // FORMAT BARU: ProposalCV_ALIAS_NamaKelompok_Timestamp.pdf
    $kelompok_clean = preg_replace('/[^A-Za-z0-9]/', '', $data_k['nama_kelompok']);
    $timestamp = date('YmdHis');
    $proposal_cv_filename = "ProposalCV_{$alias_prodi}_{$kelompok_clean}_{$timestamp}.pdf";
    $proposal_cv_path = $upload_dir . $proposal_cv_filename;
    
    // Upload
    if (!move_uploaded_file($file_proposal_cv['tmp_name'], $proposal_cv_path)) {
        $_SESSION['error'] = "Upload file gagal";
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }
    // Simpan ke database
    mysqli_begin_transaction($conn);

    try {
        // Insert pengajuan magang (TANPA file, karena file disimpan di tabel dokumen_magang)
        // PERHATIAN: id_mitra bisa NULL jika mitra masih pending
        $query_insert = "INSERT INTO pengajuan_magang 
                        (id_kelompok, id_mahasiswa_ketua, id_mitra, tanggal_pengajuan, status_pengajuan) 
                        VALUES (?, ?, ?, CURDATE(), ?)";
        
        // Status pengajuan tergantung mitra
        $status_pengajuan = ($mitra_status === 'pending') ? 'menunggu_mitra' : 'menunggu';
        
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, 'iiis',
            $id_kelompok, 
            $id_mahasiswa, 
            $id_mitra, // Bisa NULL jika pending
            $status_pengajuan
        );
        mysqli_stmt_execute($stmt_insert);

        // Ambil ID pengajuan yang baru dibuat
        $id_pengajuan_baru = mysqli_insert_id($conn);

        // Insert file ke tabel dokumen_magang
        $jenis_dokumen = 'proposalcv';
        $tanggal_upload = date('Y-m-d');

        $query_dokumen = "INSERT INTO dokumen_magang 
                        (id_pengajuan, jenis, file_path, tanggal_upload) 
                        VALUES (?, ?, ?, ?)";
        $stmt_dokumen = mysqli_prepare($conn, $query_dokumen);
        mysqli_stmt_bind_param($stmt_dokumen, 'isss', 
            $id_pengajuan_baru, 
            $jenis_dokumen, 
            $proposal_cv_filename, 
            $tanggal_upload
        );
        mysqli_stmt_execute($stmt_dokumen);

        // Update status mahasiswa
        $query_update_status = "UPDATE mahasiswa m
                                JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
                                SET m.status_magang = 'pra-magang'
                                WHERE ak.id_kelompok = ? 
                                AND m.status_magang NOT IN ('magang_aktif', 'selesai')"; // ✅ BENAR
        $stmt_update = mysqli_prepare($conn, $query_update_status);

        // Tambahkan error checking
        if ($stmt_update === false) {
            throw new Exception("Query prepare error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_update, 'i', $id_kelompok);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

        // Notifikasi ke Korbid
        $query_korbid = "SELECT id, nama FROM users WHERE role = 'Koordinator Bidang Magang'";
        $result_korbid = mysqli_query($conn, $query_korbid);

        while ($korbid = mysqli_fetch_assoc($result_korbid)) {
            if ($mitra_status === 'pending') {
                $pesan = "Pengajuan magang baru dari Kelompok ID:$id_kelompok. PERHATIAN: Mitra '{$mitra_data['nama']}' masih dalam status PENDING. Harap review mitra terlebih dahulu.";
            } else {
                $pesan = "Pengajuan magang baru dari Kelompok ID:$id_kelompok ke Mitra '{$mitra_data['nama']}' menunggu persetujuan Anda.";
            }
            
            $query_notif = "INSERT INTO notifikasi (id_user, pesan, status_baca, tanggal)
                            VALUES (?, ?, 'baru', NOW())";
            $stmt_notif = mysqli_prepare($conn, $query_notif);
            mysqli_stmt_bind_param($stmt_notif, 'is', $korbid['id'], $pesan);
            mysqli_stmt_execute($stmt_notif);
        }

        mysqli_commit($conn);

        // Clear session
        unset($_SESSION['selected_mitra']);

        // Success message tergantung status mitra
        if ($mitra_status === 'pending') {
            $_SESSION['success'] = "Pengajuan magang berhasil dikirim! CATATAN: Mitra '{$mitra_data['nama']}' masih menunggu persetujuan Koordinator Bidang Magang. Pengajuan Anda akan diproses setelah mitra disetujui.";
        } else {
            $_SESSION['success'] = "Pengajuan magang berhasil dikirim ke Mitra '{$mitra_data['nama']}'.";
        }
        
        header("Location: ../index.php?page=status_pengajuan");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);

       if (file_exists($proposal_cv_path )) unlink($proposal_cv_path );

        $_SESSION['error'] = "Kesalahan: " . $e->getMessage();
        header("Location: ../index.php?page=berkas_Magang");
        exit;
    }
}

// Jika request tidak valid
$_SESSION['error'] = "Request tidak valid";
header("Location: ../index.php?page=berkas_Magang");
exit;
?>