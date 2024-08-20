<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require('config.php');

    $email = cleanInput($_POST['email']);
    $id = cleanInput($_POST['id']);

    function checkRateLimit($conn, $email, $id) {
        $stmt = $conn->prepare("SELECT last_request FROM rate_limit WHERE email = ? OR id = ?");
        $stmt->bind_param("si", $email, $id);
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

    function updateRateLimit($conn, $email, $id) {
        $stmt = $conn->prepare("INSERT INTO rate_limit (email, id, last_request) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE last_request = NOW()");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $stmt->close();
    }

    if (checkRateLimit($conn, $email, $id)) {
        echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Silakan ulangi kembali dalam 1 menit']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM applications WHERE email = ? AND id = ?");
    if ($stmt == false) {
        die(json_encode(["status" => "error", "message" => "Error preparing statement: " . $conn->error]));
    }
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (is_null($row['abstract_path']) || is_null($row['transfer_path'])) {
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap mohon upload Abstract atau bukti Transfer"]);
            $stmt->close();
            $conn->close();
            exit; 
        }

        $full_name = cleanInput($row['full_name']);
        $category = cleanInput($row['category']);
        $institution = cleanInput($row['institution']);
        $sub_institution = cleanInput($row['sub_institution']);
        $phone = cleanInput($row['phone']);
        $country = cleanInput($row['country']);

        $slug = createSlug($full_name);
        $uploadDir = "../data/$slug/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!empty($_FILES["fullpaper"]["name"])) {
            $fullpaperPath = uploadFile($_FILES["fullpaper"], $uploadDir);
            $fullpaper_path = realpath($fullpaperPath);
        } else {
            $fullpaper_path = $row['fullpaper_path'];
        }

        $stmt->close();
        $stmt = $conn->prepare("UPDATE applications SET fullpaper_path = ? WHERE email = ? AND id = ?");
        if ($stmt == false) {
            die(json_encode(["status" => "error", "message" => "Error preparing update statement: " . $conn->error]));
        }
        $stmt->bind_param("ssi", $fullpaper_path, $email, $id);

        if ($stmt->execute() == false) {
            die(json_encode(["status" => "error", "message" => "Error executing statement: " . $stmt->error]));
        }

        updateRateLimit($conn, $email, $id); // Update the rate limit timestamp

        echo json_encode(["status" => "success", "message" => "Data berhasil disimpan"]);

        $ch = curl_init();
        $url = $isdev ? "http://localhost/deviicas/mail/submitfp.php" : "http://iicacs.com/mail/submitfp.php";
        curl_setopt($ch, CURLOPT_URL, $url);
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
            'fullpaper_path' => $fullpaper_path,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response == false) {
            echo json_encode(["status" => "error", "message" => "cURL Error: " . curl_error($ch)]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data berhasil direkam" . $response]);
        }
        curl_close($ch);
    } else {
        echo json_encode(["status" => "error", "message" => "Tidak ada data yang ditemukan dengan email dan ID yang diberikan"]);
    }

    $stmt->close();
    $conn->close();
}
?>
