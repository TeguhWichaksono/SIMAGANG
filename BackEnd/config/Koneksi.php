<?php
class Koneksi {
    private $host = "localhost";
    private $db_name = "simagang";
    private $username = "root";
    private $password = "";

    public $conn;

    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die("Koneksi gagal: " . $this->conn->connect_error);
        }

        return $this->conn;
    }
}

// --- tes koneksi ---
// $db = new Database();
// $conn = $db->getConnection();

// if ($conn) {
//     echo "Koneksi berhasil ke database!";
// }
?>