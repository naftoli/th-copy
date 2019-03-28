<?
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
require 'db.php';

$offset = 500;
if (isset($_GET['limit'])) {
	$limit = $_GET['limit'] . ',' . ($_GET['limit'] + $offset);
} else {
	$limit = "0," . $offset;
}

$files = array();
$sql = "select f.file_id, f.file_name, t.thumb 
		from files f 
		join users u on u.user_photo_id = f.file_id 
		join thumbs t using (file_id) 
		limit " . $limit;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$files[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			th, td {
				padding: 5px;
				font-size: 12px;
				font-family: 'Arial';
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Image</th>
				<th>Thumbnail</th>
				<th>File ID</th>
				<th>Name</th>
			</tr>
			<?
			foreach ($files as $index => $file) {
				$src = "file_view.php?id=" . $file['file_id'];
				echo "<tr><td><img width='200' src='" . $src . "' /></td><td><img src='thumb/" . $file['thumb'] . "' /></td><td>" . 
					$file['file_id'] . "</td><td>" . $file['file_name'] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>