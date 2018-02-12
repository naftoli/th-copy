<?
require '../../db.php';

$schoolID = mysql_real_escape_string($_POST['school']);
$names = $_POST['names'];
$numbers = $_POST['numbers'];
$num = count($names);

$sql = "update chidon_schools set";
for ($i = 0; $i < $num; $i++) {
	if ($i == 0) {
		$sql .= " chaperone_name = '" . mysql_real_escape_string($names[$i]) . "', 
			chaperone_phone = '" . mysql_real_escape_string($numbers[$i]) . "',";
	} else {
		$j = $i+1;
		$sql .= " chaperone_name{$j} = '" . mysql_real_escape_string($names[$i]) . "', 
			chaperone_phone{$j} = '" . mysql_real_escape_string($numbers[$i]) . "',";
	}
}
$sql = substr($sql, 0, strlen($sql)-1);
$sql .= " where chidon_schools_id = " . $schoolID;
//echo $sql;
if (@mysql_query($sql)) {
	echo 1;
} else {
	echo 0;
}
?>