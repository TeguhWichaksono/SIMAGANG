<?php
include '../Koneksi/koneksi.php'; // koneksi database

if (!isset($_GET['token'])) die("Token tidak ditemukan!");

$token = $_GET['token'];

// Ambil data user berdasarkan token
$query = mysqli_query($conn, "SELECT email, token_expired FROM users WHERE reset_token='$token' LIMIT 1");
$data = mysqli_fetch_assoc($query);

if (!$data) die("Token tidak valid!");

$expired_time = strtotime($data['token_expired']);
if (time() > $expired_time) die("Token sudah kadaluarsa! Silakan lakukan permintaan reset ulang.");

$email = $data['email'];
$message = "";
$redirect = false;

if (isset($_POST['submit'])) {
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password_baru === $konfirmasi) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "
            UPDATE users 
            SET password='$password_hash', reset_token=NULL, token_expired=NULL 
            WHERE email='$email'
        ");

        if ($update) {
            $message = "✅ Password berhasil diperbarui! Anda akan diarahkan ke halaman login...";
            $redirect = true; // tanda untuk redirect
        } else {
            $message = "❌ Terjadi kesalahan saat memperbarui password!";
        }
    } else {
        $message = "❌ Password baru dan konfirmasi tidak cocok!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Reset Password | SI MAGANG</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
@import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap");
* { margin:0; padding:0; box-sizing:border-box; font-family: "Montserrat", sans-serif; }

body {
    background: linear-gradient(to right, #e2e2e2, #c9d6ff);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.reset-container {
    background-color: #fff;
    border-radius: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    width: 400px;
    padding: 40px 35px;
    text-align: center;
    transition: all 0.3s ease;
}

h2 { color: #512da8; font-size: 24px; margin-bottom: 15px; }
p { font-size: 14px; color: #666; margin-bottom: 25px; }

input {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}

input:focus {
    border-color: #2575fc;
    background-color: #fff;
    box-shadow: 0 0 6px rgba(37,117,252,0.3);
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 25px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 15px;
    font-size: 15px;
}

button:hover {
    background: linear-gradient(135deg, #2575fc, #6a11cb);
    transform: translateY(-2px);
}

.back-link {
    display:inline-block;
    margin-top:20px;
    font-size:13px;
    color:#512da8;
    text-decoration:none;
    transition: all 0.3s ease;
}

.back-link:hover { color:#2575fc; text-decoration:underline; }

.message {
    margin-bottom: 15px;
    font-size: 14px;
    padding: 10px 15px;
    border-radius: 8px;
    color: #fff;
}

.success { background-color: #28a745; }
.error { background-color: #dc3545; }
</style>
</head>
<body>
<div class="reset-container">
    <h2>Perbarui Password</h2>
    <p>Masukkan password baru Anda dan konfirmasi untuk mengupdate akun.</p>

    <?php if(!empty($message)) {
        $class = $redirect ? 'success' : 'error';
        echo "<div class='message $class'>$message</div>";
    } ?>

    <?php if(!$redirect): ?>
    <form method="POST">
        <input type="password" name="password_baru" placeholder="Masukkan Password Baru" required>
        <input type="password" name="konfirmasi" placeholder="Konfirmasi Password Baru" required>
        <button type="submit" name="submit"><i class="fa fa-lock"></i> Simpan Password</button>
    </form>
    <?php endif; ?>

    <a href="login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke halaman masuk</a>
</div>

<?php if($redirect): ?>
<script>
// redirect otomatis ke halaman login setelah 3 detik
setTimeout(() => { window.location.href = 'login.php'; }, 5000);
</script>
<?php endif; ?>
</body>
</html>
