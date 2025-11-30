<?php
/**
 * Bulletproof Get Mitra
 * Handles any unexpected output
 */

// Start output buffering FIRST
ob_start();

// Include koneksi
include_once '../../Koneksi/koneksi.php';

// Clean any unexpected output
ob_clean();

// NOW set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    // Query mitra aktif
    $query = "SELECT id_mitra, nama_mitra, alamat, bidang, kontak 
              FROM mitra_perusahaan 
              WHERE status = 'aktif' 
              ORDER BY nama_mitra ASC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Query error: ' . mysqli_error($conn));
    }
    
    $data = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
            'id' => intval($row['id_mitra']),
            'nama' => $row['nama_mitra'],
            'alamat' => $row['alamat'] ?? '',
            'bidang' => $row['bidang'] ?? '',
            'kontak' => $row['kontak'] ?? ''
        );
    }
    
    mysqli_close($conn);
    
    // Clean buffer before output
    ob_clean();
    
    // Send JSON
    echo json_encode(array(
        'success' => true,
        'data' => $data,
        'total' => count($data),
        'message' => count($data) > 0 ? 'Data berhasil diambil' : 'Tidak ada mitra aktif'
    ));
    
} catch (Exception $e) {
    // Clean buffer
    ob_clean();
    
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'data' => array(),
        'total' => 0
    ));
}

// End output buffering
ob_end_flush();
exit;
?>