<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "mnprasetya@gmail.com";
    $subject = "Job Application";
    $message = "Full name: " . $_POST['full_name'] . "\n" .
               "Category: " . $_POST['category'] . "\n" .
               "Institution: " . $_POST['Institution'] . "\n" .
               "Sub-Institution: " . $_POST['Sub-Institution'] . "\n" .
               "Phone Number: " . $_POST['Phone'] . "\n" .
               "Email: " . $_POST['email'] . "\n" .
               "Country: " . $_POST['country'] . "\n";

    $headers = "From: " . $_POST['email'] . "\r\n";
    $headers .= "Reply-To: " . $_POST['email'] . "\r\n";

    $attachments = [];

    // Handle file attachments
    $files = ['abstract', 'fullpaper', 'transfer'];
    foreach ($files as $file) {
        if (isset($_FILES[$file]) && $_FILES[$file]['error'] == UPLOAD_ERR_OK) {
            $fileName = $_FILES[$file]['name'];
            $fileTmpName = $_FILES[$file]['tmp_name'];
            $fileType = $_FILES[$file]['type'];
            $fileContent = file_get_contents($fileTmpName);

            $boundary = md5(time());

            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

            $message .= "--$boundary\r\n";
            $message .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
            $message .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($fileContent)) . "\r\n";
        }
    }

    $message .= "--$boundary--";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo "Email successfully sent to $to";
    } else {
        echo "Email sending failed";
    }
}
?>
