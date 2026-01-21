<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message, $isHtml = true, $attachments = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->setFrom('cth@mashpia.com', 'Chayolei Tzivos Hashem');
        $mail->addReplyTo('cth@mashpia.com', 'Chayolei Tzivos Hashem');
        $mail->addAddress($to);
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $message;
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }
        $mail->send();
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
    return 0;
}