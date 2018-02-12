<?
require '../../../db.php';

$schools = array();
/*
$sql = "select distinct school_id, school_name from schools 
		join users u using (school_id) 
		where (school_era is null or school_era = 5776) 
		and chayolei = 1 
		order by school_name";
*/
$sql = "select school_id, school_name from schools 
		where chayolei = 1
		order by school_name";
$result = mysql_query( $sql );
$i = 1;
while ($row = mysql_fetch_assoc($result)) {
	if ($row['school_id'] == 61) $schools[0][$row['school_id']] = $row['school_name'];
	else $schools[$i++][$row['school_id']] = $row['school_name'];
}
echo json_encode( $schools );
?>