<?
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('max_execution_time', 600);
require 'db.php';

if (isset($_GET['s']) && isset($_GET['e'])) {
	$start = $_GET['s'];
	$end = $_GET['e'];
} else {
	$start = 14165;
	$end = 14175;
}
$files = array();
$sql = "select f.file_id, f.file_content_type from files f 
		join users u on f.file_id = u.user_photo_id 
		left join thumbs t using (file_id)  
		where t.file_id is null
		#limit $start, $end";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$files[++$start] = $row;
}

foreach ($files as $index => $file) {
	$type = $file['file_content_type'];
	$pos = strrpos($type, '/');
	$ext = substr($type, $pos+1);
	$fname = 'thumb' . ($index + 1) . '.' . $ext;
	$handle = fopen('http://mashpia.com/file_view.php?id=' . $file['file_id'], 'r');
	if ($handle) {
		try {
			$image = new Imagick();
			$image->readImageFile( $handle );
			$image->thumbnailImage( 100, 0 );
			$image->writeImage( 'thumbs/' . $fname );
			$image->destroy();
			$sql = "insert into thumbs set thumb = '" . $fname . "', file_id = " . $file['file_id'];
			mysql_query($sql);
		} catch (ImagickException $e) {
			echo $e->getMessage();
		}
	}
}
echo "Numbers $index - $end done.";
if ($start < 14001) {
	header("Location: createThumbs.php?s=" . $end . "&e=" . ($end + 250));
	exit;
}
?>