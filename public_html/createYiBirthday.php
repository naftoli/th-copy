<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<?
		require_once 'db.php';

		$users = array();
		$sql = "select user_id from users 
				where user_registered > 0";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$users[] = $row['user_id'];
		}

		require_once 'class.birthdayYi.php';
		foreach ($users as $id) {
			$b = new BirthdayYi($id);
			$b->setBirthday();
			
			$errors = $b->getErrors();
			if ($errors) {
				echo "Number of Errors: " . count($errors) . "<br />";
				echo "<pre>";
				print_r($errors);
				echo "</pre>";
			}
		}
		echo "Done.";
		?>
	</body>
</html>