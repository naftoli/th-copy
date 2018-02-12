<? 
include("../db.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
<?
$added = 0;
$not_added = array();

if (($tasks = fopen("tasks.csv", "r")) !== false) {

	while (($data = fgetcsv($tasks, 1000, ",")) !== false) {
	
		$id = $data[0];
		$points = mysql_real_escape_string($data[1]);
		
		$sql = "update date_tasks
				set points = '$points'
				where date_task_id = $id";
		
		//echo ++$added . ": " . $sql . "<br />";
		
		$query = mysql_query($sql) or die($sql . "<br />" . mysql_error());
		
		if ($query) 
			$added++;
		else {
			$not_added[] = $id;
			echo "*** ERROR->" . $sql . "<br />";
		}
		
	}
	
	fclose($tasks);

		var_dump($not_added);
}

echo "Edited:" . $added . "<br />";

?>
	</BODY>
</HTML>
