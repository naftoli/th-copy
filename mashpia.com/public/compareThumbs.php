<?
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('max_execution_time', 500);
require 'db.php';

$files = array();
$sql = "select file_id, file_content_type  
		from files f 
		join users u on u.user_photo_id = f.file_id  
		where u.user_registered > 0 
		limit 0,50";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$files[] = $row;
}

foreach ($files as $index => $file) {
	$type = $file['file_content_type'];
	$pos = strrpos($type, '/');
	$ext = substr($type, $pos+1);
	$handle = fopen('http://mashpia.com/file_view.php?id=' . $file['file_id'], 'r');
	if ($handle) {
		try {
			$thumb = new Imagick();
			$thumb->readImageFile( $handle );
			$thumb->thumbnailImage( 100, 0 );
			$thumb->writeImage( 'thumbs/thumbA' . ($index+1) . '.' . $ext );
			$thumb->destroy();
		} catch (ImagickException $e) {
			echo $e->getMessage() . "<br />";
		}
	}
}

foreach ($files as $index => $file) {
	$type = $file['file_content_type'];
	$pos = strrpos($type, '/');
	$ext = substr($type, $pos+1);
	$handle = fopen('http://mashpia.com/file_view.php?id=' . $file['file_id'], 'r');
	if ($handle) {
		try {
			$thumb->readImageFile( $handle );
			$thumb->setImageOpacity(1.0);
			$thumb->resizeImage( 100, 0, Imagick::FILTER_LANCZOS, 1 );
			$thumb->writeImage( 'thumbs/thumbB' . ($index+1) . '.' . $ext );
			$thumb->destroy();
		} catch (ImagickException $e) {
			echo $e->getMessage() . "<br />";
		}
	}
}

foreach ($files as $index => $file) {
	$type = $file['file_content_type'];
	$pos = strrpos($type, '/');
	$ext = substr($type, $pos+1);
	$handle = fopen('http://mashpia.com/file_view.php?id=' . $file['file_id'], 'r');
	if ($handle) {
		try {
			$thumb->readImageFile( $handle );
			$thumb->setImageOpacity(1.0);
			$thumb->resizeImage( 100, 0, Imagick::FILTER_CATROM, 1 );
			$thumb->writeImage( 'thumbs/thumbC' . ($index+1) . '.' . $ext );
			$thumb->destroy();
		} catch (ImagickException $e) {
			echo $e->getMessage() . "<br />";
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="UTF-8" />
		<style>
			.img {
				float: left;
				padding: 10px;
			}
		</style>
	</head>
	
	<body>
		<?
		$j = 1;
		$types = array('A', 'B', 'C');
		for ($i = 1; $i < 51; $i++) {
			foreach ($types as $type) {
	        	echo "<div class='img'><img width='200' src='/thumbs/thumb" . $type . $i . ".jpeg' />";
				echo "<br />Filesize: " . filesize( 'thumbs/thumb' . $type . $i . '.jpeg' ) . "</div>";
				if ($j++ % 3 == 0) echo "<div style='clear:both'></div>";
			}
		}		   
		?>
	</body>
</html>