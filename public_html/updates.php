<?
$admin_auth = array('school');
require_once 'header.php';
?>
<html>
	
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	
	<body>
		<? require_once 'admin_header.php';	?>
		<h1>Updates</h1>
		<?
		
		// get latest 3 postings from db
		$sql = "select * from weekly_emails order by date desc limit 3";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 0) {
			echo "No records found.";
			exit;
		}
		
		// display postings
		while ($row = mysql_fetch_assoc($result)) {
			echo "Date: " . $row['date'] . "<br />";
			echo "Subject: " . $row['subject'] . "<br />";
			echo $row['email'] . "<br />";
			
			//get attachments if there are any
			$sql2 = "select * from attachments where email_id = " . $row['email_id'];
			$result2 = mysql_query($sql2);
			if (mysql_num_rows($result2) > 0) {
				while ($attachment = mysql_fetch_assoc($result2)) {
					echo "Attachment: <a href='" . $attachment['src'] . "'>" . $attachment['src'] . "</a><br />";
				}
			}
			echo "<br /><hr />";
		}
		?>
	</body>
	
</html>
