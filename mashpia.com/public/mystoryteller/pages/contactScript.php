<?
//echo "<pre>"; print_r($_POST); echo "</pre>"; 
$error = '';

$to = 'mystoryteller@sbcglobal.net';
$cc = 'motherofperl@sbcglobal.net';
//$to = 'naftolir@gmail.com';
$from = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
	$error = "Invalid email address. Please try again.";
}

$subject = htmlentities( trim( $_POST['subject'] ), ENT_QUOTES, 'UTF-8'); 
$message = htmlentities( trim( $_POST['message'] ), ENT_QUOTES, 'UTF-8' );
$name = htmlentities( trim( $_POST['name'] ), ENT_QUOTES, 'UTF-8' );

if (empty($error)) {
	$headers = 'From: ' . $from . "\r\n";
	$headers .= 'CC: ' . $cc . "\r\n";
	if (mail($to, $subject, $message, $headers)) {
		echo 1;
	} else {
		echo 0;
	}
} else {
	echo $error;
}
?>