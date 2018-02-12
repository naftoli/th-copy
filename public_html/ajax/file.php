<?
require '../db.php';
$action = $_POST['action'];
$file = $_POST['file'];
$folder = $_POST['folder'];

if ($action == 'delete') {
	if ($folder == 'letters') {
		unlink("../$folder/$file");
		//delete from db
		$sql = "delete from communicate_files where file = '$file'";
		mysql_query($sql);
	}
}
?>