<?
require_once 'db.php';

$missions = array();
$sql = "select date_tasks_mission_id, level, school_type_id, name from date_tasks_missions dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		where dtm.start_date >= 2456913 
		and dtm.end_date <= 2456919 
		and subject_id = 40 
		and dtm.mission_name = \"חי אלול\" 
		order by level, school_type_id, name, date_tasks_mission_id";

$result = mysql_query($sql);
$i = 1;
while ($row = mysql_fetch_assoc($result)) {
	if ($i++ != 1) {
		$missions[$row['date_tasks_mission_id']] = 1;
	}
	if ($i == 4) {
		$i = 1;
	}
}

foreach ($missions as $id => $v) {
	echo $id . ",";
}
