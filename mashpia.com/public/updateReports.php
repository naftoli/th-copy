<?
require 'db.php';

$parshos = array();
$sql = "select * from parshos where year = 5776";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parshos[] = $row;
}

foreach ($parshos as $parsha) {
	$sql = "insert into reports 
			set report_name = '" . $parsha['name'] . "', 
			report_type = 'mission_cover_sheet', 
			start_date = " . $parsha['start'] . ", 
			end_date = " . $parsha['end'] . ", 
			visibility = 'all'";
	mysql_query($sql);
}
?>