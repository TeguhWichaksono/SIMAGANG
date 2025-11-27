<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "simagang";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    // Log error, jangan output HTML
    error_log("Database connection failed: " . mysqli_connect_error());
    // Throw exception instead of die
    throw new Exception("Database connection failed");
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
?>