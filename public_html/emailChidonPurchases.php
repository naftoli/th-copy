<?
$admin_auth = array('school'); 
require('header.php');

$purchases = array();
$sql = "select * from chidon where year = 5775";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$purchases[] = $row;
}

//echo "<pre>"; print_r($purchases); echo "</pre>";

$errors = array();
foreach ($purchases as $purchase) {
	$tickets = '';
	if ($purchase['mqty'] > 0) {
		$tickets .= $purchase['mqty'] . " Men Tickets for the Boys Chidon.<br />";
	}
	if ($purchase['gqty'] > 0) {
		$tickets .= $purchase['gqty'] . " Women Tickets for the Boys Chidon.<br />";
	}
	if ($purchase['ggqty'] > 0) {
		$tickets .= $purchase['ggqty'] . " Women Tickets (for the Girls Chidon).<br />";
	}
	if ($purchase['chidon_reg_id'] > 0) {
		$sql = "select name, last_name from chidon_reg where chidon_reg_id = " . $purchase['chidon_reg_id'];
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$honor = $row['name'] . ' ' . $row['last_name'];
	}
	if ($purchase['chidon_reg_id2'] > 0) {
		$sql = "select name, last_name from chidon_reg where chidon_reg_id = " . $purchase['chidon_reg_id2'];
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$honor2 = $row['name'] . ' ' . $row['last_name'];
	}
	$inHonorOf = '';
	if (isset($honor)) {
		$inHonorOf .= $honor;
	}
	if (isset($honor2)) {
		if (empty($inHonorOf)) {
			$inHonorOf .= $honor2;
		} else {
			$inHonorOf .= " and " . $honor2;
		}
	}
	
	$method = $purchase['method'];
	if ($method == 'event pickup') {
$message = <<<HTML
Dear $purchase[name],
<br /><br />
Thank you for purchasing tickets to the International Chidon Sefer Hamitzvos Championships.
<br /><br />
You ordered<br />
$tickets
Honoring $inHonorOf.<br />
Your Tickets are VIP Seats.<br />
Your tickets are available for pick up at the door of the event.
<br /><br />
We look forward to welcoming you at the chidon.<br />
The Chidon Team
<br /><br />
P.S. If you have any changes that need to be made please let us know right away and don’t leave it till the last minute.
HTML;

	} else if ($method == 'jcm pickup') {
$message = <<<HTML
Dear $purchase[name],
<br /><br />
Thank you for purchasing tickets to the International Chidon Sefer Hamitzvos Championships.
<br /><br />
You ordered<br />
$tickets
Honoring $inHonorOf.<br />
Your Tickets are VIP Seats.<br />
Your tickets are available for pick up in the JCM from 9am to 5pm on Thursday and 9 am to 1 pm on Friday or at the door.
<br /><br />
We look forward to welcoming you at the chidon.<br />
The Chidon Team
<br /><br />
P.S. If you have any changes that need to be made please let us know right away and don’t leave it till the last minute.
HTML;

	} else if ($method == 'ship') {
$message = <<<HTML
Dear $purchase[name],
<br /><br />
Thank you for purchasing tickets to the International Chidon Sefer Hamitzvos Championships.
<br /><br />
You ordered:<br />
$tickets
Honoring $inHonorOf.<br />
Your tickets are VIP Seats.<br />
Your tickets have been / will be shipped to:<br />
{$purchase['address']}<br />
{$purchase['city']}, {$purchase['state']} {$purchase['zip']}
<br /><br />
We look forward to welcoming you at the chidon.<br />
The Chidon Team
<br /><br />
P.S. If you have any changes that need to be made please let us know right away and don’t leave it till the last minute.
HTML;
	}
	
	//echo $message . "<br /><br />";
	//$to = "$row[email]";
	$to = "chidon@tzivoshashem.org";
	$subject = "Your Chidon Tickets";
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= "From: <chidon@tzivoshashem.org>" . "\r\n";
	$headers .= "CC: <naftolir@gmail.com>" . "\r\n";
	if (!mail($to, $subject, $message, $headers)) {
		$errors[] = "Error mailing to " . $to . ".<br /> The following message was not sent:<br />" . $message;
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	<body>
		<?
		if (!empty($errors)) {
			foreach ($errors as $error) {
				echo $error . "<br /><br />";
			}
		}
		?>
	</body>
</html>