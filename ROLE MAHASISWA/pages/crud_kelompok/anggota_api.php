<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require '../../../Koneksi/koneksi.php';

$id_kelompok = $_GET['id_kelompok'] ?? null;
if (!$id_kelompok) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT ak.id_anggota, u.nama, u.nim, m.kontak, ak.peran
    FROM tb_anggota_kelompok ak
    JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    WHERE ak.id_kelompok = ?
    ORDER BY ak.peran DESC, u.nama ASC
");
$stmt->execute([$id_kelompok]);

$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($anggota);
