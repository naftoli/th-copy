<?
$imagePath = urldecode($_GET['img']);
$startX = -1 * $_GET['x'];
$startY = -1 * $_GET['y'];
$width = $_GET['w'];
$height = $_GET['h'];

try {
    $image = new Imagick($imagePath);
    $image->cropImage($width, $height, $startX, $startY);
	$image->thumbnailImage( 150, 0 );
	$image->writeImage( $imagePath );
	$image->destroy();
} catch (ImagickException $e) {
	echo $e->getMessage();
}

?>
<html>
	<head>
		
	</head>
	<body>
		<img src="<?=$imagePath?>" />
	</body>
</html>