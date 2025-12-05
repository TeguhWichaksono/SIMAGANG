<?php
$servername = "localhost";
$username = "root";
$password = "";
<<<<<<< HEAD
$database = "simagang"; // ganti sesuai nama databasenya
=======
$database = "5imagang";
>>>>>>> origin/arilmun

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
<<<<<<< HEAD
    die("Koneksi gagal: " . mysqli_connect_error());
}
=======
    // Log error, jangan output HTML
    error_log("Database connection failed: " . mysqli_connect_error());
    // Throw exception instead of die
    throw new Exception("Database connection failed");
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
>>>>>>> origin/arilmun
?>