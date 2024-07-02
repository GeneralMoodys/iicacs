<?php

error_reporting(0);
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

    $to = "projectiicacs@gmail.com"; // Replace with the actual recipient email address
    $subject = "Submit for $category from $email";
    $body = "Full name: $fullName\n" .
            "Category : $category\n" .
            "Institution : $institution\n" .
            "Sub-Institution: $subInstitution\n" .
            "Phone Number: $phone\n" .
            "Email    : $email\n" .
            "Country  : $country\n" ;

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host = 'mail.iicacs.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dev@iicacs.com'; // Replace with your SMTP username
        $mail->Password = '@kudil123'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email sender and recipient settings
        $mail->setFrom('info@iicacs.com', $category); 
        $mail->addReplyTo($email, $fullName);
        $mail->addAddress($to);

        // Attach the abstract file if the category is "Presenter"
        if ($category === 'Presenter' && isset($_FILES['abstract_path']) && $_FILES['abstract_path']['error'] == UPLOAD_ERR_OK) {
            $abstractFilePath = $_FILES['abstract_path']['tmp_name'];
            $abstractFileName = $_FILES['abstract_path']['name'];
            $mail->addAttachment($abstractFilePath, $abstractFileName);
        }

        // Email subject and body
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email
        $mail->send();
        echo "Email has been sent successfully";
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
