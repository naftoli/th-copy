<? 
include("db.php");

$labels_file = fopen("labels.csv", "r");
$row_num = 0;
while ($data = fgetcsv($labels_file, 1000, ",")) {
	$row_num++;
	
	if ($row_num > 1) {		
		$sql = "SELECT label_id FROM labels WHERE label_name='" . $data[5] . "'";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$label_id = $row["label_id"];
		if ($label_id > 0) {
			//$update_one = "UPDATE date_tasks SET label_id=" . $label_id . " WHERE date_task_id=" . $data[0];
			//$query_one = mysql_query($update_one);
			//$update_two = "UPDATE labels SET sequence_number=" . $data[7] . " WHERE label_id=" . $label_id;
			//$query_one = mysql_query($update_two);
			$update = "UPDATE date_tasks SET sequence_number=" . $data[7] . " WHERE date_task_id=" . $data[0];
			$query = mysql_query($update);
		}
	}
	
}
fclose($labels_file);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
	</HEAD>
	
	<BODY>
		<? echo "# OF ROWS:" . $row_num . "<br />"; ?>
	</BODY>
</HTML>
