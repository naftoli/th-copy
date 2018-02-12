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
		if (isset($_POST['submit'])) {
			require_once 'db.php';
			
			/*
			echo "<pre>";
			print_r($_FILES);
			echo "</pre>";
			exit;
			*/
			
			//if subject and body are not empty, insert into db
			if (!empty($_POST['subject']) && !empty($_POST['body'])) {
				$subject = mysql_escape_string(trim($_POST['subject']));
				$body = mysql_escape_string(trim($_POST['body']));
				$sql = "insert into weekly_emails values(null, now(), '$subject', '$body')";
				//echo $sql;
			
				if (mysql_query($sql)) {
					//get last inserted id and find out if there is any attachments and upload to server
					$email_id = mysql_insert_id();
					$i = 0;
					//echo "size of file: " . $_FILES['attachment']['size'][$i];
					while (!empty($_FILES['attachment']['name'][$i]) 
							&& ($_FILES['attachment']['size'][$i] < 20000)) {
						$name = $_FILES['attachment']['name'][$i];
						$tmp = $_FILES['attachment']['tmp_name'][$i];
						$newName = "uploads/$name";
						if (move_uploaded_file($tmp, $newName)) {
							//insert info into attachments table
							$sql = "insert into attachments values(null, $email_id, '$newName')";
							mysql_query($sql);
						}
						$i++;
					}
					$msg = "Thank you, your email was saved into the database.";
				}
			}
			if ($msg)
				echo "<p>$msg</p>";
		}	
		?>
		<p>
			Please use the following form to upload the weekly email.
		</p>
		<div>
			<form action="admin_updates.php" enctype="multipart/form-data" method="post">
				Subject:<br /><input type="text" name="subject" size="60" /><br />
				Body:<br />
				<textarea name="body" cols="80" rows="20">
				</textarea><br />
				Attachments:<br />
				<input type="file" accept="image/*" name="attachment[]"/><br />
				<input type="file" accept="image/*" name="attachment[]"/><br />
				<input type="file" accept="image/*" name="attachment[]"/><br />
				<input type="file" accept="image/*" name="attachment[]"/><br />
				<input type="submit" name="submit" value="submit" />	
			</form>
		</div>	
	</body>
	
</html>