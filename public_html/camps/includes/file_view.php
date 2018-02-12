<? 
$no_login = true; 

include ("db.php");
$sql = 'SELECT file_name, file_content_type, UNIX_TIMESTAMP(file_last_mod) file_last_mod, ' . ($_SERVER['REQUEST_METHOD'] !== 'HEAD' ? 'file_data, ' : '') . 'LENGTH(file_data) file_length FROM files WHERE file_id=' . $_GET['id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);

if(!$row) {
	header("HTTP/1.0 404 Not Found");
	echo "File not found.";
} 
else {
	$image = $row['file_data'];
	header("Content-type: image/gif"); 
	print $image;
}
?>
