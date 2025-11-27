<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require '../../../Koneksi/koneksi.php';

$id_kelompok = $_GET['id_kelompok'] ?? null;

if (!$id_kelompok) {
    echo json_encode([]);
    exit;
}

// Query untuk ambil daftar anggota kelompok
$stmt = mysqli_prepare($conn, "
    SELECT 
        ak.id_anggota, 
        u.nama, 
        u.nim, 
        m.kontak, 
        ak.peran,
        m.id_mahasiswa
    FROM anggota_kelompok ak
    JOIN mahasiswa m ON ak.id_mahasiswa = m.id_mahasiswa
    JOIN users u ON m.id_user = u.id
    WHERE ak.id_kelompok = ?
    ORDER BY ak.peran DESC, u.nama ASC
");

if (!$stmt) {
    echo json_encode(['error' => 'Query preparation failed: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id_kelompok);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$anggota = [];
while ($row = mysqli_fetch_assoc($result)) {
    $anggota[] = [
        'id_anggota' => $row['id_anggota'],
        'nama' => $row['nama'],
        'nim' => $row['nim'],
        'kontak' => $row['kontak'] ?? '-',
        'peran' => $row['peran'],
        'id_mahasiswa' => $row['id_mahasiswa']
    ];
}

mysqli_stmt_close($stmt);

echo json_encode($anggota, JSON_UNESCAPED_UNICODE);