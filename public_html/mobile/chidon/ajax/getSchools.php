<?
require '../../../db.php';
$schools = array();
$sql = "select * from chidon_schools where year = 5776 order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[] = $row;
}
echo json_encode($schools);
?>