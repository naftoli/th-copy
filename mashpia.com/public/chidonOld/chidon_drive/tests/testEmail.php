<?php
include_once '../ajax/sendEmail.php';
$amount = 50.5;
$trans_id = 111111;
$email = 'naftoli@tzivoshashem.org';
if ( sendEmail( $amount, $trans_id, $email ) ) echo "Sent.";
else echo "Error.";