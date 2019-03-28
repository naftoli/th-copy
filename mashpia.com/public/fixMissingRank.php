<?
require 'db.php';

$updated = 0;
$sql = "select user_id, user_start_date from users 
		left join rank_marks rm using (user_id) 
		where rm.user_id is null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$insert = "insert into rank_marks 
				set rank_ord = 1, 
				user_id = " . $row['user_id'] . ", 
				date_promoted = " . $row['user_start_date'];
	if (mysql_query($insert)) {
		$updated++;
	}
}
echo "Updated: " . $updated;
?>