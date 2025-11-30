<?php
/**
 * File untuk debugging session mitra
 * Lokasi: ROLE MAHASISWA/pages/debug_session_mitra.php
 */

session_start();
header('Content-Type: application/json');

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'session_id' => session_id(),
    'session_exists' => isset($_SESSION['selected_mitra']),
    'session_data' => $_SESSION['selected_mitra'] ?? null,
    'all_session_keys' => array_keys($_SESSION),
    'session_user_id' => $_SESSION['id'] ?? 'NOT SET',
    'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NOT ACTIVE'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>