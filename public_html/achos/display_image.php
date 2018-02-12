<?php 
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

$query = "SELECT file_data FROM users JOIN files ON files.file_id=users.user_photo_id WHERE users.user_id=6053";
$image = mysql_fetch_assoc(mq($query));

$string_image = ImageCreateFromString($image['file_data']);
$width = imagesx($string_image);
$height = imagesy($string_image);

//echo "WIDTH:" . $width . " HEIGHT:" . $height . " <br />";

//$new_image = imagecreatetruecolor(150,600);
//ImageCopyResampled($new_image, $string_image, 0, 0, 0, 0, 150, 600, $width, $height);


header("Content-type: image/jpeg");
header('Content-Length: ' . strlen($image['file_data']));
//imagejpeg($new_image, null, 100);
echo $image['file_data'];


?>
	
	<script>
	</script>
		
	<body>
		BODY
	</body>

</html>
