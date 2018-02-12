<?
require '../../db.php';

$sql = array();
$new_students = fopen("teams.csv", "r");
$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {
	$data = split(",", $strLine);
	$id = $data[0];
	$team = $data[1];
	$sql[] = "update chidon_reg 
			set team = " . $team . "  
			where chidon_reg_id = " . $id;
}

foreach ($sql as $qry) {
	mysql_query($qry) or die($qry . "<br />" . mysql_error());
}
?>