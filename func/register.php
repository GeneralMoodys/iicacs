<?php
require_once('config.php');

function checkRateLimit($conn, $phone, $email) {
    $stmt = $conn->prepare("SELECT last_request FROM rate_limit WHERE phone = ? OR email = ?");
    $stmt->bind_param("ss", $phone, $email);
    $stmt->execute();
    $stmt->bind_result($lastRequest);
    $stmt->fetch();
    $stmt->close();
    if ($lastRequest) {
        $currentTime = time();
        $lastRequestTime = strtotime($lastRequest);
        return ($currentTime - $lastRequestTime) < 60; // check if the last request was made within the last 60 seconds
    }
    return false;
}

function updateRateLimit($conn, $phone, $email) {
    $stmt = $conn->prepare("INSERT INTO rate_limit (phone, email, last_request) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE last_request = NOW()");
    $stmt->bind_param("ss", $phone, $email);
    $stmt->execute();
    $stmt->close();
}

function checkDuplicate($conn, $phone, $email) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE phone = ? OR email = ?");
    $stmt->bind_param("ss", $phone, $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = cleanInput($_POST['full_name']);
    $category = cleanInput($_POST['category']);
    $institution = cleanInput($_POST['Institution']);
    $sub_institution = cleanInput($_POST['Sub-Institution']);
    $phone = cleanInput($_POST['Phone']);
    $email = cleanInput($_POST['email']);
    $country = cleanInput($_POST['country']);
    
    if (checkRateLimit($conn, $phone, $email)) {
        echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Silakan ulangi kembali dalam 1 menit']);
        exit;
    }

    if (checkDuplicate($conn, $phone, $email)) {
        echo json_encode(['status' => 'error', 'message' => 'Maaf! Nomor telepon atau email sudah terdaftar']);
        exit;
    }
    
    $abstractPath = NULL; 
    $abstractAbsolutePath = NULL;

    if ($category != 'Participant' && isset($_FILES["abstract"])) {
        $slug = createSlug($full_name);
        $uploadDir = "../data/$slug/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $abstractPath = uploadFile($_FILES["abstract"], $uploadDir);
        
        if ($abstractPath !== null) {
            $abstractAbsolutePath = realpath($abstractPath);
        } else {
            $abstractPath = NULL;
        }
    }

    $stmt = $conn->prepare("INSERT INTO applications (full_name, category, institution, sub_institution, phone, email, country, abstract_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssss", $full_name, $category, $institution, $sub_institution, $phone, $email, $country, $abstractPath);
    if ($stmt->execute()) {
        updateRateLimit($conn, $phone, $email); // Update the rate limit timestamp

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $isdev ? "http://localhost/deviicas/mail/register.php" : "http://iicacs.com/mail/register.php");  
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
            'abstract_path' => $abstractAbsolutePath
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            echo json_encode(['status' => 'error', 'message' => "cURL Error: " . curl_error($ch)]);
        } else {
            echo json_encode(['status' => 'success', 'message' => "Registration Success"]);
        }
        curl_close($ch);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Error: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
