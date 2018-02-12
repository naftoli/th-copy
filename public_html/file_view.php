<?php
$no_login = true; 
require('header.php'); 

$row = mysql_fetch_assoc(mqu('SELECT file_name, file_content_type, UNIX_TIMESTAMP(file_last_mod) file_last_mod, ' . ($_SERVER['REQUEST_METHOD'] !== 'HEAD' ? 'file_data, ' : '') . 'LENGTH(file_data) file_length FROM files WHERE file_id = 0' . preg_replace('/\D/', '', gr('id'))));

if(!$row) {
	header("HTTP/1.0 404 Not Found");
	echo "File not found.";
} 
else {
	$disposition = gr('m') == 'd' ? 'attachment' : 'inline';
	header("Content-type: {$row['file_content_type']}");
	header("Content-Disposition: $disposition; filename=\"{$row['file_name']}\"");
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $row['file_last_mod']) . ' GMT');
	header("Content-Length: {$row['file_length']}");
	
	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] === gmdate('D, d M Y H:i:s', $row['file_last_mod']) . ' GMT') {
		header("HTTP/1.0 304 not modified");
	} 
	elseif ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
		print $row['file_data'];
        //imagejpeg($im);
		//$row['file_data']);
  }
}
?>
