<?php
include_once '../ajax/sendEmail.php';
$amount = 50.5;
$trans_id = 111111;
$email = 'naftoli@tzivoshashem.org';
$name = 'naftoli rapoport';
if ( sendEmail( $amount, $trans_id, $email, $name ) ) echo "Sent.";
else echo "Error.";