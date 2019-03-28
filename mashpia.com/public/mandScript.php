<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<?
require 'db.php';

$tasks = array();
$sql = "select subject_id, lang_id, cat from date_tasks dt 
		join date_tasks_missions dtm using (date_tasks_mission_id) 
		where mandatory_qty = 1 
		and start_date >= 2457277 
		group by cat";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$tasks[] = $row;
}

$sql = array();
foreach ($tasks as $task) {
	$sql[] = "insert into mandatory_cats 
			set subject_id = " . $task['subject_id'] . ", 
			lang_id = " . $task['lang_id'] . ", 
			cat = \"" . $task['cat'] . "\", 
			year = 5776";	
}

mysql_query('set names utf8');
foreach ($sql as $str) {
	//echo $str . "<br />";
	mysql_query($str);
}
?>
	</body>
</html>