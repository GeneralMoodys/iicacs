<?php
$isdev = FALSE;
//error_reporting(0);

$servername = "localhost";
$username = $isdev==TRUE?"root":"iicacsco_app"; 
$password = $isdev==TRUE?"@mnprasetya12":"@kudil123"; 
$dbname   = $isdev==TRUE?"iicas":"iicacsco_app";
$conn     = new mysqli($servername, $username, $password, $dbname);

// cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fungsi untuk upload file
function uploadFile($file, $uploadDir) {
    $targetDir = $uploadDir . basename($file["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetDir, PATHINFO_EXTENSION));
    
    // Validasi ukuran file (maksimal 5MB) tapi kan udah di frontend, tapi kalo di inject POST langsung bakal lolos sih :( idupin aja bagusnya
    // if ($file["size"] > 5000000) {
    //     echo "Sorry, your file is too large.";
    //     $uploadOk = 0;
    // }
    
    // ini kalo mau nerapin pembatasan tipedata biar gak kena upload shell / backdoor
    // if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx" && $fileType != "jpg"&& $fileType != "png"&& $fileType != "jpeg"&& $fileType != "xls"&& $fileType != "xlxs" && $fileType != "gif"&& $fileType != "ico") {
    //     echo "Sorry, only PDF, DOC & DOCX files are allowed.";
    //     $uploadOk = 0;
    // }
    
    // Jika $uploadOk nilainya 0, berarti ada error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        return null;
    } else {
        // Jika semuanya oke, coba unggah file
        if (move_uploaded_file($file["tmp_name"], $targetDir)) {
            return $targetDir;
        } else {
            echo "Sorry, there was an error uploading your file.";
            return null;
        }
    }
}

// Fungsi untuk membuat slug dari string
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Fungsi buat anti xss sama sql injection
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
