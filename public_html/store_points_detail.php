<?
require('db.php');

function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		
		return cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
}

$school_id = $_GET['school_id'];
$sql = "select * from users where user_registered > 0 and school_id = " . $school_id;
$sql = "
	select u.user_id, u.first, u.last, c.class_grade, c.class_sub 
	from users as u 
	join classes as c using (class_id) 
	where u.user_registered > 0 
	and u.school_id = $school_id  
	order by c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);

while ($user_row = mysql_fetch_assoc($result)) {

	$sql2 = "SELECT IFNULL(SUM(mark_points), 0) mark_points 
		FROM ( 
		SELECT SUM(mark_points) mark_points 
		FROM date_tasks_marks 
		JOIN ord ON (mark_inactive = 0 AND ord.num = 1) WHERE user_id = {$user_row['user_id']} AND mark_date >= " . dateThisYear(13, 18) . " 
		UNION ALL SELECT SUM(award_points) mark_points 
		FROM points WHERE user_id = " . $user_row['user_id'];
		$sql2 .= " AND award_date >= 2455621";
		$sql2 .= ") marks";
		
	$result2 = mysql_query($sql2);
	$row = mysql_fetch_assoc($result2);
	echo $user_row['class_grade'] . "-" . $user_row['class_sub'] . ": " . $user_row['first'] . " " . $user_row['last'] . " = " . ceil($row['mark_points']) . "<br />";
}
?>
