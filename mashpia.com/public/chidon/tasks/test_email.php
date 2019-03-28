<?php
$email = 'naftolir@gmail.com';
$subject = 'Chidon Shabbaton 5779';
$message = "Congrats! Your child is now enrolled in the shabbaton.";

$headers = 'From: chidon@tzivoshashem.com' . "\r\n" .
          'Reply-To: chidon@tzivoshashem.com' . "\r\n";
@mail( $email, $subject, $message, $headers );