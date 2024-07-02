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

    // untuk judul 
   
    if (isset($_POST['abstract_path'])) {
        $name = 'Abstract';
    } elseif (isset($_POST['fullpaper_path'])) {
        $name = 'Full Paper';
    } else {
        $name = 'Updated';
    }
      
    $to = "iicacs@isi-ska.ac.id"; //ganti email  iicacs@isi-ska.ac.id
    $subject = "Submit for Fullpaper from $email";
    $body = "Full name: $fullName\n" .
            "Category : $category\n" .
            "Institution : $institution\n" .
            "Sub-Institution: $subInstitution\n" .
            "Phone Number: $phone\n" .
            "Email    : $email\n" .
            "Country  : $country\n" ;

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
        $mail->setFrom('info@iicacs.com', $name.' Collection : '.$category); //GANTI INI JUDUL EMAILNYA NI
        $mail->addReplyTo($email, $fullName);
        $mail->addAddress($to);

        // tambahin lampiran file yang dikirim dari CURL
        $files = ['abstract_path', 'fullpaper_path', 'transfer_path'];
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

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;
        // kalo mau pake html
        //     $mail->Body = '
        //     <html>
        //     <head>
        //         <title>Job Application</title>
        //     </head>
        //     <body>
        //         <h2>' . $faviconImg . ' Job Application from ' . $fullName . '</h2>
        //         <p>Full name: ' . $fullName . '</p>
        //         <p>Category: ' . $category . '</p>
        //         <p>Institution: ' . $institution . '</p>
        //         <p>Sub-Institution: ' . $subInstitution . '</p>
        //         <p>Phone Number: ' . $phone . '</p>
        //         <p>Email: ' . $email . '</p>
        //         <p>Country: ' . $country . '</p>
        //     </body>
        //     </html>
        // ';
        $mail->send();
        echo "Email has been sent successfully";
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
