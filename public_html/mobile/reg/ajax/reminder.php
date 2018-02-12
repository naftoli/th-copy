<?
require '../../../db.php';

$email = mysql_real_escape_string($_POST['email']);
$sql = "select username, password, first, last from admins where admin_email = '" . $email . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$username = $row['username'];
	$password = $row['password'];
	$name = $row['first'] . ' ' . $row['last'];
	
	//mail to user
	$to = $email;
	$subject = "Chayolei Tzivos Hashem Account Info";
	
	$msg = "Hi <b>" . $name . "</b>, here is your account information:<br />";
	$msg .= "username: " . $username . "<br /> password: " . $password . "<br />";
	$msg .= "You can click <a href='mashpia.com/mobile'>here</a> to login.";
	
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= "From: cth@tzivoshashem.org" . "\r\n";
	$headers .= "Reply-to: cth@tzivoshashem.org" . "\r\n";
	
	if (mail($to, $subject, $msg, $headers)) {
		echo 1;
	} else {
		echo 0;
	}
} else {
	echo 0;
}
