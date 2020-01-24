<?php
$email = "naftoli@tzivoshashem.org";
$trans_id = 111111111;
$amount = 50.5;
// send email confirmation
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';
$headers[] = 'From: chidon@tzivoshashem.com';
$headers[] = 'Reply-To: chidon@tzivoshashem.com';

$root = $_SERVER['DOCUMENT_ROOT'];
if ( $root == 'chidondrive.com' ) $img = "http://" . $root . "/site/img/Chidon-Drive-5780-email-header.jpg";
else $img = "http://" . $root . "chidonOld/chidon_drive/site/img/Chidon-Drive-5780-email-header.jpg";

$subject = "Chidon Drive Donation";
$message = "<img src='$img' style='max-width: 100%; height: auto;' />";
$message .= "<p>Thank you for your generous donation of $" . number_format( $amount, 2 ) . ".</p><p>Your support enables us to show our children how meaningful their learning is, and to drive them to go mechayil el choyil.</p>";
$message .= "<p>Your transaction id is: " . $trans_id . "</p>";
@mail( $email, $subject, $message, implode("\r\n", $headers) );
?>