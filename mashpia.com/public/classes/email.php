<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

class Email {
    private $error;

    public function sendEmail(array $params) {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        try {
            if (isset($params['fromAlias'])) $mail->setFrom($params['from'], $params['fromAlias']);
            else $mail->setFrom($params['from']);
            $mail->addAddress($params['to']);
            $mail->isHTML(true);
            $mail->Subject = $params['subject'];
            $mail->Body = $params['msg'];
            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->error = $mail->errorMessage();
            return false;
        }
    }

    public function getError() {
        return $this->error;
    }
}