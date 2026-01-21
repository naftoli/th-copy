<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message, $isHtml = true, $attachments = []) {
    // add footer to message
    $message = addFooterToMessage($message);
    // send email
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

function addFooterToMessage($message) {
    $msg_footer = <<<FOOTER
    <br /><br />
    <div align="center">
    &copy; 2019 Tzivos Hashem<br />
    <address>
      792 Eastern Pkwy, Brooklyn, NY 11213
    </address>
    <br />
    <a href="http://mashpia.com/privacy.html">Privacy Policy</a><br />
    To unsubscibe from these emails please click <a href="http://mashpia.com/unsubscribe.html">here</a><br />
    </div>
    FOOTER;
    $message .= $msg_footer;
    return $message;
}