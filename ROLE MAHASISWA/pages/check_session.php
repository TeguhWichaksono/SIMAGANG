<?php
// pages/check_session.php
// File untuk debugging session

session_start();
header('Content-Type: application/json');

echo json_encode([
    'session_id' => session_id(),
    'session_status' => session_status(),
    'mitra_data' => [
        'id' => $_SESSION['selected_mitra_id'] ?? 'TIDAK ADA',
        'nama' => $_SESSION['selected_mitra_nama'] ?? 'TIDAK ADA',
        'alamat' => $_SESSION['selected_mitra_alamat'] ?? 'TIDAK ADA',
        'bidang' => $_SESSION['selected_mitra_bidang'] ?? 'TIDAK ADA',
        'kontak' => $_SESSION['selected_mitra_kontak'] ?? 'TIDAK ADA'
    ],
    'all_session' => $_SESSION,
    'session_save_path' => session_save_path(),
    'cookies' => $_COOKIE
], JSON_PRETTY_PRINT);
?>