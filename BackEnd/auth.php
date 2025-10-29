<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('config/Koneksi.php');
var_dump($_POST);
exit;
session_start();

// --- REGISTER ---
if (isset($_POST['register'])) {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role     = $_POST['role'];

    // Cek apakah email sudah ada
    $cek = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='register.html';</script>";
        exit;
    }

    // Simpan data user baru
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.html';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat registrasi.'); window.location='register.html';</script>";
    }
}

// --- LOGIN ---
if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Simpan session
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            // Arahkan sesuai role
            switch ($user['role']) {
                case 'admin':
                    header("Location: dashboard_admin.php");
                    break;
                case 'korbid':
                    header("Location: dashboard_korbid.php");
                    break;
                case 'dosen':
                    header("Location: dashboard_dosen.php");
                    break;
                case 'mahasiswa':
                    header("Location: dashboard_mahasiswa.php");
                    break;
                default:
                    header("Location: dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location='login.html';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location='login.html';</script>";
    }
}
?>
