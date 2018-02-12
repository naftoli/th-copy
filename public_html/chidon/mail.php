<style type="text/css">

body {

	background-color: #FFF;

	background-image: url(images/main_bg.gif);

	background-repeat: repeat;

}

body,td,th {

	font-family: Arial, Helvetica, sans-serif;

	color: #FFF;

	text-align: center;

}

.b {

	color: #000;

}

</style>

<body class="b"><a href="http://CHIdon613.com"><img src="images/chidon_logo.png" width="166" height="163" /></a>

<br />

<?php

$school = $_POST['school'];

$name = $_POST['name'];

$email = $_POST['email'];

$phone = $_POST['phone'];

$message = $_POST['message'];

$formcontent=" School: $school \n From: $name \n Phone: $phone \n Message: $message";

$recipient = "chidon@tzivoshashem.org";

$subject = "The Chidon Contact Form";

$mailheader = "From: $email \r\n";

mail($recipient, $subject, $formcontent, $mailheader) or die("Error!");

echo "We have received your message :)



We will endeavour to reply to you shortly." . " -" . "<a href='http://chidon613.com' style='text-decoration:none;color:#7ac143;'> Click Here Return Home</a>";

?>

