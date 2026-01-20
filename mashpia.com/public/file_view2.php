<? 
require 'db.php';
$row = mysql_fetch_assoc(
mqu('SELECT file_content_type, file_data 
	FROM files WHERE file_id = ' . $_GET['id']));

// Allow CORS requests for images (prevents tainted canvas errors in html2pdf/html2canvas)
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) );
header("Content-type: {$row['file_content_type']}");
print $row['file_data'];
?>
