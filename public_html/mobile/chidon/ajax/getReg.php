<?
require '../../../db.php';

$children = array();
$school = mysql_real_escape_string($_POST['school']);

$sql = "select * from chidon_reg where chidon_schools_id = " . $school . " order by last_name, name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	
	$mark1 = $row['mark1'];
	$mark2 = $row['mark2'];
	$mark3 = $row['mark3'];
	$bonus = $row['bonus'];
	
	$avg = ($mark1 + $mark2 + $bonus) / 2;
	if ($mark3) {
		$avg = round(($mark1 + $mark2 + $mark3 + $bonus) / 3);
	}
	
	if ($avg >= 70) {
		$children[] = $row;
	}
}

echo json_encode($children);
?>