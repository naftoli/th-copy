<? 
require 'db.php';
$row = mysql_fetch_assoc(
mqu('SELECT file_content_type, file_data 
	FROM files WHERE file_id = ' . $_GET['id']));

header("Content-type: {$row['file_content_type']}");
print $row['file_data'];
?>
