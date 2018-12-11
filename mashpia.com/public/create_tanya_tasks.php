<?
require_once 'db.php';

$skip = array('וָאֵרָא', 'בְּשַׁלַּח', 'כִּי תִשָּׂא', 'אַחֲרֵי מוֹת', 'בְּמִדְבַּר', 'חֻקַּת');
$startDate = 2456621;
$endDate = 2456900;

$subject_id = 27;
$track_id = 1;
$levels = array(6,7,8,9,10,11,12,13,14);
$school_types = array(2,3,12,13);

$parshos = array();
$sql = "select * from parshos where start >= $startDate and end <= $endDate";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parshos[] = $row;
}

$queries = array();
mysql_query("SET AUTOCOMMIT=0");
mysql_query("BEGIN");
$commit = true;
$errors = array();
foreach ($parshos as $parsha) {
	if (in_array($parsha['name'], $skip)) continue;
	foreach ($levels as $level) {
		foreach ($school_types as $type) {
			$sql = "insert into date_tasks_missions 
					set mission_name = 'תניא בעל פה', 
					mission_description = 'תניא בעל פה', 
					mission_value = 1.0, 
					start_date = $parsha[start], 
					end_date = $parsha[end], 
					default_on = 1, 
					subject_id = $subject_id, 
					track_id = $track_id, 
					level = $level 
					school_type_id = $type"; 
			//echo $sql . "<br />";
			//$queries[] = $sql;
			if (mysql_query($sql)) {
				$id = mysql_insert_id();
				//$id = 111;
				$sql2 = "insert into date_tasks 
						set date_tasks_mission_id = $id, 
						ord = 1, 
						name = 'Enter the amount of lines of תניא בעל פה that you know by heart.',
						mandatory_qty = 1, 
						optional_qty = 0, 
						label_id = 33, 
						quantity = 150, 
						points = 1, 
						daily_task = 0, 
						default_on = 1, 
						cat = 'תניא בעל פה', 
						needed = 0, 
						focus_task = 0";
				//$queries[] = $sql2;
				if (!mysql_query($sql2)) {
					$commit = false;
				}			
			} else {
				$errors[] = mysql_error() . "<br />" . $sql;
				$commit = false;
			}		
		}
	}
}
if ($commit) {
	mysql_query("COMMIT");
	echo "Updated";
} else {
	mysql_query("ROLLBACK");
	echo "Not Updated";
}
mysql_query("SET AUTOCOMMIT=1");
echo "<pre>";
print_r($errors);
echo "</pre>";
?>