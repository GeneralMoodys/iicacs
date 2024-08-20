<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require('config.php');
    // Ambil data dari form dan bersihkan
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);

    // Pencarian data berdasarkan email dan phone
    $stmt = $conn->prepare("SELECT * FROM applications WHERE email = ? AND phone = ?");
    if ($stmt === false) {
        echo die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    
    // Jika data ditemukan, ambil datanya
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // print_r($row);

        // Data dari database
        $full_name = cleanInput($row['full_name']);
        $category = cleanInput($row['category']);
        $institution = cleanInput($row['institution']);
        $sub_institution = cleanInput($row['sub_institution']);
        $phone = cleanInput($row['phone']);
        $country = cleanInput($row['country']);

        // Buat slug dari nama lengkap
        $slug = createSlug($full_name);
        $uploadDir = "../data/$slug/";

        // Buat folder jika belum ada
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Unggah file update filenya
        if (!empty($_FILES["transfer"]["name"])) {
            $transferPath = uploadFile($_FILES["transfer"], $uploadDir);
            $transferAbsolutePath = realpath($transferPath);
        } else {
            // Jika tidak ada file yang diupload, gunakan path yang sudah ada
            $transferAbsolutePath = $row['transfer_path'];
        }

        // Persiapkan pernyataan untuk update
        $stmt->close();
        $stmt = $conn->prepare("UPDATE applications SET transfer_path = ? WHERE email = ? AND phone = ?");
        if ($stmt === false) {
            echo die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
        }
        $stmt->bind_param("sss", $transferAbsolutePath, $email, $phone);

        // Eksekusi pernyataan
        if ($stmt->execute() === false) {
           echo die(json_encode("Error executing statement: " . $stmt->error));
        }

         // Tanggapan sukses
         echo json_encode("Berhasil menyimpan data");

        // Kirim data ke mail/register.php menggunakan cURL
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
            // 'abstract_path' => $abstractAbsolutePath,
            // 'fullpaper_path' => $fullpaperAbsolutePath
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            // echo json_encode("Email response: " . $response);
            // echo json_encode($response);
        }
        curl_close($ch);
    } else {
        // Jika data tidak ditemukan, kembalikan pesan error
        // die("No record found with the given email and id.");
        echo json_encode('No record found ');
        
    }

    // Tutup pernyataan dan koneksi
    $stmt->close();
    $conn->close();
}
?>
