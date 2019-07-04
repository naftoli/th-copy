<?php
ini_set('display_errors',1);
require_once '../../vendor/autoload.php';

$transport = (new Swift_SmtpTransport('smtp.mashpia.com', 465))
  ->setUsername('_mainaccount@mashpia.com')
  ->setPassword('9pGftnyx;yvczPx6')
;

$mailer = new Swift_Mailer( $transport );

$message = (new Swift_Message("Just Testing"))
  ->setFrom("cth@mashpia.com")
  ->setTo("naftoli@tzivoshashem.org")
  ->setBody("Just a test.")
;

$mailer->send( $message );