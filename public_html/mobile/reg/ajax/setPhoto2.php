<?
require '../../../db.php';
$file = mysql_real_escape_string( $_POST['photo'] );

$target = "../" . $file;
try {
	//create thumb from image
	$image = new Imagick( $target );
	$width = $image->getImageWidth();
	if ($width > 250) {	
		$image->thumbnailImage( 250, 0 );
		$image->writeImage( $target );
	}
	$image->destroy();
} catch (ImagickException $e) {}
?>