<?
require_once 'db.php';

$tasks = array();
$sql = "select dtm.* from date_tasks_missions dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		where dt.name like 'I learned (new) תניא בעל פה%'";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$type = $row['school_type_id'];
	$year = $row['level'];
	$date = $row['start_date'] . '-' . $row['end_date'];
	$tasks[$date][$type][] = $year;
}

$start = 2456565;
$types = array(2,3);
$levels = array(6,7,8,9,10,11,12,13,14);

$missing = array();
$months = array_keys($tasks);
do {
	$d = $start . '-' . ($start + 6);
	if (!in_array($d,$months)) {
		$missing['date'][] = $d;
	}
} while (($start += 7) < 2456900);

$tasks2 = array();
$sql = "select dtm.* from date_tasks_missions dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		where dt.name = 'I did חזרה on the  תניא I know בעל פה (before davening) for at least 5 minutes.'";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$type = $row['school_type_id'];
	$year = $row['level'];
	$date = $row['start_date'] . '-' . $row['end_date'];
	$tasks2[$date][$type][] = $year;
}

$tasks3 = array();
$sql = "select dtm.* from date_tasks_missions dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		where dt.name = 'Enter the amount of lines of תניא בעל פה that you know by heart.'";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$type = $row['school_type_id'];
	$year = $row['level'];
	$date = $row['start_date'] . '-' . $row['end_date'];
	$tasks2[$date][$type][] = $year;
}

echo "<pre>";
print_r($tasks);
echo "--------------------------------------<br />";
print_r($tasks2);
echo "--------------------------------------<br />";
print_r($tasks3);
//print_r($missing);
echo "</pre>";
?>