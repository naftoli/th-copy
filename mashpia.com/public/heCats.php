<?
require 'db.php';

$cats = array();
$sql = "select cat from date_tasks 
		join date_tasks_missions using (date_tasks_mission_id) 
		where start_date > 2456920 
		and created_by_school is null 
		and personal = 0 
		and school_type_id in (2,3) 
		group by cat";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$cats[] = $row['cat'];
}
echo "<pre>"; print_r($cats); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<?
		$newCats = array();
		foreach ($cats as $index => $cat) {
			$pos = strpos($cat, '.');
			$order = substr($cat, ($pos + 1));
			$str = substr($cat, 0, $pos);
			$newStr = $order . ' ' . $str;
			$sql = "update date_tasks 
					set cat = \"" . mysql_real_escape_string($newStr) . "\" 
					where cat = \"" . mysql_real_escape_string($cat) . "\"";
			//echo $sql . "<br />";
			//mysql_query($sql) or die(mysql_error());
			//echo $newStr . "<br />";
			//$newCats[$order] = $str;
		}
		//ksort($newCats);
		//echo "<pre>"; print_r($newCats); echo "</pre>";
		?>
	</body>
</html>