<?php
require_once('config.php');
if($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $uploadDir = "../data/$slug/";

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
