<?php
$file = htmlspecialchars($_GET['the_file']);
$parse_url = parse_url($file) ;
$path_info = pathinfo($parse_url['path']) ;
//print_r ($path_info); 
$file_basename = $path_info['basename'] ;
$file_extension = $path_info['extension'] ;
if ($file_extension=='mp3' || $file_extension=='ogg') {
		// Maximum size of chunks (in bytes).
		$maxRead = 1 * 1024 * 1024; // 1MB
		
		// Give a nice name to your download.
		$fileName = $file_basename;
		
		// Open a file in read mode.
		$fh = fopen($file, 'r');
		
		// These headers will force download on browser,
		// and set the custom file name for the download, respectively.
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		
		// Run this until we have read the whole file.
		// feof (eof means "end of file") returns `true` when the handler
		// has reached the end of file.
		while (!feof($fh)) {
			// Read and output the next chunk.
			echo fread($fh, $maxRead);
		
			// Flush the output buffer to free memory.
			ob_flush();
		}
		
		// Exit to make sure not to output anything else.
		exit;
} else {
	echo "<script>window.close();</script>";
	//die();
}

/*
$filex=htmlspecialchars($_GET['the_file']);
//$filex='audio/wm_follow_me.mp3';
$ext = pathinfo($filex, PATHINFO_EXTENSION);
if ($ext=='mp3' || $ext=='ogg') {
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($filex).'"');
	header('Content-Length: ' . filesize($filex));
	readfile($filex);
} else {
	echo "<script>window.close();</script>";
	//die();
}*/
?>