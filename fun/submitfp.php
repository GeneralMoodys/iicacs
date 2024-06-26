<?php
require_once('config.php');
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // ambil data dari form terus terapin anti antian
    $phone = cleanInput($_POST['Phone']);
    $email = cleanInput($_POST['email']);

    // Buat slug dari nama lengkap
    $slug = createSlug($email);
    // $date = date('Y-m-d'); //format foldernya by tanggal jadi rapih cuy
    $uploadDir = "../data/$slug/";

    // folder di atas ada apa ngga? kalo ga ada ya kita buat dulu dong
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // unggah file
    //$abstractPath = uploadFile($_FILES["payment"], $uploadDir);
    $fullpaperPath = uploadFile($_FILES["fullpaper"], $uploadDir);
    // $transferPath = uploadFile($_FILES["transfer"], $uploadDir);

    //$abstractAbsolutePath = realpath($abstractPath);
    // $fullpaperAbsolutePath = realpath($fullpaperPath);
     $transferAbsolutePath = realpath($transferPath);
    
    $stmt = $conn->prepare("INSERT INTO applications (phone, email, fullpaper_path) VALUES (?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("sss", $phone, $email, $fullpaperPath);
    if ($stmt->execute()) {
        // echo "New record created successfully";
        // sleep(2);
        // Kirim data ke mail/submitfp.php menggunakan cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $isdev==TRUE?"http://localhost/deviicas/mail/submitfp.php":"http://iicacs.com/mail/submitfp.php");  
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'Phone' => $phone,
            'email' => $email,
            'fullpaper_path' => $fullpaperAbsolutePath
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
