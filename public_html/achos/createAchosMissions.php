<?
require 'db.php';

//create missions
$sql = "select mission_number from date_tasks_missions order by mission_number desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$missionNum = ++$row['mission_number'];
$missionIDs = array();

require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();
//$levels = array(2,3,4);
$subjects = array(2,3,4);

foreach ($parshos as $parsha) {
	// if ($parsha['start'] < 2457685) continue;
	foreach ($subjects as $subject) {
		$sql = "insert into date_tasks_missions 
				set school_type_id = 1, 
				subject_id = $subject, 
				level = 1, 
				track_id = 1, 
				mission_name = \"" . $parsha['name'] . "\", 
				mission_number = " . $missionNum++  . ", 
				mission_value = 1.0, 
				start_date = " . $parsha['start'] . ", 
				end_date = " . $parsha['end'];
		//echo $sql . "<br />";
		mysql_query($sql);
		$missionIDs[] = mysql_insert_id();
	}
}
echo "<pre>"; print_r($missionIDs); echo "</pre>";
?>