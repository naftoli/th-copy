<?
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );

$sql = "select thumb from thumbs t 
		join files f using (file_id) 
		join users u on u.user_photo_id = f.file_id 
		where user_id = " . $user_id;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	echo $row['thumb'];
} else {
	echo 0;
}
?>