<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// send the pdf to ohel
$mail = new PHPMailer(true);
try {
    $mail->setFrom('admin@mashpia.com', 'Mashpia');
    // $mail->addAddress('ohel@ohelchabad.org');
    $mail->addAddress('naftoli@tzivoshashem.org');
    $mail->Subject = 'Duch';
    $mail->Body = 'Please find the attached Duch PDF';
    $mail->addAttachment('duch_pdf/duch.pdf');
    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
}