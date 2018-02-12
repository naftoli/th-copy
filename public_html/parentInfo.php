<?
$admin_auth = array('school'); 
require_once('header.php'); 
if ($admin_user['auth'] != 'super') {
	echo "You have no permission to view this page.";
	exit;
}

$info['emails'] = array();
$info['numbers'] = array();

$sql = "select a.admin_email, a.admin_phone_mobile from admins a 
		join admin_auths aa using (admin_id) 
		join users u on u.user_id = aa.id";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$email = $row['admin_email'];
	$phone = $row['admin_phone_mobile'];
	
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		if (array_search($email, $info['emails']) === false) {
			$info['emails'][] = $email;
		}
	}
	
	if (array_search($phone, $info['numbers']) === false) {
		$info['numbers'][] = $phone;
	}
}
//echo "<pre>"; print_r($info); echo "</pre>";
if (isset($_POST['submit'])) {
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'Reply-to: cth@tzivoshashem.org' . "\r\n";
	$headers .= 'From: cth@tzivoshashem.org' . "\r\n";
	
	$subject = $_POST['subject'];
	$msg = $_POST['msg'];
	$to = implode(';', $info['emails']);
	
	//echo $subject . "<br />";
	//echo $msg . "<br />";
	//echo $to;
	mail($to, $subject, $msg, $headers);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Parent Email / Phone List</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			.right {
				float: right;
				width: 35%;
				font-size: 12px;
			}
			.left {
				float: left;
				width: 60%;
				font-size: 14px;
			}
			.email {
				font-size: 16px;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Parent Email / Phone List</h1>
		
		<div class='left'>
			<div>
				<?
				foreach ($info['emails'] as $email) {
					echo $email . "; ";
				}
				?>
			</div>
			<div class="email">
				<br /><br />
				<form action="parentInfo.php" method="post">
					You can send an email to all of the above parents by entering the form below and clicking "Send Email".<br /><br />
					Subject:<br />
					<input size="50" type="text" name="subject" value="Email from Chayolei Tzivos Hashem" /><br /><br />
					Message:<br />
					<textarea rows="10" cols="60" name="msg"></textarea><br />
					<input type="submit" name="submit" value="Send Email" />
				</form>
			</div>
		</div>
		<div class='right'>
			<?
			foreach ($info['numbers'] as $number) {
				echo $number . "<br />";
			}
			?>
		</div>
	</body>
</html>