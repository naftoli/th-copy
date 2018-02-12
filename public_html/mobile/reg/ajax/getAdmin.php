<?php
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
		thumb( '../' . $row['father_pic'] );
	}
	if (!empty( $row['mother_pic'] )) {
		thumb( '../' . $row['mother_pic'] );
	}
	$row['password'] = '*********';
	echo json_encode( $row );
} else {
	echo -1;
}
?>