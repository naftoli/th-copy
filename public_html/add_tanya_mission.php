<?
require_once 'db.php';

$subject = 27;
$track = 1;

$users = array();
$sql = "select * from users where user_id > 16230 and school_id = 7";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

foreach ($users as $user) {
	$year = "select class_grade from classes where class_id = " . $user['class_id'];
	//echo $year . "<br />";
	$year_res = mysql_query($year);
	$row = mysql_fetch_row($year_res);
	$y = $row[0];
	switch ($y) {
		case 'Pre-school 1':
	    case 'Pre1a':
	        $level = 6;
	        break;
	    case '1':
	        $level = 7;
	        break;
	    case '2':
	        $level = 8;
	        break;
	    case '3':
	        $level = 9;
	        break;
	    case '4':
	        $level = 10;
	        break;
	    case '5':
	        $level = 11;
	        break;
	    case '6':
	        $level = 12;
	        break;
	    case '7':
	        $level = 13;
	        break;
	    case '8':
	        $level = 14;
	        break;
	    default:
	        $level = 14;
	        break;
	}
	$insert = "insert into user_tracks values($user[user_id], $subject, $track, $level, 1)";
	//echo $insert . "<br />";
	mysql_query($insert);
}

