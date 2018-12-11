<?
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );

$sql = "select u.mobile_pic, u.user_photo_id, t.thumb 
		from users u 
		left join thumbs t on t.file_id = u.user_photo_id 
		where u.user_id = " . $user_id;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	echo json_encode(array(
		'mobile_pic' => $row['mobile_pic'], 
		'photo' => $row['user_photo_id'], 
		'thumb' => $row['thumb']
	));
} else {
	echo 0;
}
?>