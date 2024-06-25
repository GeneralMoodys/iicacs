<?php

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil data dari form
    $fullName = $_POST['full_name'];
    $category = $_POST['category']; 
    $institution = $_POST['Institution']; 
    $subInstitution = $_POST['Sub-Institution']; 
    $phone = $_POST['Phone'];
    $email = $_POST['email'];
    $country = $_POST['country'];

    // Mengatur informasi email
    $to = "mnprasetya.labs@gmail.com";
    // $to = "adifuadil@gmail.com";
    $subject = "Job Application from $fullName";
    $body = "Full name: $fullName\n" .
            "Category: $category\n" .
            "Institution: $institution\n" .
            "Sub-Institution: $subInstitution\n" .
            "Phone Number: $phone\n" .
            "Email: $email\n" .
            "Country: $country\n";

    // Inisialisasi PHPMailer
    $mail = new PHPMailer(true);
    try {
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        // $mail->SMTPDebug = SMTP::DEBUG_OFF;

        // Konfigurasi server SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.iicacs.com';
        // $mail->Host = 'purbayan.idweb.host'; //host SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'dev@iicacs.com'; //email SMTP
        $mail->Password = '@kudil123'; //password SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587; 
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        // $mail->Port = 465; 

        // Pengaturan penerima dan pengirim email
        $mail->setFrom('info@iicacs.com', 'Job Application');
        $mail->addReplyTo($email, $fullName); 
        $mail->addAddress($to);

        // Menambahkan lampiran file
        $files = ['abstract', 'fullpaper', 'transfer'];
        foreach ($files as $file) {
            if (isset($_FILES[$file]) && $_FILES[$file]['error'] == UPLOAD_ERR_OK) {
                $mail->addAttachment($_FILES[$file]['tmp_name'], $_FILES[$file]['name']);
            } else {
                echo "Error uploading file $file: " . $_FILES[$file]['error'] . "<br>";
            }
        }


        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body; //gak perlu nl2br jika men teks biasa

        // Mengirim email
        $mail->send();
        echo "Email has been sent successfully";
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
