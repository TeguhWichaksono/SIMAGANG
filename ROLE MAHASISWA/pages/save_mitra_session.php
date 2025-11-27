<?php
/**
 * FIXED: Save Mitra Session dengan struktur konsisten
 * Lokasi: ROLE MAHASISWA/pages/save_mitra_session.php
 */

ob_start();
session_start();
require_once '../../Koneksi/koneksi.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

try {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    
    if (!$input) {
        throw new Exception('Data tidak valid');
    }
    
    // Cek apakah mitra pending atau approved
    $mitra_status = $input['mitra_status'] ?? 'approved';
    
    if ($mitra_status === 'pending') {
        // Mitra baru yang belum di-approve
        $id_pengajuan = intval($input['id_pengajuan_mitra'] ?? 0);
        
        if ($id_pengajuan <= 0) {
            throw new Exception('ID Pengajuan tidak valid');
        }
        
        // Verify pengajuan exists
        $query = "SELECT * FROM pengajuan_mitra WHERE id_pengajuan = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_pengajuan);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $pengajuan = mysqli_fetch_assoc($result);
        
        if (!$pengajuan) {
            throw new Exception('Pengajuan mitra tidak ditemukan');
        }
        
        // Save ke session dengan struktur array
        $_SESSION['selected_mitra'] = [
            'status' => 'pending',
            'id_mitra' => null,
            'id_pengajuan_mitra' => $id_pengajuan,
            'nama' => $input['nama_mitra'] ?? '',
            'alamat' => $input['alamat_mitra'] ?? '',
            'bidang' => $input['bidang_mitra'] ?? '',
            'kontak' => $input['kontak_mitra'] ?? ''
        ];
        
        mysqli_stmt_close($stmt);
        
    } else {
        // Mitra yang sudah approved
        $id_mitra = intval($input['id_mitra'] ?? 0);
        
        if ($id_mitra <= 0) {
            throw new Exception('ID Mitra tidak valid');
        }
        
        // Verify mitra exists
        $query = "SELECT * FROM mitra_perusahaan WHERE id_mitra = ? AND status = 'aktif'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_mitra);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $mitra = mysqli_fetch_assoc($result);
        
        if (!$mitra) {
            throw new Exception('Mitra tidak ditemukan atau tidak aktif');
        }
        
        // Save ke session dengan struktur array
        $_SESSION['selected_mitra'] = [
            'status' => 'approved',
            'id_mitra' => intval($mitra['id_mitra']),
            'id_pengajuan_mitra' => null,
            'nama' => $mitra['nama_mitra'],
            'alamat' => $mitra['alamat'] ?? '',
            'bidang' => $mitra['bidang'] ?? '',
            'kontak' => $mitra['kontak'] ?? ''
        ];
        
        mysqli_stmt_close($stmt);
    }
    
    // Force write session
    session_write_close();
    mysqli_close($conn);
    
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Mitra berhasil disimpan',
        'session_data' => $_SESSION['selected_mitra']
    ]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
exit;
?>