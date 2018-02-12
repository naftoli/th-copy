<? 
	set_time_limit(86400); // 24 hours
    $no_login = true;
	require('header.php'); 
	$sql = 'SELECT school_logo_id FROM schools WHERE school_id = '.$_GET['school_id'];
	$rs = mysql_query($sql);
	while($photo_id = mysql_fetch_array($rs))
	{
		if($photo_id["school_logo_id"] >0)
		{
			$row = mysql_fetch_assoc(mqu('SELECT file_name, file_content_type, UNIX_TIMESTAMP(file_last_mod) file_last_mod, ' . ($_SERVER['REQUEST_METHOD'] !== 'HEAD' ? 'file_data, ' : '') . 'LENGTH(file_data) file_length FROM files WHERE file_id = '.$photo_id['school_logo_id']));
			
			$disposition = gr('m') == 'd' ? 'attachment' : 'inline';
			header("Content-type: {$row['file_content_type']}");
			header("Content-Disposition: $disposition; filename=\"{$row['file_name']}\"");
			header('Last-Modified: ' . gmdate('D, d M Y H:i:S', $row['file_last_mod']) . ' GMT');
			header("Content-Length: {$row['file_length']}");

			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] === gmdate('D, d M Y H:i:S', $row['file_last_mod']) . ' GMT') 
			{
				header("HTTP/1.0 304 not modified");
			} 
			elseif ($_SERVER['REQUEST_METHOD'] !== 'HEAD') 
			{
				print $row['file_data'];
			}		
		}
		else{
			error404();
		}
	}
	function error404()
	{
		header("HTTP/1.0 404 Not Found");
		echo "File not found.";
	}
?>