<?php
include '../../Koneksi/koneksi.php';

if (isset($_POST['import_data'])) {
    
    // Validasi file
    $fileName = $_FILES['file_csv']['name'];
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    
    if ($fileExt !== 'csv') {
        echo "<script>alert('Format file harus .CSV!'); window.location.href='../index.php?page=data_Mitra';</script>";
        exit;
    }

    // Buka file
    $file = fopen($_FILES['file_csv']['tmp_name'], "r");
    
    // Skip baris header (opsional, jika csv punya header)
    fgetcsv($file); 

    $sukses = 0;
    $gagal = 0;

    while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
        // Urutan Kolom di CSV: 0=Nama, 1=Bidang, 2=Alamat, 3=Kontak, 4=Status, 5=Lat, 6=Long
        
        $nama    = mysqli_real_escape_string($conn, $row[0] ?? '');
        $bidang  = mysqli_real_escape_string($conn, $row[1] ?? '');
        $alamat  = mysqli_real_escape_string($conn, $row[2] ?? '');
        $kontak  = mysqli_real_escape_string($conn, $row[3] ?? '');
        $status  = mysqli_real_escape_string($conn, $row[4] ?? 'aktif');
        
        // Handle Koordinat (jika kosong set NULL)
        $lat     = !empty($row[5]) ? "'" . mysqli_real_escape_string($conn, $row[5]) . "'" : "NULL";
        $long    = !empty($row[6]) ? "'" . mysqli_real_escape_string($conn, $row[6]) . "'" : "NULL";

        if(!empty($nama)) {
            $query = "INSERT INTO mitra_perusahaan (nama_mitra, bidang, alamat, kontak, status, latitude, longitude) 
                      VALUES ('$nama', '$bidang', '$alamat', '$kontak', '$status', $lat, $long)";
            
            if (mysqli_query($conn, $query)) {
                $sukses++;
            } else {
                $gagal++;
            }
        }
    }
    
    fclose($file);
    
    echo "<script>
            alert('Import Selesai!\\nBerhasil: $sukses\\nGagal: $gagal'); 
            window.location.href='../index.php?page=data_Mitra';
          </script>";
}
?>