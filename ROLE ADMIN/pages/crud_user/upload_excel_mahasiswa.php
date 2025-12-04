<?php
include "../../../Koneksi/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    
    $file = $_FILES['excel_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validasi extension
    $allowedExt = ['csv', 'xlsx', 'xls'];
    
    if (!in_array($fileExt, $allowedExt)) {
        header("Location: ../../index.php?page=manajemen_User&error=invalid_file");
        exit();
    }

    if ($fileError !== 0) {
        header("Location: ../../index.php?page=manajemen_User&error=upload_failed");
        exit();
    }

    $success = 0;
    $failed = 0;
    $allData = []; // Simpan semua data dari semua sheet

    // ========== PROSES FILE CSV ==========
    if ($fileExt === 'csv') {
        if (($handle = fopen($fileTmpName, 'r')) !== FALSE) {
            
            // Skip header row (baris pertama)
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $nama = isset($data[0]) ? trim($data[0]) : '';
                $nim = isset($data[1]) ? trim($data[1]) : '';
                
                if (!empty($nama) && !empty($nim)) {
                    $allData[] = ['nama' => $nama, 'nim' => $nim];
                }
            }
            fclose($handle);
        }
    } 
    // ========== PROSES FILE XLSX/XLS ==========
    else if ($fileExt === 'xlsx' || $fileExt === 'xls') {
        
        // Cek library PhpSpreadsheet
        if (!file_exists('../../../vendor/autoload.php')) {
            header("Location: ../../index.php?page=manajemen_User&error=library_missing");
            exit();
        }
        
        require '../../../vendor/autoload.php';
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpName);
            
            // Loop semua sheet (Golongan A, B, C, D, dll)
            foreach ($spreadsheet->getAllSheets() as $worksheet) {
                $rows = $worksheet->toArray();
                
                // Skip header (baris pertama) di tiap sheet
                array_shift($rows);
                
                foreach ($rows as $row) {
                    $nama = isset($row[0]) ? trim($row[0]) : '';
                    $nim = isset($row[1]) ? trim($row[1]) : '';
                    
                    if (!empty($nama) && !empty($nim)) {
                        $allData[] = ['nama' => $nama, 'nim' => $nim];
                    }
                }
            }
            
        } catch (Exception $e) {
            header("Location: ../../index.php?page=manajemen_User&error=read_failed&msg=" . urlencode($e->getMessage()));
            exit();
        }
    }

    // ========== INSERT DATA KE DATABASE ==========
    if (count($allData) > 0) {
        foreach ($allData as $mahasiswa) {
            $nama = mysqli_real_escape_string($conn, $mahasiswa['nama']);
            $nim = mysqli_real_escape_string($conn, $mahasiswa['nim']);
            
            // Generate email default
            $email = $nim . '@student.polije.ac.id';
            
            // Password default: nim (di-hash)
            $password = password_hash($nim, PASSWORD_DEFAULT);
            
            // Role default
            $role = 'mahasiswa';
            
            // Cek apakah NIM sudah ada
            $checkQuery = mysqli_query($conn, "SELECT id FROM users WHERE nim = '$nim'");
            
            if (mysqli_num_rows($checkQuery) == 0) {
                $query = mysqli_query($conn, "INSERT INTO users (nama, nim, email, password, role) 
                                               VALUES ('$nama', '$nim', '$email', '$password', '$role')");
                
                if ($query) {
                    $success++;
                } else {
                    $failed++;
                }
            } else {
                $failed++; // NIM duplikat
            }
        }
        
        // Redirect dengan info hasil
        header("Location: ../../index.php?page=manajemen_User&success_upload=$success&failed_upload=$failed");
        exit();
    } else {
        header("Location: ../../index.php?page=manajemen_User&error=no_data");
        exit();
    }

} else {
    header("Location: ../../index.php?page=manajemen_User");
    exit();
}
?>