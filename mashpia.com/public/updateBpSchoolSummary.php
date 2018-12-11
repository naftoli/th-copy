<?
require 'db.php';
$learned = array();
$sql = "select * from lines_learned where campaign_id in (5,6)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$learned[$row['campaign_id']][$row['school_id']] = $row['lines_learned'];
}

foreach ($learned as $campaign => $info) {
	foreach ($info as $school => $lines) {
		$sql = "select num_lines from bp_school_summary where campaign_id = $campaign and school_id = $school";
		$result = mysql_query($sql);
		$num = 0;
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$num = $row['num_lines'];
		}
		if ($lines > $num) {
			$sql = "insert into bp_school_summary 
					set num_lines = $lines, 
					school_id = $school, 
					campaign_id = $campaign  
					on duplicate key update num_lines = $lines";
			echo $sql . "<br />";
			mysql_query($sql);
		}
	}
}
?>