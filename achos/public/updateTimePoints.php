<?
require_once 'db.php';

$updated = 0;
$marks = array();
$sql = "select * from date_tasks_marks where done_qty >= mark_quantity and mark_quantity > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$done = $row['done_qty'];
	$needs = $row['mark_quantity'];
	$multiply = (int)($done / $needs);
	if ($multiply) {
		$id = $row['date_task_id'];
		$user_id = $row['user_id'];
		$points = $row['mark_points'];
		$points *= $multiply;
		$sql = "update date_tasks_marks set mark_points = " . $points . " where date_task_id = $id and user_id = $user_id";
		if (mysql_query($sql)) {
			$updated++;
		}
	}
}

echo "<pre>";
//print_r($marks);
echo "</pre>";

echo $updated;
?>
