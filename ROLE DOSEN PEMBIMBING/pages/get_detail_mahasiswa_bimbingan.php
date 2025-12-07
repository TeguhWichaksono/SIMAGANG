<?php
/**
 * get_detail_mahasiswa_bimbingan.php
 * AJAX: Get Detail Mahasiswa untuk Modal
 */

if (!isset($_GET['id_mahasiswa'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID Mahasiswa tidak ditemukan'
    ]);
    exit;
}

$id_mahasiswa = intval($_GET['id_mahasiswa']);

// Get ID Dosen dari session
$query_dosen = "SELECT id_dosen FROM dosen WHERE id_user = ?";
$stmt_dosen = mysqli_prepare($conn, $query_dosen);
mysqli_stmt_bind_param($stmt_dosen, 'i', $_SESSION['id']);
mysqli_stmt_execute($stmt_dosen);
$result_dosen = mysqli_stmt_get_result($stmt_dosen);
$row_dosen = mysqli_fetch_assoc($result_dosen);

if (!$row_dosen) {
    echo json_encode([
        'success' => false,
        'message' => 'Data Dosen tidak ditemukan'
    ]);
    exit;
}

$id_dosen = $row_dosen['id_dosen'];

// Query Detail Mahasiswa
$query = "
    SELECT 
        m.id_mahasiswa,
        u.id AS id_user,
        u.nama AS nama_mahasiswa,
        u.email,
        u.foto_profil,
        u.nim,
        m.prodi,
        m.kontak,
        m.angkatan,
        m.status_magang,
        k.id_kelompok,
        k.nama_kelompok,
        mp.nama_mitra,
        mp.alamat AS alamat_mitra,
        mp.bidang AS bidang_mitra,
        ak.peran AS peran_kelompok
    FROM mahasiswa m
    JOIN users u ON m.id_user = u.id
    LEFT JOIN anggota_kelompok ak ON m.id_mahasiswa = ak.id_mahasiswa
    LEFT JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    LEFT JOIN pengajuan_magang pm ON k.id_kelompok = pm.id_kelompok AND pm.status_pengajuan = 'diterima'
    LEFT JOIN mitra_perusahaan mp ON pm.id_mitra = mp.id_mitra
    WHERE m.id_mahasiswa = ?
    AND k.id_dosen_pembimbing = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $id_mahasiswa, $id_dosen);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mahasiswa = mysqli_fetch_assoc($result);

if (!$mahasiswa) {
    echo json_encode([
        'success' => false,
        'message' => 'Data mahasiswa tidak ditemukan atau bukan mahasiswa bimbingan Anda'
    ]);
    exit;
}

// Query Progress Logbook
$query_logbook = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status_validasi = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status_validasi = 'disetujui' THEN 1 ELSE 0 END) AS disetujui,
        SUM(CASE WHEN status_validasi = 'ditolak' THEN 1 ELSE 0 END) AS ditolak
    FROM logbook_harian
    WHERE id_mahasiswa = ?
";

$stmt_logbook = mysqli_prepare($conn, $query_logbook);
mysqli_stmt_bind_param($stmt_logbook, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt_logbook);
$result_logbook = mysqli_stmt_get_result($stmt_logbook);
$logbook = mysqli_fetch_assoc($result_logbook);

// Response
echo json_encode([
    'success' => true,
    'data' => [
        'mahasiswa' => $mahasiswa,
        'logbook' => [
            'total' => intval($logbook['total']),
            'pending' => intval($logbook['pending']),
            'disetujui' => intval($logbook['disetujui']),
            'ditolak' => intval($logbook['ditolak'])
        ]
    ]
]);

exit;
?>