<?php
// Suppress any output before JSON
ob_start();
require '../../../db.php';

function thumb( $img ) {
	$handle = fopen($img, 'r');
	try {
		$image = new Imagick();
		$image->readImageFile( $handle );
		$width = $image->getImageWidth();
		if ($width > 150) {
			$image->thumbnailImage( 150, 0 );
			$image->writeImage( $img );
		}
		$image->destroy();
	} catch (ImagickException $e) {
		//echo $e->getMessage();
	}
}

$admin = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$sql = "select * from admins where admin_id = " . $admin;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	if (!empty( $row['father_pic'] )) {
		$fileFather = '../' . $row['father_pic'];
		if (file_exists($fileFather))
			thumb($fileFather);
	}
	if (!empty( $row['mother_pic'] )) {
		$fileMother = '../' . $row['mother_pic'];
		if (file_exists($fileMother))
			thumb($fileMother);
	}
	$row['password'] = '*********';
	
	// Clear any error output and send clean JSON
	ob_end_clean();
	echo json_encode( $row );
} else {
	ob_end_clean();
	echo -1;
}
?>