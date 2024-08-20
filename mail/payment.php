<?php

error_reporting(0);
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ambil data dari curl
    $phone = $_POST['Phone'];
    $email = $_POST['email'];
    $fullName = $_POST['full_name'];
    // untuk judul 
   
    if (isset($_POST['abstract_path'])) {
        $name = 'Data from '.$email;
    } elseif (isset($_POST['transfer_path'])) {
        $name = 'Data from '.$email;
    } else {
        $name = 'Data from '.$email;
    }
    $typenya = $category!='Presenter' ? 'Participant - ':'';
     $to = "iicacs@isi-ska.ac.id"; //ganti email  iicacs@isi-ska.ac.id
    // $to = "projectiicacs@gmail.com"; // Replace with the actual 
    $subject = "$typenya"."Transfer Evidence from $email";
    $body = "Full name: $fullName\n" .
            "Category : $category\n" .
            "Phone Number: $phone\n" .
            "Email    : $email\n" .
            "Attachment of Transfer\n" ;

    // Inisialisasi PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi server SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.iicacs.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dev@iicacs.com'; //ini ganti aja kalo ada yg official
        $mail->Password = '@kudil123'; //ini passwordnya
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        // $mail->Port = 465; // port yang sesuai dengan konfigurasi SMTPSecure


        // Pengaturan penerima dan pengirim email
        $mail->setFrom('info@iicacs.com', 'IICACS Information - '.$email); //GANTI INI JUDUL EMAILNYA NI
        $mail->addReplyTo($email);
        $mail->addAddress($to);

        // tambahin lampiran file yang dikirim dari CURL
        $files = ['abstract_path', 'fullpaper_path', 'transfer_path'];
        foreach ($files as $file) {
            if (isset($_POST[$file])) {
                $filePath = $_POST[$file];
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath);
                } else {
                    // echo "File does not exist: $filePath<br>";
                }
            } else {
                // echo "File path not set for: $file<br>";
            }
        }

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        // echo "Email has been sent successfully";
        // Kirim email konfirmasi ke peserta
        $confirmationMail = new PHPMailer(true);
        try {
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
            $confirmationMail->Subject = "Informasi Bukti Pembayaran";
            $confirmationMail->Body = "Halo $fullName,\n\n" .
                                    "Terima kasih telah mengupload bukti pembayaran IICACS.\n\n" .
                                    "Salam,\n" .
                                    "Tim IICACS";
            $confirmationMail->send();
            
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$confirmationMail->ErrorInfo}"]);
        }

    } catch (Exception $e) {
        // echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
