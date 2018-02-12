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

if (($tasks = fopen("school_types.csv", "r")) !== false) {

	while (($data = fgetcsv($tasks, 1000, ",")) !== false) {
	
		$id = $data[0];
		$type = $data[1];
		$type2 = trim($data[2]) == '' ? null : $data[2];
		
		if ($type2) {
			$sql = "update users set school_type_id = $type where school_id = $id and gender = 'm'";
			execute($sql);
			$sql = "update users set school_type_id = $type2 where school_id = $id and gender = 'f'";
			execute($sql);
		}
		else {
			$sql = "update users set school_type_id = $type where school_id = $id";
			execute($sql);
		}		
	}	
	fclose($tasks);
	var_dump($not_added);
}

echo "Edited:" . $added . "<br />";

function execute($sql) {
	global $added;
//	echo $added . ": " . $sql . "<br />";

	$query = mysql_query($sql) or die($sql . "<br />" . mysql_error());
	if ($query) $added++;
	else $not_added[] = $id;

}
?>
	</BODY>
</HTML>
