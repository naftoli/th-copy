<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function emailToOhel($fileName) {
    // send the pdf to ohel
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.mashpia.com';                     //Set the SMTP server to send through
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->setFrom('admin@mashpia.com', 'Mashpia');
        // $mail->addAddress('ohel@ohelchabad.org');
        $mail->addAddress('naftoli@tzivoshashem.org');
        $mail->Subject = 'Duch';
        $mail->Body = 'Please find the attached Duch PDF';
        $mail->addAttachment($fileName);
        $mail->send();
        return false;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

// Check if a file was uploaded successfully
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']); // Sanitize filename if needed
    $uploadDir = './duch_pdf/'; // Specify your target directory on the server

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destPath = $uploadDir . $fileName;

    // Move the file from the temporary location to the desired folder
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // email the file to the Ohel
        $error = emailToOhel($destPath);
        if ($error) {
            echo json_encode(['success' => false, 'error' => $error, 'message' => null]);
        } else {
            echo json_encode(['success' => true, 'error' => null, 'message' => 'File has been emailed to the Ohel.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to move file. Check directory permissions.', 'message' => null]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or an error occurred.', 'message' => null]);
}