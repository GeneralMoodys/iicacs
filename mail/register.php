<?php

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name'];
    $category = $_POST['category'];
    $institution = $_POST['Institution'];
    $subInstitution = $_POST['Sub-Institution'];
    $phone = $_POST['Phone'];
    $email = $_POST['email'];
    $country = $_POST['country'];

    $typenya = $category!='Presenter' ? 'Participant - ':'';
      $to = "iicacs@isi-ska.ac.id"; // Replace with the actual recipient email address
    // $to = "projectiicacs@gmail.com"; // Replace with the actual recipient email address
   // $to = "mnprasetya.labs@gmail.com"; // Replace with the actual 
    $subject = "$typenya"."Submision from $email";
    $body = "Full name: $fullName\n" .
            "Category : $category\n" .
            "Institution : $institution\n" .
            "Sub-Institution: $subInstitution\n" .
            "Phone Number: $phone\n" .
            "Email    : $email\n" .
            "Country  : $country\n" ;

    // Initialize PHPMailer untuk email ke admin
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi server SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.iicacs.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dev@iicacs.com'; // Ganti dengan username SMTP Anda
        $mail->Password = '@kudil123'; // Ganti dengan password SMTP Anda
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Pengaturan email pengirim dan penerima
        $mail->setFrom('info@iicacs.com', 'IICACS Information - '.$email); //GANTI INI JUDUL EMAILNYA NI
        $mail->addReplyTo($email, $fullName);
        $mail->addAddress($to);

        // Lampirkan file jika ada
        $files = ['abstract_path'];
        foreach ($files as $file) {
            if (isset($_POST[$file])) {
                $filePath = $_POST[$file];
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath);
                } else {
                    echo "File does not exist: $filePath<br>";
                }
            } else {
                echo "File path not set for: $file<br>";
            }
        }

        // Subjek dan isi email
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Kirim email ke admin
        $mail->send();
        echo json_encode(['status' => 'success', 'message' => "Email has been sent successfully"]);

        // Kirim email konfirmasi ke peserta
        try {
            $confirmationMail = new PHPMailer(true);
            // Konfigurasi server SMTP
            $confirmationMail->isSMTP();
            $confirmationMail->Host = 'mail.iicacs.com';
            $confirmationMail->SMTPAuth = true;
            $confirmationMail->Username = 'dev@iicacs.com'; // Ganti dengan username SMTP Anda
            $confirmationMail->Password = '@kudil123'; // Ganti dengan password SMTP Anda
            $confirmationMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $confirmationMail->Port = 587;

            // Pengaturan email pengirim dan penerima
            $confirmationMail->setFrom('info@iicacs.com', 'IICACS');
            $confirmationMail->addAddress($email);

            // Subjek dan isi email konfirmasi
            $confirmationMail->isHTML(false);
            $confirmationMail->Subject = "Registrasi sebagai $category berhasil";
            $confirmationMail->Body = "Halo $fullName,\n\n" .
                                      "Terima kasih telah mendaftar sebagai $category di IICACS. Registrasi Anda berhasil.\n" .
                                      "Selanjutnya silahkan melakukan pembayaran sesuai dengan Kategori nya.\n\n".
                                      "Salam,\n" .
                                      "Tim IICACS";
            $confirmationMail->send();
            
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$confirmationMail->ErrorInfo}"]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
    }
}
?>
