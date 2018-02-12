<?
require 'db.php';

$users = array();
$sql = "SELECT user_id, user_registered
		FROM users u
		LEFT JOIN rank_marks rm
		USING ( user_id ) 
		WHERE rank_ord IS NULL 
		AND u.user_registered >0";
		
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[$row['user_id']] = $row['user_registered'];
}

foreach ($users as $user => $date) {
	$arrDate = explode('-', $date);
	$dd = substr($arrDate[2], 0, 2);
	$jd = gregoriantojd($arrDate[1], $dd, $arrDate[0]);
	$sql = "insert into rank_marks 
			set user_id = " . $user . ", 
			rank_ord = 1, 
			date_promoted = " . $jd;
	mysql_query($sql);
}
?>
