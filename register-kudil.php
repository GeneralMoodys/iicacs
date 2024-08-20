<?php
set_time_limit(900);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


$mail = new PHPMailer(true);
// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
$mail->SMTPDebug = 2;
$mail->isSMTP();
// $mail->Host = 'mail.iicacs.com';
$mail->Host = 'purbayan.idweb.host';
$mail->SMTPAuth = true;
$mail->Username = 'info@iicacs.com';
$mail->Password = '#iicacs2024#'; 
// $mail->SMTPSecure = 'tls';
$mail->Port = 465;

// Set pengirim dan penerima
$mail->setFrom('info@iicacs.com', 'INFO | iicacs.com');
$mail->addAddress('fujiantoch@gmail.com', 'Fujianto');

// Konten email
$mail->isHTML(true);
$mail->Subject = 'Subject of Email';
$mail->Body = 'Body of the Email';

// Lampiran
// $attachment_files = [
//     '/path/to/attachment1.pdf', // Ganti dengan path file
//     '/path/to/attachment2.docx' // Ganti dengan path file 
// ];

// foreach ($attachment_files as $file_path) {
//     $mail->addAttachment($file_path);
// }

// Kirim email
try {
    $mail->send();
    echo 'Email berhasil dikirim';
} catch (Exception $e) {
    echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
}
?>