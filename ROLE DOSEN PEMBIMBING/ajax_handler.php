<?php
/**
 * ajax_handler.php
 * Central AJAX Router untuk semua endpoint
 */

session_start();
header('Content-Type: application/json');

include '../Koneksi/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Security check
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Dosen Pembimbing') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Route ke file yang sesuai
switch ($action) {
    case 'get_detail_logbook':
        include 'pages/get_detail_logbook.php';
        break;
        
    case 'update_validasi_logbook':
        include 'pages/update_validasi_logbook.php';
        break;
        
    case 'get_detail_mahasiswa_bimbingan': 
        include 'pages/get_detail_mahasiswa_bimbingan.php';
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}

exit;
?>