<?
require '../../../db.php';
$subject = mysql_real_escape_string( $_POST['subject'] );
$medal = mysql_real_escape_string( $_POST['medal'] );

$sql = "select profile_photo_id from medals_subjects where subject_id = " . $subject . " and medal_ord = " . $medal;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row['profile_photo_id'];
?>