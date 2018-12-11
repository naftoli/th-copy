<?
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
    if(!isset($result)|| $result == "" || $result == 0) 
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
