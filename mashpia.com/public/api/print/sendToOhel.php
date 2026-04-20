<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function fixRelativeUrls($html) {
    $base = 'https://mashpia.com';

    // Fix root-relative src and href (e.g. src="/scripts/..." => src="https://mashpia.com/scripts/...")
    // Handles both double and single quotes
    $html = preg_replace('/\bsrc="(\/(?!\/)[^"]*)"/', 'src="' . $base . '$1"', $html);
    $html = preg_replace('/\bhref="(\/(?!\/)[^"]*)"/', 'href="' . $base . '$1"', $html);
    $html = preg_replace("/\\bsrc='(\\/(?!\\/)[^']*)'/", "src='" . $base . "$1'", $html);
    $html = preg_replace("/\\bhref='(\\/(?!\\/)[^']*)'/", "href='" . $base . "$1'", $html);

    return $html;
}

function createDocraptorDocument($html) {
    // Fix all root-relative URLs before sending to DocRaptor
    $html = fixRelativeUrls($html);

    $docraptor = new DocRaptor\DocApi();
    $docraptor->getConfig()->setUsername("CIrbbDsV2QqOc-ULQnQv");
    $docraptor->getConfig()->setDebug(true);

    $doc = new DocRaptor\Doc();
    $doc->setTest(true);                        // test documents are free but watermarked
    $doc->setDocumentContent($html);            // supply content directly
    $doc->setName(time() . ".pdf");             // help you find a document later
    $doc->setDocumentType("pdf");               // pdf or xls or xlsx
    $doc->setJavascript(true);                  // enable JavaScript processing

    $prince_options = new DocRaptor\PrinceOptions();
    $prince_options->setBaseurl("https://mashpia.com");  // pretend URL when using document_content
    $doc->setPrinceOptions($prince_options);

    $create_response = $docraptor->createDoc($doc);

    // Save the PDF to a local file
    $uploadDir = __DIR__ . '/duch_pdf/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = $uploadDir . $doc->getName();
    file_put_contents($fileName, $create_response->getData());

    return $fileName;
}

function emailToOhel($fileName = null, $html = null) {
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

if (isset($_POST['html'])) {
    $html = $_POST['html'];
    $file = createDocraptorDocument($html);
    $error = emailToOhel($file);
    echo json_encode(['success' => !$error, 'error' => $error, 'message' => 'File has been emailed to the Ohel.']);
} else {
    echo json_encode(['success' => false, 'error' => 'No file or html uploaded or an error occurred.', 'message' => null]);
}