<?php
ini_set('display_errors',1);
require_once '../../../vendor/autoload.php';

// Create the Transport
$transport = (new Swift_SmtpTransport('mail.mashpia.com', 465))
  ->setUsername('_mainaccount@mashpia.com')
  ->setPassword('Chayolei@Th5778')
;

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

// Create a message
$message = (new Swift_Message('Wonderful Subject'))
  ->setFrom(['john@doe.com' => 'John Doe'])
  ->setTo(['naftoli@tzivoshashem.org'])
  ->setBody('Here is the message itself')
  ;

// Send the message
$result = $mailer->send($message);
