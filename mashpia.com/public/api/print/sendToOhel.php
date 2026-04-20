<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function createDocraptorDocument($html) {
    $docraptor = new DocRaptor\DocApi();
    $docraptor->getConfig()->setUsername("CIrbbDsV2QqOc-ULQnQv");
    $docraptor->getConfig()->setDebug(true);

    $doc = new DocRaptor\Doc();
    $doc->setTest(true);                                                   // test documents are free but watermarked
    $doc->setDocumentContent($html);     // supply content directly
    // $doc->setDocumentUrl("http://docraptor.com/examples/invoice.html"); // or use a url
    $doc->setName(time() . ".pdf");                                    // help you find a document later
    $doc->setDocumentType("pdf");                                          // pdf or xls or xlsx
    $doc->setJavascript(true);                                          // enable JavaScript processing
    // $prince_options = new DocRaptor\PrinceOptions();                    // pdf-specific options
    // $doc->setPrinceOptions($prince_options);
    // $prince_options->setMedia("screen");                                // use screen styles instead of print styles
    // $prince_options->setBaseurl("https://mashpia.com");                    // pretend URL when using document_content

    $create_response = $docraptor->createDoc($doc);
    
    // save the response to a file
    $fileName = "https://mashpia.com/api/print/duch_pdf/" . $doc->getName();
    file_put_contents($fileName, $create_response->getData());
    return $fileName;
}

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
    return false;
}

// Check if a html was uploaded successfully
/*
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
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
} else 
*/
if (isset($_POST['html'])) {
    $html = $_POST['html'];
    $file = createDocraptorDocument($html);   
    $error = emailToOhel($file);
    echo json_encode(['success' => !$error, 'error' => $error, 'message' => 'File has been emailed to the Ohel.']);
} else {
    echo json_encode(['success' => false, 'error' => 'No file or html uploaded or an error occurred.', 'message' => null]);
}