<?
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

function addFile($file, $missing = NULL) {

	switch($file['error']) {
	
		case UPLOAD_ERR_INI_SIZE:
		
		case UPLOAD_ERR_FORM_SIZE:
			echo T_('File is too large.');
			exit;
		break;
		
		case UPLOAD_ERR_PARTIAL:
			echo T_('File was only partially uploaded.');
			exit;
		break;
		
		case UPLOAD_ERR_NO_FILE:
			return $missing;
		break;
		
		case UPLOAD_ERR_OK:
			if (is_uploaded_file($file['tmp_name'])) {
				
				if(mysql_result(mq("SELECT GET_LOCK('files', 30)"),0) != 1) {
					trigger_error('could not get lock', E_USER_ERROR);
				}
				
				$count = 0;
				do {
					if ($count++ > 100000) 
						trigger_error('could not get ID', E_USER_ERROR);
						
					$id = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
					
				} while (mysql_result(mq("SELECT COUNT(*) FROM files WHERE file_id = $id"),0) != 0);

        /*
          prepare, in command line as regular user:
            mkdir FILE_DIR
            chmod 2371 FILE_DIR
            sudo chown www-data FILE_DIR
        */
        /*
        //alternate: rtrim(chunk_split(strtr(rtrim(base64_encode(pack('N', floatval($id))), '='), '/', '-'), 2, '/'), '/')
        $file_path = rtrim(chunk_split(str_pad(base_convert($id, 10, 16), 8, '0', STR_PAD_LEFT), 2, '/'), '/');

        if(file_exists(FILE_DIR . $file_path)) trigger_error("$file_path exits, but is not in database", E_USER_ERROR);
        $umask = umask(0002); //problematic for multi-threading, but necessary
        if(!is_dir(dirname(FILE_DIR . $file_path)) && !mkdir(dirname(FILE_DIR . $file_path), 02371, true)) trigger_error("could not make directory for $file_path", E_USER_ERROR);
        umask($umask);
        //copy instead of move_uploaded_file to maintain group ownership of the file
        if(!copy($file['tmp_name'], FILE_DIR . $file_path)) trigger_error("could not store file $file_path", E_USER_ERROR);
        if(!chmod(FILE_DIR . $file_path, 0464)) trigger_error("could not chmod $file_path", E_USER_ERROR);

        mq("INSERT INTO files (file_id, file_name, file_content_type, file_path) VALUES ($id, " . ms($file['name']) . ', ' . ms(mime_content_type($file['tmp_name'])) . ', ' . ms($file_path) . ')');
        */
        //investigate LOAD_FILE
		
				mq("INSERT INTO files (file_id, file_name, file_content_type, file_data) VALUES ($id, " . ms($file['name']) . ', ' . ms(mime_content_type($file['tmp_name'])) . ', ' . ms(file_get_contents($file['tmp_name'])) . ')');
				mq("SELECT RELEASE_LOCK('files')");

				return $id;
			} 
			else {
				trigger_error('not uploaded file', E_USER_ERROR);
			}
		break;
		
		default:
			trigger_error("file upload error #{$file['error']}", E_USER_ERROR);
		break;
		
	}
}

function saveFile( $file, $target, $file_name, $missing = false ) {
	switch($file['error']) {
		
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			echo T_('File is too large. Please go back and try again.');
			exit;
		break;
	
		case UPLOAD_ERR_PARTIAL:
			echo T_('File was only partially uploaded. Please go back and try again.');
			exit;
		break;
	
		case UPLOAD_ERR_NO_FILE:
			return $missing;
		break;
	
	}
	// check for any other errors...
	if($file['error'] !== UPLOAD_ERR_OK) {
		return false;
	}
	// make sure it is uploaded..
	if (!is_uploaded_file($file['tmp_name'])) {
		return false;
	}
	// combine the target with the file_name
	$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
	$file_name = $file_name . "." . $ext; // add the extension to the image
	$target_file = $target . $file_name;
	
	// make sure that the file does not already exist....
	$i = 1;
	while (file_exists($target_file)) {
		$file_name_tmp = "v" . $i++ . "." . $file_name;
		$target_file = $target . $file_name_tmp;
	}
	
	if (!move_uploaded_file($file['tmp_name'], $target_file)) { // actually move it
		return false;
	}
	
	try { // the image was uploaded. so lets try to scale it down
		$image = new Imagick( $target_file );
		$image->thumbnailImage( 250, 0, false, true ); // compress the image down to a smaller size...
		// attempt to fix the rotation
		try {
			if ( in_array( exif_imagetype( $target_file ), [ IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM ] ) && exif_read_data( $target_file )) {
				$exif = @exif_read_data( $target_file ); // read the data
				$orientation = $exif['Orientation']; // get the orientation
				if($orientation){ // this will only run if orientation is set which can only happen if $_POST['action'] is set to fix
					switch($orientation) {  
						case 3: $image->rotateimage("#FFF", 180); $thumb->rotateimage("#FFF", 180); break; // upside down
						case 6: $image->rotateimage("#FFF", 90); $thumb->rotateimage("#FFF", 90); break; // rotate 90 degrees CW
						case 8: $image->rotateimage("#FFF", -90); $thumb->rotateimage("#FFF", -90);break; // rotate 90 degrees CCW
					}
				}
			}
		} catch (ImagickException $e) {} // rotation not fixed. do nothing about it.
		
		$image->writeImage( $target_file ); // write the file...
		
	} catch (ImagickException $e) {
		return false;
	} // the image was not uploaded/cropped correctly.
	
	return $target_file;
}

function addFileNew($file, $missing = NULL) {

	switch($file['error']) {
	
		case UPLOAD_ERR_INI_SIZE:
		
		case UPLOAD_ERR_FORM_SIZE:
			echo T_('File is too large.');
			exit;
		break;
		
		case UPLOAD_ERR_PARTIAL:
			echo T_('File was only partially uploaded.');
			exit;
		break;
		
		case UPLOAD_ERR_NO_FILE:
			return $missing;
		break;
		
		case UPLOAD_ERR_OK:
			if (is_uploaded_file($file['tmp_name'])) {
				
				$file_name = 'img/' . $file['name'];
				// make sure file doesn't exist
				$i = 1;
				while (file_exists($file_name)) {
					$file_name = 'img/' . $i++ . $file['name'];
				}
				$target = 'mobile/reg/' . $file_name;
				if (move_uploaded_file($file['tmp_name'], $target)) {
					try {
						//create thumb from image
						$image = new Imagick( $target );						
						$image->thumbnailImage( 250, 0 ); // is it this that is flipping the images?
						// hotfix for rotating images
						if (
						   (exif_imagetype($target) == IMAGETYPE_JPEG ||
							exif_imagetype($target) == IMAGETYPE_TIFF_II ||
							exif_imagetype($target) == IMAGETYPE_TIFF_MM ) && exif_read_data($target)
						){
							$exif = @exif_read_data($target);
							$orientation = $exif['Orientation'];
							if($orientation){ // this will only run if orientation is set which can only happen if $_POST['action'] is set to fix
								switch($orientation) {  
									case 3: // upside down
										$image->rotateimage("#FFF", 180);break;
									// these two cases pulled from stackoverflow: https://stackoverflow.com/questions/4266656/how-to-stop-php-imagick-auto-rotating-images-based-on-exif-orientation-data
									case 6: // rotate 90 degrees CW
										$image->rotateimage("#FFF", 90);break;
									case 8: // rotate 90 degrees CCW
										$image->rotateimage("#FFF", -90);break;
								}
							}
						}
						$image->writeImage( $target );
						$image->destroy();
						
					} catch (Exception $e) {}
					return $file_name;
				} else {
					return false;
				}
			} 
			else {
				trigger_error('not uploaded file', E_USER_ERROR);
			}
		break;
		
		default:
			trigger_error("file upload error #{$file['error']}", E_USER_ERROR);
		break;
		
	}
}

function addLogoNew($file, $missing = NULL) {

	switch($file['error']) {
	
		case UPLOAD_ERR_INI_SIZE:
		
		case UPLOAD_ERR_FORM_SIZE:
			echo T_('File is too large.');
			exit;
		break;
		
		case UPLOAD_ERR_PARTIAL:
			echo T_('File was only partially uploaded.');
			exit;
		break;
		
		case UPLOAD_ERR_NO_FILE:
			return $missing;
		break;
		
		case UPLOAD_ERR_OK:
			if (is_uploaded_file($file['tmp_name'])) {
				
				$file_name = $file['name'];
				// make sure file doesn't exist
				$i = 1;
				while (file_exists($file_name)) {
					$file_name = '' . $i++ . $file['name'];
				}
				$target = 'logos/' . $file_name;
				if (move_uploaded_file($file['tmp_name'], $target)) {
					try {
						//create thumb from image
						$image = new Imagick( $target );						
						$image->thumbnailImage( 250, 0 );
						$image->writeImage( $target );
						$image->destroy();
						
					} catch (ImagickException $e) {}
					return $file_name;
				} else {
					return false;
				}
			} 
			else {
				trigger_error('not uploaded file', E_USER_ERROR);
			}
		break;
		
		default:
			trigger_error("file upload error #{$file['error']}", E_USER_ERROR);
		break;
		
	}
}

function delFiles($file_id) {
  if(!$file_id) return;
  $row = mysql_fetch_assoc(mq("SELECT file_id, file_path FROM files WHERE file_id = $file_id"));
  if(!$row) return;
  if(!is_null($row['file_path'])) {
    unlink(FILE_DIR . $row['file_path']);
    $dir = dirname($row['file_path']);
    while(@rmdir(FILE_DIR . $dir) && ($dir = dirname($dir)) != '.');
  }
  mq("DELETE FROM files WHERE file_id = {$row['file_id']}");
}

function linkImgFile($id, $width=NULL, $height=NULL, $extra = '') 
{
	$result = mysql_unbuffered_query("SELECT file_name FROM files WHERE file_id = $id");
    if(!isset($result) || $result == "" || $result == 0) 
    {
        $row = array();
        $row['file_name'] = "";
    } 
    else
    {
        $row = mysql_fetch_assoc($result);
    } 
    if(!is_null($width)) $width = "WIDTH='$width'";
    if(!is_null($height)) $height = "HEIGHT='$height'";
    return "<IMG SRC='/file_view.php?id=$id' $width $height $extra ALT='" . es($row['file_name']) . "'>";
}

function maxFileSize() {
  return min(min(maxUploadSize(), maxDBquery()/2), units2bytes(ini_get('memory_limit'))/4-units2bytes('4M')); //not exactly accurate - you need to account for the size of the rest of the query, but unless the file is all nulls, it is good enough due to the /2
}
?>
