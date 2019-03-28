<?php
require('db.php');
include('SimpleImage.php');

$id = 0;

if (isset($_FILES['image'])) {

	foreach ($_FILES as $file) { 
	
		if ($file['tmp_name'] > '') { 
		
			$random_number = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
			$file_name = 'imagecache/' . $random_number . '.jpg';
								
			$image = new SimpleImage();
			$image->load($file['tmp_name']);
			$image->resize(75,75);
			$image->save($file_name);					

			if(mysql_result(mq("SELECT GET_LOCK('files', 30)"),0) != 1) 
				trigger_error('could not get lock', E_USER_ERROR);
								
				$count = 0;
				do {
					if ($count++ > 100000) 
						trigger_error('could not get ID', E_USER_ERROR);
									
					$id = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
				} while (mysql_result(mq("SELECT COUNT(*) FROM files WHERE file_id = $id"),0) != 0);
							
				echo "<input type='hidden' name='FILE ID' value='" . $id . "'>\n";
							
				mq("INSERT INTO files (file_id, file_name, file_content_type, file_data) VALUES ($id, " . ms($file['name']) . ', ' . ms(mime_content_type($file['tmp_name'])) . ', ' . ms(file_get_contents($file_name)) . ')');
				mq("SELECT RELEASE_LOCK('files')");
							
				unlink($file_name);
		
		}
		
	}
	
}
else {
	echo "<input type='hidden' name='FILE ID' value='NOT SET'>\n";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
	
	<head>
	</head>
	
	<body>
		<img SRC='/file_view.php?id=1228072188'>

		<form method="post" action="image_resize.php" accept-charset="UTF-8" enctype="multipart/form-data">
			<INPUT type="file" name="image" id="image" class="file">
			<input type="submit" value="SAVE">
		</form>
		
		<? if ($id > 0) : ?>
			<img SRC='file_view.php?id=<?=$id;?>'>
		<? endif; ?>
	</body>
	
</html>