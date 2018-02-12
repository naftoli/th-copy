<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if ($_POST["action"] == "inputimg") {
	// 'images' refers to your file input name attribute
	if (empty($_FILES['images'])) {
		echo json_encode(array('error'=>'No files found for upload.')); 
		// or you can throw an exception 
		return; // terminate
	}
	 
	// get the files posted
	$images = $_FILES['images'];
	 
	// get user id posted
	//$userid = empty($_POST['userid']) ? '' : $_POST['userid'];
	 
	// get user name posted
	//$username = empty($_POST['username']) ? '' : $_POST['username'];
	 
	// a flag to see if everything is ok
	$success = true;
	 
	// file paths to store
	$paths = array();
	 
	// get file names
	$filenames = $images['name'];
	//echo($filenames);
	 
	// loop and process files
	for($i=0; $i < count($filenames); $i++){
		$target = "parents/" . basename($filenames[$i]);		
		if (move_uploaded_file($images['tmp_name'][$i], $target)) {
			$paths[] = $target;
			try {
				//create thumb from image
				$image = new Imagick($target);
				
				//$width = $image->getImageWidth();
				//$height = $image->getImageHeight();
				//if ($width > $height) {
					//rotate left 90 degrees
					//$image->rotateImage(new ImagickPixel(), -90);
				//}
				
				$image->thumbnailImage( 250, 0 );
				$image->writeImage( $target );
				$image->destroy();
			} catch (ImagickException $e) {}
		} else {
			$success = false;
			break;
		}
	}

	// check and process based on successful status 
	if ($success === true) {
		$output = $paths[0];

		// for example you can get the list of files uploaded this way
		// $output = ['uploaded' => $paths];
	} elseif ($success === false) {
		$output = array('error'=>'Error while uploading images. Contact the system administrator');
		// delete any uploaded files
		foreach ($paths as $file) {
			unlink($file);
		}
	} else {
		$output = array('error'=>'No files were processed.');
	}
	 
	// return a json encoded response for plugin to process successfully
	echo json_encode($output);
	//$conn->close();
//
}
?>