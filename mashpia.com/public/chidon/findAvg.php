<?
require '../db.php';

$below70 = array();
$sql = "select * from chidon_reg where chidon_schools_id in (
		select chidon_schools_id from chidon_schools where year = 5776)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$mark1 = intval($row['mark1']) + intval($row['bonus']);
	$mark2 = intval($row['mark2']);
	$avg = ($mark1 + $mark2) / 2;
	if ($avg < 70) {
		$below70[] = $row['chidon_reg_id'];
	}
}

echo implode(',', $below70);
?>