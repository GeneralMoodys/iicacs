<?php
$isdev = FALSE;
error_reporting(0);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ambil data dari form terus terapin anti antian
    $full_name = cleanInput($_POST['full_name']);
    $category = cleanInput($_POST['category']);
    $institution = cleanInput($_POST['Institution']);
    $sub_institution = cleanInput($_POST['Sub-Institution']);
    $phone = cleanInput($_POST['Phone']);
    $email = cleanInput($_POST['email']);
    $country = cleanInput($_POST['country']);

    // Buat slug dari nama lengkap
    $slug = createSlug($full_name);
    // $date = date('Y-m-d'); //format foldernya by tanggal jadi rapih cuy
    $uploadDir = "data/$slug/";

    // folder di atas ada apa ngga? kalo ga ada ya kita buat dulu dong
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // unggah file
    $abstractPath = uploadFile($_FILES["abstract"], $uploadDir);
    // $fullpaperPath = uploadFile($_FILES["fullpaper"], $uploadDir);
    // $transferPath = uploadFile($_FILES["transfer"], $uploadDir);

    $abstractAbsolutePath = realpath($abstractPath);
    // $fullpaperAbsolutePath = realpath($fullpaperPath);
    // $transferAbsolutePath = realpath($transferPath);
    
    $stmt = $conn->prepare("INSERT INTO applications (full_name, category, institution, sub_institution, phone, email, country, abstract_path, fullpaper_path, transfer_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssssss", $full_name, $category, $institution, $sub_institution, $phone, $email, $country, $abstractPath, $fullpaperPath, $transferPath);
    if ($stmt->execute()) {
        // echo "New record created successfully";
        // sleep(2);
        // Kirim data ke mail/register.php menggunakan cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $isdev==TRUE?"http://localhost/deviicas/mail/register.php":"http://iicacs.com/mail/register.php");  
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'full_name' => $full_name,
            'category' => $category,
            'Institution' => $institution,
            'Sub-Institution' => $sub_institution,
            'Phone' => $phone,
            'email' => $email,
            'country' => $country,
            'abstract_path' => $abstractAbsolutePath,
            'fullpaper_path' => $fullpaperAbsolutePath,
            'transfer_path' => $transferAbsolutePath
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "Email response: " . $response;
        }
        curl_close($ch);
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
