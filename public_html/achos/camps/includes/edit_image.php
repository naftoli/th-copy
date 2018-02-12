<?php
include_once ("db.php");
include_once ("file_save.php");
	
$user_id = $_POST["user_id"];
$user_photo_id = addFile($_FILES['photo'], $user_photo_id);
$sql = "UPDATE users SET user_photo_id=" . $user_photo_id . " WHERE user_id=" . $user_id;
$query = mysql_query($sql);
if ($query) {
	echo "<img src='includes/file_view.php?id=" . $user_photo_id . "' height='150' />";
}
else {
	echo "0";
}
?>