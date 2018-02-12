<?
require '../../db.php';

$sql = array();
$new_students = fopen("boysInfo.csv", "r");
$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {
	$data = split(",", $strLine);	
	$sql[] = "update chidon_reg 
			set bus_number = '" . $data[1] . "', 
			walking_group = '" . $data[2] . "', 
			meeting_point = '" . $data[3] . "'   
			where chidon_reg_id = " . $data[0];
}

$updated = 0;
foreach ($sql as $qry) {
	if (mysql_query($qry)) {
		$updated++;
	} else {
		echo mysql_error();
	}
}
echo "Updated: " . $updated;
?>