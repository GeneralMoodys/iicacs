<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require('config.php');

    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);

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

    if (checkRateLimit($conn, $phone, $email)) {
        echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Silakan ulangi kembali dalam 1 menit']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM applications WHERE email = ? AND phone = ?");
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Error preparing statement: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $full_name = cleanInput($row['full_name']);
        $category = cleanInput($row['category']);
        $institution = cleanInput($row['institution']);
        $sub_institution = cleanInput($row['sub_institution']);
        $country = cleanInput($row['country']);

        $slug = createSlug($full_name);
        $uploadDir = "../data/$slug/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!empty($_FILES["transfer"]["name"])) {
            $transferPath = uploadFile($_FILES["transfer"], $uploadDir);
            $transferAbsolutePath = realpath($transferPath);
        } else {
            $transferAbsolutePath = $row['transfer_path'];
        }

        $stmt->close();
        $stmt = $conn->prepare("UPDATE applications SET transfer_path = ? WHERE email = ? AND phone = ?");
        if ($stmt === false) {
            echo json_encode(["status" => "error", "message" => "Error preparing update statement: " . $conn->error]);
            exit;
        }
        $stmt->bind_param("sss", $transferAbsolutePath, $email, $phone);

        if ($stmt->execute() === false) {
            echo json_encode(["status" => "error", "message" => "Error executing statement: " . $stmt->error]);
            exit;
        }

        updateRateLimit($conn, $phone, $email); // Update the rate limit timestamp

        $ch = curl_init();
        $url = $isdev ? "http://localhost/deviicas/mail/payment.php" : "http://iicacs.com/mail/payment.php";
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
            'transfer_path' => $transferAbsolutePath
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            echo json_encode(["status" => "error", "message" => "cURL Error: " . curl_error($ch)]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data berhasil di rekam"]);
        }
        curl_close($ch);
    } else {
        echo json_encode(["status" => "error", "message" => "Email atau nomor telepon salah"]);
    }

    $stmt->close();
    $conn->close();
}
?>
