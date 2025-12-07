<?php
include '../Koneksi/koneksi.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // pastikan PHPMailer sudah diinstall via composer

$message = "";

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);

    // cek apakah email terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    if (mysqli_num_rows($cek) == 1) {
        // buat token unik
        $token = bin2hex(random_bytes(50));
        $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // simpan token ke database
        mysqli_query($conn, "UPDATE users SET reset_token='$token', token_expired='$expired' WHERE email='$email'");

        // link reset password
        $reset_link = "http://localhost/SIMAGANG/Login/masukSandi.php?token=" . $token;

        // kirim email
        $mail = new PHPMailer(true);
        try {
            // konfigurasi SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'e31241242@student.polije.ac.id'; // ganti email kamu
            $mail->Password   = 'pystazkjobonchak'; // ganti App Password Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // header email
            $mail->setFrom('e31241242@student.polije.ac.id', 'SI MAGANG | Politeknik Negeri Jember');
            $mail->addAddress($email);
            $mail->addReplyTo('no_reply@gmail.com', 'SI MAGANG Support');

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(true);

            // template email
            $email_template = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Reset Password - SI MAGANG</title>
            </head>
            <body style='font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;'>
                <div style='max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>
                    <h2 style='color:#2575fc; text-align:center;'>Reset Password</h2>
                    <p>Halo,</p>
                    <p>Kami menerima permintaan untuk mereset password akun kamu. Klik tombol di bawah ini untuk melanjutkan proses reset password:</p>
                    <div style='text-align:center; margin:20px 0;'>
                        <a href='{$reset_link}'
                           style='background:#2575fc; color:#fff; padding:12px 20px; text-decoration:none; border-radius:5px; display:inline-block;'>
                           🔒 Reset Password
                        </a>
                    </div>
                    <p>Jika kamu tidak merasa meminta reset password, abaikan email ini.</p>
                    <hr style='margin:30px 0; border:none; border-top:1px solid #eee;'>
                    <p style='font-size:13px; color:#777; text-align:center;'>
                        Email ini dikirim secara otomatis oleh sistem <b>SI MAGANG</b>.<br>
                        © 2025 Politeknik Negeri Jember.
                    </p>
                </div>
            </body>
            </html>";

            $mail->Subject = '🔑 Reset Password - SI MAGANG';
            $mail->Body    = $email_template;
            $mail->AltBody = "Silakan buka link berikut untuk mereset password Anda: {$reset_link}";

            $mail->send();
            $message = "✅ Link reset password telah dikirim ke email Anda. Silakan cek folder Inbox atau Spam.";
        } catch (Exception $e) {
            $message = "❌ Email gagal dikirim. Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "❌ Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Lupa Password | SI MAGANG</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
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
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    width: 400px;
    padding: 40px 35px;
    text-align: center;
}
h1 { font-size:24px; color:#512da8; margin-bottom:10px; }
p { font-size:14px; color:#666; margin-bottom:25px; }
input {
    width: 100%; padding:12px 15px; margin:10px 0;
    border:1.5px solid #ddd; border-radius:8px;
    background-color:#f9f9f9; font-size:14px; outline:none;
    transition: all 0.3s ease;
}
input:focus {
    border-color:#2575fc; background-color:#fff;
    box-shadow:0 0 6px rgba(37,117,252,0.3);
}
button {
    width:100%; padding:12px; border:none; border-radius:25px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color:white; font-weight:bold; cursor:pointer; transition: all 0.3s ease;
    margin-top:15px;
}
button:hover {
    background: linear-gradient(135deg, #2575fc, #6a11cb);
    transform: translateY(-2px);
}
.back-link {
    display:inline-block; margin-top:20px; font-size:13px; color:#512da8;
    text-decoration:none; transition: all 0.3s ease;
}
.back-link:hover { color:#2575fc; text-decoration:underline; }
.message { margin-bottom:15px; color:#333; font-size:14px; }
</style>
</head>
<body>
<div class="reset-container">
    <h1>Lupa Password</h1>
    <p>Masukkan email Anda untuk menerima link reset password ke Gmail Anda.</p>

    <?php if(!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form action="" method="POST">
        <input type="email" name="email" placeholder="Masukkan Email Anda" required />
        <button type="submit" name="submit">Kirim Link Reset</button>
    </form>

    <a href="login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke halaman masuk</a>
</div>
</body>
</html>
