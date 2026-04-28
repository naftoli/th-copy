<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

/**
 * Email one PDF to the Ohel address (used by duch.php after client-side merge).
 *
 * @return string|false PHPMailer error string on failure, false on success
 */
function emailToOhel($filePath) {
    $mail = new PHPMailer(true);
    $msg  = "<html><body>
        Please find the attached Duch PDF.<br /><br />
        To Unsubscribe please click <a href='http://mashpia.com/unsubscribe.php'>here</a>.<br /><br />
        Click <a href='http://mashpia.com/privacy.html'>here</a> for our Privacy Policy.<br /><br />
        Thank you,<br />Tzivos Hashem Team
    </body></html>";

    try {
        $mail->setFrom('cth@mashpia.com', 'Chayolei Tzivos Hashem');
        $mail->addAddress('naftoli@tzivoshashem.org');
        $mail->addReplyTo('cth@tzivoshashem.org', 'Chayolei Tzivos Hashem');
        $mail->isHTML(true);
        $mail->Subject = 'Duch';
        $mail->Body    = $msg;

        if ($filePath && file_exists($filePath)) {
            $mail->addAttachment($filePath);
        } else {
            return 'No attachment file.';
        }

        $mail->send();
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }

    if ($filePath && file_exists($filePath)) {
        @unlink($filePath);
    }

    return false;
}

if (empty($_FILES['pdf']) || !is_uploaded_file($_FILES['pdf']['tmp_name'])) {
    echo json_encode(['success' => false, 'error' => 'No PDF uploaded.', 'message' => null]);
    exit;
}

$uploadDir = __DIR__ . '/duch_pdf/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$safeName = 'duch_upload_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
$path = $uploadDir . $safeName;

if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $path)) {
    echo json_encode(['success' => false, 'error' => 'Could not save uploaded PDF.', 'message' => null]);
    exit;
}

$fh = fopen($path, 'rb');
$magic = $fh ? fread($fh, 4) : '';
if ($fh) {
    fclose($fh);
}
if ($magic !== '%PDF') {
    @unlink($path);
    echo json_encode(['success' => false, 'error' => 'Uploaded file is not a valid PDF.', 'message' => null]);
    exit;
}

$emailError = emailToOhel($path);

if ($emailError) {
    echo json_encode(['success' => false, 'error' => $emailError, 'message' => null]);
} else {
    echo json_encode([
        'success' => true,
        'error'   => null,
        'message' => 'Duch PDF emailed successfully.',
    ]);
}
