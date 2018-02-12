<?
chdir('../../../');
require 'db.php';
$file = $_FILES['image'];

if (is_uploaded_file($file['tmp_name'])) {
	$count = 0;
	do {
		if ($count++ > 100000) {
			echo 'Could not get ID.';
			exit;
		}
		$id = mysql_result(mysql_query('SELECT FLOOR(RAND() * 4294967295)'),0);
	} while (mysql_result(mysql_query("SELECT COUNT(*) FROM files WHERE file_id = $id"),0) != 0);

	mq("INSERT INTO files (file_id, file_name, file_content_type, file_data) VALUES ($id, " . ms($file['name']) . ', ' . ms(mime_content_type($file['tmp_name'])) . ', ' . ms(file_get_contents($file['tmp_name'])) . ')');
	echo $id;
} else {
	echo 0;
}
?>