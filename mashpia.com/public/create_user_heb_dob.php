<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<?
		$id = $_GET['id'];
		require_once 'db.php';
		require_once 'class.birthday.php';
		require_once 'class.birthdayYi.php';
		
		$b = new Birthday($id);
		$b->setBirthday();
		$by = new BirthdayYi($id);
		$by->setBirthday();
		
		$errors = $b->getErrors();
		if ($errors) {
			echo "Number of Errors: " . count($errors) . "<br />";
		    echo "<pre>";
		    print_r($errors);
		    echo "</pre>";
		}
		?>
	</body>
</html>