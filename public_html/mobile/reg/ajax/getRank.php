<?php
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user'] );

$info = array();
$sql = "select max(rank_ord) as rank from rank_marks where user_id = " . $user_id;
$result = mysql_query( $sql );
if (mysql_num_rows($result)) {
	$row = mysql_fetch_assoc($result);
	$info['rank'] = $row['rank'];
} else {
	$info['rank'] = 1;
}
$rank = $info['rank'];
/*
$sql = "select medals_required from ranks where rank_ord = " . ++$rank;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$info['medalsNeeded'] = $row['medals_required'];
*/
$medals = array();
$sql = "select medal_ord, subject_id, profile_photo_id, date_awarded 
		from medal_marks 
		join medals_subjects using (subject_id, medal_ord) 
		where user_id = " . $user_id . "
		order by date_awarded, medal_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$jdate = jdtojewish($row['date_awarded'], true, CAL_JEWISH_ADD_GERESHAYIM);
	$awarded = $str1 = iconv ('WINDOWS-1255', 'UTF-8', $jdate); // convert to utf-8
	//$pos = strrpos($awarded, ' ');
	//$awarded = substr($awarded, 0, $pos);
	$medals[] = array(
		'subject'	=>	$row['subject_id'],
		'medal'		=>	$row['medal_ord'], 
		'photo'		=>	$row['profile_photo_id'], 
		'awarded'	=>	$awarded
	);
}
$info['totalMedals'] = mysql_num_rows($result);
$info['medals'] = $medals;

$medalsInfo = array();
$sql = "select rank_ord, medals_required from ranks order by rank_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$medalsInfo[$row['rank_ord']] = $row['medals_required'];
}
$info['medalsInfo'] = $medalsInfo;

echo json_encode( $info );
?>