<?php
session_start();
include '../Koneksi/koneksi.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['id'])) {
        throw new Exception('User tidak terautentikasi. Silakan login kembali.');
    }

    $id_user = $_POST['id_user'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $nip = trim($_POST['nip'] ?? '');

    if (empty($id_user) || $id_user != $_SESSION['id']) {
        throw new Exception('ID user tidak valid');
    }

    if (empty($email)) {
        throw new Exception('Email tidak boleh kosong');
    }

    if (empty($nip)) {
        throw new Exception('NIP tidak boleh kosong');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }

    // Update email pada tabel users
    $stmt1 = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE id = ?");
    if (!$stmt1) throw new Exception('Prepare users update failed: ' . mysqli_error($conn));

    mysqli_stmt_bind_param($stmt1, 'si', $email, $id_user);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Gagal update data user: ' . mysqli_error($conn));
    mysqli_stmt_close($stmt1);

    // Cek apakah data dosen sudah ada
    $stmt2 = mysqli_prepare($conn, "SELECT id_user FROM dosen WHERE id_user = ?");
    if (!$stmt2) throw new Exception('Prepare dosen select failed: ' . mysqli_error($conn));

    mysqli_stmt_bind_param($stmt2, 'i', $id_user);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_store_result($stmt2);

    if (mysqli_stmt_num_rows($stmt2) > 0) {
        // Jika sudah ada, update nip dan prodi
        mysqli_stmt_close($stmt2);

        $stmt3 = mysqli_prepare($conn, "UPDATE dosen SET nip = ?, prodi = ? WHERE id_user = ?");
        if (!$stmt3) throw new Exception('Prepare dosen update failed: ' . mysqli_error($conn));

        mysqli_stmt_bind_param($stmt3, 'ssi', $nip, $prodi, $id_user);
    } else {
        // Jika belum ada, insert data baru
        mysqli_stmt_close($stmt2);

        $stmt3 = mysqli_prepare($conn, "INSERT INTO dosen (id_user, nip, prodi) VALUES (?, ?, ?)");
        if (!$stmt3) throw new Exception('Prepare dosen insert failed: ' . mysqli_error($conn));

        mysqli_stmt_bind_param($stmt3, 'iss', $id_user, $nip, $prodi);
    }

    if (!mysqli_stmt_execute($stmt3)) {
        throw new Exception('Gagal menyimpan data dosen: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt3);

    $response['success'] = true;
    $response['message'] = 'Profil berhasil diperbarui';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
    exit;
}
?>
