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
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.mashpia.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = '_mainaccount@mashpia.com';             //SMTP username
            $mail->Password   = 'Naftoli8770!';                         //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
//            $mail->setFrom('from@example.com', 'Mailer');
//            $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
//            $mail->addAddress('ellen@example.com');               //Name is optional
//            $mail->addReplyTo('info@example.com', 'Information');
//            $mail->addCC('cc@example.com');
//            $mail->addBCC('bcc@example.com');

            //Attachments
//            $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
//            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
//            $mail->isHTML(true);                                  //Set email format to HTML
//            $mail->Subject = 'Here is the subject';
//            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
//            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

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