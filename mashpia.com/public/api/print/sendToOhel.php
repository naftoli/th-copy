<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function emailToOhel($fileName = null, $html = null) {
    // send the pdf to ohel
    $mail = new PHPMailer(true);
    $msg = "<html>
    <body>
        Please find the attached Duch PDF.<br /><br />
        To Unsubscribe please click <a href='http://mashpia.com/unsubscribe.php'>here</a>.<br /><br />
        Click <a href='http://mashpia.com/privacy.html'>here</a> for our Privacy Policy.<br /><br />
        Thank you,<br />
        Tzivos Hashem Team
    </body>
    </html>";
    try {
        $mail->setFrom('cth@mashpia.com', 'Chayolei Tzivos Hashem');
        // $mail->addAddress('ohel@ohelchabad.org');
        // $mail->addBCC('naftoli@tzivoshashem.org');
        $mail->addAddress('naftoli@tzivoshashem.org');
        $mail->addReplyTo('cth@tzivoshashem.org', 'Chayolei Tzivos Hashem');
        $mail->isHTML(true);
        $mail->Subject = 'Duch';
        $mail->Body = $html ? $html : $msg;
        if ($fileName) $mail->addAttachment($fileName);
        $mail->send();
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
    return 0;
}

// Check if a html was uploaded successfully
if (isset($_POST['html'])) {
    $html = $_POST['html'];
    $error = emailToOhel(null, $html);
} else if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Check if a file was uploaded successfully
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
    echo json_encode(['success' => false, 'error' => 'No file or html uploaded or an error occurred.', 'message' => null]);
}