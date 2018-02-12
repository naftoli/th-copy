<?
require '../../../db.php';
$user = mysql_real_escape_string( $_POST['user'] );
$level = mysql_real_escape_string( $_POST['level'] );
$age = mysql_real_escape_string( $_POST['age'] );

$sql = "update user_tracks set track_id = " . $level . " where user_id = " . $user . " and subject_id = 1";
if (mysql_query($sql)) {
	$qry = "select qty, minutes from tehillim_ladders where ladder = " . $level . " and age = " . $age;
	$res = mysql_query($qry);
	$row = mysql_fetch_assoc($res);
	echo json_encode( 
	array(
		'qty' => $row['qty'], 
		'min' => $row['minutes']
		) 
	);
} else {
	echo 0;
}
?>