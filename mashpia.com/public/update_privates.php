<?php
require_once("db.php");

$sql = "
	SELECT u.user_id  
	FROM users AS u
	LEFT JOIN rank_marks AS rm ON ( u.user_id = rm.user_id ) 
	WHERE rm.rank_ord IS NULL";
$result = mysql_query($sql);

$inserted = 0;
while ($row = mysql_fetch_row($result)) {
	$sql2 = "insert into rank_marks (rank_ord, user_id, date_promoted) values(1, $row[0], " . unixtojd() . ")";
	//echo $sql2 . "<br />";
	if (mysql_query($sql2))
		$inserted++;
}
echo "Inserted: " . $inserted;
?>